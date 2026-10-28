<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceSheetRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSheet;
use App\Models\Student;
use App\Services\ParafService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Tambah Lembar Presensi / Bulan Baru (Fitur tombol plus `+`)
     */
    public function storeSheet(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'month_name' => 'required|string|max:100',
            'days_count' => 'required|integer|in:18,20,21,22,23,28,29,30,31',
        ]);

        $newSheet = $student->attendanceSheets()->create([
            'month_name' => $validated['month_name'],
            'total_target_days' => $validated['days_count'],
        ]);

        for ($i = 1; $i <= $validated['days_count']; $i++) {
            $newSheet->records()->create([
                'day_number' => $i,
                'status' => 'H', // default hadir
            ]);
        }

        $newSheet->recalculateTotals();

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'presensi',
            'sheet' => $newSheet->id,
        ])->with('success', "Lembar Presensi Bulan {$validated['month_name']} berhasil ditambahkan!");
    }

    /**
     * Update Data Kehadiran Hari Kerja dan Hitung Ulang Rekapitulasi Otomatis
     */
    public function updateSheet(UpdateAttendanceSheetRequest $request, Student $student, AttendanceSheet $sheet): RedirectResponse
    {
        if ($sheet->student_id !== $student->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $validated = $request->validated();

        $sheet->update(['month_name' => $validated['month_name']]);

        $firstNewParaf = null;

        foreach ($validated['records'] as $index => $recData) {
            $record = AttendanceRecord::where('id', $recData['id'])
                ->where('attendance_sheet_id', $sheet->id)
                ->first();

            if ($record) {
                $status = ! empty($recData['status']) ? $recData['status'] : null;
                // Keterangan khusus untuk Sakit (S), Izin (I), dan Alpa (A). Kalo Hadir (H) tidak perlu keterangan.
                $notes = ($status === 'H') ? null : ($recData['notes'] ?? null);

                $base64 = $recData['paraf_data'] ?? null;
                $file = $request->file("records.{$index}.paraf_file") ?: $request->file("records.{$recData['id']}.paraf_file");
                $shouldRemove = ! empty($recData['remove_paraf']);

                if ($shouldRemove) {
                    ParafService::deleteParaf($record->paraf_path);
                    $parafPath = $student->default_paraf_path ? 'none' : null;
                } else {
                    $parafPath = ParafService::processParaf(
                        base64Data: $base64,
                        uploadedFile: $file,
                        shouldRemove: false,
                        currentPath: $record->paraf_path,
                        prefix: 'attendance_paraf'
                    );

                    if (($base64 || $file) && $parafPath && $parafPath !== 'none') {
                        $firstNewParaf = $parafPath;
                    }
                }

                $record->update([
                    'day_date' => $recData['day_date'] ?? null,
                    'status' => $status,
                    'notes' => $notes,
                    'paraf_path' => $parafPath,
                    'is_signed' => isset($recData['is_signed']) ? (bool) $recData['is_signed'] : false,
                ]);
            }
        }

        // Jika siswa belum punya paraf default atau diminta update default, jadikan paraf ini master
        if ($firstNewParaf && (! $student->default_paraf_path || $request->boolean('update_default_paraf', true))) {
            $student->update(['default_paraf_path' => $firstNewParaf]);
        }

        // Kalkulasi ulang rekapitulasi kehadiran (Jumlah Hadir, Sakit, Izin, Alpa)
        $sheet->recalculateTotals();

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'presensi',
            'sheet' => $sheet->id,
        ])->with('success', "Daftar Hadir Bulan {$sheet->month_name} dan rekapitulasi berhasil diperbarui!");
    }

    /**
     * Hapus Lembar Presensi
     */
    public function destroySheet(Student $student, AttendanceSheet $sheet): RedirectResponse
    {
        if ($sheet->student_id !== $student->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        if ($student->attendanceSheets()->count() <= 1) {
            return back()->with('error', 'Minimal harus terdapat 1 lembar presensi.');
        }

        $sheet->delete();

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'presensi',
        ])->with('success', 'Lembar presensi berhasil dihapus.');
    }

    /**
     * Tambah 1 Hari Presensi (Opsional: Sabtu/Minggu atau hari tambahan)
     */
    public function addRecord(Request $request, Student $student, AttendanceSheet $sheet): RedirectResponse|JsonResponse
    {
        if ($sheet->student_id !== $student->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $nextDay = ($sheet->records()->max('day_number') ?: 0) + 1;
        $record = $sheet->records()->create([
            'day_number' => $nextDay,
            'day_date' => "Hari ke-{$nextDay}",
            'status' => 'H',
            'notes' => null,
            'is_signed' => true,
        ]);

        $sheet->recalculateTotals();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Hari ke-{$nextDay} berhasil ditambahkan!",
                'record' => [
                    'id' => $record->id,
                    'day_number' => $record->day_number,
                    'day_date' => $record->day_date,
                    'status' => $record->status,
                    'notes' => $record->notes,
                    'is_signed' => $record->is_signed,
                ],
                'sheet_id' => $sheet->id,
                'student_id' => $student->id,
                'total_target_days' => $sheet->total_target_days,
                'total_present' => $sheet->total_present,
            ]);
        }

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'presensi',
            'sheet' => $sheet->id,
        ])->with('success', "Hari ke-{$nextDay} berhasil ditambahkan ke daftar hadir!");
    }

    /**
     * Hapus 1 Baris Presensi (Opsional: Jika Sabtu/Minggu libur)
     */
    public function destroyRecord(Request $request, Student $student, AttendanceSheet $sheet, AttendanceRecord $record): RedirectResponse|JsonResponse
    {
        if ($sheet->student_id !== $student->id || $record->attendance_sheet_id !== $sheet->id) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        $record->delete();

        // Re-index nomor urut day_number agar rapi berurutan 1, 2, 3...
        $records = $sheet->records()->orderBy('id')->get();
        foreach ($records as $index => $rec) {
            if ($rec->day_number !== $index + 1) {
                $rec->update(['day_number' => $index + 1]);
            }
        }

        $sheet->recalculateTotals();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Baris kehadiran berhasil dihapus.',
                'total_target_days' => $sheet->total_target_days,
                'total_present' => $sheet->total_present,
            ]);
        }

        return redirect()->route('students.show', [
            'student' => $student->id,
            'tab' => 'presensi',
            'sheet' => $sheet->id,
        ])->with('success', 'Baris kehadiran berhasil dihapus.');
    }
}
