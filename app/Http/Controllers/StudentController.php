<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuickUpdateCoverRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\ActivityLog;
use App\Models\ActivitySheet;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSheet;
use App\Models\Evaluation;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Dashboard Rekapitulasi Utama (Multi-Siswa 1-5 Siswa)
     */
    public function index(): View
    {
        $students = Student::with(['activitySheets.activityLogs', 'attendanceSheets', 'evaluation'])
            ->orderBy('id')
            ->get();

        $totalStudents = $students->count();
        $canAddStudent = $totalStudents < 5;

        return view('students.index', compact('students', 'totalStudents', 'canAddStudent'));
    }

    /**
     * Menampilkan detail formulir 5 lembar untuk siswa tertentu
     */
    public function show(Student $student, Request $request): View
    {
        $allStudents = Student::select('id', 'name', 'nis', 'class', 'major')->orderBy('id')->get();
        
        $student->load([
            'activitySheets.activityLogs',
            'attendanceSheets.records',
            'evaluation'
        ]);

        // Pastikan siswa memiliki minimal 1 lembar kegiatan
        if ($student->activitySheets->isEmpty()) {
            $sheet = $student->activitySheets()->create([
                'sheet_number' => 1,
                'month_name' => 'Oktober 2026',
            ]);
            for ($i = 1; $i <= 10; $i++) {
                $sheet->activityLogs()->create(['row_index' => $i]);
            }
            $student->load('activitySheets.activityLogs');
        }

        // Pastikan siswa memiliki minimal 1 lembar kehadiran
        if ($student->attendanceSheets->isEmpty()) {
            $attSheet = $student->attendanceSheets()->create([
                'month_name' => 'Oktober 2026',
                'total_target_days' => 30,
            ]);
            for ($i = 1; $i <= 30; $i++) {
                $attSheet->records()->create([
                    'day_number' => $i,
                    'status' => 'H',
                ]);
            }
            $attSheet->recalculateTotals();
            $student->load('attendanceSheets.records');
        }

        // Pastikan siswa memiliki record evaluasi
        if (!$student->evaluation) {
            $student->evaluation()->create([
                'evaluation_date' => 'Jakarta, 31 Oktober 2026',
                'mentor_name' => $student->mentor_name ?: 'Pembimbing Industri',
            ]);
            $student->load('evaluation');
        }

        $activeTab = $request->query('tab', 'cover'); // cover, identitas, uraian, presensi, penilaian

        return view('students.show', compact('student', 'allStudents', 'activeTab'));
    }

    /**
     * Form tambah siswa baru (Maksimal 5 Siswa)
     */
    public function create(): View|RedirectResponse
    {
        $count = Student::count();
        if ($count >= 5) {
            return redirect()->route('students.index')->with('error', 'Maksimal 5 siswa dalam rekap kelompok ini.');
        }

        return view('students.create', compact('count'));
    }

    /**
     * Simpan data siswa baru beserta template berkas lengkap
     */
    public function store(Request $request): RedirectResponse
    {
        $count = Student::count();
        if ($count >= 5) {
            return redirect()->route('students.index')->with('error', 'Kapasitas penuh (maksimal 5 siswa).');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'nis' => 'required|string|max:30|unique:students,nis',
            'nisn' => 'nullable|string|max:30',
            'class' => 'required|string|max:50',
            'major' => 'required|string|max:100',
            'field_of_expertise' => 'nullable|string|max:100',
            'internship_period' => 'nullable|string|max:100',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|string|max:50',
            'gender' => 'required|in:Laki-Laki,Perempuan',
            'religion' => 'nullable|string|max:30',
            'student_address' => 'nullable|string|max:500',
            'student_phone' => 'nullable|string|max:30',
            'father_name' => 'nullable|string|max:100',
            'father_phone' => 'nullable|string|max:30',
            'mother_name' => 'nullable|string|max:100',
            'mother_phone' => 'nullable|string|max:30',
            'parent_address' => 'nullable|string|max:500',
            'school_name' => 'required|string|max:150',
            'school_address' => 'nullable|string|max:500',
            'company_name' => 'required|string|max:150',
            'company_address' => 'nullable|string|max:500',
            'teacher_name' => 'required|string|max:100',
            'teacher_phone' => 'nullable|string|max:30',
            'mentor_name' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            $validated['photo_path'] = $photoPath;

            $student = Student::create($validated);

            // Inisialisasi Lembar Uraian Kegiatan Slide 1 (10 baris)
            $sheet = $student->activitySheets()->create([
                'sheet_number' => 1,
                'month_name' => 'Oktober 2026',
            ]);
            for ($i = 1; $i <= 10; $i++) {
                $sheet->activityLogs()->create(['row_index' => $i]);
            }

            // Inisialisasi Lembar Presensi Kehadiran (30 hari)
            $attSheet = $student->attendanceSheets()->create([
                'month_name' => 'Oktober 2026',
                'total_target_days' => 30,
            ]);
            for ($i = 1; $i <= 30; $i++) {
                $attSheet->records()->create([
                    'day_number' => $i,
                    'status' => 'H',
                ]);
            }
            $attSheet->recalculateTotals();

            // Inisialisasi Lembar Penilaian DU/DI
            $student->evaluation()->create([
                'evaluation_date' => 'Jakarta, 31 Oktober 2026',
                'mentor_name' => $student->mentor_name ?: 'Pembimbing Industri',
            ]);

            DB::commit();

            return redirect()->route('students.show', $student->id)->with('success', "Siswa {$student->name} berhasil ditambahkan!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage());
        }
    }

    /**
     * Update data Identitas & Pas Foto Siswa
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();


        if (empty($validated['class'])) {
            $validated['class'] = $student->class ?? 'XI';
        }
        if (empty($validated['major'])) {
            $validated['major'] = $student->major ?? 'Rekayasa Perangkat Lunak';
        }
        if (empty($validated['field_of_expertise'])) {
            $validated['field_of_expertise'] = $student->field_of_expertise ?? 'Bisnis Manajemen dan Teknologi';
        }

        if ($request->hasFile('photo')) {
            if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        $student->update($validated);

        return redirect()->route('students.show', [$student->id, 'tab' => 'identitas'])->with('success', 'Data Identitas Peserta berhasil diperbarui!');
    }

    /**
     * Hapus Siswa
     */
    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Update cepat Jurusan, Kelas, dan Tahun Pelajaran dari Halaman Cover
     */
    public function quickUpdateCover(QuickUpdateCoverRequest $request, Student $student): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $student->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jurusan, Kelas, dan Tahun Pelajaran berhasil diperbarui!',
                'data' => $student,
            ]);
        }

        return back()->with('success', 'Pengaturan Jurusan & Cover berhasil disimpan!');
    }

    /**
     * Kloning profil siswa untuk angkatan / jurusan baru
     */
    public function cloneStudent(Request $request, Student $student): RedirectResponse|JsonResponse
    {
        $count = Student::count();
        if ($count >= 5) {
            $msg = 'Batas maksimal 5 siswa kelompok telah tercapai. Hapus siswa yang tidak terpakai jika ingin menambah kloning baru.';
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'major' => 'nullable|string|max:100',
            'class' => 'nullable|string|max:50',
            'academic_year' => 'nullable|string|max:30',
            'name' => 'nullable|string|max:150',
        ]);

        $newMajor = $validated['major'] ?? $student->major;
        $newClass = $validated['class'] ?? $student->class;
        $newYear = $validated['academic_year'] ?? ($student->academic_year ?: '2026/2027');
        $newName = $validated['name'] ?? ($student->name . ' (Kloning ' . $newClass . ')');

        DB::beginTransaction();
        try {
            // Generate NIS unik
            $newNis = (string)(intval($student->nis ?: '14450') + $count + rand(1, 9));
            while (Student::where('nis', $newNis)->exists()) {
                $newNis = (string)(intval($newNis) + 1);
            }

            $newStudent = Student::create([
                'name' => $newName,
                'nis' => $newNis,
                'nisn' => $student->nisn ? $student->nisn . rand(1, 9) : null,
                'class' => $newClass,
                'major' => $newMajor,
                'field_of_expertise' => $student->field_of_expertise ?: 'Bisnis Manajemen dan Teknologi',
                'internship_period' => $student->internship_period,
                'academic_year' => $newYear,
                'birth_place' => $student->birth_place,
                'birth_date' => $student->birth_date,
                'gender' => $student->gender,
                'religion' => $student->religion,
                'student_address' => $student->student_address,
                'student_phone' => $student->student_phone,
                'father_name' => $student->father_name,
                'father_phone' => $student->father_phone,
                'mother_name' => $student->mother_name,
                'mother_phone' => $student->mother_phone,
                'parent_address' => $student->parent_address,
                'school_name' => $student->school_name,
                'school_address' => $student->school_address,
                'company_name' => $student->company_name,
                'company_address' => $student->company_address,
                'teacher_name' => $student->teacher_name,
                'teacher_phone' => $student->teacher_phone,
                'mentor_name' => $student->mentor_name,
                'photo_path' => null,
            ]);

            // Inisialisasi lembar kegiatan siap pakai
            $sheet1 = $newStudent->activitySheets()->create(['sheet_number' => 1, 'month_name' => 'Oktober ' . explode('/', $newYear)[0]]);
            for ($i = 1; $i <= 10; $i++) {
                $sheet1->activityLogs()->create(['row_index' => $i]);
            }

            // Inisialisasi lembar presensi (18 Hari Kerja Senin - Jumat tanpa akhir pekan)
            $att = $newStudent->attendanceSheets()->create([
                'month_name' => 'Oktober ' . explode('/', $newYear)[0],
                'total_target_days' => 18,
            ]);

            $workDays = [
                'Kamis, 01-10-2026', 'Jumat, 02-10-2026', 'Senin, 05-10-2026', 'Selasa, 06-10-2026',
                'Rabu, 07-10-2026', 'Kamis, 08-10-2026', 'Jumat, 09-10-2026', 'Senin, 12-10-2026',
                'Selasa, 13-10-2026', 'Rabu, 14-10-2026', 'Kamis, 15-10-2026', 'Jumat, 16-10-2026',
                'Senin, 19-10-2026', 'Selasa, 20-10-2026', 'Rabu, 21-10-2026', 'Kamis, 22-10-2026',
                'Jumat, 23-10-2026', 'Senin, 26-10-2026',
            ];

            foreach ($workDays as $idx => $dateStr) {
                $att->records()->create([
                    'day_number' => $idx + 1,
                    'day_date' => $dateStr,
                    'status' => 'H',
                    'notes' => null,
                    'is_signed' => true,
                ]);
            }
            $att->recalculateTotals();

            // Inisialisasi evaluasi
            $newStudent->evaluation()->create([
                'evaluation_date' => 'Jakarta, 31 Oktober ' . explode('/', $newYear)[0],
                'mentor_name' => $newStudent->mentor_name ?: 'Pembimbing Industri',
            ]);

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Siswa baru ({$newStudent->name}) berhasil dikloning untuk jurusan {$newMajor}!",
                    'student_id' => $newStudent->id,
                ]);
            }

            return redirect()->route('students.show', $newStudent->id)->with('success', "Template siswa berhasil dikloning untuk {$newMajor}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $request->expectsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 500)
                : back()->with('error', 'Gagal kloning: ' . $e->getMessage());
        }
    }

    /**
     * Mode Cetak Berkas Fisik A4 (Cover, Identitas, Uraian Kegiatan, Presensi, Penilaian)
     */
    public function printReport(Student $student): View
    {
        $student->load([
            'activitySheets.activityLogs',
            'attendanceSheets.records',
            'evaluation'
        ]);

        return view('students.print', compact('student'));
    }
}
