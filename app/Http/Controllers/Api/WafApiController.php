<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WafLog;
use Illuminate\Http\JsonResponse;

class WafApiController extends Controller
{
    /**
     * GET /api/v1/waf/status
     * Mengambil status WAF, metrik pertahanan, dan log blokir terkini
     */
    public function status(): JsonResponse
    {
        $totalBlocked = WafLog::count();
        $recentAttacks = WafLog::orderBy('id', 'desc')->limit(10)->get();

        $byThreatType = WafLog::selectRaw('threat_type, count(*) as total')
            ->groupBy('threat_type')
            ->pluck('total', 'threat_type');

        return response()->json([
            'success' => true,
            'waf_status' => 'ACTIVE',
            'protection_engine' => 'SMK20-Adaptive-Firewall-v1.0',
            'active_modules' => [
                'SQL_INJECTION' => 'ENABLED (Deep Pattern & Blind SQLi)',
                'CROSS_SITE_SCRIPTING' => 'ENABLED (DOM, Stored, Reflected)',
                'PATH_TRAVERSAL' => 'ENABLED (LFI/RFI Directory Traversal)',
                'REMOTE_CODE_EXECUTION' => 'ENABLED (PHP Function Call & Command Injection)',
                'BOT_SCANNER_DEFENSE' => 'ENABLED (SQLMap, Nikto, WPScan, Acunetix, Nmap)',
                'SECURITY_HEADERS' => 'ENFORCED (CSP, HSTS, X-Frame-Options, NoSniff)',
            ],
            'statistics' => [
                'total_blocked' => $totalBlocked,
                'breakdown' => $byThreatType,
            ],
            'recent_blocked_incidents' => $recentAttacks,
        ]);
    }
}
