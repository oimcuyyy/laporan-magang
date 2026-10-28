<?php

use App\Http\Controllers\Api\ActivitySheetApiController;
use App\Http\Controllers\Api\AttendanceSheetApiController;
use App\Http\Controllers\Api\EvaluationApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\WafApiController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // API Health Check (Aman dari Information Disclosure: tidak mengekspos db driver, kredensial, atau error trace)
    Route::get('health', function () {
        try {
            DB::connection()->getPdo();

            return response()->json([
                'status' => 'UP',
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            Log::error('API Health check database error: '.$e->getMessage());

            return response()->json([
                'status' => 'DOWN',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    });

    // Endpoint Terproteksi Autentikasi (Mencegah Akses API Tanpa Autentikasi)
    Route::middleware('api.auth')->group(function () {
        // Siswa (1 - 5 Siswa)
        Route::get('students', [StudentApiController::class, 'index']);
        Route::post('students', [StudentApiController::class, 'store']);
        Route::get('students/{student}', [StudentApiController::class, 'show']);
        Route::post('students/{student}', [StudentApiController::class, 'update']);
        Route::patch('students/{student}/cover', [StudentApiController::class, 'updateCover']);
        Route::post('students/{student}/clone', [StudentApiController::class, 'cloneStudent']);
        Route::delete('students/{student}', [StudentApiController::class, 'destroy']);

        // Slide Uraian Kegiatan (10 baris per slide)
        Route::post('students/{student}/activity-sheets', [ActivitySheetApiController::class, 'store']);
        Route::put('students/{student}/activity-sheets/{sheet}', [ActivitySheetApiController::class, 'update']);
        Route::delete('students/{student}/activity-sheets/{sheet}', [ActivitySheetApiController::class, 'destroy']);

        // Presensi Kehadiran Bulanan & Rekapitulasi
        Route::post('students/{student}/attendance-sheets', [AttendanceSheetApiController::class, 'store']);
        Route::put('students/{student}/attendance-sheets/{sheet}', [AttendanceSheetApiController::class, 'update']);
        Route::delete('students/{student}/attendance-sheets/{sheet}', [AttendanceSheetApiController::class, 'destroy']);

        // Penilaian DU/DI & Predikat Otomatis
        Route::put('students/{student}/evaluation', [EvaluationApiController::class, 'update']);

        // Web Application Firewall (WAF) Telemetry
        Route::get('waf/status', [WafApiController::class, 'status']);
    });
});
