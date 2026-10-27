<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Berkas Laporan Magang - {{ $student->name }} (SMK Negeri 20 Jakarta)</title>
    <!-- Immediate URL Integrity & Tampering Shield -->
    <script>
        (function() {
            function checkPrintUrlTampering() {
                var hash = window.location.hash;
                var search = window.location.search;
                if (hash || search) {
                    if (document.documentElement) document.documentElement.style.display = 'none';
                    var fullUrl = window.location.href;
                    var detail = 'Tautan cetak berkas fisik tidak mengizinkan penambahan parameter atau anchor apapun.';
                    window.location.replace('/tampered-url?url=' + encodeURIComponent(fullUrl) + '&detail=' + encodeURIComponent(detail));
                }
            }
            checkPrintUrlTampering();
            window.addEventListener('hashchange', checkPrintUrlTampering);
            window.addEventListener('popstate', checkPrintUrlTampering);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #e2e8f0;
            color: #000000;
            line-height: 1.35;
            font-size: 11pt;
        }

        .print-toolbar {
            position: fixed;
            top: 15px;
            right: 20px;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            padding: 8px 14px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(15, 23, 42, 0.05);
        }

        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        }

        .btn-print:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        .btn-download {
            background: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25);
        }

        .btn-download:hover {
            background: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        }

        .btn-download:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-close {
            background: #f8fafc;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            font-size: 13.5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .spin-icon {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        /* Lembar A4 Fisik */
        .page-a4 {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            padding: 16mm 18mm 16mm 22mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            html, body {
                width: 210mm;
                height: auto;
                background: #ffffff;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-toolbar {
                display: none !important;
            }
            .page-a4 {
                width: 100% !important;
                height: 270mm !important;
                min-height: 270mm !important;
                max-height: 270mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                overflow: hidden !important;
            }
        }

        /* ---------------------------------------------------- */
        /* LEMBAR 1: COVER                                      */
        /* ---------------------------------------------------- */
        .cover-page {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            min-height: 100%;
            position: relative;
            box-sizing: border-box;
            padding-bottom: 2mm;
        }

        .cover-top-section {
            width: 100%;
        }

        .cover-top-corner {
            position: absolute;
            top: -12mm;
            left: -15mm;
            width: 140px;
            height: 140px;
            pointer-events: none;
            z-index: 1;
        }

        .cover-bottom-corner {
            position: absolute;
            bottom: -8mm;
            right: -10mm;
            width: 155px;
            height: 155px;
            pointer-events: none;
            z-index: 1;
        }

        .cover-logos-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px;
            position: relative;
            z-index: 2;
        }

        .cover-main-titles {
            text-align: center;
            margin-top: 8mm;
            position: relative;
            z-index: 2;
        }

        .cover-main-titles h1 {
            font-size: 29pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 2px;
            line-height: 1.1;
        }

        .cover-main-titles h2 {
            font-size: 17.5pt;
            font-weight: 900;
            color: #e11d48;
            letter-spacing: 1px;
            margin-top: 5px;
            margin-bottom: 16px;
        }

        .cover-school-text {
            font-size: 16.5pt;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 1px;
        }

        .cover-academic-year {
            font-size: 14.5pt;
            font-weight: 900;
            color: #0f172a;
            margin-top: 4px;
        }

        .cover-middle-section {
            width: 100%;
            margin: auto 0;
            position: relative;
            z-index: 2;
        }

        .cover-field-text {
            text-align: right;
            padding-right: 5mm;
        }

        .cover-field-text div {
            font-size: 13.5pt;
            font-weight: 900;
            color: #e11d48;
            line-height: 1.25;
        }

        .cover-bottom-section {
            width: 100%;
            margin-top: auto;
            position: relative;
            z-index: 2;
        }

        .cover-student-table {
            width: 100%;
            font-size: 11.5pt;
            font-weight: 600;
        }

        .cover-student-table td {
            padding: 4.5px 6px;
        }

        .dotted-line {
            border-bottom: 1.5px dotted #000000;
            font-weight: 700;
        }

        /* ---------------------------------------------------- */
        /* LEMBAR 2: IDENTITAS PESERTA                          */
        /* ---------------------------------------------------- */
        .page-title {
            text-align: center;
            font-size: 13.5pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 6mm;
            text-decoration: underline;
        }

        .identity-table {
            width: 100%;
            font-size: 9.5pt;
            border-collapse: separate;
            border-spacing: 0 4px;
        }

        .identity-table td {
            vertical-align: top;
            padding: 2px 4px;
        }

        .dot-fill {
            border-bottom: 1px dotted #475569;
            padding-bottom: 2px;
            font-weight: 600;
        }

        .photo-box-physical {
            width: 30mm;
            height: 40mm;
            border: 1px solid #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8pt;
            color: #64748b;
            overflow: hidden;
        }

        .photo-box-physical img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ---------------------------------------------------- */
        /* LEMBAR 3: URAIAN KEGIATAN MAGANG (10 BARIS TERSTRUKTUR) */
        /* ---------------------------------------------------- */
        .table-activity {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.8pt;
            margin-top: 3mm;
        }

        .table-activity th,
        .table-activity td {
            border: 1px solid #000000;
            padding: 5px 6px;
            box-sizing: border-box;
        }

        .table-activity th {
            text-align: center;
            font-weight: 700;
            background: #f8fafc;
            height: 32px;
        }

        .table-activity tr.activity-row {
            height: 18.5mm;
        }

        /* ---------------------------------------------------- */
        /* LEMBAR 4: DAFTAR HADIR MAGANG                        */
        /* ---------------------------------------------------- */
        .table-attendance {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 2mm;
        }

        .table-attendance th,
        .table-attendance td {
            border: 1px solid #000000;
            padding: 1.8px 3px;
            vertical-align: middle;
            line-height: 1.15;
            box-sizing: border-box;
        }

        .table-attendance th {
            text-align: center;
            font-weight: 700;
            background: #f8fafc;
        }

        /* ---------------------------------------------------- */
        /* LEMBAR 5: TABEL RESMI PENILAIAN                      */
        /* ---------------------------------------------------- */
        .table-official {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 3mm;
        }

        .table-official th,
        .table-official td {
            border: 1px solid #000000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .table-official th {
            text-align: center;
            font-weight: 700;
            background: #f8fafc;
        }
    </style>
