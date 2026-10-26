<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ActivitySheet;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSheet;
use App\Models\Evaluation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Akun Pengguna Administrator Guru SMKN 20 Jakarta
        User::firstOrCreate(
            ['email' => 'admin@smkn20.sch.id'],
            [
                'name' => 'Admin Guru SMKN 20',
                'password' => Hash::make('AdminMagang2026!'),
            ]
        );

        // 1. Siswa Utama (Sesuai Berkas Fisik Foto Asli)
        $student1 = Student::create([
            'name' => 'Muhammad Rochimuloh',
            'nis' => '14450',
            'nisn' => '0081234567',
            'class' => 'XI RPL',
            'major' => 'Rekayasa Perangkat Lunak',
            'field_of_expertise' => 'Bisnis Manajemen dan Teknologi',
            'internship_period' => '01 Oktober 2026 - 30 November 2026',
            'birth_place' => 'Jakarta',
            'birth_date' => '09 Desember 2008',
            'gender' => 'Laki-Laki',
            'religion' => 'Islam',
            'student_address' => 'Jl. Damai IV, RT. 002 / RW. 002',
            'student_phone' => '081314854567',
            'father_name' => 'Mubasir',
            'father_phone' => '081298765432',
            'mother_name' => 'Rita Murni',
            'mother_phone' => '081287654321',
            'parent_address' => 'Jl. Damai IV, RT. 002 / RW. 002',
            'school_name' => 'SMK Negeri 20 Jakarta',
            'school_address' => 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kec. Cilandak, Kota Jaksel',
            'company_name' => 'Kantor Lurah Bintaro',
            'company_address' => 'Jl. B.C. Veteran Raya No. 1, RT 1/RW 3, Bintaro',
            'teacher_name' => 'Kuswanto',
            'teacher_phone' => '0858-8192-1976',
            'mentor_name' => 'Sujatmo, S.Sos',
            'photo_path' => null,
        ]);

        // Lembar Kegiatan Slide 1 (Bulan Oktober - 10 baris dari foto)
        $sheet1 = ActivitySheet::create([
            'student_id' => $student1->id,
            'sheet_number' => 1,
            'month_name' => 'Oktober 2026',
        ]);

        $logsData = [
            ['Kamis, 01-10-26', 'Perpanjang Makam', 'Membantu pegawai mengurus perpanjang makam', 'Telah dicek'],
            ['Jumat, 02-10-26', 'Perpanjang Makam', 'Membantu pegawai mengisi data di web JAKEVO dan membuat web laporan', 'Sesuai prosedur'],
            ['Senin, 05-10-26', 'Perpanjang Makam', 'Mengembangkan web laporan dan membantu pegawai mengisi formulir', 'Baik'],
            ['Selasa, 06-10-26', 'Perpanjang Makam', 'Membantu pegawai mengisi formulir di web JAKEVO dan mengembangkan web laporan', 'Baik'],
            ['Rabu, 07-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam di web JAKEVO', 'Selesai'],
            ['Kamis, 08-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui web JAKEVO', 'Selesai'],
            ['Jumat, 09-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui web JAKEVO', 'Selesai'],
            ['Senin, 12-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui JAKEVO dan mengembangkan web laporan', 'Sangat rapi'],
            ['Selasa, 13-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui JAKEVO dan mengembangkan web laporan', 'Baik'],
            ['Rabu, 14-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui JAKEVO dan mengembangkan web laporan', 'Telah disetujui'],
        ];

        foreach ($logsData as $idx => $log) {
            ActivityLog::create([
                'activity_sheet_id' => $sheet1->id,
                'row_index' => $idx + 1,
                'day_date' => $log[0],
                'division' => $log[1],
                'job_description' => $log[2],
                'supervisor_note' => $log[3],
                'is_approved' => true,
            ]);
        }

        // Lembar Kegiatan Slide 2 (Slide tambahan dinamis untuk membuktikan fitur penambahan lembar)
        $sheet2 = ActivitySheet::create([
            'student_id' => $student1->id,
            'sheet_number' => 2,
            'month_name' => 'Oktober 2026 (Lanjutan)',
        ]);

        $logsDataSheet2 = [
            ['Kamis, 15-10-26', 'Pelayanan Umum', 'Membantu registrasi administrasi kependudukan warga', 'Baik'],
            ['Jumat, 16-10-26', 'Pelayanan Umum', 'Mengarsipkan dokumen fisik dan digital', 'Selesai'],
            ['Senin, 19-10-26', 'Pelayanan Umum', 'Membantu pembuatan surat pengantar warga', 'Sesuai'],
            ['Selasa, 20-10-26', 'Pelayanan Umum', 'Melakukan pendataan berkas domisili kelurahan', 'Baik'],
            ['Rabu, 21-10-26', 'Pelayanan Umum', 'Membantu penginputan data warga di sistem JAKEVO', 'Selesai'],
            ['Kamis, 22-10-26', 'Pelayanan Umum', 'Mengelola berkas permohonan santunan kelurahan', 'Baik'],
            ['Jumat, 23-10-26', 'Pelayanan Umum', 'Melakukan verifikasi berkas perpanjangan makam', 'Selesai'],
            ['Senin, 26-10-26', 'Pelayanan Umum', 'Menginput rekapitulasi data layanan kelurahan per Oktober', 'Telah dicek'],
        ];

        foreach ($logsDataSheet2 as $idx => $log) {
            ActivityLog::create([
                'activity_sheet_id' => $sheet2->id,
                'row_index' => $idx + 1,
                'day_date' => $log[0],
                'division' => $log[1],
                'job_description' => $log[2],
                'supervisor_note' => $log[3],
                'is_approved' => true,
            ]);
        }

        // Lembar Daftar Hadir Siswa 1 (Oktober 2026 - Berjalan 18 Hari Kerja sampai 26 Oktober)
        $attSheet = AttendanceSheet::create([
            'student_id' => $student1->id,
            'month_name' => 'Oktober 2026',
            'total_target_days' => 18,
            'total_present' => 18,
            'total_sick' => 0,
            'total_permission' => 0,
            'total_absent' => 0,
        ]);

        $workDays = [
            'Kamis, 01-10-2026',
            'Jumat, 02-10-2026',
            'Senin, 05-10-2026',
            'Selasa, 06-10-2026',
            'Rabu, 07-10-2026',
            'Kamis, 08-10-2026',
            'Jumat, 09-10-2026',
            'Senin, 12-10-2026',
            'Selasa, 13-10-2026',
            'Rabu, 14-10-2026',
            'Kamis, 15-10-2026',
            'Jumat, 16-10-2026',
            'Senin, 19-10-2026',
            'Selasa, 20-10-2026',
            'Rabu, 21-10-2026',
            'Kamis, 22-10-2026',
            'Jumat, 23-10-2026',
            'Senin, 26-10-2026',
        ];

        foreach ($workDays as $idx => $dateStr) {
            AttendanceRecord::create([
                'attendance_sheet_id' => $attSheet->id,
                'day_number' => $idx + 1,
                'day_date' => $dateStr,
                'status' => 'H', // Hadir terus
                'notes' => null, // Kalo hadir tidak usah pakai keterangan
                'is_signed' => true,
            ]);
        }
        $attSheet->recalculateTotals();

        // Penilaian Siswa 1
        Evaluation::create([
            'student_id' => $student1->id,
            'aspect_a_score' => 94.00,
            'aspect_a_grade' => 'A',
            'aspect_b_score' => 90.00,
            'aspect_b_grade' => 'B',
            'aspect_c_score' => 95.00,
            'aspect_c_grade' => 'A',
            'average_score' => 93.00,
            'final_grade' => 'A',
            'evaluation_date' => 'Jakarta, 31 Oktober 2026',
            'mentor_name' => 'Sujatmo, S.Sos',
        ]);

        // 2. Siswa ke-2: Ahmad Fauzi
        $student2 = Student::create([
            'name' => 'Ahmad Fauzi',
            'nis' => '14451',
            'nisn' => '0081234568',
            'class' => 'XI',
            'major' => 'Rekayasa Perangkat Lunak',
            'field_of_expertise' => 'Bisnis Manajemen dan Teknologi',
            'internship_period' => '01 Oktober 2026 - 30 November 2026',
            'birth_place' => 'Jakarta',
            'birth_date' => '14 Februari 2008',
            'gender' => 'Laki-Laki',
            'religion' => 'Islam',
            'student_address' => 'Jl. Bintaro Raya No. 12',
            'student_phone' => '081399887766',
            'father_name' => 'H. Suryadi',
            'school_name' => 'SMK Negeri 20 Jakarta',
            'school_address' => 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kota Jaksel',
            'company_name' => 'Kantor Lurah Bintaro',
            'company_address' => 'Jl. B.C. Veteran Raya No. 1, Bintaro',
            'teacher_name' => 'Kuswanto',
            'teacher_phone' => '0858-8192-1976',
            'mentor_name' => 'Sujatmo, S.Sos',
        ]);

        $sheetS2 = ActivitySheet::create(['student_id' => $student2->id, 'sheet_number' => 1, 'month_name' => 'Oktober 2026']);
        for ($i = 1; $i <= 5; $i++) {
            ActivityLog::create([
                'activity_sheet_id' => $sheetS2->id,
                'row_index' => $i,
                'day_date' => "Hari ke-$i",
                'division' => 'Jaringan & TI',
                'job_description' => 'Membantu maintenance jaringan komputer kelurahan',
                'supervisor_note' => 'Baik',
                'is_approved' => true,
            ]);
        }
        $attS2 = AttendanceSheet::create(['student_id' => $student2->id, 'month_name' => 'Oktober 2026', 'total_target_days' => 30]);
        for ($i = 1; $i <= 30; $i++) {
            AttendanceRecord::create([
                'attendance_sheet_id' => $attS2->id,
                'day_number' => $i,
                'day_date' => "Hari-$i",
                'status' => 'H',
                'is_signed' => true,
            ]);
        }
        $attS2->recalculateTotals();

        Evaluation::create([
            'student_id' => $student2->id,
            'aspect_a_score' => 88.00,
            'aspect_a_grade' => 'B',
            'aspect_b_score' => 85.00,
            'aspect_b_grade' => 'B',
            'aspect_c_score' => 90.00,
            'aspect_c_grade' => 'B',
            'average_score' => 87.67,
            'final_grade' => 'B',
            'evaluation_date' => 'Jakarta, 31 Oktober 2026',
            'mentor_name' => 'Sujatmo, S.Sos',
        ]);

        // 3. Siswa ke-3: Siti Rahmawati
        $student3 = Student::create([
            'name' => 'Siti Rahmawati',
            'nis' => '14452',
            'nisn' => '0081234569',
            'class' => 'XI',
            'major' => 'Rekayasa Perangkat Lunak',
            'field_of_expertise' => 'Bisnis Manajemen dan Teknologi',
            'internship_period' => '01 Oktober 2026 - 30 November 2026',
            'birth_place' => 'Jakarta',
            'birth_date' => '22 Agustus 2008',
            'gender' => 'Perempuan',
            'religion' => 'Islam',
            'student_address' => 'Jl. Veteran No. 45',
            'student_phone' => '081377665544',
            'father_name' => 'Rahmat Hidayat',
            'school_name' => 'SMK Negeri 20 Jakarta',
            'school_address' => 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kota Jaksel',
            'company_name' => 'Kantor Lurah Bintaro',
            'company_address' => 'Jl. B.C. Veteran Raya No. 1, Bintaro',
            'teacher_name' => 'Kuswanto',
            'teacher_phone' => '0858-8192-1976',
            'mentor_name' => 'Sujatmo, S.Sos',
        ]);
        $sheetS3 = ActivitySheet::create(['student_id' => $student3->id, 'sheet_number' => 1, 'month_name' => 'Oktober 2026']);
        for ($i = 1; $i <= 4; $i++) {
            ActivityLog::create([
                'activity_sheet_id' => $sheetS3->id,
                'row_index' => $i,
                'day_date' => "Hari ke-$i",
                'division' => 'Sekretariat',
                'job_description' => 'Membantu penataan arsip surat masuk dan keluar',
                'supervisor_note' => 'Rapi',
                'is_approved' => true,
            ]);
        }
        $attS3 = AttendanceSheet::create(['student_id' => $student3->id, 'month_name' => 'Oktober 2026', 'total_target_days' => 30]);
        for ($i = 1; $i <= 30; $i++) {
            AttendanceRecord::create([
                'attendance_sheet_id' => $attS3->id,
                'day_number' => $i,
                'day_date' => "Hari-$i",
                'status' => 'H',
                'is_signed' => true,
            ]);
        }
        $attS3->recalculateTotals();

        Evaluation::create([
            'student_id' => $student3->id,
            'aspect_a_score' => 95.00,
            'aspect_a_grade' => 'A',
            'aspect_b_score' => 93.00,
            'aspect_b_grade' => 'A',
            'aspect_c_score' => 96.00,
            'aspect_c_grade' => 'A',
            'average_score' => 94.67,
            'final_grade' => 'A',
            'evaluation_date' => 'Jakarta, 31 Oktober 2026',
            'mentor_name' => 'Sujatmo, S.Sos',
        ]);
    }
}
