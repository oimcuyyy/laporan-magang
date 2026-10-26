<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectPresentationData
{
    /**
     * Mengunci seluruh aksi manipulasi data pada mode presentasi:
     * - Request baca (GET, HEAD, OPTIONS) bebas diakses publik tanpa login.
     * - Request manipulasi (POST, PUT, PATCH, DELETE) wajib menyertakan kunci presenter yang sah,
     *   jika tidak atau mencurigakan, langsung dilempar ke respon 401 Unauthorized.
     *
     * Fitur Kemudahan Pengembang:
     * 1. Toggle via .env: Set `PRESENTATION_LOCK=false` untuk mematikan kunci saat dev lokal.
     * 2. Secret URL Unlock: Kunjungi `/?unlock=<kunci>` sekali di browser untuk mengizinkan edit.
     * 3. Header: Kirim header `X-PRESENTER-KEY: <kunci>`.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) (config('services.api.key') ?: env('API_SECRET_KEY') ?: 'smkn20-magang-presentasi-2026');

        // Buka kunci browser via query parameter: misal ?unlock=smkn20-magang-presentasi-2026
        if ($request->has('unlock') && hash_equals($expectedKey, (string) $request->query('unlock'))) {
            if ($request->hasSession()) {
                $request->session()->put('presenter_unlocked', true);
            }
        }

        // Kunci browser kembali jika diinginkan: ?lock=true
        if ($request->has('lock') && $request->hasSession()) {
            $request->session()->forget('presenter_unlocked');
        }

        // 1. Izinkan akses baca publik tanpa batasan
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // 2. Jika PRESENTATION_LOCK=false di .env, mode kunci dinonaktifkan (bebas edit untuk dev lokal)
        if (! env('PRESENTATION_LOCK', true)) {
            return $next($request);
        }

        // 3. Cek apakah sesi browser ini sudah pernah di-unlock dengan kunci sah
        if ($request->hasSession() && $request->session()->get('presenter_unlocked') === true) {
            return $next($request);
        }

        // 4. Cek kunci presenter via Header, Bearer Token, atau Input Field
        $providedKey = $request->header('X-PRESENTER-KEY')
            ?: $request->header('X-API-KEY')
            ?: $request->bearerToken()
            ?: (string) $request->input('presenter_key');

        if ($providedKey && hash_equals($expectedKey, (string) $providedKey)) {
            return $next($request);
        }

        // 5. Tolak manipulasi tidak sah dan lempar ke 401 Unauthorized
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Unauthorized: Mode Presentasi Aktif. Data terkunci dan tidak dapat dimanipulasi.',
            ], 401);
        }

        abort(401, 'Unauthorized: Mode Presentasi Aktif. Data terkunci dan tidak dapat dimanipulasi oleh pengunjung.');
    }
}
