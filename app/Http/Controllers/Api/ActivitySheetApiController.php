<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ActivitySheet;
use App\Models\Student;
use App\Services\ParafService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivitySheetApiController extends Controller
{
    /**
     * POST /api/v1/students/{student}/activity-sheets
     * Menambah lembar/slide uraian kegiatan baru (Tombol `+`)
     */
    public function store(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'month_name' => 'required|string|max:100',
        ]);

        $maxSheet = $student->activitySheets()->max('sheet_number') ?: 0;
        $nextSheetNum = $maxSheet + 1;

        $sheet = $student->activitySheets()->create([
            'sheet_number' => $nextSheetNum,
            'month_name' => $validated['month_name'],
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $sheet->activityLogs()->create(['row_index' => $i]);
        }

        $sheet->load('activityLogs');

        return response()->json([
            'success' => true,
            'message' => "Slide Lembar ke-{$nextSheetNum} berhasil ditambahkan!",
            'data' => $sheet,
        ], 201);
    }

    /**
     * PUT /api/v1/students/{student}/activity-sheets/{sheet}
     * Menyimpan 10 baris kegiatan pada lembar/slide tertentu
     */
    public function update(Request $request, Student $student, ActivitySheet $sheet): JsonResponse
    {
        if ($sheet->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'month_name' => 'required|string|max:100',
            'logs' => 'required|array',
            'logs.*.id' => 'required|integer|exists:activity_logs,id',
            'logs.*.day_date' => 'nullable|string|max:100',
            'logs.*.division' => 'nullable|string|max:100',
            'logs.*.job_description' => 'nullable|string',
            'logs.*.supervisor_note' => 'nullable|string|max:100',
            'logs.*.is_approved' => 'nullable|boolean',
            'logs.*.paraf_data' => 'nullable|string',
            'logs.*.remove_paraf' => 'nullable|boolean',
        ]);

        $sheet->update(['month_name' => $validated['month_name']]);

        foreach ($validated['logs'] as $logData) {
            $log = ActivityLog::where('id', $logData['id'])
                ->where('activity_sheet_id', $sheet->id)
                ->first();

            if ($log) {
                $base64 = $logData['paraf_data'] ?? null;
                $shouldRemove = ! empty($logData['remove_paraf']);

                $parafPath = ParafService::processParaf(
                    base64Data: $base64,
                    shouldRemove: $shouldRemove,
                    currentPath: $log->paraf_path,
                    prefix: 'activity_paraf'
                );

                $log->update([
                    'day_date' => $logData['day_date'] ?? null,
                    'division' => $logData['division'] ?? null,
                    'job_description' => $logData['job_description'] ?? null,
                    'supervisor_note' => $logData['supervisor_note'] ?? null,
                    'paraf_path' => $parafPath,
                    'is_approved' => ! empty($logData['is_approved']),
                ]);
            }
        }

        $sheet->load('activityLogs');

        return response()->json([
            'success' => true,
            'message' => "Lembar Uraian Kegiatan ke-{$sheet->sheet_number} berhasil disimpan!",
            'data' => $sheet,
        ]);
    }

    /**
     * DELETE /api/v1/students/{student}/activity-sheets/{sheet}
     */
    public function destroy(Student $student, ActivitySheet $sheet): JsonResponse
    {
        if ($sheet->student_id !== $student->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($student->activitySheets()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal harus terdapat 1 lembar kegiatan.',
            ], 422);
        }

        $sheet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lembar kegiatan berhasil dihapus.',
        ]);
    }
}