</head>
<body>
@php
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $todayIndo = now()->format('j') . ' ' . ($bulanIndo[(int) now()->format('n')] ?? now()->format('F')) . ' ' . now()->format('Y');
@endphp
    <!-- Floating Toolbar -->
    <div class="print-toolbar" id="mainPrintToolbar">
        <a href="{{ route('students.show', $student->id) }}" class="btn-close" title="Kembali ke halaman berkas siswa">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Web
        </a>
        <button type="button" onclick="window.print()" class="btn-print" title="Buka dialog cetak browser (bisa Simpan sebagai PDF)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Dokumen
        </button>
        <button type="button" id="btnDownloadPdf" onclick="handleDownloadPdfDirectly()" class="btn-download" title="Unduh langsung file PDF 5 lembar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span id="btnDownloadPdfText">Download PDF</span>
        </button>
    </div>

    <!-- Wrapper untuk seluruh 5 lembar A4 -->
    <div id="printDocumentWrapper">
    <!-- ==================================================== -->
    <!-- LEMBAR 1: COVER LAPORAN (FOTO 1)                     -->
    <!-- ==================================================== -->
    <div class="page-a4">
        <div class="cover-page">
            <!-- Corner SVG Graphics -->
            <svg class="cover-top-corner" viewBox="0 0 100 100">
                <path d="M0,0 L70,0 L0,70 Z" fill="#2563eb" opacity="0.2"/>
                <path d="M0,0 L45,0 L0,45 Z" fill="#1d4ed8" opacity="0.4"/>
                <rect x="15" y="-10" width="12" height="55" rx="6" transform="rotate(45 15 -10)" fill="#3b82f6"/>
            </svg>

            <svg class="cover-bottom-corner" viewBox="0 0 100 100">
                <path d="M100,100 L30,100 L100,30 Z" fill="#1e3a8a" opacity="0.2"/>
                <rect x="65" y="45" width="28" height="40" rx="8" fill="#2563eb" opacity="0.9"/>
                <rect x="55" y="60" width="30" height="35" rx="8" fill="none" stroke="#60a5fa" stroke-width="2.5"/>
            </svg>

            <!-- 1. Bagian Atas: Logo & Judul Utama -->
            <div class="cover-top-section">
                <div class="cover-logos-row">
                    <img src="{{ asset('images/logo-jayaraya.png') }}" alt="Logo Jaya Raya DKI Jakarta" style="height:85px;width:auto;max-width:85px;object-fit:contain;">
                    <img src="{{ asset('images/logo-smkn20.png') }}" alt="Logo SMK Negeri 20 Jakarta" style="height:85px;width:auto;max-width:85px;object-fit:contain;">
                </div>

                <div class="cover-main-titles">
                    <h1>LAPORAN</h1>
                    <h2>KEGIATAN KELAS INDUSTRI</h2>
                    <div class="cover-school-text">{{ strtoupper($student->school_name) }}</div>
                    <div class="cover-academic-year">TAHUN PELAJARAN {{ $student->academic_year ?: '2026/2027' }}</div>
                </div>
            </div>

            <!-- 2. Bagian Tengah: Bidang Keahlian -->
            <div class="cover-middle-section">
                <div class="cover-field-text">
                    <div>BIDANG KEAHLIAN</div>
                    <div>{{ strtoupper($student->field_of_expertise ?: 'BISNIS MANAJEMEN DAN TEKNOLOGI') }}</div>
                </div>
            </div>

            <!-- 3. Bagian Bawah: Identitas Siswa -->
            <div class="cover-bottom-section">
                <table class="cover-student-table">
                    <tr>
                        <td style="width:170px;">Nama Siswa</td>
                        <td style="width:20px;">:</td>
                        <td class="dotted-line">{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <td>Kelas</td>
                        <td>:</td>
                        <td class="dotted-line">{{ $student->class }}</td>
                    </tr>
                    <tr>
                        <td>Program Keahlian</td>
                        <td>:</td>
                        <td class="dotted-line">{{ $student->major }}</td>
                    </tr>
                    <tr>
                        <td>Periode Magang</td>
                        <td>:</td>
                        <td class="dotted-line">{{ $student->internship_period ?: '01 Oktober 2026 - 30 November 2026' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- LEMBAR 2: IDENTITAS PESERTA (FOTO 2)                 -->
    <!-- ==================================================== -->
    <div class="page-a4">
        <h2 class="page-title">IDENTITAS PESERTA</h2>

        <table class="identity-table">
            <tr>
                <td style="width:210px;">Nama Siswa</td>
                <td style="width:15px;">:</td>
                <td class="dot-fill">{{ $student->name }}</td>
            </tr>
            <tr>
                <td>Nomor Induk Siswa</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->nis }}</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->birth_place }}, {{ $student->birth_date }}</td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->gender }}</td>
            </tr>
            <tr>
                <td>Agama</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->religion }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->student_address }}</td>
            </tr>
            <tr>
                <td>Nomor HP</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->student_phone }}</td>
            </tr>
            <tr><td colspan="3" style="height:4px;"></td></tr>
            <tr>
                <td>Nama Orang Tua</td>
                <td>:</td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left:15px;">Ayah</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->father_name }}</td>
            </tr>
            <tr>
                <td style="padding-left:15px;">Nomor HP</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->father_phone }}</td>
            </tr>
            <tr>
                <td style="padding-left:15px;">Ibu</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->mother_name }}</td>
            </tr>
            <tr>
                <td style="padding-left:15px;">Nomor HP</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->mother_phone }}</td>
            </tr>
            <tr>
                <td>Alamat Orang Tua</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->parent_address }}</td>
            </tr>
            <tr><td colspan="3" style="height:4px;"></td></tr>
            <tr>
                <td>Nama Sekolah</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->school_name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->school_address }}</td>
            </tr>
            <tr>
                <td>Nama Tempat Praktik</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->company_name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->company_address }}</td>
            </tr>
            <tr>
                <td>Nama Guru Pembimbing</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->teacher_name }}</td>
            </tr>
            <tr>
                <td>Nomor HP</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->teacher_phone }}</td>
            </tr>
        </table>

        <!-- Foto & Tanda Tangan Tanggal Sesuai Foto 2 -->
        <div style="margin-top:6mm;display:flex;justify-content:space-between;align-items:flex-end;">
            <div class="photo-box-physical">
                @if($student->photo_url)
                    <img src="{{ $student->photo_url }}" alt="Foto {{ $student->name }}">
                @else
                    Pas Foto<br>3 x 4
                @endif
            </div>

            <div style="text-align:center;min-width:180px;">
                <p>Jakarta, {{ $todayIndo }}</p>
                <div style="height:16mm;"></div>
                <p style="border-top:1px solid #000000;display:inline-block;padding-top:2px;min-width:160px;">
                    ( {{ $student->name }} )
                </p>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- LEMBAR 3: URAIAN KEGIATAN MAGANG (FOTO 3) - PER SLIDE-->
    <!-- ==================================================== -->
    @foreach($student->activitySheets as $sheet)
    <div class="page-a4">
        <h2 class="page-title">URAIAN KEGIATAN MAGANG</h2>
        <div style="font-weight:700;margin-bottom:4mm;font-size:10.5pt;">
            Bulan : <span style="border-bottom:1px dotted #000000;display:inline-block;min-width:120px;">{{ $sheet->month_name }}</span>
            @if($student->activitySheets->count() > 1)
                <span style="float:right;font-size:9pt;font-weight:600;color:#64748b;">(Lembar ke-{{ $sheet->sheet_number }})</span>
            @endif
        </div>

        <table class="table-activity">
            <thead>
                <tr>
                    <th style="width:38px;">No.</th>
                    <th style="width:125px;">Hari / Tanggal</th>
                    <th style="width:130px;">Divisi / Bagian</th>
                    <th>Uraian Pekerjaan</th>
                    <th style="width:120px;">Pembimbing Industri</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $logsByRow = $sheet->activityLogs->keyBy('row_index');
                @endphp
                @for($r = 1; $r <= 10; $r++)
                    @php
                        $log = $logsByRow->get($r);
                    @endphp
                    <tr class="activity-row">
                        <td style="text-align:center;font-weight:700;vertical-align:top;padding-top:6px;">{{ $r }}.</td>
                        <td style="vertical-align:top;padding-top:6px;font-size:8.8pt;">{{ $log ? $log->day_date : '' }}</td>
                        <td style="vertical-align:top;padding-top:6px;font-size:8.8pt;">{{ $log ? $log->division : '' }}</td>
                        <td style="vertical-align:top;padding-top:6px;font-size:8.8pt;word-break:break-word;line-height:1.35;">{{ $log ? $log->job_description : '' }}</td>
                        <td style="text-align:center;font-size:8pt;vertical-align:top;padding-top:6px;">
                            @if($log && $log->supervisor_note)
                                {{ $log->supervisor_note }}
                            @endif
                            @if($log && $log->is_approved)
                                <div style="font-weight:700;color:#065f46;margin-top:2px;">[Disetujui]</div>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    @endforeach

    <!-- ==================================================== -->
    <!-- LEMBAR 4: DAFTAR HADIR MAGANG (FOTO 4)               -->
    <!-- ==================================================== -->
    @foreach($student->attendanceSheets as $attSheet)
    <div class="page-a4">
        <h2 class="page-title">DAFTAR HADIR MAGANG</h2>
        <div style="font-weight:700;margin-bottom:3mm;font-size:10.5pt;">
            Bulan : <span style="border-bottom:1px dotted #000000;display:inline-block;min-width:120px;">{{ $attSheet->month_name }}</span>
        </div>

        <table class="table-official table-attendance">
            <thead>
                <tr>
                    <th rowspan="2" style="width:30px;">No.</th>
                    <th rowspan="2" style="width:160px;">Hari, Tanggal</th>
                    <th rowspan="2" style="width:45px;">Hadir</th>
                    <th colspan="3" style="width:85px;">Tidak Hadir</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th style="width:28px;">S</th>
                    <th style="width:28px;">I</th>
                    <th style="width:28px;">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attSheet->records as $record)
                    <tr>
                        <td style="text-align:center;font-weight:600;">{{ $record->day_number }}.</td>
                        <td>{{ $record->day_date }}</td>
                        <td style="text-align:center;font-weight:700;font-size:11pt;">{!! $record->status === 'H' ? '&#10003;' : '' !!}</td>
                        <td style="text-align:center;font-weight:700;font-size:11pt;">{!! $record->status === 'S' ? '&#10003;' : '' !!}</td>
                        <td style="text-align:center;font-weight:700;font-size:11pt;">{!! $record->status === 'I' ? '&#10003;' : '' !!}</td>
                        <td style="text-align:center;font-weight:700;font-size:11pt;">{!! $record->status === 'A' ? '&#10003;' : '' !!}</td>
                        <td>{{ $record->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Rekapitulasi Kehadiran Fisik Sesuai Foto 4 -->
        <div style="margin-top:3.5mm;display:flex;justify-content:space-between;align-items:flex-start;font-size:9pt;">
            <div style="line-height:1.45;">
                <table style="border:none;">
                    <tr>
                        <td style="width:180px;">Rekapitulasi Kehadiran</td>
                        <td style="width:15px;">:</td>
                        <td>........................</td>
                    </tr>
                    <tr>
                        <td>Jumlah Seharusnya Hadir</td>
                        <td>:</td>
                        <td><strong>{{ $attSheet->total_target_days }}</strong> Hari</td>
                    </tr>
                    <tr>
                        <td>Tidak Hadir</td>
                        <td>:</td>
                        <td>S: {{ $attSheet->total_sick }} | I: {{ $attSheet->total_permission }} | A: {{ $attSheet->total_absent }}</td>
                    </tr>
                    <tr>
                        <td>Jumlah Hadir</td>
                        <td>:</td>
                        <td><strong>{{ $attSheet->total_present }}</strong> Hari</td>
                    </tr>
                </table>
            </div>

            <div style="text-align:center;min-width:180px;">
                <p>Pembimbing Industri</p>
                <div style="height:14mm;"></div>
                <p style="border-top:1px dotted #000000;display:inline-block;padding-top:2px;min-width:160px;">
                    {{ $student->mentor_name ?: '..........................................' }}
                </p>
            </div>
        </div>
    </div>
    @endforeach

    <!-- ==================================================== -->
    <!-- LEMBAR 5: LAPORAN PENILAIAN MAGANG (FOTO 5)          -->
    <!-- ==================================================== -->
    @php
        $eval = $student->evaluation;
    @endphp
    <div class="page-a4">
        <h2 class="page-title">LAPORAN PENILAIAN MAGANG</h2>

        <table style="width:100%;font-size:10pt;border-collapse:separate;border-spacing:0 3px;margin-bottom:4mm;">
            <tr>
                <td style="width:190px;">Nama Siswa</td>
                <td style="width:15px;">:</td>
                <td class="dot-fill">{{ $student->name }}</td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->nis }} @if($student->nisn) / {{ $student->nisn }} @endif</td>
            </tr>
            <tr>
                <td>Kelas / Program Keahlian</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->class }} / {{ $student->major }}</td>
            </tr>
            <tr>
                <td>Tempat Magang</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->company_name }}</td>
            </tr>
            <tr>
                <td>Waktu Pelaksanaan</td>
                <td>:</td>
                <td class="dot-fill">{{ $student->internship_period ?: '01 Oktober 2026 - 30 November 2026' }}</td>
            </tr>
        </table>

        <p style="font-size:9.5pt;margin-bottom:2mm;line-height:1.4;">
            Penilaian ini dilakukan oleh Pembimbing Dunia Usaha dan Dunia Industri (DUDI) selama peserta melaksanakan magang.<br>
            Penilaian meliputi pengetahuan, praktik/keterampilan dan sikap.
        </p>

        <table class="table-official" style="margin-top:2mm;">
            <thead>
                <tr>
                    <th rowspan="2" style="width:40px;">No.</th>
                    <th rowspan="2">Aspek Yang Dinilai</th>
                    <th colspan="2" style="width:160px;">Penilaian</th>
                </tr>
                <tr>
                    <th style="width:80px;">Nilai</th>
                    <th style="width:80px;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" style="background:#f1f5f9;font-weight:700;">SOFTSKILL</td>
                </tr>
                <tr>
                    <td style="text-align:center;font-weight:700;">A</td>
                    <td>Menerapkan Softskill yang dibutuhkan dunia kerja.</td>
                    <td style="text-align:center;font-weight:700;">{{ $eval->aspect_a_score ? number_format($eval->aspect_a_score, 1) : '-' }}</td>
                    <td style="text-align:center;font-weight:700;">{{ $eval->aspect_a_grade ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align:center;font-weight:700;">B</td>
                    <td>Menerapkan norma, Prosedur Operasional Standar (POS) dan Kesehatan Keselamatan Kerja (K3) yang ada pada dunia kerja.</td>
                    <td style="text-align:center;font-weight:700;">{{ $eval->aspect_b_score ? number_format($eval->aspect_b_score, 1) : '-' }}</td>
                    <td style="text-align:center;font-weight:700;">{{ $eval->aspect_b_grade ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="text-align:center;font-weight:700;">C</td>
                    <td>Menerapkan kompetensi teknis yang sudah dipelajari di sekolah dan/atau baru dipelajari pada dunia kerja sesuai konsentrasi keahlian.</td>
                    <td style="text-align:center;font-weight:700;">{{ $eval->aspect_c_score ? number_format($eval->aspect_c_score, 1) : '-' }}</td>
                    <td style="text-align:center;font-weight:700;">{{ $eval->aspect_c_grade ?: '-' }}</td>
                </tr>
                <tr style="background:#f8fafc;font-weight:800;">
                    <td colspan="2" style="text-align:right;padding-right:15px;">NILAI RATA-RATA:</td>
                    <td style="text-align:center;">{{ $eval->average_score ? number_format($eval->average_score, 2) : '-' }}</td>
                    <td style="text-align:center;">{{ $eval->final_grade ?: '-' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Keterangan Nilai & Tanda Tangan Sesuai Foto 5 -->
        <div style="margin-top:3.5mm;display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <p style="font-weight:700;font-size:8.5pt;margin-bottom:2px;">Keterangan Nilai:</p>
                <table class="table-official" style="width:220px;font-size:7.8pt;margin-top:0;">
                    <thead>
                        <tr>
                            <th>Nilai</th>
                            <th>Predikat</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align:center;">91 - 100</td>
                            <td style="text-align:center;font-weight:700;">A</td>
                            <td>Sangat Baik</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">81 - 90</td>
                            <td style="text-align:center;font-weight:700;">B</td>
                            <td>Baik</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">75 - 80</td>
                            <td style="text-align:center;font-weight:700;">C</td>
                            <td>Cukup Baik</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;">&lt; 75</td>
                            <td style="text-align:center;font-weight:700;">D</td>
                            <td>Kurang</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="text-align:center;min-width:200px;font-size:9pt;">
                <p>{{ $eval->evaluation_date ?: ('Jakarta, ' . $todayIndo) }}</p>
                <p style="margin-top:2px;">Pembimbing Dunia Industri</p>
                <div style="height:15mm;"></div>
                <p style="border-top:1px dotted #000000;display:inline-block;padding-top:2px;min-width:170px;font-weight:700;">
                    {{ $eval->mentor_name ?: $student->mentor_name ?: '..........................................' }}
                </p>
            </div>
        </div>
    </div>
    </div> <!-- /#printDocumentWrapper -->

    <script>
        async function handleDownloadPdfDirectly() {
            const btn = document.getElementById('btnDownloadPdf');
            const btnText = document.getElementById('btnDownloadPdfText');
            
            if (typeof html2pdf === 'undefined') {
                window.print();
                return;
            }

            btn.disabled = true;
            btnText.textContent = 'Menyiapkan PDF...';

            // Floating progress toast
            const toast = document.createElement('div');
            toast.id = 'pdfToast';
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#0f172a;color:#ffffff;padding:12px 20px;border-radius:12px;font-size:0.88rem;font-weight:600;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:9999;display:flex;align-items:center;gap:10px;';
            toast.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="spin-icon"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg> Sedang merender 5 lembar A4 ke file PDF...';
            document.body.appendChild(toast);

            try {
                const element = document.getElementById('printDocumentWrapper');
                const studentNameSlug = "{{ \Illuminate\Support\Str::slug($student->name) ?: 'siswa' }}";
                const opt = {
                    margin: 0,
                    filename: `Laporan_Magang_${studentNameSlug}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['css', 'legacy'] }
                };

                await html2pdf().set(opt).from(element).save();

                toast.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Berhasil mendownload berkas PDF!';
                setTimeout(() => toast.remove(), 3500);
            } catch (err) {
                console.error('Gagal generate html2pdf:', err);
                toast.remove();
                window.print();
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Download PDF';
            }
        }
    </script>
</body>
</html>
