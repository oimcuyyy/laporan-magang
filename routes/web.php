<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('students.index');
});

// Rute Publik Presentasi (Bebas Dilihat Siapa Saja: Guru, Penguji, Siswa, Tamu Tanpa Login)
Route::get('students', [StudentController::class, 'index'])->name('students.index');
Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::get('students/{student}/print', [StudentController::class, 'printReport'])->name('students.print');

// Rute Mutasi Data Laporan Magang (Diproteksi CSRF, Validasi Ketat Server-Side, & Anti-Flood Throttling)
Route::middleware(['throttle:60,1'])->group(function () {
    // Tambah, Edit, Kloning, Hapus Siswa
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::match(['put', 'patch', 'post'], 'students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::patch('students/{student}/quick-cover', [StudentController::class, 'quickUpdateCover'])->name('students.quick-cover');
    Route::post('students/{student}/clone', [StudentController::class, 'cloneStudent'])->name('students.clone');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    // Uraian Kegiatan (Slides & 10 Baris per Lembar)
    Route::post('students/{student}/activity-sheets', [ActivityController::class, 'storeSheet'])->name('activity-sheets.store');
    Route::put('students/{student}/activity-sheets/{sheet}', [ActivityController::class, 'updateSheet'])->name('activity-sheets.update');
    Route::delete('students/{student}/activity-sheets/{sheet}', [ActivityController::class, 'destroySheet'])->name('activity-sheets.destroy');

    // Presensi / Kehadiran Bulanan
    Route::post('students/{student}/attendance-sheets', [AttendanceController::class, 'storeSheet'])->name('attendance-sheets.store');
    Route::put('students/{student}/attendance-sheets/{sheet}', [AttendanceController::class, 'updateSheet'])->name('attendance-sheets.update');
    Route::delete('students/{student}/attendance-sheets/{sheet}', [AttendanceController::class, 'destroySheet'])->name('attendance-sheets.destroy');
    Route::post('students/{student}/attendance-sheets/{sheet}/records', [AttendanceController::class, 'addRecord'])->name('attendance-records.store');
    Route::delete('students/{student}/attendance-sheets/{sheet}/records/{record}', [AttendanceController::class, 'destroyRecord'])->name('attendance-records.destroy');

    // Penilaian DU/DI & Predikat Otomatis
    Route::put('students/{student}/evaluation', [EvaluationController::class, 'update'])->name('evaluations.update');
});

// Health Check Endpoint (Aman dari Information Disclosure: hanya status UP/DOWN tanpa membocorkan driver/credential)
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'UP',
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (Throwable $e) {
        Log::error('Health check database error: '.$e->getMessage());

        return response()->json([
            'status' => 'DOWN',
            'timestamp' => now()->toIso8601String(),
        ], 503);
    }
})->name('health.check');
