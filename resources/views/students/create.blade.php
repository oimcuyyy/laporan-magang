@extends('layouts.app')

@section('title', 'Tambah Siswa Laporan Magang - SMK Negeri 20 Jakarta')

@push('styles')
<style>
    .form-card {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 2rem;
        max-width: 900px;
        margin: 0 auto;
    }

    .form-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--slate-200);
        padding-bottom: 1.25rem;
    }

    .form-header h2 {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--slate-900);
    }

    .form-header p {
        color: var(--slate-500);
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 2px solid var(--primary-light);
        padding-bottom: 0.4rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .form-group-full {
        grid-column: span 2;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--slate-700);
        margin-bottom: 0.35rem;
    }

    .form-control {
        width: 100%;
        padding: 0.6rem 0.85rem;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-sm);
        font-size: 0.9rem;
        font-family: inherit;
        background: #ffffff;
        color: var(--slate-900);
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 75px;
    }

    .photo-preview-box {
        width: 110px;
        height: 140px;
        border: 2px dashed #cbd5e1;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--slate-50);
        margin-bottom: 0.75rem;
    }

    .photo-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        border-top: 1px solid var(--slate-200);
        padding-top: 1.5rem;
        margin-top: 1.5rem;
    }

    @media (max-width: 640px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .form-group-full {
            grid-column: span 1;
        }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-header">
        <h2>Tambah Siswa Baru (Slot {{ $count + 1 }} dari 5)</h2>
        <p>Isi data identitas peserta magang sesuai berkas fisik SMK Negeri 20 Jakarta.</p>
    </div>

    <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Bagian 1: Identitas Siswa -->
        <div class="form-section">
            <h3 class="section-title">
                <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                1. Data Pribadi Siswa
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nama Siswa <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Muhammad Rochimuloh" required>
                </div>

                <div class="form-group">
                    <label for="nis">Nomor Induk Siswa (NIS) <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="nis" name="nis" class="form-control" value="{{ old('nis') }}" placeholder="Contoh: 14450" required>
                </div>

                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <input type="text" id="nisn" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="Contoh: 0081234567">
                </div>

                <div class="form-group">
                    <label for="class">Kelas <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="class" name="class" class="form-control" value="{{ old('class', 'XI') }}" required>
                </div>

                <div class="form-group">
                    <label for="major">Program / Konsentrasi Keahlian <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="major" name="major" class="form-control" value="{{ old('major', 'Rekayasa Perangkat Lunak') }}" required>
                </div>

                <div class="form-group">
                    <label for="field_of_expertise">Bidang Keahlian</label>
                    <input type="text" id="field_of_expertise" name="field_of_expertise" class="form-control" value="{{ old('field_of_expertise', 'Bisnis Manajemen dan Teknologi') }}">
                </div>

                <div class="form-group">
                    <label for="internship_period">Periode Magang</label>
                    <input type="text" id="internship_period" name="internship_period" class="form-control" value="{{ old('internship_period', '01 Oktober 2026 - 30 November 2026') }}">
                </div>

                <div class="form-group">
                    <label for="gender">Jenis Kelamin <span style="color:var(--danger)">*</span></label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="Laki-Laki" {{ old('gender') === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="birth_place">Tempat Lahir</label>
                    <input type="text" id="birth_place" name="birth_place" class="form-control" value="{{ old('birth_place', 'Jakarta') }}">
                </div>

                <div class="form-group">
                    <label for="birth_date">Tanggal Lahir</label>
                    <input type="text" id="birth_date" name="birth_date" class="form-control" value="{{ old('birth_date', '09 Desember 2008') }}" placeholder="Contoh: 09 Desember 2008">
                </div>

                <div class="form-group">
                    <label for="religion">Agama</label>
                    <input type="text" id="religion" name="religion" class="form-control" value="{{ old('religion', 'Islam') }}">
                </div>

                <div class="form-group">
                    <label for="student_phone">Nomor HP Siswa</label>
                    <input type="text" id="student_phone" name="student_phone" class="form-control" value="{{ old('student_phone') }}" placeholder="Contoh: 081314854567">
                </div>

                <div class="form-group form-group-full">
                    <label for="student_address">Alamat Siswa</label>
                    <textarea id="student_address" name="student_address" class="form-control" placeholder="Contoh: Jl. Damai IV, RT. 002 / RW. 002">{{ old('student_address') }}</textarea>
                </div>

                <div class="form-group form-group-full">
                    <label for="photo">Pas Foto Siswa (3x4)</label>
                    <div style="display:flex;gap:1.25rem;align-items:center;">
                        <div class="photo-preview-box" id="previewContainer">
                            <span style="font-size:0.75rem;color:var(--slate-400);text-align:center;">Preview Foto</span>
                        </div>
                        <div>
                            <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event)">
                            <small style="color:var(--slate-500);display:block;margin-top:0.35rem;">Format JPG, PNG, atau WEBP. Maksimal 2MB.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Data Orang Tua -->
        <div class="form-section">
            <h3 class="section-title">
                <svg class="icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                2. Data Orang Tua / Wali
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="father_name">Nama Ayah</label>
                    <input type="text" id="father_name" name="father_name" class="form-control" value="{{ old('father_name') }}" placeholder="Contoh: Mubasir">
                </div>

                <div class="form-group">
                    <label for="father_phone">Nomor HP Ayah</label>
                    <input type="text" id="father_phone" name="father_phone" class="form-control" value="{{ old('father_phone') }}">
                </div>

                <div class="form-group">
                    <label for="mother_name">Nama Ibu</label>
                    <input type="text" id="mother_name" name="mother_name" class="form-control" value="{{ old('mother_name') }}" placeholder="Contoh: Rita Murni">
                </div>

                <div class="form-group">
                    <label for="mother_phone">Nomor HP Ibu</label>
                    <input type="text" id="mother_phone" name="mother_phone" class="form-control" value="{{ old('mother_phone') }}">
                </div>

                <div class="form-group form-group-full">
                    <label for="parent_address">Alamat Orang Tua</label>
                    <textarea id="parent_address" name="parent_address" class="form-control">{{ old('parent_address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Sekolah & Tempat Praktik DU/DI -->
        <div class="form-section">
            <h3 class="section-title">
                <svg class="icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                3. Sekolah & Tempat Praktik (DU/DI)
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="school_name">Nama Sekolah <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="school_name" name="school_name" class="form-control" value="{{ old('school_name', 'SMK Negeri 20 Jakarta') }}" required>
                </div>

                <div class="form-group">
                    <label for="teacher_name">Nama Guru Pembimbing <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="teacher_name" name="teacher_name" class="form-control" value="{{ old('teacher_name', 'Kuswanto') }}" required>
                </div>

                <div class="form-group form-group-full">
                    <label for="school_address">Alamat Sekolah</label>
                    <input type="text" id="school_address" name="school_address" class="form-control" value="{{ old('school_address', 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kec. Cilandak, Kota Jaksel') }}">
                </div>

                <div class="form-group">
                    <label for="company_name">Nama Tempat Praktik (Instansi/DU-DI) <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name', 'Kantor Lurah Bintaro') }}" required>
                </div>

                <div class="form-group">
                    <label for="teacher_phone">Nomor HP Guru Pembimbing</label>
                    <input type="text" id="teacher_phone" name="teacher_phone" class="form-control" value="{{ old('teacher_phone', '0858-8192-1976') }}">
                </div>

                <div class="form-group form-group-full">
                    <label for="company_address">Alamat Tempat Praktik</label>
                    <input type="text" id="company_address" name="company_address" class="form-control" value="{{ old('company_address', 'Jl. B.C. Veteran Raya No. 1, RT 1/RW 3, Bintaro') }}">
                </div>

                <div class="form-group">
                    <label for="mentor_name">Nama Pembimbing Industri</label>
                    <input type="text" id="mentor_name" name="mentor_name" class="form-control" value="{{ old('mentor_name', 'Pembimbing Industri') }}">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan & Buat Lembar Berkas
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const container = document.getElementById('previewContainer');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.innerHTML = `<img src="${e.target.result}" alt="Preview Foto">`;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
