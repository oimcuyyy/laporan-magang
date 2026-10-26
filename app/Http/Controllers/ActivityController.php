<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateActivitySheetRequest;
use App\Models\ActivityLog;
use App\Models\ActivitySheet;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Tambah Slide / Lembar Uraian Kegiatan Baru (Fitur tombol plus `+`)
     */
    public function storeSheet(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'month_name' => 'required|string|max:100',
        ]);

        $maxSheetNum = $student->activitySheets()->max('sheet_number') ?: 0;
        $nextSheetNum = $maxSheetNum + 1;

        $newSheet = $student->activitySheets()->create([
            'sheet_number' => $nextSheetNum,
            'month_name' => $validated['month_name'],
        ]);

        // Siapkan 10 baris kosong per lembar sesuai format berkas fisik
        for ($i = 1; $i <= 10; $i++) {
            $newSheet->activityLogs()->create([
                'row_index' => $i,
            ]);
        }

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'uraian',
            'sheet' => $newSheet->id,
        ])->with('success', "Lembar Uraian Kegiatan ke-{$nextSheetNum} ({$validated['month_name']}) berhasil ditambahkan!");
    }

    /**
     * Update 10 Baris Uraian Kegiatan pada Lembar Tertentu
     */
    public function updateSheet(UpdateActivitySheetRequest $request, Student $student, ActivitySheet $sheet): RedirectResponse
    {
        // Validasi kepemilikan
        if ($sheet->student_id !== $student->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $validated = $request->validated();

        $sheet->update(['month_name' => $validated['month_name']]);

        foreach ($validated['logs'] as $logData) {
            $log = ActivityLog::where('id', $logData['id'])
                ->where('activity_sheet_id', $sheet->id)
                ->first();

            if ($log) {
                $log->update([
                    'day_date' => $logData['day_date'] ?? null,
                    'division' => $logData['division'] ?? null,
                    'job_description' => $logData['job_description'] ?? null,
                    'supervisor_note' => $logData['supervisor_note'] ?? null,
                    'is_approved' => isset($logData['is_approved']) ? (bool)$logData['is_approved'] : false,
                ]);
            }
        }

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'uraian',
            'sheet' => $sheet->id,
        ])->with('success', "Lembar ke-{$sheet->sheet_number} berhasil disimpan!");
    }

    /**
     * Hapus Lembar Kegiatan (Jika lebih dari 1 lembar)
     */
    public function destroySheet(Student $student, ActivitySheet $sheet): RedirectResponse
    {
        if ($sheet->student_id !== $student->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        if ($student->activitySheets()->count() <= 1) {
            return back()->with('error', 'Minimal harus terdapat 1 lembar kegiatan.');
        }

        $sheet->delete();

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'uraian',
        ])->with('success', 'Lembar kegiatan berhasil dihapus.');
    }
}
