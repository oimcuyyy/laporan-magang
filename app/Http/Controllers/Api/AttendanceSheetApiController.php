<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSheet;
use App\Models\Student;
use App\Services\ParafService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSheetApiController extends Controller
{
    /**
     * POST /api/v1/students/{student}/attendance-sheets
     * Menambah lembar presensi bulan baru (Tombol `+`)
     */
    public function store(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'month_name' => 'required|string|max:100',
            'days_count' => 'required|integer|in:28,29,30,31',
        ]);

        $sheet = $student->attendanceSheets()->create([
            'month_name' => $validated['month_name'],
            'total_target_days' => $validated['days_count'],
        ]);

        for ($i = 1; $i <= $validated['days_count']; $i++) {
            $sheet->records()->create([
                'day_number' => $i,
                'status' => 'H',
            ]);
        }

        $sheet->recalculateTotals();
        $sheet->load('records');

        return response()->json([
            'success' => true,
            'message' => "Lembar Presensi Bulan {$sheet->month_name} berhasil dibuat!",
            'data' => $sheet,
        ], 201);
    }

    /**
     * PUT /api/v1/students/{student}/attendance-sheets/{sheet}
     * Menyimpan presensi dan menghitung ulang otomatis rekapitulasi kehadiran
     */
    public function update(Request $request, Student $student, AttendanceSheet $sheet): JsonResponse
    {
        if ($sheet->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'month_name' => 'required|string|max:100',
            'records' => 'required|array',
            'records.*.id' => 'required|integer|exists:attendance_records,id',
            'records.*.day_date' => 'nullable|string|max:100',
            'records.*.status' => 'nullable|in:H,S,I,A',
            'records.*.notes' => 'nullable|string|max:150',
            'records.*.is_signed' => 'nullable|boolean',
            'records.*.paraf_data' => 'nullable|string',
            'records.*.remove_paraf' => 'nullable|boolean',
        ]);

        $sheet->update(['month_name' => $validated['month_name']]);

        foreach ($validated['records'] as $recData) {
            $record = AttendanceRecord::where('id', $recData['id'])
                ->where('attendance_sheet_id', $sheet->id)
                ->first();

            if ($record) {
                $base64 = $recData['paraf_data'] ?? null;
                $shouldRemove = ! empty($recData['remove_paraf']);

                $parafPath = ParafService::processParaf(
                    base64Data: $base64,
                    shouldRemove: $shouldRemove,
                    currentPath: $record->paraf_path,
                    prefix: 'attendance_paraf'
                );

                $record->update([
                    'day_date' => $recData['day_date'] ?? null,
                    'status' => ! empty($recData['status']) ? $recData['status'] : null,
                    'notes' => $recData['notes'] ?? null,
                    'paraf_path' => $parafPath,
                    'is_signed' => ! empty($recData['is_signed']),
                ]);
            }
        }

        $sheet->recalculateTotals();
        $sheet->load('records');

        return response()->json([
            'success' => true,
            'message' => "Presensi Bulan {$sheet->month_name} berhasil diperbarui!",
            'recap' => [
                'total_target_days' => $sheet->total_target_days,
                'total_present' => $sheet->total_present,
                'total_sick' => $sheet->total_sick,
                'total_permission' => $sheet->total_permission,
                'total_absent' => $sheet->total_absent,
            ],
            'data' => $sheet,
        ]);
    }

    /**
     * DELETE /api/v1/students/{student}/attendance-sheets/{sheet}
     */
    public function destroy(Student $student, AttendanceSheet $sheet): JsonResponse
    {
        if ($sheet->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($student->attendanceSheets()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal harus ada 1 lembar presensi.',
            ], 422);
        }

        $sheet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lembar presensi berhasil dihapus.',
        ]);
    }
}
