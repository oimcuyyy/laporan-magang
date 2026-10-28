<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateActivitySheetRequest;
use App\Models\ActivityLog;
use App\Models\ActivitySheet;
use App\Models\Student;
use App\Services\ParafService;
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

        $firstNewParaf = null;

        foreach ($validated['logs'] as $index => $logData) {
            $log = ActivityLog::where('id', $logData['id'])
                ->where('activity_sheet_id', $sheet->id)
                ->first();

            if ($log) {
                $base64 = $logData['paraf_data'] ?? null;
                $file = $request->file("logs.{$index}.paraf_file") ?: $request->file("logs.{$logData['id']}.paraf_file");
                $shouldRemove = ! empty($logData['remove_paraf']);

                if ($shouldRemove) {
                    ParafService::deleteParaf($log->paraf_path);
                    $parafPath = $student->default_paraf_path ? 'none' : null;
                } else {
                    $parafPath = ParafService::processParaf(
                        base64Data: $base64,
                        uploadedFile: $file,
                        shouldRemove: false,
                        currentPath: $log->paraf_path,
                        prefix: 'activity_paraf'
                    );

                    if (($base64 || $file) && $parafPath && $parafPath !== 'none') {
                        $firstNewParaf = $parafPath;
                    }
                }

                $log->update([
                    'day_date' => $logData['day_date'] ?? null,
                    'division' => $logData['division'] ?? null,
                    'job_description' => $logData['job_description'] ?? null,
                    'supervisor_note' => $logData['supervisor_note'] ?? null,
                    'paraf_path' => $parafPath,
                    'is_approved' => isset($logData['is_approved']) ? (bool) $logData['is_approved'] : false,
                ]);
            }
        }

        // Jika siswa belum punya paraf default atau diminta update default, jadikan paraf ini master
        if ($firstNewParaf && (! $student->default_paraf_path || $request->boolean('update_default_paraf', true))) {
            $student->update(['default_paraf_path' => $firstNewParaf]);
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
