-- =========================================================================
-- SUPABASE POSTGRESQL SCHEMA FOR LAPORAN MAGANG
-- Website Laporan Kegiatan Magang (SMK Negeri 20 Jakarta)
-- Dilengkapi dengan ROW LEVEL SECURITY (RLS) & Kebijakan Akses (Policies)
-- Siap dieksekusi langsung di Supabase SQL Editor
-- =========================================================================

BEGIN;

-- -------------------------------------------------------------------------
-- 1. STRUKTUR TABEL
-- -------------------------------------------------------------------------

-- 1.1 Tabel Siswa (Mendukung 1-5 Siswa per Rekapitulasi)
CREATE TABLE IF NOT EXISTS public.students (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    nis VARCHAR(30) UNIQUE NOT NULL,
    nisn VARCHAR(30),
    class VARCHAR(50) DEFAULT 'XI',
    major VARCHAR(100) DEFAULT 'Rekayasa Perangkat Lunak',
    field_of_expertise VARCHAR(100) DEFAULT 'Bisnis Manajemen dan Teknologi',
    internship_period VARCHAR(100) DEFAULT '01 Oktober 2026 - 31 Desember 2026',
    birth_place VARCHAR(100) DEFAULT NULL,
    birth_date VARCHAR(50) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT 'Laki-Laki',
    religion VARCHAR(30) DEFAULT 'Islam',
    student_address TEXT DEFAULT NULL,
    student_phone VARCHAR(30) DEFAULT NULL,
    
    -- Data Orang Tua
    father_name VARCHAR(100) DEFAULT NULL,
    father_phone VARCHAR(30) DEFAULT NULL,
    mother_name VARCHAR(100) DEFAULT NULL,
    mother_phone VARCHAR(30) DEFAULT NULL,
    parent_address TEXT DEFAULT NULL,
    
    -- Sekolah & Industri (DU/DI)
    school_name VARCHAR(150) DEFAULT 'SMK Negeri 20 Jakarta',
    school_address TEXT DEFAULT 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kec. Cilandak, Kota Jaksel',
    company_name VARCHAR(150) DEFAULT 'Kantor Lurah Bintaro',
    company_address TEXT DEFAULT 'Jl. B.C. Veteran Raya No. 1, RT 1/RW 3, Bintaro',
    teacher_name VARCHAR(100) DEFAULT 'Kuswanto',
    teacher_phone VARCHAR(30) DEFAULT '0858-8192-1976',
    mentor_name VARCHAR(100) DEFAULT 'Sujatmo, S.Sos',
    photo_path VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 1.2 Tabel Slide Lembar Uraian Kegiatan (Bisa ditambah lembar slide bila > 10 baris)
CREATE TABLE IF NOT EXISTS public.activity_sheets (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT REFERENCES public.students(id) ON DELETE CASCADE,
    sheet_number INT NOT NULL DEFAULT 1,
    month_name VARCHAR(50) DEFAULT 'Oktober 2026',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, sheet_number)
);

-- 1.3 Tabel Detail Log Kegiatan Harian (1-10 Baris per Lembar/Slide)
CREATE TABLE IF NOT EXISTS public.activity_logs (
    id BIGSERIAL PRIMARY KEY,
    activity_sheet_id BIGINT REFERENCES public.activity_sheets(id) ON DELETE CASCADE,
    row_index SMALLINT NOT NULL, -- Baris 1 sampai 10
    day_date VARCHAR(50),        -- Contoh: 'Kamis, 01-10-26'
    division VARCHAR(100),       -- Contoh: 'Perpanjang Makam'
    job_description TEXT,        -- Uraian Pekerjaan Harian
    supervisor_note VARCHAR(100),
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(activity_sheet_id, row_index)
);

