@extends('layouts.app')

@section('title', 'Rekap Kelompok Laporan Magang - SMK Negeri 20 Jakarta')

@push('styles')
<style>
    .dashboard-header {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        padding: 1.75rem 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
    }

    .header-content h2 {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--slate-900);
    }

    .header-content p {
        color: var(--slate-500);
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }

    .meta-pills {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }

    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.25rem 0.65rem;
        background: var(--slate-100);
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        color: var(--slate-700);
        font-weight: 600;
    }

    /* Grid Siswa */
    .students-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(370px, 1fr));
        gap: 1.5rem;
    }

    .student-card {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        transition: all 0.2s ease-in-out;
        display: flex;
        flex-direction: column;
    }

    .student-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        border-color: #cbd5e1;
    }

    .card-top {
        padding: 1.5rem;
        border-bottom: 1px solid var(--slate-100);
        display: flex;
        gap: 1.15rem;
        align-items: flex-start;
    }

    .student-avatar {
        width: 68px;
        height: 85px;
        border-radius: var(--radius-sm);
        object-fit: cover;
        background: var(--slate-100);
        border: 2px solid #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: 700;
        color: var(--slate-400);
        font-size: 0.8rem;
        text-align: center;
        padding: 4px;
    }

    .card-info {
        flex: 1;
        min-width: 0;
    }

    .student-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--slate-900);
        line-height: 1.3;
        margin-bottom: 0.25rem;
    }

    .student-nis {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        color: var(--slate-500);
        margin-bottom: 0.5rem;
    }

    .student-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    /* Progress & Section Badges */
    .card-body {
        padding: 1.25rem 1.5rem;
        flex: 1;
        background: #fafafa;
        border-bottom: 1px solid var(--slate-100);
    }

    .sheet-stats-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: var(--slate-600);
    }

    .stat-label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .stat-value {
        font-weight: 700;
        color: var(--slate-900);
    }

    /* Card Footer Buttons */
    .card-footer {
        padding: 1rem 1.5rem;
        background: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }

    /* Add Slot Card */
    .add-slot-card {
        background: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: var(--radius-lg);
        min-height: 320px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem;
        text-decoration: none;
        color: var(--slate-600);
        transition: all 0.2s ease;
    }

    .add-slot-card:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .add-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--slate-100);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: var(--slate-500);
        transition: all 0.2s;
    }

    .add-slot-card:hover .add-icon-circle {
        background: var(--primary);
        color: #ffffff;
    }

    /* Responsive Mobile Layout for Students List */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.25rem 1rem;
            margin-bottom: 1.25rem;
        }
        .header-content h2 {
            font-size: 1.25rem;
        }
        .students-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .card-top {
            padding: 1.15rem;
            gap: 0.85rem;
        }
        .student-avatar {
            width: 58px;
            height: 72px;
        }
        .card-body {
            padding: 1rem 1.15rem;
        }
        .card-footer {
            padding: 0.85rem 1.15rem;
            flex-direction: column;
            gap: 0.65rem;
        }
        .card-footer .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<!-- Dashboard Banner -->
<div class="dashboard-header">
    <div class="header-content">
        <h2>Rekapitulasi Laporan Magang</h2>
        <p>Kelas Industri SMK Negeri 20 Jakarta &bull; Bidang Bisnis Manajemen dan Teknologi</p>
        <div class="meta-pills">
            <span class="meta-pill">
                <svg class="icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Kapasitas: {{ $totalStudents }} dari 5 Siswa
            </span>
            <span class="meta-pill">
                <svg class="icon" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                5 Lembar Berkas Lengkap
            </span>
            <span class="meta-pill">
                <svg class="icon" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Supabase PostgreSQL
            </span>
        </div>
    </div>

    @if($canAddStudent)
        <a href="{{ route('students.create') }}" class="btn btn-primary">
            <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Siswa (Slot {{ $totalStudents + 1 }}/5)
        </a>
    @endif
