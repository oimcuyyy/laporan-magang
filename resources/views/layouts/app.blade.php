<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laporan Magang - SMK Negeri 20 Jakarta')</title>
    
    <!-- Comprehensive Universal URL Integrity & Tampering Shield (All Pages, Queries, & Hashes) -->
    <script>
        (function() {
            function enforceUniversalUrlIntegrity() {
                var path = window.location.pathname;
                
                // Jangan periksa jika sudah berada di halaman blokir 401
                if (path === '/tampered-url') return;

                var search = window.location.search;
                var hash = window.location.hash;
                var isTampered = false;
                var reason = '';

                // 1. Validasi Hash / Anchor di semua halaman
                if (hash) {
                    var isStudentDetail = /\/students\/\d+$/.test(path);
                    if (isStudentDetail) {
                        var allowedPageAnchors = [
                            '#page-1-cover',
                            '#page-2-identitas',
                            '#page-3-uraian',
                            '#page-4-presensi',
                            '#page-5-penilaian'
                        ];
                        if (allowedPageAnchors.indexOf(hash) === -1) {
                            isTampered = true;
                            reason = 'Bagian akhir tautan (' + hash + ') tidak sah atau dimanipulasi.';
                        }
                    } else {
                        // Di halaman selain detail siswa (/students, /students/create, dll.) tidak boleh ada hash
                        isTampered = true;
                        reason = 'Halaman ini tidak mengizinkan penambahan anchor atau tautan akhir (' + hash + ').';
                    }

                    // Deteksi karakter aneh / injeksi pada hash
                    if (hash.indexOf('/', 1) !== -1 || (hash.match(/#/g) || []).length > 1 || /[\<\>'"\(\)\{\};]/.test(hash)) {
                        isTampered = true;
                        reason = 'Terdeteksi karakter manipulasi tidak sah pada tautan akhir (' + hash + ').';
                    }
                }

                // 2. Validasi Query Parameter (?key=val) di semua halaman
                if (!isTampered && search) {
                    var isStudentDetail = /\/students\/\d+$/.test(path);
                    var params = new URLSearchParams(search);

                    if (isStudentDetail) {
                        var allowedKeys = ['tab', 'sheet'];
                        var allowedTabs = ['cover', 'identitas', 'uraian', 'presensi', 'penilaian'];

                        params.forEach(function(val, key) {
                            if (allowedKeys.indexOf(key) === -1) {
                                isTampered = true;
                                reason = 'Parameter query ("' + key + '") tidak diizinkan pada halaman berkas siswa.';
                            } else if (key === 'tab' && allowedTabs.indexOf(val) === -1) {
                                isTampered = true;
                                reason = 'Nilai tab ("' + val + '") telah diubah ke nilai yang tidak sah.';
                            } else if (key === 'sheet' && (!/^\d+$/.test(val) || parseInt(val, 10) <= 0)) {
                                isTampered = true;
                                reason = 'Nilai sheet ("' + val + '") tidak valid.';
                            }
                        });
                    } else {
                        // Di halaman /students, /students/create, dll. tidak boleh ada query parameter apapun
                        isTampered = true;
                        reason = 'Tautan halaman ini telah disisipi parameter query yang tidak sah.';
                    }
                }

                if (isTampered) {
                    if (document.documentElement) {
                        document.documentElement.style.display = 'none';
                    }
                    var fullUrl = window.location.href;
                    window.location.replace('/tampered-url?url=' + encodeURIComponent(fullUrl) + '&detail=' + encodeURIComponent(reason));
                }
            }

            enforceUniversalUrlIntegrity();
            window.addEventListener('hashchange', enforceUniversalUrlIntegrity);
            window.addEventListener('popstate', enforceUniversalUrlIntegrity);
        })();
    </script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --accent: #0284c7;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            --shadow-md: 0 6px 14px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -2px rgba(15, 23, 42, 0.04);
            --shadow-lg: 0 12px 24px -4px rgba(15, 23, 42, 0.07), 0 4px 8px -3px rgba(15, 23, 42, 0.03);
            --shadow-xl: 0 24px 38px -6px rgba(15, 23, 42, 0.09), 0 8px 14px -6px rgba(15, 23, 42, 0.04);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: var(--slate-800);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Bar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.03);
        }

        .nav-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: var(--slate-900);
            transition: opacity 0.2s ease;
        }
        .brand-link:hover {
            opacity: 0.88;
        }

        .brand-logo-badge {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.02em;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .brand-text h1 {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--slate-900);
            line-height: 1.2;
        }

        .brand-text p {
            font-size: 0.75rem;
            color: var(--slate-500);
            font-weight: 500;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Buttons - Modern Rounded Pills */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.86rem;
            font-weight: 700;
            padding: 0.55rem 1.25rem;
            border-radius: 9999px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af, #1d4ed8);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--slate-700);
            border: 1.5px solid var(--slate-200);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .btn-secondary:hover {
            background-color: var(--slate-50);
            border-color: var(--slate-300);
            color: var(--slate-900);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.06);
        }

        .btn-success {
            background-color: var(--success);
            color: #ffffff;
            border-radius: 9999px;
        }
        .btn-success:hover {
            background-color: #059669;
        }

        .btn-outline-primary {
            background-color: transparent;
            color: var(--primary);
            border: 1.5px solid var(--primary);
            border-radius: 9999px;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-light);
        }

        .btn-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1.5px solid #fca5a5;
            border-radius: 9999px;
        }
        .btn-danger:hover {
            background-color: #fecaca;
        }

        .btn-sm {
            padding: 0.42rem 0.95rem;
            font-size: 0.82rem;
            border-radius: 9999px;
        }

        /* Alerts */
        .alert-container {
            max-width: 1300px;
            margin: 1rem auto 0;
            padding: 0 1.5rem;
        }

        .alert {
            padding: 0.85rem 1.25rem;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        /* Main Wrapper */
        .main-wrapper {
            max-width: 1300px;
            margin: 1.5rem auto;
            padding: 0 1.5rem 3rem;
            width: 100%;
            flex: 1;
        }

        /* Footer */
        .footer {
            background: #ffffff;
            border-top: 1px solid var(--slate-200);
            padding: 1.25rem 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--slate-500);
            margin-top: auto;
        }

        /* Micro badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            line-height: 1;
        }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }

        /* Unified Form Controls & Input Boxes - Elegant Soft Curves */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 0.45rem;
            letter-spacing: -0.01em;
        }
        .form-label .required {
            color: var(--danger);
            margin-left: 2px;
        }
        .form-control {
            width: 100%;
            height: 44px;
            padding: 0.55rem 0.95rem;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--slate-900);
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            padding-right: 2.2rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 14px;
        }
        .form-control:hover {
            border-color: #cbd5e1;
            background-color: #ffffff;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            background-color: #ffffff;
        }
        textarea.form-control, textarea.form-control-textarea {
            height: auto;
            min-height: 85px;
            line-height: 1.5;
            padding: 0.75rem 0.95rem;
        }
        .form-hint {
            display: block;
            font-size: 0.75rem;
            color: var(--slate-500);
            margin-top: 0.35rem;
        }

        /* Structured Form Section Cards - Smooth Rounded Cards */
        .form-section-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 1.6rem;
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.03);
            margin-bottom: 1.5rem;
            transition: all 0.2s ease;
        }
        .form-section-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.05);
        }
        .form-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid var(--slate-100);
        }
        .form-section-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--slate-900);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .form-section-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: var(--primary-light);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Segmented Radio Pills for Attendance (H, S, I, A) */
        .radio-chip-group {
            display: inline-flex;
            gap: 0.3rem;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
        }
        .radio-chip {
            position: relative;
            cursor: pointer;
            margin: 0;
        }
        .radio-chip input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .radio-chip-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
            font-weight: 700;
            border-radius: 9999px;
            color: var(--slate-600);
            transition: all 0.15s ease;
            user-select: none;
        }
        .radio-chip input[type="radio"]:hover + .radio-chip-label {
            background: #ffffff;
            color: var(--slate-900);
        }
        .radio-chip input[type="radio"]:checked + .radio-chip-label.chip-h {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
        }
        .radio-chip input[type="radio"]:checked + .radio-chip-label.chip-s {
            background: #f59e0b;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);
        }
        .radio-chip input[type="radio"]:checked + .radio-chip-label.chip-i {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.4);
        }
        .radio-chip input[type="radio"]:checked + .radio-chip-label.chip-a {
            background: #ef4444;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
        }

        /* Printable Paper View (A4 Authentic Photo Simulation) */
        .paper-sheet-a4 {
            background: #ffffff;
            max-width: 820px;
            margin: 0 auto;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.08);
            padding: 3.5rem 3rem;
            border-radius: 20px;
            position: relative;
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            line-height: 1.6;
        }
        .paper-dotted {
            border-bottom: 1px dotted #334155;
            min-height: 1.4em;
            display: inline-block;
            width: 100%;
        }

        /* Responsive Mobile Layout & Touch Target Improvements */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0.65rem 0.85rem;
                gap: 0.5rem;
            }
            .brand-logo-badge {
                width: 36px;
                height: 36px;
                font-size: 0.95rem;
                border-radius: 10px;
            }
            .brand-text h1 {
                font-size: 0.95rem;
            }
            .brand-text p {
                font-size: 0.7rem;
            }
            .nav-actions {
                gap: 0.4rem;
            }
            .nav-actions .btn {
                padding: 0.42rem 0.75rem;
                font-size: 0.78rem;
            }
            .main-wrapper {
                padding: 0 0.75rem 2rem;
                margin: 1rem auto;
            }
            .alert-container {
                padding: 0 0.75rem;
            }
            .btn {
                min-height: 42px;
                touch-action: manipulation;
            }
            .form-control {
                height: 44px;
                font-size: 16px; /* Mencegah auto-zoom iOS Safari pada text input */
            }
            .radio-chip-group {
                gap: 0.25rem;
                padding: 3px;
            }
            .radio-chip-label {
                width: 36px;
                height: 36px;
                font-size: 0.85rem;
            }
            .form-section-card {
                padding: 1.15rem;
                border-radius: 16px;
            }
        }

        @media (max-width: 480px) {
            .brand-text p {
                display: none;
            }
        }

        @media print {
            .navbar, .student-switcher-bar, .workspace-header, .tabs-nav, .nav-actions, .footer, .btn, .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .main-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .paper-sheet-a4 {
                border: none !important;
                box-shadow: none !important;
                padding: 1cm !important;
                max-width: 100% !important;
            }
        }

        /* Icons SVG helper */
        .icon {
            width: 1.1rem;
            height: 1.1rem;
            stroke-width: 2;
            stroke: currentColor;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
            vertical-align: middle;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header Navigation -->
    <header class="navbar">
        <div class="nav-container">
            <a href="{{ route('students.index') }}" class="brand-link">
                <img src="{{ asset('images/logo-smkn20.png') }}" alt="Logo SMK Negeri 20" style="height:38px;width:auto;max-width:38px;object-fit:contain;filter:drop-shadow(0 2px 3px rgba(0,0,0,0.1));">
                <div class="brand-text">
                    <h1>Laporan Magang</h1>
                    <p>SMK Negeri 20 Jakarta &bull; Kelas Industri</p>
                </div>
            </a>

            <div class="nav-actions">
                <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Rekap Siswa
                </a>

                @if(isset($totalStudents) && $totalStudents < 5)
                    <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
                        <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Siswa ({{ $totalStudents }}/5)
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Notification Messages -->
    <div class="alert-container">
        @if(session('error'))
            <div class="alert alert-danger">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;color:inherit;">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <div>
                    <strong>Perhatian! Terdapat kesalahan pengisian:</strong>
                    <ul style="margin-top:0.35rem;margin-left:1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:bold;color:inherit;">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="main-wrapper">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; {{ date('Y') }} <strong>Laporan Magang &bull; SMK Negeri 20 Jakarta</strong>. Mendukung Database Supabase PostgreSQL & Mode Cetak Berkas Fisik A4.</p>
    </footer>

    <!-- SweetAlert2 Library (Lokal + CDN Fallback) -->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        if (typeof Swal === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
        }
    </script>
    <script>
        // Konfigurasi default SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}"
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Pemberitahuan',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ef4444'
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Periksa Kembali Formulir',
                    html: '<ul style="text-align:left;font-size:0.9rem;color:#b91c1c;margin:0;padding-left:1.25rem;">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Tutup & Perbaiki'
                });
            @endif

            // Tangani semua form submit standar agar memunculkan SweetAlert loading secara otomatis
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (e.defaultPrevented || form.id === 'formAddDay' || form.dataset.async === 'true') {
                    return;
                }
                if (form.checkValidity && !form.checkValidity()) {
                    return;
                }

                Swal.fire({
                    title: 'Menyimpan Perubahan...',
                    text: 'Mohon tunggu, data sedang disimpan ke sistem.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });

            // Notifikasi SweetAlert saat tombol Cetak A4 diklik
            document.addEventListener('click', function(e) {
                const printBtn = e.target.closest('a[href*="/print"]');
                if (printBtn) {
                    Toast.fire({
                        icon: 'info',
                        title: 'Mempersiapkan pratinjau cetak berkas fisik A4...'
                    });
                }
            });
        });

        // Global Helper: Konfirmasi Hapus dengan SweetAlert2
        function confirmDelete(event, formElement, titleText, descText) {
            event.preventDefault();
            Swal.fire({
                title: titleText || 'Apakah Anda yakin?',
                text: descText || 'Tindakan ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus Data...',
                        text: 'Sedang memproses penghapusan.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    formElement.submit();
                }
            });
            return false;
        }

        // ==========================================
        // Fitur Rilok Otomatis dari Web (Background Auto-Reload & Auto-Sync)
        // ==========================================
        (function() {
            // Pulihkan posisi scroll setelah reload otomatis
            const savedScroll = sessionStorage.getItem('app_auto_reload_scroll');
            if (savedScroll !== null) {
                window.scrollTo({ top: parseInt(savedScroll, 10), behavior: 'instant' });
                sessionStorage.removeItem('app_auto_reload_scroll');
            }

            let lastInteractionTime = Date.now();
            ['input', 'keydown', 'mousedown', 'touchstart', 'scroll'].forEach(evt => {
                window.addEventListener(evt, () => {
                    lastInteractionTime = Date.now();
                }, { passive: true });
            });

            function canSafelyReload() {
                const activeEl = document.activeElement;
                const isTyping = activeEl && (
                    activeEl.tagName === 'INPUT' || 
                    activeEl.tagName === 'TEXTAREA' || 
                    activeEl.tagName === 'SELECT' || 
                    activeEl.isContentEditable
                );
                const isModalOpen = document.querySelector('.modal-backdrop.show') || 
                                    document.querySelector('.swal2-shown') || 
                                    (document.getElementById('coverDropdownMenu') && document.getElementById('coverDropdownMenu').style.display === 'block');

                // Jangan reload jika user sedang mengetik atau modal/menu terbuka
                if (isTyping || isModalOpen) return false;

                // Tunda jika baru ada interaksi dalam 10 detik terakhir
                if (Date.now() - lastInteractionTime < 10000) return false;

                return true;
            }

            function triggerSilentReload() {
                if (!canSafelyReload()) return;
                sessionStorage.setItem('app_auto_reload_scroll', window.scrollY);
                window.location.reload();
            }

            // Rilok otomatis saat user kembali membuka / fokus ke tab halaman ini
            window.addEventListener('focus', () => {
                if (canSafelyReload()) {
                    triggerSilentReload();
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
