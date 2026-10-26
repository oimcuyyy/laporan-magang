<?php

namespace App\Http\Middleware;

use App\Models\WafLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebProtectiveFirewall
{
    /**
     * Pola deteksi ancaman WAF (Regex Patterns)
     */
    protected array $rules = [
        'SQL_INJECTION' => [
            'severity' => 'CRITICAL',
            'pattern' => '/(?:\bunion\b\s+(?:all\s+)?\bselect\b|\bselect\b.+?\bfrom\b\s+(?:information_schema|sys|users|sqlite_master)|--\s*$|;--|\bor\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?|\band\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?|\bsleep\s*\(\s*\d+\s*\)|\bbenchmark\s*\(\s*\d+|\bwaitfor\s+delay\b)/i',
        ],
        'CROSS_SITE_SCRIPTING' => [
            'severity' => 'HIGH',
            'pattern' => '/(?:<script\b[^>]*>(.*?)<\/script>|javascript:[^\s"\'`]+|\bonerror\s*=|\bonload\s*=|\bonclick\s*=|\bdocument\.cookie\b|\bwindow\.location\b|\balert\s*\(|\bprompt\s*\(|<iframe\b)/i',
        ],
        'PATH_TRAVERSAL' => [
            'severity' => 'CRITICAL',
            'pattern' => '/(?:\.\.\/|\.\.\\|%2e%2e%2f|%2e%2e\/|\/etc\/passwd|\/etc\/shadow|\/windows\/system32|\bcmd\.exe\b|\bpowershell\.exe\b)/i',
        ],
        'REMOTE_CODE_EXECUTION' => [
            'severity' => 'CRITICAL',
            'pattern' => '/(?:\b(?:system|passthru|shell_exec|exec|popen|proc_open)\s*\(|`[^`]+`|\bphpinfo\s*\(|\beval\s*\(|\bbase64_decode\s*\()/i',
        ],
    ];

    /**
     * Daftar bot / scanner rentan yang langsung diblokir
     */
    protected array $badBots = [
        'sqlmap', 'nikto', 'wpscan', 'acunetix', 'masscan', 'dirbuster', 'nmap', 'zgrab', 'gobuster',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Periksa User-Agent
        $userAgent = (string) $request->userAgent();
        foreach ($this->badBots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return $this->blockRequest($request, 'BAD_BOT', 'HIGH', "Scanner Bot Terdeteksi: {$bot}", $userAgent);
            }
        }

        // 2. Kumpulkan semua input untuk inspeksi mendalam (Query, Body, Path)
        $inputs = array_merge(
            $request->query(),
            $request->except(['_token', 'photo']), // Kecualikan CSRF token dan file biner foto
            ['request_path' => $request->path()]
        );

        $payloadString = $this->flattenPayload($inputs);

        // 3. Uji kecocokan pola ancaman WAF
        foreach ($this->rules as $threatType => $ruleConfig) {
            if (preg_match($ruleConfig['pattern'], $payloadString, $matches)) {
                return $this->blockRequest(
                    $request,
                    $threatType,
                    $ruleConfig['severity'],
                    $matches[0],
                    mb_substr($payloadString, 0, 500)
                );
            }
        }

        // 4. Lanjutkan request jika aman
        $response = $next($request);

        // 5. Hardening Security Headers (Respons)
        $response->headers->set('X-WAF-Protection', 'Active-Shield-v1.0; SMK Negeri 20');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content-Security-Policy (CSP) untuk pertahanan terhadap serangan XSS
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self';";
        $response->headers->set('Content-Security-Policy', $csp);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Ratakan struktur array/nested input menjadi satu string untuk inspeksi regex
     */
    protected function flattenPayload(array $data): string
    {
        $result = '';
        foreach ($data as $key => $value) {
            $result .= ' '.$key.'=';
            if (is_array($value)) {
                $result .= $this->flattenPayload($value);
            } else {
                $result .= ' '.(string) $value;
            }
        }

        return $result;
    }

    /**
     * Blokir request dan catat ke log keamanan
     */
    protected function blockRequest(Request $request, string $threatType, string $severity, string $matchedPattern, ?string $payload): Response
    {
        // Catat ke database WAF logs secara aman dengan proteksi DoS flooding
        try {
            $ip = $request->ip() ?: '127.0.0.1';
            $cacheKey = 'waf_log_count_'.md5($ip);
            $logCount = (int) cache()->get($cacheKey, 0);

            if ($logCount < 15) {
                cache()->put($cacheKey, $logCount + 1, now()->addMinute());
                WafLog::create([
                    'ip_address' => $ip,
                    'method' => $request->method(),
                    'request_url' => $request->fullUrl(),
                    'threat_type' => $threatType,
                    'severity' => $severity,
                    'matched_pattern' => $matchedPattern,
                    'blocked_payload' => $payload,
                    'user_agent' => $request->userAgent(),
                ]);
            } else {
                Log::warning("WAF DB flood protection: IP {$ip} melebihi batas pencatatan database.");
            }
        } catch (\Throwable $e) {
            Log::error('Gagal mencatat WAF log: '.$e->getMessage());
        }

        $responseData = [
            'success' => false,
            'waf_status' => 'BLOCKED',
            'error' => 'WAF_INTERCEPTION',
            'message' => 'Permintaan diblokir oleh Web Application Firewall (WAF) SMK Negeri 20 Jakarta.',
            'threat_type' => $threatType,
            'severity' => $severity,
            'client_ip' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($responseData, 401, [
                'X-WAF-Status' => 'BLOCKED',
                'X-WAF-Reason' => $threatType,
            ]);
        }

        $escapedThreatType = htmlspecialchars($threatType, ENT_QUOTES, 'UTF-8');
        $escapedSeverity = htmlspecialchars($severity, ENT_QUOTES, 'UTF-8');
        $escapedIp = htmlspecialchars($request->ip() ?: 'Unknown', ENT_QUOTES, 'UTF-8');
        $escapedTimestamp = htmlspecialchars($responseData['timestamp'], ENT_QUOTES, 'UTF-8');

        // Tampilan HTML alert keamanan jika request melalui web browser
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>401 Unauthorized - WAF Protection</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #1e293b; border: 1px solid #ef4444; border-radius: 12px; padding: 2.5rem; max-width: 550px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h1 { color: #ef4444; font-size: 1.75rem; margin-bottom: 0.5rem; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; margin-bottom: 1.25rem; }
        .badge { background: #fee2e2; color: #991b1b; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; display: inline-block; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 0.6rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <div style="margin-bottom:1rem;color:#ef4444;display:flex;justify-content:center;">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h1>401 Akses Ditolak oleh WAF</h1>
        <p>Aktivitas atau parameter permintaan Anda terdeteksi mencurigakan dan telah diblokir secara otomatis oleh <strong>Web Application Firewall (WAF)</strong> demi menjaga keamanan sistem.</p>
        <div>
            <span class="badge">Tipe Ancaman: {$escapedThreatType}</span>
            <span class="badge" style="background:#fef3c7;color:#92400e;">Severity: {$escapedSeverity}</span>
        </div>
        <p style="margin-top:1rem;font-size:0.8rem;color:#64748b;">IP: {$escapedIp} &bull; Waktu: {$escapedTimestamp}</p>
        <a href="/" class="btn">&larr; Kembali ke Halaman Utama</a>
    </div>
</body>
</html>
HTML;

        return response($html, 401, [
            'Content-Type' => 'text/html',
            'X-WAF-Status' => 'BLOCKED',
            'X-WAF-Reason' => $threatType,
        ]);
    }
}