-- 1.4 Tabel Lembar Presensi / Daftar Hadir Bulanan (Bisa ditambah lembar bulan baru)
CREATE TABLE IF NOT EXISTS public.attendance_sheets (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT REFERENCES public.students(id) ON DELETE CASCADE,
    month_name VARCHAR(50) DEFAULT 'Oktober 2026',
    total_target_days INT DEFAULT 30,
    total_present INT DEFAULT 0,
    total_sick INT DEFAULT 0,
    total_permission INT DEFAULT 0,
    total_absent INT DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 1.5 Tabel Detail Record Kehadiran Harian (Hari ke-1 s/d 30)
CREATE TABLE IF NOT EXISTS public.attendance_records (
    id BIGSERIAL PRIMARY KEY,
    attendance_sheet_id BIGINT REFERENCES public.attendance_sheets(id) ON DELETE CASCADE,
    day_number SMALLINT NOT NULL, -- 1 sampai 30
    day_date VARCHAR(50),
    status CHAR(1), -- 'H' (Hadir), 'S' (Sakit), 'I' (Izin), 'A' (Alpa)
    notes VARCHAR(150),
    is_signed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(attendance_sheet_id, day_number)
);

-- 1.6 Tabel Laporan Penilaian Magang DU/DI
CREATE TABLE IF NOT EXISTS public.evaluations (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT UNIQUE REFERENCES public.students(id) ON DELETE CASCADE,
    aspect_a_score NUMERIC(5,2),
    aspect_a_grade VARCHAR(5),
    aspect_b_score NUMERIC(5,2),
    aspect_b_grade VARCHAR(5),
    aspect_c_score NUMERIC(5,2),
    aspect_c_grade VARCHAR(5),
    average_score NUMERIC(5,2),
    final_grade VARCHAR(5),
    evaluation_date VARCHAR(50) DEFAULT 'Jakarta, 31 Oktober 2026',
    mentor_name VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 1.7 Tabel Web Application Firewall (WAF) Logs
CREATE TABLE IF NOT EXISTS public.waf_logs (
    id BIGSERIAL PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    method VARCHAR(10) NOT NULL,
    request_url TEXT NOT NULL,
    threat_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) DEFAULT 'HIGH',
    matched_pattern TEXT NOT NULL,
    blocked_payload TEXT,
    user_agent VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indexes untuk Kecepatan Query
CREATE INDEX IF NOT EXISTS idx_activity_sheets_student ON public.activity_sheets(student_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_sheet ON public.activity_logs(activity_sheet_id);
CREATE INDEX IF NOT EXISTS idx_attendance_sheets_student ON public.attendance_sheets(student_id);
CREATE INDEX IF NOT EXISTS idx_attendance_records_sheet ON public.attendance_records(attendance_sheet_id);
CREATE INDEX IF NOT EXISTS idx_waf_logs_threat ON public.waf_logs(threat_type);

-- -------------------------------------------------------------------------
-- 2. AKTIFKAN ROW LEVEL SECURITY (RLS) PADA SEMUA TABEL
-- -------------------------------------------------------------------------

ALTER TABLE public.students ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.activity_sheets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.activity_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.attendance_sheets ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.attendance_records ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.evaluations ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.waf_logs ENABLE ROW LEVEL SECURITY;

-- -------------------------------------------------------------------------
-- 3. RLS POLICIES (KEBIJAKAN AKSES TERTUTUP & AMAN)
-- Hanya izinkan akses mutasi melalui backend Laravel (service_role / authenticated)
-- Blokir mutasi liar dari kunci publik (anon) melalui PostgREST
-- -------------------------------------------------------------------------

-- 3.1 Kebijakan Tabel students
DROP POLICY IF EXISTS "students_policy_all" ON public.students;
CREATE POLICY "students_service_role_policy" ON public.students
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- 3.2 Kebijakan Tabel activity_sheets
DROP POLICY IF EXISTS "activity_sheets_policy_all" ON public.activity_sheets;
CREATE POLICY "activity_sheets_service_role_policy" ON public.activity_sheets
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- 3.3 Kebijakan Tabel activity_logs
DROP POLICY IF EXISTS "activity_logs_policy_all" ON public.activity_logs;
CREATE POLICY "activity_logs_service_role_policy" ON public.activity_logs
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- 3.4 Kebijakan Tabel attendance_sheets
DROP POLICY IF EXISTS "attendance_sheets_policy_all" ON public.attendance_sheets;
CREATE POLICY "attendance_sheets_service_role_policy" ON public.attendance_sheets
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- 3.5 Kebijakan Tabel attendance_records
DROP POLICY IF EXISTS "attendance_records_policy_all" ON public.attendance_records;
CREATE POLICY "attendance_records_service_role_policy" ON public.attendance_records
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- 3.6 Kebijakan Tabel evaluations
DROP POLICY IF EXISTS "evaluations_policy_all" ON public.evaluations;
CREATE POLICY "evaluations_service_role_policy" ON public.evaluations
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- 3.7 Kebijakan Tabel waf_logs
DROP POLICY IF EXISTS "waf_logs_policy_all" ON public.waf_logs;
CREATE POLICY "waf_logs_service_role_policy" ON public.waf_logs
    FOR ALL
    TO service_role, authenticated
    USING (true)
    WITH CHECK (true);

-- -------------------------------------------------------------------------
-- 4. DATA AWAL (SEED DATA DARI FOTO BERKAS ASLI SMK 20)
-- -------------------------------------------------------------------------

-- Siswa 1: Template Berkas SMKN 20
INSERT INTO public.students (
    id, name, nis, nisn, class, major, field_of_expertise, internship_period,
    birth_place, birth_date, gender, religion, student_address, student_phone,
    father_name, father_phone, mother_name, mother_phone, parent_address,
    school_name, school_address, company_name, company_address,
    teacher_name, teacher_phone, mentor_name
) VALUES (
    1, 'Nama Siswa / Nama Anda', '14450', '0081234567', 'XI RPL', 'Rekayasa Perangkat Lunak',
    'Bisnis Manajemen dan Teknologi', '01 Oktober 2026 - 31 Desember 2026',
    'Jakarta', '01 Januari 2008', 'Laki-Laki', 'Islam', 'Jl. Alamat Tempat Tinggal Siswa RT/RW', '08xxxxxxxxxx',
    'Nama Ayah / Wali', '08xxxxxxxxxx', 'Nama Ibu / Wali', '08xxxxxxxxxx', 'Jl. Alamat Orang Tua / Wali RT/RW',
    'SMK Negeri 20 Jakarta', 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kec. Cilandak, Kota Jaksel',
    'Kantor Lurah Bintaro', 'Jl. B.C. Veteran Raya No. 1, RT 1/RW 3, Bintaro',
    'Kuswanto, M.Kom', '0858-8192-1976', 'Sujatmo, S.Sos'
) ON CONFLICT (id) DO NOTHING;

-- Slide 1: Uraian Kegiatan Bulan Oktober 2026 (10 Baris sesuai Foto 3)
INSERT INTO public.activity_sheets (id, student_id, sheet_number, month_name)
VALUES (1, 1, 1, 'Oktober 2026')
ON CONFLICT (student_id, sheet_number) DO NOTHING;

INSERT INTO public.activity_logs (activity_sheet_id, row_index, day_date, division, job_description, supervisor_note, is_approved)
VALUES
(1, 1, 'Kamis, 01-10-26', 'Perpanjang Makam', 'Membantu pegawai mengurus perpanjang makam', 'Telah dicek', true),
(1, 2, 'Jumat, 02-10-26', 'Perpanjang Makam', 'Membantu pegawai mengisi data di web JAKEVO dan membuat web laporan', 'Sesuai prosedur', true),
(1, 3, 'Senin, 05-10-26', 'Perpanjang Makam', 'Mengembangkan web laporan dan membantu pegawai mengisi formulir', 'Baik', true),
(1, 4, 'Selasa, 06-10-26', 'Perpanjang Makam', 'Membantu pegawai mengisi formulir di web JAKEVO dan mengembangkan web laporan', 'Baik', true),
(1, 5, 'Rabu, 07-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam di web JAKEVO', 'Selesai', true),
(1, 6, 'Kamis, 08-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui web JAKEVO', 'Selesai', true),
(1, 7, 'Jumat, 09-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui web JAKEVO', 'Selesai', true),
(1, 8, 'Senin, 12-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui JAKEVO dan mengembangkan web laporan', 'Sangat rapi', true),
(1, 9, 'Selasa, 13-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui JAKEVO dan mengembangkan web laporan', 'Baik', true),
(1, 10, 'Rabu, 14-10-26', 'Perpanjang Makam', 'Mengisi/menginput perpanjangan makam melalui JAKEVO dan mengembangkan web laporan', 'Telah disetujui', true)
ON CONFLICT (activity_sheet_id, row_index) DO NOTHING;

-- Slide 2: Uraian Kegiatan Lanjutan (Berjalan sampai 26 Oktober 2026, Baris 9-10 Kosong)
INSERT INTO public.activity_sheets (id, student_id, sheet_number, month_name)
VALUES (2, 1, 2, 'Oktober 2026 (Lembar Lanjutan)')
ON CONFLICT (student_id, sheet_number) DO NOTHING;

INSERT INTO public.activity_logs (activity_sheet_id, row_index, day_date, division, job_description, supervisor_note, is_approved)
VALUES
(2, 1, 'Kamis, 15-10-26', 'Pelayanan Umum', 'Membantu registrasi administrasi kependudukan warga', 'Baik', true),
(2, 2, 'Jumat, 16-10-26', 'Pelayanan Umum', 'Mengarsipkan dokumen fisik dan digital', 'Selesai', true),
(2, 3, 'Senin, 19-10-26', 'Pelayanan Umum', 'Membantu pembuatan surat pengantar warga', 'Sesuai', true),
(2, 4, 'Selasa, 20-10-26', 'Pelayanan Umum', 'Melakukan pendataan berkas domisili kelurahan', 'Baik', true),
(2, 5, 'Rabu, 21-10-26', 'Pelayanan Umum', 'Membantu penginputan data warga di sistem JAKEVO', 'Selesai', true),
(2, 6, 'Kamis, 22-10-26', 'Pelayanan Umum', 'Mengelola berkas permohonan santunan kelurahan', 'Baik', true),
(2, 7, 'Jumat, 23-10-26', 'Pelayanan Umum', 'Melakukan verifikasi berkas perpanjangan makam', 'Selesai', true),
(2, 8, 'Senin, 26-10-26', 'Pelayanan Umum', 'Menginput rekapitulasi data layanan kelurahan per Oktober', 'Telah dicek', true)
ON CONFLICT (activity_sheet_id, row_index) DO NOTHING;

-- Presensi Bulan Oktober 2026 (Berjalan sampai tanggal 26 Oktober 2026)
INSERT INTO public.attendance_sheets (id, student_id, month_name, total_target_days, total_present, total_sick, total_permission, total_absent)
VALUES (1, 1, 'Oktober 2026', 30, 16, 1, 1, 0)
ON CONFLICT (id) DO NOTHING;

-- Sisipkan 30 baris absensi (Tanggal 1 s/d 26 terisi, Tanggal 27 s/d 30 NULL / Belum Berjalan)
INSERT INTO public.attendance_records (attendance_sheet_id, day_number, day_date, status, notes, is_signed)
VALUES
(1, 1, 'Kamis, 01-10-26', 'H', 'Hadir tepat waktu', true),
(1, 2, 'Jumat, 02-10-26', 'H', 'Hadir tepat waktu', true),
(1, 3, 'Sabtu, 03-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 4, 'Minggu, 04-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 5, 'Senin, 05-10-26', 'H', 'Hadir tepat waktu', true),
(1, 6, 'Selasa, 06-10-26', 'H', 'Hadir tepat waktu', true),
(1, 7, 'Rabu, 07-10-26', 'H', 'Hadir tepat waktu', true),
(1, 8, 'Kamis, 08-10-26', 'H', 'Hadir tepat waktu', true),
(1, 9, 'Jumat, 09-10-26', 'H', 'Hadir tepat waktu', true),
(1, 10, 'Sabtu, 10-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 11, 'Minggu, 11-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 12, 'Senin, 12-10-26', 'H', 'Hadir tepat waktu', true),
(1, 13, 'Selasa, 13-10-26', 'S', 'Surat dokter terlampir', false),
(1, 14, 'Rabu, 14-10-26', 'H', 'Hadir tepat waktu', true),
(1, 15, 'Kamis, 15-10-26', 'H', 'Hadir tepat waktu', true),
(1, 16, 'Jumat, 16-10-26', 'H', 'Hadir tepat waktu', true),
(1, 17, 'Sabtu, 17-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 18, 'Minggu, 18-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 19, 'Senin, 19-10-26', 'H', 'Hadir tepat waktu', true),
(1, 20, 'Selasa, 20-10-26', 'H', 'Hadir tepat waktu', true),
(1, 21, 'Rabu, 21-10-26', 'I', 'Izin urusan keluarga', false),
(1, 22, 'Kamis, 22-10-26', 'H', 'Hadir tepat waktu', true),
(1, 23, 'Jumat, 23-10-26', 'H', 'Hadir tepat waktu', true),
(1, 24, 'Sabtu, 24-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 25, 'Minggu, 25-10-26', NULL, 'Libur Akhir Pekan', false),
(1, 26, 'Senin, 26-10-26', 'H', 'Hadir tepat waktu', true),
(1, 27, 'Selasa, 27-10-26', NULL, 'Belum Berjalan', false),
(1, 28, 'Rabu, 28-10-26', NULL, 'Belum Berjalan', false),
(1, 29, 'Kamis, 29-10-26', NULL, 'Belum Berjalan', false),
(1, 30, 'Jumat, 30-10-26', NULL, 'Belum Berjalan', false)
ON CONFLICT (attendance_sheet_id, day_number) DO NOTHING;

-- Penilaian DU/DI Sesuai Foto 5 (Nilai Softskill & Teknis)
INSERT INTO public.evaluations (
    student_id, aspect_a_score, aspect_a_grade, aspect_b_score, aspect_b_grade,
    aspect_c_score, aspect_c_grade, average_score, final_grade, evaluation_date, mentor_name
) VALUES (
    1, 95.00, 'A', 90.00, 'B', 92.00, 'A', 92.33, 'A',
    'Jakarta, 31 Oktober 2026', 'Sujatmo, S.Sos'
) ON CONFLICT (student_id) DO NOTHING;

-- Update sequences agar auto-increment berikutnya tidak konflik
SELECT setval('students_id_seq', COALESCE((SELECT MAX(id) FROM public.students), 1));
SELECT setval('activity_sheets_id_seq', COALESCE((SELECT MAX(id) FROM public.activity_sheets), 1));
SELECT setval('activity_logs_id_seq', COALESCE((SELECT MAX(id) FROM public.activity_logs), 1));
SELECT setval('attendance_sheets_id_seq', COALESCE((SELECT MAX(id) FROM public.attendance_sheets), 1));
SELECT setval('attendance_records_id_seq', COALESCE((SELECT MAX(id) FROM public.attendance_records), 1));
SELECT setval('evaluations_id_seq', COALESCE((SELECT MAX(id) FROM public.evaluations), 1));

COMMIT;