</div>

<!-- Grid Daftar Siswa (1 - 5 Siswa) -->
<div class="students-grid">
    @foreach($students as $student)
        <div class="student-card">
            <!-- Top Profile -->
            <div class="card-top">
                @if($student->photo_url)
                    <img src="{{ $student->photo_url }}" alt="Foto {{ $student->name }}" class="student-avatar">
                @else
                    <div class="student-avatar">
                        Pas Foto<br>3x4
                    </div>
                @endif
                <div class="card-info">
                    <h3 class="student-name">{{ $student->name }}</h3>
                    <p class="student-nis">NIS: {{ $student->nis }} @if($student->nisn) &bull; NISN: {{ $student->nisn }} @endif</p>
                    <div class="student-tags">
                        <span class="badge badge-blue">Kelas {{ $student->class }}</span>
                        <span class="badge badge-gray">{{ $student->major }}</span>
                    </div>
                </div>
            </div>

            <!-- Body: Ringkasan 5 Lembar Berkas -->
            <div class="card-body">
                <div class="sheet-stats-list">
                    <div class="stat-row">
                        <span class="stat-label">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Guru Pembimbing:
                        </span>
                        <span class="stat-value">{{ $student->teacher_name ?: '-' }}</span>
                    </div>

                    <div class="stat-row">
                        <span class="stat-label">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            Tempat Praktik:
                        </span>
                        <span class="stat-value">{{ $student->company_name ?: '-' }}</span>
                    </div>

                    <div class="stat-row">
                        <span class="stat-label">
                            <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Uraian Kegiatan:
                        </span>
                        <span class="stat-value">
                            {{ $student->activitySheets->count() }} Slide Lembar
                            <span class="badge badge-blue">{{ $student->activitySheets->sum(fn($s) => $s->activityLogs->whereNotNull('job_description')->count()) }} Terisi</span>
                        </span>
                    </div>

                    <div class="stat-row">
                        <span class="stat-label">
                            <svg class="icon" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            Presensi / Kehadiran:
                        </span>
                        <span class="stat-value">
                            {{ $student->attendanceSheets->sum('total_present') }} Hari Hadir
                        </span>
                    </div>

                    <div class="stat-row">
                        <span class="stat-label">
                            <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                            Nilai DU/DI:
                        </span>
                        <span class="stat-value">
                            @if($student->evaluation && $student->evaluation->average_score)
                                <span class="badge badge-green">{{ number_format($student->evaluation->average_score, 1) }} (Predikat {{ $student->evaluation->final_grade }})</span>
                            @else
                                <span class="badge badge-amber">Belum Dinilai</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="card-footer">
                <a href="{{ route('students.show', $student->id) }}" class="btn btn-primary btn-sm" style="flex:1;">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Buka Berkas
                </a>

                <a href="{{ route('students.print', $student->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Mode Cetak Fisik A4">
                    <svg class="icon" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Cetak A4
                </a>

                <form action="{{ route('students.destroy', $student->id) }}" method="POST" onsubmit="return confirmDelete(event, this, 'Hapus Siswa {{ $student->name }}?', 'Seluruh berkas laporan magang (5 lembar) siswa ini akan dihapus permanen.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Siswa">
                        <svg class="icon" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </form>
            </div>
        </div>
    @endforeach

    <!-- Slot Kosong Jika Siswa Kurang dari 5 -->
    @if($canAddStudent)
        <a href="{{ route('students.create') }}" class="add-slot-card">
            <div class="add-icon-circle">
                <svg class="icon" style="width:28px;height:28px;" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <strong style="font-size:1.05rem;color:var(--slate-800);">Tambah Siswa Anggota</strong>
            <p style="font-size:0.85rem;margin-top:0.35rem;color:var(--slate-500);">Slot ke-{{ $totalStudents + 1 }} dari maksimal 5 siswa kelompok magang</p>
        </a>
    @endif
</div>
@endsection
