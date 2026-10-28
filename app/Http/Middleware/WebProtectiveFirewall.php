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
            $request->except(['_token', 'photo', 'mentor_signature', 'mentor_signature_data']), // Kecualikan CSRF token dan file/data biner foto
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
            if (in_array($key, ['_token', 'photo', 'paraf_data', 'mentor_signature_data', 'paraf_file', 'mentor_signature'], true)) {
                continue;
            }
            if (is_string($value) && (str_starts_with($value, 'data:image/') || strlen($value) > 2000)) {
                continue;
            }
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
     * Render response khusus ketika terdeteksi pengubahan / manipulasi tautan atau parameter URL
     */
    public static function renderTamperedUrlResponse(Request $request, string $detailReason = 'Tautan atau parameter pada URL telah diubah-ubah secara tidak sah.', ?string $customUrl = null): Response
    {
        $responseData = [
            'success' => false,
            'status' => 'error',
            'code' => 401,
            'waf_status' => 'BLOCKED',
            'error' => 'URL_TAMPERING_DETECTED',
            'warning' => 'PERINGATAN: (Jangan mengubah-ubah di web ini)',
            'message' => 'Akses ditolak: Tautan atau parameter URL telah diubah-ubah secara tidak sah.',
            'detail' => $detailReason,
            'client_ip' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($responseData, 401, [
                'X-WAF-Status' => 'BLOCKED',
                'X-WAF-Reason' => 'URL_TAMPERING',
            ]);
        }

        $escapedDetail = htmlspecialchars($detailReason, ENT_QUOTES, 'UTF-8');
        $escapedIp = htmlspecialchars($request->ip() ?: 'Unknown', ENT_QUOTES, 'UTF-8');
        $escapedTimestamp = htmlspecialchars($responseData['timestamp'], ENT_QUOTES, 'UTF-8');
        $escapedUrl = htmlspecialchars($customUrl ?: $request->fullUrl(), ENT_QUOTES, 'UTF-8');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized - Manipulasi URL Terdeteksi</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #090d16;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(12px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.35);
            }
            50% {
                transform: scale(1.04);
                box-shadow: 0 0 20px 4px rgba(239, 68, 68, 0.25);
            }
        }
        .card {
            background: rgba(30, 41, 59, 0.92);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 18px;
            padding: 2.25rem;
            max-width: 560px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.05);
            animation: cardEnter 240ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem auto;
            background: rgba(239, 68, 68, 0.12);
            border: 1.5px solid rgba(239, 68, 68, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            animation: iconPulse 2.8s ease-in-out infinite;
        }
        h1 {
            color: #ef4444;
            font-size: 1.7rem;
            margin: 0 0 0.35rem 0;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .subtitle {
            color: #f87171;
            font-size: 0.95rem;
            margin: 0 0 1.25rem 0;
            font-weight: 600;
        }
        .warning-banner {
            background: rgba(127, 29, 29, 0.85);
            border: 1px solid #ef4444;
            color: #fef2f2;
            padding: 0.85rem 1.15rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.96rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .reason-box {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.25rem;
            text-align: left;
        }
        .reason-box p {
            margin: 0 0 0.5rem 0;
            color: #e2e8f0;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .reason-box code {
            font-size: 0.8rem;
            color: #cbd5e1;
            word-break: break-all;
            background: #020617;
            padding: 0.35rem 0.6rem;
            border-radius: 6px;
            display: block;
            border: 1px solid #1e293b;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: #ffffff;
            padding: 0.75rem 1.65rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
            margin-top: 0.5rem;
            transition: transform 160ms ease-out, background 160ms ease, box-shadow 160ms ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
        }
        .btn:active {
            transform: scale(0.97);
        }
        .meta {
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h1>401 Akses Ditolak</h1>
        <p class="subtitle">Manipulasi Link / Parameter URL Terdeteksi</p>
        
        <div class="warning-banner">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>PERINGATAN: (Jangan mengubah-ubah di web ini)</span>
        </div>

        <div class="reason-box">
            <p><strong>Keterangan Sistem:</strong> {$escapedDetail}</p>
            <code>URL: {$escapedUrl}</code>
        </div>

        <p style="color:#94a3b8;font-size:0.85rem;line-height:1.5;margin-bottom:1rem;">
            Aktivitas pengubahan tautan atau parameter URL secara manual dilarang demi menjaga integritas data rekapitulasi laporan magang SMK Negeri 20 Jakarta.
        </p>

        <a href="/students" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Halaman Utama yang Aman
        </a>

        <div class="meta">
            IP: {$escapedIp} &bull; Waktu: {$escapedTimestamp} &bull; SMK Negeri 20 Jakarta
        </div>
    </div>
</body>
</html>
HTML;

        return response($html, 401, [
            'Content-Type' => 'text/html',
            'X-WAF-Status' => 'BLOCKED',
            'X-WAF-Reason' => 'URL_TAMPERING',
        ]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized - WAF Protection</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #090d16;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        @keyframes cardEnter {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(12px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        @keyframes iconPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.35);
            }
            50% {
                transform: scale(1.04);
                box-shadow: 0 0 20px 4px rgba(239, 68, 68, 0.25);
            }
        }
        .card {
            background: rgba(30, 41, 59, 0.92);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 18px;
            padding: 2.25rem;
            max-width: 560px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.05);
            animation: cardEnter 240ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem auto;
            background: rgba(239, 68, 68, 0.12);
            border: 1.5px solid rgba(239, 68, 68, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            animation: iconPulse 2.8s ease-in-out infinite;
        }
        h1 {
            color: #ef4444;
            font-size: 1.7rem;
            margin: 0 0 0.5rem 0;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }
        .warning-banner {
            background: rgba(127, 29, 29, 0.85);
            border: 1px solid #ef4444;
            color: #fef2f2;
            padding: 0.85rem 1.15rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.96rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }
        .badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
            margin: 0 4px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: #fff;
            padding: 0.75rem 1.65rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.92rem;
            margin-top: 1.5rem;
            transition: transform 160ms ease-out, background 160ms ease, box-shadow 160ms ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
        }
        .btn:active {
            transform: scale(0.97);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h1>401 Akses Ditolak oleh WAF</h1>
        
        <div class="warning-banner">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>PERINGATAN: (Jangan mengubah-ubah di web ini)</span>
        </div>

        <p>Aktivitas atau parameter permintaan Anda terdeteksi mencurigakan dan telah diblokir secara otomatis oleh <strong>Web Application Firewall (WAF)</strong> demi menjaga keamanan sistem.</p>
        <div>
            <span class="badge">Tipe Ancaman: {$escapedThreatType}</span>
            <span class="badge" style="background:#fef3c7;color:#92400e;">Severity: {$escapedSeverity}</span>
        </div>
        <p style="margin-top:1rem;font-size:0.8rem;color:#64748b;">IP: {$escapedIp} &bull; Waktu: {$escapedTimestamp}</p>
        <a href="/students" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Halaman Utama
        </a>
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
