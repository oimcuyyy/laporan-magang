<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentApiController extends Controller
{
    /**
     * GET /api/v1/students
     * Mengambil daftar seluruh siswa dalam rekap kelompok (1-5 siswa)
     */
    public function index(): JsonResponse
    {
        $students = Student::with(['activitySheets.activityLogs', 'attendanceSheets', 'evaluation'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'total_students' => $students->count(),
            'max_capacity' => 5,
            'can_add' => $students->count() < 5,
            'data' => $students->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'nis' => $s->nis,
                    'nisn' => $s->nisn,
                    'class' => $s->class,
                    'major' => $s->major,
                    'field_of_expertise' => $s->field_of_expertise,
                    'teacher_name' => $s->teacher_name,
                    'company_name' => $s->company_name,
                    'photo_url' => $s->photo_url,
                    'activity_sheets_count' => $s->activitySheets->count(),
                    'attendance_sheets_count' => $s->attendanceSheets->count(),
                    'evaluation' => $s->evaluation ? [
                        'average_score' => $s->evaluation->average_score,
                        'final_grade' => $s->evaluation->final_grade,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * GET /api/v1/students/{student}
     * Mengambil data lengkap 5 lembar untuk satu siswa
     */
    public function show(Student $student): JsonResponse
    {
        $student->load([
            'activitySheets.activityLogs',
            'attendanceSheets.records',
            'evaluation'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'class' => $student->class,
                'major' => $student->major,
                'field_of_expertise' => $student->field_of_expertise,
                'internship_period' => $student->internship_period,
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
                'photo_url' => $student->photo_url,
                'activity_sheets' => $student->activitySheets,
                'attendance_sheets' => $student->attendanceSheets,
                'evaluation' => $student->evaluation,
            ],
        ]);
    }

    /**
     * POST /api/v1/students
     * Mendaftarkan siswa baru (maksimal 5 siswa)
     */
    public function store(Request $request): JsonResponse
    {
        $count = Student::count();
        if ($count >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Kapasitas maksimal 5 siswa telah tercapai.',
            ], 422);
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
            if ($request->hasFile('photo')) {
                $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
            }

            $student = Student::create($validated);

            // Inisialisasi Lembar Uraian Kegiatan Slide 1 (10 baris)
            $sheet = $student->activitySheets()->create([
                'sheet_number' => 1,
                'month_name' => 'Oktober 2026',
            ]);
            for ($i = 1; $i <= 10; $i++) {
                $sheet->activityLogs()->create(['row_index' => $i]);
            }

            // Inisialisasi Lembar Presensi Kehadiran
            $attSheet = $student->attendanceSheets()->create([
                'month_name' => 'Oktober 2026',
                'total_target_days' => 30,
            ]);
            for ($i = 1; $i <= 30; $i++) {
                $attSheet->records()->create(['day_number' => $i, 'status' => 'H']);
            }
            $attSheet->recalculateTotals();

            // Inisialisasi Lembar Penilaian DU/DI
            $student->evaluation()->create([
                'evaluation_date' => 'Jakarta, 31 Oktober 2026',
                'mentor_name' => $student->mentor_name ?: 'Pembimbing Industri',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Siswa {$student->name} berhasil ditambahkan!",
                'data' => $student,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat data siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/students/{student} (with _method=PUT or direct PUT)
     * Update data identitas & upload pas foto
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'nis' => 'required|string|max:30|unique:students,nis,' . $student->id,
            'nisn' => 'nullable|string|max:30',
            'class' => 'nullable|string|max:50',
            'major' => 'nullable|string|max:100',
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

        return response()->json([
            'success' => true,
            'message' => 'Identitas peserta berhasil diperbarui!',
            'data' => $student,
        ]);
    }

    /**
     * PATCH /api/v1/students/{student}/cover
     * Update Jurusan, Kelas, dan Tahun Pelajaran dari cover
     */
    public function updateCover(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'major' => 'required|string|max:100',
            'class' => 'required|string|max:50',
            'academic_year' => 'nullable|string|max:30',
            'field_of_expertise' => 'nullable|string|max:100',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jurusan, Kelas, dan Tahun Pelajaran berhasil diperbarui!',
            'data' => $student,
        ]);
    }

    /**
     * POST /api/v1/students/{student}/clone
     */
    public function cloneStudent(Request $request, Student $student): JsonResponse
    {
        $count = Student::count();
        if ($count >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Kapasitas maksimal 5 siswa kelompok sudah penuh.',
            ], 422);
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

            $sheet1 = $newStudent->activitySheets()->create(['sheet_number' => 1, 'month_name' => 'Bulan 1']);
            for ($i = 1; $i <= 10; $i++) {
                $sheet1->activityLogs()->create(['row_index' => $i]);
            }

            $att = $newStudent->attendanceSheets()->create(['month_name' => 'Bulan 1', 'total_target_days' => 30]);
            for ($i = 1; $i <= 30; $i++) {
                $att->records()->create(['day_number' => $i, 'status' => 'H']);
            }
            $att->recalculateTotals();

            $newStudent->evaluation()->create([
                'evaluation_date' => 'Jakarta, ' . date('d F Y'),
                'mentor_name' => $newStudent->mentor_name ?: 'Pembimbing Industri',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Siswa baru ({$newStudent->name}) berhasil dikloning untuk jurusan {$newMajor}!",
                'data' => $newStudent,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal kloning siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/students/{student}
     * Menghapus siswa
     */
    public function destroy(Student $student): JsonResponse
    {
        if ($student->photo_path && Storage::disk('public')->exists($student->photo_path)) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil dihapus.',
        ]);
    }
}
