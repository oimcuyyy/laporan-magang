<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAuthenticated
{
    /**
     * Memvalidasi bahwa pemanggil API memiliki autentikasi yang sah:
     * 1. Header `X-API-KEY` yang cocok
     * 2. Header `Authorization: Bearer <API_KEY>` yang cocok
     * 3. Session browser terautentikasi (Guru/Admin yang sedang login di web)
     *
     * Catatan: Parameter query `api_key` sengaja tidak diizinkan untuk mencegah kebocoran via URL/Logs (CWE-598).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.api.key') ?: env('API_SECRET_KEY');

        // 1. Cek Header X-API-KEY
        $providedKey = $request->header('X-API-KEY');

        // 2. Cek Authorization: Bearer <token>
        if (! $providedKey && $bearer = $request->bearerToken()) {
            $providedKey = $bearer;
        }

        // 3. Cek apakah pengguna sedang login di sesi web browser (Same-Origin Web)
        $isAuthenticatedSession = auth('web')->check() || ($request->hasSession() && $request->session()->has('_token'));

        $isKeyValid = $expectedKey && $providedKey && hash_equals((string) $expectedKey, (string) $providedKey);

        if ($isKeyValid || $isAuthenticatedSession) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'code' => 401,
            'message' => 'Unauthorized: Akses API ditolak. Masukkan API Key yang valid pada header X-API-KEY atau Authorization: Bearer <token>.',
        ], 401);
    }
}
