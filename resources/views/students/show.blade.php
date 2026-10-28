@extends('layouts.app')

@section('title', 'Berkas Laporan - ' . $student->name)

@push('styles')
<style>
    /* Emil Kowalski Animation Principles */
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes sectionHighlight {
        0% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.45);
        }
        50% {
            box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.22);
            border-color: #3b82f6;
        }
        100% {
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        }
    }

    @keyframes dropdownEnter {
        from {
            opacity: 0;
            transform: scale(0.96) translateY(-4px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Emil Kowalski Button & Interactive Element Press Feedback */
    button, .btn, .btn-pill, .switcher-pill, .btn-action, .btn-secondary, .btn-primary, .btn-sheet {
        transition: transform 160ms cubic-bezier(0.23, 1, 0.32, 1), background-color 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    button:active, .btn:active, .btn-pill:active, .switcher-pill:active, .btn-action:active, .btn-sheet:active {
        transform: scale(0.97) !important;
    }

    .word-page-section {
        transition: box-shadow 260ms cubic-bezier(0.23, 1, 0.32, 1), border-color 260ms ease;
    }

    .word-page-section.highlight-pulse {
        animation: sectionHighlight 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Student Switcher Bar */
    .student-switcher-bar {
        background: #ffffff;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
    }

    .switcher-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--slate-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .switcher-pills {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.25rem;
    }

    .switcher-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 1.15rem;
        border-radius: 9999px;
        border: 1.5px solid var(--slate-200);
        background: #ffffff;
        color: var(--slate-700);
        text-decoration: none;
        font-size: 0.84rem;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }

    .switcher-pill:hover {
        background: var(--slate-50);
        border-color: var(--slate-300);
        color: var(--slate-900);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .switcher-pill.active {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* Top Workspace Header */
    .workspace-header {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04);
    }

    .student-brief {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .student-brief-img {
        width: 58px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
        border: 2px solid #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        background: var(--slate-100);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: var(--slate-400);
        text-align: center;
        font-weight: 700;
    }

    .student-brief-info h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--slate-900);
        letter-spacing: -0.02em;
    }

    .student-brief-info p {
        font-size: 0.85rem;
        color: var(--slate-500);
        margin-top: 0.15rem;
    }

    /* Paraf Upload UI Components */
    .paraf-slot-container {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.35rem;
    }
    .paraf-preview-box {
        position: relative;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
    }
    .paraf-thumb-img {
        max-height: 36px;
        max-width: 100%;
        object-fit: contain;
        display: block;
        mix-blend-mode: multiply;
        transition: transform 0.2s ease;
    }
    .paraf-actions {
        position: absolute;
        top: 2px;
        right: 2px;
        display: flex;
        gap: 3px;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 4px;
        padding: 2px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .btn-paraf-action {
        width: 18px;
        height: 18px;
        border-radius: 3px;
        border: none;
        background: #f1f5f9;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: background 0.15s, color 0.15s;
    }
    .btn-paraf-action:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .btn-paraf-remove:hover {
        background: #fee2e2;
        color: #dc2626;
    }
    .btn-upload-paraf {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: 5px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-upload-paraf:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    .btn-upload-paraf-sm {
        padding: 3px 6px;
        font-size: 0.7rem;
    }
    .btn-batch-paraf {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 0.7rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-batch-paraf:hover {
        background: #e0f2fe;
        border-color: #38bdf8;
        color: #0369a1;
    }

    /* Word Document 2-Column Layout (Vertikal Navigation Pane di Kiri ala Microsoft Word) */
    .word-document-layout {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 1.75rem;
        align-items: start;
        margin-top: 1.25rem;
    }

    @media (max-width: 992px) {
        .word-document-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Word Navigation Sidebar (Navigation Pane) */
    .word-nav-sidebar {
        position: sticky;
        top: 85px;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        padding: 1.15rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04);
        z-index: 40;
    }

    .word-nav-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 0.85rem;
    }

    .word-nav-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .word-nav-count {
        font-size: 0.72rem;
        font-weight: 700;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 2px 8px;
        border-radius: 9999px;
    }

    .word-nav-list {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .word-nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0.85rem;
        border-radius: 14px;
        text-decoration: none;
        color: #334155;
        background: transparent;
        border: 1.5px solid transparent;
        transition: transform 180ms cubic-bezier(0.23, 1, 0.32, 1), background-color 180ms ease, border-color 180ms ease, color 180ms ease, box-shadow 180ms ease;
        position: relative;
    }

    .word-nav-item:active {
        transform: scale(0.98);
    }

    .word-nav-item:nth-child(1) { animation: fadeSlideIn 240ms cubic-bezier(0.23, 1, 0.32, 1) 0ms both; }
    .word-nav-item:nth-child(2) { animation: fadeSlideIn 240ms cubic-bezier(0.23, 1, 0.32, 1) 40ms both; }
    .word-nav-item:nth-child(3) { animation: fadeSlideIn 240ms cubic-bezier(0.23, 1, 0.32, 1) 80ms both; }
    .word-nav-item:nth-child(4) { animation: fadeSlideIn 240ms cubic-bezier(0.23, 1, 0.32, 1) 120ms both; }
    .word-nav-item:nth-child(5) { animation: fadeSlideIn 240ms cubic-bezier(0.23, 1, 0.32, 1) 160ms both; }

    .word-nav-item:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
        transform: translateX(3px);
        color: #0f172a;
    }

    .word-nav-item.active {
        background: linear-gradient(135deg, #eff6ff, #f0f9ff);
        border-color: #93c5fd;
        color: #1d4ed8;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
    }

    .word-page-num {
        width: 32px;
        height: 38px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 800;
        color: #64748b;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        flex-shrink: 0;
        transition: all 0.2s ease;
        line-height: 1;
    }

    .word-page-num small {
        font-size: 0.52rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        margin-top: 1px;
    }

    .word-nav-item:hover .word-page-num {
        border-color: #94a3b8;
        color: #0f172a;
    }

    .word-nav-item.active .word-page-num {
        background: #2563eb;
        border-color: #1d4ed8;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }

    .word-nav-item.active .word-page-num small {
        color: rgba(255, 255, 255, 0.85);
    }

    .word-nav-info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .word-nav-name {
        font-size: 0.86rem;
        font-weight: 700;
        color: inherit;
        line-height: 1.25;
    }

    .word-nav-desc {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
    }

    .word-nav-item.active .word-nav-desc {
        color: #3b82f6;
    }

    .word-nav-chevron {
        font-size: 0.85rem;
        color: #94a3b8;
        opacity: 0;
        transform: translateX(-4px);
        transition: all 0.2s ease;
    }

    .word-nav-item:hover .word-nav-chevron,
    .word-nav-item.active .word-nav-chevron {
        opacity: 1;
        transform: translateX(0);
        color: #2563eb;
    }

    .word-nav-footer {
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .word-nav-footer-badge {
        font-size: 0.72rem;
        color: #64748b;
        text-align: center;
        background: #f8fafc;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-weight: 600;
    }

    .btn-sidebar-print,
    .btn-sidebar-pdf {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 0.85rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-sidebar-print {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        color: #1e40af;
    }
    .btn-sidebar-print:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
        transform: translateY(-1px);
    }
    .btn-sidebar-pdf {
        background: linear-gradient(135deg, #059669, #10b981);
        border: 1.5px solid transparent;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
    }
    .btn-sidebar-pdf:hover {
        background: linear-gradient(135deg, #047857, #059669);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }

    .word-document-content {
        min-width: 0;
    }

    /* Word Page Divider - Pemisah Halaman Bersambung ala Microsoft Word */
    .word-page-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.25rem;
        margin: 3.5rem 0 2rem;
        position: relative;
        user-select: none;
    }

    .word-page-divider-line {
        flex: 1;
        height: 1px;
        background: #cbd5e1;
        border-top: 1.5px dashed #94a3b8;
    }

    .word-page-divider-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        padding: 0.4rem 1.15rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #334155;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        letter-spacing: -0.01em;
    }

    /* Tab Content Cards (Lembar Halaman Dokumen) */
    .tab-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 24px;
        box-shadow: 0 12px 32px -5px rgba(15, 23, 42, 0.05);
        padding: 2.25rem;
        margin-bottom: 2rem;
        scroll-margin-top: 90px;
    }

    /* Floating Control Bar for Cover (No harsh boxes - High Z-Index so popup sits in front) */
    .cover-control-bar {
        position: relative;
        z-index: 50;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 9999px;
        padding: 0.6rem 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
        backdrop-filter: blur(10px);
    }

    /* Custom Popover Dropdown Components (100% Anti-Kotak Kaku) */
    .custom-dropdown-container {
        position: relative;
        display: inline-block;
        z-index: 60;
    }

    .custom-dropdown-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 9999px;
        padding: 0.45rem 1rem 0.45rem 0.85rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        user-select: none;
    }

    .custom-dropdown-trigger:hover {
        border-color: #3b82f6;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.12);
    }

    .custom-dropdown-trigger.open {
        border-color: #2563eb;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14);
    }

    .trigger-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
    }

    .trigger-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .trigger-selected {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    .trigger-chevron {
        color: #2563eb;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        margin-left: 0.2rem;
    }

    .custom-dropdown-trigger.open .trigger-chevron {
        transform: rotate(180deg);
    }

    /* Floating Popover Menu - Must be in front of cover container */
    .custom-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 9999 !important;
        min-width: 340px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 20px;
        padding: 0.6rem;
        box-shadow: 0 25px 60px -10px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(0,0,0,0.03);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px) scale(0.97);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .custom-dropdown-menu.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    .custom-dropdown-menu-sm {
        min-width: 260px;
    }

    .dropdown-header {
        font-size: 0.72rem;
        font-weight: 800;
        color: #94a3b8;
        letter-spacing: 0.08em;
        padding: 0.4rem 0.75rem 0.35rem;
        text-transform: uppercase;
    }

    .dropdown-list {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.85rem;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        user-select: none;
        border: 1px solid transparent;
    }

    .dropdown-item:hover {
        background: #f1f5f9;
        transform: translateX(2px);
    }

    .dropdown-item.active {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .dropdown-item.active .item-title {
        color: #1d4ed8;
    }

    .item-badge {
        font-size: 0.74rem;
        font-weight: 800;
        padding: 0.25rem 0.55rem;
        border-radius: 8px;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    /* Warna Jurusan SMK Negeri 20: RPL (Hitam), MP (Oren), BR (Biru Tua), BD (Biru Muda) */
    .badge-rpl { background: #09090b; color: #ffffff; border: 1px solid #27272a; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
    .badge-mp { background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; }
    .badge-br { background: #1e3a8a; color: #ffffff; border: 1px solid #172554; box-shadow: 0 1px 2px rgba(30,58,138,0.25); }
    .badge-bd { background: #e0f2fe; color: #0284c7; border: 1px solid #7dd3fc; }
    .badge-custom { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

    /* Accent Hover & Active State per Jurusan */
    .major-option.rpl-opt:hover, .major-option.rpl-opt.active { background: #f8fafc; border-color: #09090b; }
    .major-option.rpl-opt.active .item-title { color: #09090b; }
    .major-option.rpl-opt .item-check { background: #09090b; }

    .major-option.mp-opt:hover, .major-option.mp-opt.active { background: #fff7ed; border-color: #ea580c; }
    .major-option.mp-opt.active .item-title { color: #ea580c; }
    .major-option.mp-opt .item-check { background: #ea580c; }

    .major-option.br-opt:hover, .major-option.br-opt.active { background: #eff6ff; border-color: #1e3a8a; }
    .major-option.br-opt.active .item-title { color: #1e3a8a; }
    .major-option.br-opt .item-check { background: #1e3a8a; }

    .major-option.bd-opt:hover, .major-option.bd-opt.active { background: #f0f9ff; border-color: #0284c7; }
    .major-option.bd-opt.active .item-title { color: #0284c7; }
    .major-option.bd-opt .item-check { background: #0284c7; }

    .item-content {
        flex: 1;
        min-width: 0;
    }

    .item-title {
        font-size: 0.86rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }

    .item-sub {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 0.1rem;
    }

    .item-check {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        font-size: 0.7rem;
        font-weight: 900;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .dropdown-item.active .item-check {
        display: flex;
    }

    .dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0.35rem 0;
    }

    .dropdown-item.item-custom {
        color: #2563eb;
    }

    .dropdown-item.item-custom:hover {
        background: #eff6ff;
    }

    .year-val {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f172a;
    }

    .year-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        background: #ecfdf5;
        color: #065f46;
        margin-left: auto;
    }

    .btn-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 1.15rem;
        border-radius: 9999px;
        font-size: 0.84rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }
    .btn-pill-primary {
        background: linear-gradient(135deg, #1e40af, #2563eb);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
    }
    .btn-pill-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
    }

    .btn-pill-secondary {
        background: #ffffff;
        color: #334155;
        border: 1.5px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .btn-pill-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }

    .btn-pill-outline {
        background: transparent;
        color: #475569;
        border: 1.5px solid transparent;
    }
    .btn-pill-outline:hover {
        background: rgba(241, 245, 249, 0.8);
        color: #0f172a;
    }

    /* Cover Page Simulation - Luxurious Clean Folio */
    .cover-container {
        max-width: 780px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        padding: 3.5rem 3rem;
        box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(15, 23, 42, 0.03);
        position: relative;
        z-index: 1;
        overflow: hidden;
    }

    /* Geometric Corner Graphics (Sesuai Foto 1) */
    .cover-corner-top-left {
        position: absolute;
        top: 0;
        left: 0;
        width: 140px;
        height: 140px;
        opacity: 0.85;
        pointer-events: none;
    }

    .cover-corner-bottom-right {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 170px;
        height: 170px;
        opacity: 0.9;
        pointer-events: none;
    }

    .cover-header-logos {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }

    .cover-logo-box {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cover-title-area {
        text-align: center;
        margin-bottom: 3.5rem;
        position: relative;
        z-index: 2;
    }

    .cover-title-area h1 {
        font-size: 2.25rem;
        font-weight: 800;
        color: #1e3a8a;
        letter-spacing: 0.05em;
        line-height: 1.15;
    }

    .cover-title-area h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #e11d48;
        letter-spacing: 0.02em;
        margin-top: 0.4rem;
        margin-bottom: 1.5rem;
    }

    .cover-school-name {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: 0.02em;
    }

    .cover-year {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
        margin-top: 0.2rem;
    }

    .cover-field-box {
        text-align: right;
        margin: 3rem 0;
        position: relative;
        z-index: 2;
    }

    .cover-field-box span {
        display: block;
        font-size: 1.1rem;
        font-weight: 800;
        color: #e11d48;
        line-height: 1.3;
    }

    .cover-meta-table {
        margin-top: 2rem;
        width: 100%;
        max-width: 550px;
        font-size: 1.05rem;
        font-weight: 600;
        color: #1e293b;
        position: relative;
        z-index: 2;
    }

    .cover-meta-table td {
        padding: 0.4rem 0.5rem;
    }

    .cover-dots {
        border-bottom: 1px dotted #64748b;
        padding-bottom: 2px;
    }

    /* Realistic Form Tables (Foto 2, 3, 4, 5) - Sleek Soft Bordered */
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.88rem;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
    }

    .data-table th,
    .data-table td {
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        padding: 0.65rem 0.8rem;
        vertical-align: top;
    }

    .data-table th:last-child,
    .data-table td:last-child {
        border-right: none;
    }

    .data-table tbody tr:last-child td {
        border-bottom: none;
    }

    .data-table tbody tr:hover {
        background-color: #fbfcfe;
    }

    .data-table th {
        background: #f8fafc;
        font-weight: 700;
        color: var(--slate-700);
        font-size: 0.82rem;
        letter-spacing: 0.02em;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.8rem 0.85rem;
    }

    .table-input {
        width: 100%;
        box-sizing: border-box;
        padding: 0.55rem 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #ffffff;
        font-family: inherit;
        font-size: 0.87rem;
        color: var(--slate-900);
        line-height: 1.45;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }

    .table-input:hover {
        background: #fafbfc;
        border-color: #cbd5e1;
    }

    .table-input:focus {
        outline: none;
        background: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    textarea.table-input {
        min-height: 54px;
        line-height: 1.45;
        resize: vertical;
        display: block;
    }

    /* Custom Approval Toggle Pill */
    .approval-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.45rem;
        cursor: pointer;
        user-select: none;
        padding: 0.28rem 0.6rem;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .approval-toggle:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .approval-toggle-checkbox {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    .approval-toggle-indicator {
        width: 15px;
        height: 15px;
        border-radius: 4px;
        border: 1.5px solid #94a3b8;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }

    .approval-check-icon {
        width: 11px;
        height: 11px;
        display: none;
        color: #ffffff;
    }

    .approval-toggle-checkbox:checked + .approval-toggle-indicator {
        background: #16a34a;
        border-color: #16a34a;
    }

    .approval-toggle-checkbox:checked + .approval-toggle-indicator .approval-check-icon {
        display: block;
    }

    .approval-toggle-text {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--slate-600);
        letter-spacing: -0.01em;
    }

    .approval-toggle-checkbox:checked ~ .approval-toggle-text {
        color: #15803d;
        font-weight: 700;
    }

    /* Slide Controls for Uraian Kegiatan & Presensi */
    .slide-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.65rem 0.9rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
    }

    .slide-toolbar-left {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .slide-toolbar-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--slate-500);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .slide-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin-left: auto;
    }

    .slide-pills {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .slide-pill {
        padding: 0.4rem 0.85rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--slate-700);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .slide-pill-sub {
        font-size: 0.74rem;
        opacity: 0.82;
        font-weight: 500;
    }

    .slide-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: var(--slate-900);
        transform: translateY(-1px);
    }

    .slide-pill.active {
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25);
        font-weight: 700;
    }

    /* Sheet Meta Bar (Month & Sheet Info) */
    .sheet-meta-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
        background: #ffffff;
        padding: 0.75rem 1.15rem;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.02);
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .sheet-meta-field {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .sheet-meta-label {
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--slate-700);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Modal dialog - Sleek Glass Elevation */
    .modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }

    .modal-backdrop.show {
        display: flex;
    }

    .modal-box {
        background: #ffffff;
        border-radius: 24px;
        padding: 2.25rem;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(226, 232, 240, 0.9);
    }

    /* ==========================================================================
       RESPONSIVE MOBILE STYLES (ADAPTIVE WORD-LIKE LAYOUT FOR SMARTPHONES)
       ========================================================================== */
    @media (max-width: 992px) {
        .word-document-layout {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        /* Di layar < 992px, Sidebar Navigasi berubah menjadi Horizontal Sticky Pill Bar */
        .word-nav-sidebar {
            position: sticky;
            top: 58px;
            z-index: 45;
            padding: 0.5rem 0.65rem;
            border-radius: 16px;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-color: #cbd5e1;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
        }

        .word-nav-header {
            display: none;
        }

        .word-nav-list {
            flex-direction: row;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            gap: 0.45rem;
            scrollbar-width: none;
            padding-bottom: 2px;
        }
        .word-nav-list::-webkit-scrollbar {
            display: none;
        }

        .word-nav-item {
            flex-shrink: 0;
            padding: 0.45rem 0.85rem;
            border-radius: 9999px;
            gap: 0.5rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
        }

        .word-nav-item:hover {
            transform: none;
        }

        .word-nav-item.active {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.28);
        }

        .word-page-num {
            width: 24px;
            height: 24px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 800;
            border-width: 1px;
            box-shadow: none;
        }

        .word-page-num small {
            display: none;
        }

        .word-nav-name {
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .word-nav-desc,
        .word-nav-chevron {
            display: none;
        }

        .word-nav-footer {
            display: none;
        }

        .tab-card {
            padding: 1.35rem 1.15rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            scroll-margin-top: 130px;
        }

        .word-page-divider {
            margin: 2rem 0 1.25rem;
        }
    }

    @media (max-width: 768px) {
        .student-switcher-bar {
            padding: 0.65rem 0.85rem;
            border-radius: 14px;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }
        .switcher-label {
            font-size: 0.78rem;
            width: 100%;
        }
        .switcher-pills {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding-bottom: 2px;
        }
        .switcher-pills::-webkit-scrollbar {
            display: none;
        }
        .switcher-pill {
            padding: 0.4rem 0.85rem;
            font-size: 0.78rem;
        }

        .workspace-header {
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 1rem;
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        .student-brief {
            gap: 0.85rem;
        }
        .student-brief-img {
            width: 50px;
            height: 64px;
            border-radius: 10px;
        }
        .student-brief-info h2 {
            font-size: 1.15rem;
        }
        .workspace-header > a.btn {
            width: 100%;
            justify-content: center;
        }

        /* Lembar 1: Cover */
        .cover-container {
            padding: 1.5rem 0.85rem;
            border-radius: 16px;
        }
        .cover-corner-top-left,
        .cover-corner-bottom-right {
            opacity: 0.4;
            transform: scale(0.75);
        }
        .cover-header-logos {
            margin-bottom: 1.25rem;
        }
        .cover-header-logos img {
            max-height: 52px !important;
            max-width: 52px !important;
        }
        .cover-title-area {
            margin-bottom: 2rem;
        }
        .cover-title-area h1 {
            font-size: 1.65rem;
        }
        .cover-title-area h2 {
            font-size: 1.05rem;
            margin-bottom: 1rem;
        }
        .cover-school-name {
            font-size: 0.95rem;
        }
        .cover-year {
            font-size: 0.88rem;
        }
        .cover-field-box {
            margin: 2rem 0;
        }
        .cover-field-box span {
            font-size: 0.95rem;
        }
        .cover-meta-table {
            font-size: 0.88rem;
        }
        .cover-control-bar {
            padding: 0.65rem 0.85rem;
            border-radius: 16px;
            flex-direction: column;
            align-items: stretch;
            gap: 0.65rem;
        }
        .cover-control-bar > div:first-child {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }
        .custom-dropdown-container {
            width: 100%;
        }
        .custom-dropdown-trigger {
            width: 100%;
            justify-content: space-between;
        }
        .custom-dropdown-menu {
            min-width: 100%;
            max-width: calc(100vw - 2.5rem);
            left: 0;
            right: 0;
        }

        /* Form Grids Lembar 2 Identitas & DU/DI */
        .identitas-grid-layout {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
        }
        .form-grid-2col {
            grid-template-columns: 1fr !important;
            gap: 0.85rem !important;
        }
        .form-grid-2col > .form-group[style*="grid-column"] {
            grid-column: span 1 !important;
        }

        /* Tombol Form Submit Full Width di Mobile */
        .form-submit-row {
            text-align: stretch !important;
        }
        .form-submit-row .btn {
            width: 100%;
            justify-content: center;
        }

        /* Responsive Toolbars Lembar 3 & Lembar 4 */
        .slide-toolbar {
            padding: 0.65rem 0.85rem;
            border-radius: 16px;
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .slide-pills {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            flex-wrap: nowrap;
        }
        .slide-pills::-webkit-scrollbar {
            display: none;
        }
        .slide-toolbar > div:last-child {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .slide-toolbar > div:last-child .btn {
            flex: 1;
            min-width: 130px;
            justify-content: center;
        }

        /* Card Rekap Presensi Lembar 4 */
        .att-recap-box {
            flex-direction: column !important;
            gap: 1.5rem !important;
            padding: 1.25rem !important;
        }

        /* Grid Penilaian DU/DI Lembar 5 */
        .eval-grid-layout {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
        }

        /* Paper Sheet A4 Simulation di HP */
        .paper-sheet-a4 {
            padding: 1.25rem 0.85rem;
            border-radius: 14px;
            font-size: 0.85rem;
            line-height: 1.5;
        }
        .paper-sheet-a4 table {
            font-size: 0.8rem;
        }

        /* Modal Dialog di HP */
        .modal-box {
            padding: 1.5rem 1.25rem;
            width: 95%;
            border-radius: 20px;
        }
    }
</style>
@endpush

@section('content')
@php
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $todayIndo = now()->format('j') . ' ' . ($bulanIndo[(int) now()->format('n')] ?? now()->format('F')) . ' ' . now()->format('Y');
@endphp
<!-- Switcher Antar Siswa (1 sampai 5 Siswa) -->
<div class="student-switcher-bar">
    <div class="switcher-label">
        <svg class="icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Pilih Siswa ({{ $allStudents->count() }}/5):
    </div>
    <div class="switcher-pills">
        @foreach($allStudents as $idx => $s)
            <a href="{{ route('students.show', [$s->id, 'tab' => $activeTab]) }}" data-student-id="{{ $s->id }}" class="switcher-pill {{ $s->id === $student->id ? 'active' : '' }}">
                <span style="font-weight:800;">#{{ $idx + 1 }}</span>
                {{ $s->name }} ({{ $s->nis }})
            </a>
        @endforeach

        @if($allStudents->count() < 5)
            <a href="{{ route('students.create') }}" class="switcher-pill" style="border-style:dashed;color:var(--primary);" title="Tambah Siswa">
                <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Siswa
            </a>
        @endif
    </div>
</div>

<!-- Header Info Siswa Aktif & Shortcut Cetak -->
<div class="workspace-header">
    <div class="student-brief">
        @if($student->photo_url)
            <img src="{{ $student->photo_url }}" alt="Foto {{ $student->name }}" class="student-brief-img">
        @else
            <div class="student-brief-img">Pas Foto<br>3x4</div>
        @endif
        <div class="student-brief-info">
            <h2>{{ $student->name }}</h2>
            <p>NIS: <strong>{{ $student->nis }}</strong> &bull; Kelas: <strong>{{ $student->class }}</strong> &bull; Jurusan: <strong>{{ $student->major }}</strong> &bull; Guru: <strong>{{ $student->teacher_name }}</strong></p>
        </div>
    </div>

    <div style="display:flex;gap:0.75rem;align-items:center;">
        <a href="{{ route('students.print', $student->id) }}" target="_blank" class="btn btn-secondary">
            <svg class="icon" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Berkas Fisik A4 (5 Lembar)
        </a>
    </div>
</div>

<!-- Tata Letak Dokumen Word (Sidebar Panel Navigasi Vertikal di Kiri + Lembar Konten di Kanan) -->
<div class="word-document-layout">
    <!-- Sidebar Panel Navigasi Vertikal ala Microsoft Word (Navigation Pane) -->
    <aside class="word-nav-sidebar">
        <div class="word-nav-header">
            <div class="word-nav-title">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" style="color:#2563eb;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span>Halaman Berkas</span>
            </div>
            <span class="word-nav-count">5 Lembar</span>
        </div>

        @php
            $currentTab = request('tab', $activeTab ?? 'cover');
        @endphp
        <nav class="word-nav-list" id="wordNavList">
            <!-- 1. Cover Laporan -->
            <a href="#page-1-cover" class="word-nav-item {{ $currentTab === 'cover' ? 'active' : '' }}" data-target="page-1-cover" data-tab="cover" onclick="jumpToWordPage('page-1-cover', this); return false;" title="Halaman 1: Cover Laporan">
                <div class="word-page-num">
                    <span>1</span>
                    <small>Hal</small>
                </div>
                <div class="word-nav-info">
                    <span class="word-nav-name">Cover Laporan</span>
                    <span class="word-nav-desc">Sampul Depan Map</span>
                </div>
                <svg class="word-nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <!-- 2. Identitas Peserta -->
            <a href="#page-2-identitas" class="word-nav-item {{ $currentTab === 'identitas' ? 'active' : '' }}" data-target="page-2-identitas" data-tab="identitas" onclick="jumpToWordPage('page-2-identitas', this); return false;" title="Halaman 2: Identitas Peserta">
                <div class="word-page-num">
                    <span>2</span>
                    <small>Hal</small>
                </div>
                <div class="word-nav-info">
                    <span class="word-nav-name">Identitas Peserta</span>
                    <span class="word-nav-desc">Biodata & Tempat DUDI</span>
                </div>
                <svg class="word-nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <!-- 3. Uraian Kegiatan -->
            <a href="#page-3-uraian" class="word-nav-item {{ $currentTab === 'uraian' ? 'active' : '' }}" data-target="page-3-uraian" data-tab="uraian" onclick="jumpToWordPage('page-3-uraian', this); return false;" title="Halaman 3: Uraian Kegiatan">
                <div class="word-page-num">
                    <span>3</span>
                    <small>Hal</small>
                </div>
                <div class="word-nav-info">
                    <span class="word-nav-name">Uraian Kegiatan</span>
                    <span class="word-nav-desc">Jurnal Pekerjaan Harian</span>
                </div>
                <svg class="word-nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <!-- 4. Daftar Hadir Magang -->
            <a href="#page-4-presensi" class="word-nav-item {{ $currentTab === 'presensi' ? 'active' : '' }}" data-target="page-4-presensi" data-tab="presensi" onclick="jumpToWordPage('page-4-presensi', this); return false;" title="Halaman 4: Daftar Hadir Magang">
                <div class="word-page-num">
                    <span>4</span>
                    <small>Hal</small>
                </div>
                <div class="word-nav-info">
                    <span class="word-nav-name">Daftar Hadir</span>
                    <span class="word-nav-desc">Presensi 30 Hari</span>
                </div>
                <svg class="word-nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>

            <!-- 5. Lembar Penilaian -->
            <a href="#page-5-penilaian" class="word-nav-item {{ $currentTab === 'penilaian' ? 'active' : '' }}" data-target="page-5-penilaian" data-tab="penilaian" onclick="jumpToWordPage('page-5-penilaian', this); return false;" title="Halaman 5: Lembar Penilaian">
                <div class="word-page-num">
                    <span>5</span>
                    <small>Hal</small>
                </div>
                <div class="word-nav-info">
                    <span class="word-nav-name">Lembar Penilaian</span>
                    <span class="word-nav-desc">Nilai DU/DI Kelurahan</span>
                </div>
                <svg class="word-nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </nav>

        <div class="word-nav-footer">
            <div class="word-nav-footer-badge">
                Tahun Pelajaran {{ $student->academic_year ?: '2026/2027' }}
            </div>
            <div style="display:flex;flex-direction:column;gap:0.4rem;">
                <a href="{{ route('students.print', $student->id) }}" target="_blank" class="btn-sidebar-print" title="Buka lembar cetak fisik 5 halaman A4">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Cetak 5 Lembar A4
                </a>
                <a href="{{ route('students.print', $student->id) }}" target="_blank" class="btn-sidebar-pdf" title="Buka dan unduh dokumen berkas format PDF">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </a>
            </div>
        </div>
    </aside>

    <!-- Area Lembar Kerja Dokumen di Sebelah Kanan (Kertas Laporan) -->
    <main class="word-document-content">

<!-- HALAMAN 1: COVER LAPORAN (FOTO 1) -->
<div class="tab-card word-page-section" id="page-1-cover">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <h3 style="font-size:1.25rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.02em;">Lembar 1: Sampul / Cover Laporan Magang</h3>
            <p style="font-size:0.85rem;color:var(--slate-500);margin-top:0.2rem;">Format presisi berkas fisik map siswa dengan pemilih jurusan & angkatan terintegrasi.</p>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
            <a href="{{ route('students.print', $student->id) }}" target="_blank" class="btn-pill btn-pill-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Lembar A4
            </a>
            <a href="{{ route('students.print', $student->id) }}" target="_blank" class="btn-pill btn-pill-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download PDF
            </a>
        </div>
    </div>

    <!-- Floating Interactive Control Bar (Custom Styled Popover Selectors - 100% Anti-Kotak Kaku) -->
    <div class="cover-control-bar">
        <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
            <!-- Custom Major Popover Dropdown -->
            <div class="custom-dropdown-container" id="majorDropdownContainer">
                <button type="button" class="custom-dropdown-trigger" id="majorDropdownTrigger" onclick="toggleCustomDropdown('majorDropdownMenu', 'majorDropdownTrigger')">
                    <span class="trigger-icon" id="majorTriggerIcon">
                        @if(str_contains($student->major, 'Rekayasa'))
                            <span class="item-badge badge-rpl" style="font-size:0.68rem;padding:2px 6px;">RPL</span>
                        @elseif(str_contains($student->major, 'Perkantoran'))
                            <span class="item-badge badge-mp" style="font-size:0.68rem;padding:2px 6px;">MP</span>
                        @elseif(str_contains($student->major, 'Retail'))
                            <span class="item-badge badge-br" style="font-size:0.68rem;padding:2px 6px;">BR</span>
                        @elseif(str_contains($student->major, 'Digital'))
                            <span class="item-badge badge-bd" style="font-size:0.68rem;padding:2px 6px;">BD</span>
                        @else
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        @endif
                    </span>
                    <span class="trigger-label">PILIH JURUSAN:</span>
                    <span class="trigger-selected" id="selectedMajorText">{{ $student->major }} ({{ $student->class }})</span>
                    <svg class="trigger-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <!-- Floating Menu Card -->
                <div class="custom-dropdown-menu" id="majorDropdownMenu">
                    <div class="dropdown-header">Pilihan Jurusan SMK Negeri 20</div>
                    <div class="dropdown-list">
                        <!-- RPL: Hitam -->
                        <div class="dropdown-item major-option rpl-opt {{ $student->major === 'Rekayasa Perangkat Lunak' ? 'active' : '' }}" onclick="selectCustomMajor('Rekayasa Perangkat Lunak', 'XI RPL', 'Bisnis Manajemen dan Teknologi')">
                            <span class="item-badge badge-rpl">XI RPL</span>
                            <div class="item-content">
                                <div class="item-title">Rekayasa Perangkat Lunak</div>
                                <div class="item-sub">Bisnis Manajemen dan Teknologi</div>
                            </div>
                            <div class="item-check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </div>

                        <!-- MP 1: Oren -->
                        <div class="dropdown-item major-option mp-opt {{ $student->major === 'Manajemen Perkantoran 1' ? 'active' : '' }}" onclick="selectCustomMajor('Manajemen Perkantoran 1', 'XI MP 1', 'Bisnis Manajemen dan Teknologi')">
                            <span class="item-badge badge-mp">XI MP 1</span>
                            <div class="item-content">
                                <div class="item-title">Manajemen Perkantoran 1</div>
                                <div class="item-sub">Bisnis Manajemen dan Teknologi</div>
                            </div>
                            <div class="item-check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </div>

                        <!-- MP 2: Oren -->
                        <div class="dropdown-item major-option mp-opt {{ $student->major === 'Manajemen Perkantoran 2' ? 'active' : '' }}" onclick="selectCustomMajor('Manajemen Perkantoran 2', 'XI MP 2', 'Bisnis Manajemen dan Teknologi')">
                            <span class="item-badge badge-mp">XI MP 2</span>
                            <div class="item-content">
                                <div class="item-title">Manajemen Perkantoran 2</div>
                                <div class="item-sub">Bisnis Manajemen dan Teknologi</div>
                            </div>
                            <div class="item-check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </div>

                        <!-- BR: Biru Tua -->
                        <div class="dropdown-item major-option br-opt {{ $student->major === 'Bisnis Retail' ? 'active' : '' }}" onclick="selectCustomMajor('Bisnis Retail', 'XI BR', 'Bisnis Manajemen dan Teknologi')">
                            <span class="item-badge badge-br">XI BR</span>
                            <div class="item-content">
                                <div class="item-title">Bisnis Retail</div>
                                <div class="item-sub">Bisnis Manajemen dan Teknologi</div>
                            </div>
                            <div class="item-check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </div>

                        <!-- BD: Biru Muda -->
                        <div class="dropdown-item major-option bd-opt {{ $student->major === 'Bisnis Digital' ? 'active' : '' }}" onclick="selectCustomMajor('Bisnis Digital', 'XI BD', 'Bisnis Manajemen dan Teknologi')">
                            <span class="item-badge badge-bd">XI BD</span>
                            <div class="item-content">
                                <div class="item-title">Bisnis Digital</div>
                                <div class="item-sub">Bisnis Manajemen dan Teknologi</div>
                            </div>
                            <div class="item-check"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <div class="dropdown-item item-custom" onclick="promptCustomMajorDialog()">
                            <span class="item-badge badge-custom">+</span>
                            <div class="item-content">
                                <div class="item-title" style="color:#2563eb;">Kustom / Jurusan Lain...</div>
                                <div class="item-sub">Bisa input nama jurusan & kelas bebas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tahun Pelajaran Resmi (Tahun 2026/2027 Saja Sesuai Permintaan) -->
            <div class="custom-dropdown-container">
                <div class="custom-dropdown-trigger" style="cursor:default;background:#f8fafc;border-color:#cbd5e1;" title="Tahun Pelajaran Resmi 2026/2027">
                    <span class="trigger-icon" style="color:#0284c7;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <span class="trigger-label">TAHUN:</span>
                    <span class="trigger-selected" style="color:#0f172a;font-weight:800;">2026/2027</span>
                    <span style="font-size:0.68rem;font-weight:700;background:#ecfdf5;color:#065f46;padding:2px 8px;border-radius:9999px;margin-left:4px;">Aktif</span>
                </div>
            </div>
        </div>

        <div id="quickSaveStatus" style="font-size:0.82rem;font-weight:700;color:#059669;display:none;align-items:center;gap:0.35rem;background:#ecfdf5;padding:0.3rem 0.75rem;border-radius:9999px;">
            <svg class="icon" viewBox="0 0 24 24" style="width:14px;height:14px;color:#059669;"><polyline points="20 6 9 17 4 12"/></svg>
            Tersimpan Otomatis
        </div>
    </div>

    <div class="cover-container">
        <!-- SVG Corner Graphics (Seperti di Foto 1) -->
        <svg class="cover-corner-top-left" viewBox="0 0 100 100">
            <path d="M0,0 L70,0 L0,70 Z" fill="#2563eb" opacity="0.15"/>
            <path d="M0,0 L45,0 L0,45 Z" fill="#1d4ed8" opacity="0.3"/>
            <rect x="15" y="-10" width="12" height="50" rx="6" transform="rotate(45 15 -10)" fill="#3b82f6"/>
        </svg>

        <svg class="cover-corner-bottom-right" viewBox="0 0 100 100">
            <path d="M100,100 L30,100 L100,30 Z" fill="#1e3a8a" opacity="0.15"/>
            <rect x="65" y="45" width="28" height="40" rx="8" fill="#2563eb" opacity="0.85"/>
            <rect x="55" y="60" width="30" height="35" rx="8" fill="none" stroke="#60a5fa" stroke-width="2"/>
        </svg>

        <!-- Header Logos: Jaya Raya & SMK 20 -->
        <div class="cover-header-logos">
            <div class="cover-logo-box">
                <img src="{{ asset('images/logo-jayaraya.png') }}" alt="Logo Jaya Raya DKI Jakarta" style="height:80px;width:auto;max-width:80px;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            </div>

            <div class="cover-logo-box">
                <img src="{{ asset('images/logo-smkn20.png') }}" alt="Logo SMK Negeri 20 Jakarta" style="height:80px;width:auto;max-width:80px;object-fit:contain;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="cover-title-area">
            <h1>LAPORAN</h1>
            <h2>KEGIATAN KELAS INDUSTRI</h2>
            <div class="cover-school-name">{{ strtoupper($student->school_name) }}</div>
            <div class="cover-year" id="coverYearDisplay">TAHUN PELAJARAN {{ $student->academic_year ?: '2026/2027' }}</div>
        </div>

        <!-- Bidang Keahlian -->
        <div class="cover-field-box">
            <span>BIDANG KEAHLIAN</span>
            <span id="coverFieldDisplay">{{ strtoupper($student->field_of_expertise ?: 'BISNIS MANAJEMEN DAN TEKNOLOGI') }}</span>
        </div>

        <!-- Rincian Data Siswa -->
        <table class="cover-meta-table">
            <tr>
                <td style="width:160px;">Nama Siswa</td>
                <td style="width:20px;">:</td>
                <td class="cover-dots">{{ $student->name }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td class="cover-dots" id="coverClassDisplay">{{ $student->class }}</td>
            </tr>
            <tr>
                <td>Program Keahlian</td>
                <td>:</td>
                <td class="cover-dots" id="coverMajorDisplay">{{ $student->major }}</td>
            </tr>
            <tr>
                <td>Periode Magang</td>
                <td>:</td>
                <td class="cover-dots" id="coverPeriodDisplay">{{ $student->internship_period ?: '01 Oktober 2026 - 30 November 2026' }}</td>
            </tr>
        </table>
    </div>
</div>

<!-- PEMISAH HALAMAN 2: IDENTITAS PESERTA -->
<div class="word-page-divider">
    <div class="word-page-divider-line"></div>
    <span class="word-page-divider-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:#2563eb;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Halaman 2 dari 5 &bull; Identitas Peserta Magang
    </span>
    <div class="word-page-divider-line"></div>
</div>

<div class="tab-card word-page-section" id="page-2-identitas">
    <!-- Header Tab & View Mode Switcher -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;border-bottom:1px solid var(--slate-200);padding-bottom:1rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <h3 style="font-size:1.25rem;font-weight:800;color:var(--slate-900);">Lembar 2: Identitas Peserta Magang</h3>
            <p style="font-size:0.85rem;color:var(--slate-500);">Data tersusun rapi per section (Data Diri Siswa, Orang Tua, Sekolah, DU/DI, Pas Foto 3x4).</p>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <button type="button" id="btnViewForm" class="btn btn-primary btn-sm" onclick="switchIdentitasMode('form')">
                <svg class="icon" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Formulir Terstruktur
            </button>
            <button type="button" id="btnViewPaper" class="btn btn-secondary btn-sm" onclick="switchIdentitasMode('paper')">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Format Berkas Fisik A4 (Foto 2)
            </button>
        </div>
    </div>

    <!-- VIEW 1: FORMULIR TERSTRUKTUR (KOTAK-KOTAK RAPI TERSUSUN) -->
    <div id="identitasFormView">
        <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data" onsubmit="preserveScrollPosition()">
            @csrf
            @method('PUT')

            <div class="identitas-grid-layout" style="display:grid;grid-template-columns:1fr 280px;gap:2rem;align-items:start;">
                <!-- Kolom Utama: 3 Bagian Kartu Tersusun Rapi -->
                <div>
                    <!-- Bagian 1: Data Diri Siswa -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <span class="form-section-icon">
                                    <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                1. Data Pribadi Siswa
                            </div>
                            <span class="badge badge-blue">Siswa Aktif</span>
                        </div>

                        <div class="form-grid-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <!-- Nama Lengkap (Full Width) -->
                            <div class="form-group" style="grid-column:span 2;">
                                <label class="form-label">Nama Siswa <span class="required">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $student->name) }}" placeholder="Nama Lengkap Siswa" required>
                            </div>

                            <!-- NIS & NISN -->
                            <div class="form-group">
                                <label class="form-label">Nomor Induk Siswa (NIS) <span class="required">*</span></label>
                                <input type="text" name="nis" class="form-control" value="{{ old('nis', $student->nis) }}" placeholder="14450" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">NISN</label>
                                <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $student->nisn) }}" placeholder="0081234567">
                            </div>

                            <!-- Kelas & Kompetensi Keahlian (Jurusan) -->
                            <div class="form-group">
                                <label class="form-label">Tingkat / Kelas <span class="required">*</span></label>
                                <input type="text" name="class" class="form-control" value="{{ old('class', $student->class ?: 'XI') }}" placeholder="XI" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kompetensi Keahlian (Jurusan) <span class="required">*</span></label>
                                <input type="text" name="major" class="form-control" value="{{ old('major', $student->major ?: 'Rekayasa Perangkat Lunak') }}" placeholder="Rekayasa Perangkat Lunak" required>
                                <input type="hidden" name="field_of_expertise" value="{{ old('field_of_expertise', $student->field_of_expertise ?: 'Bisnis Manajemen dan Teknologi') }}">
                            </div>

                            <!-- Tempat Lahir & Tanggal Lahir -->
                            <div class="form-group">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="birth_place" class="form-control" value="{{ old('birth_place', $student->birth_place) }}" placeholder="Jakarta">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="text" name="birth_date" class="form-control" value="{{ old('birth_date', $student->birth_date) }}" placeholder="09 Desember 2008">
                            </div>

                            <!-- Jenis Kelamin & Agama -->
                            <div class="form-group">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" class="form-control">
                                    <option value="Laki-Laki" {{ old('gender', $student->gender) === 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
                                    <option value="Perempuan" {{ old('gender', $student->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Agama</label>
                                <input type="text" name="religion" class="form-control" value="{{ old('religion', $student->religion) }}" placeholder="Islam">
                            </div>

                            <!-- Nomor HP & Periode Magang -->
                            <div class="form-group">
                                <label class="form-label">Nomor HP / WhatsApp Siswa</label>
                                <input type="text" name="student_phone" class="form-control" value="{{ old('student_phone', $student->student_phone) }}" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Periode Magang</label>
                                <input type="text" name="internship_period" class="form-control" value="{{ old('internship_period', $student->internship_period ?: '01 Oktober 2026 - 30 November 2026') }}">
                            </div>

                            <!-- Alamat Lengkap Siswa -->
                            <div class="form-group" style="grid-column:span 2;">
                                <label class="form-label">Alamat Lengkap Siswa</label>
                                <textarea name="student_address" class="form-control form-control-textarea" rows="2" placeholder="Jl. Alamat Tempat Tinggal Siswa">{{ old('student_address', $student->student_address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 2: Data Orang Tua / Wali -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <span class="form-section-icon" style="background:#fef3c7;color:#d97706;">
                                    <svg class="icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </span>
                                2. Data Orang Tua / Wali
                            </div>
                        </div>

                        <div class="form-grid-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <!-- Ayah -->
                            <div class="form-group">
                                <label class="form-label">Nama Ayah</label>
                                <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}" placeholder="Nama Lengkap Ayah / Wali">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor HP Ayah</label>
                                <input type="text" name="father_phone" class="form-control" value="{{ old('father_phone', $student->father_phone) }}" placeholder="08xxxxxxxxxx">
                            </div>

                            <!-- Ibu -->
                            <div class="form-group">
                                <label class="form-label">Nama Ibu</label>
                                <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}" placeholder="Nama Lengkap Ibu / Wali">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor HP Ibu</label>
                                <input type="text" name="mother_phone" class="form-control" value="{{ old('mother_phone', $student->mother_phone) }}" placeholder="08xxxxxxxxxx">
                            </div>

                            <!-- Alamat Orang Tua -->
                            <div class="form-group" style="grid-column:span 2;">
                                <label class="form-label">Alamat Lengkap Orang Tua</label>
                                <textarea name="parent_address" class="form-control form-control-textarea" rows="2" placeholder="Jl. Alamat Orang Tua Siswa">{{ old('parent_address', $student->parent_address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 3: Data Sekolah & DU/DI (Tempat Praktik) -->
                    <div class="form-section-card">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <span class="form-section-icon" style="background:#ecfdf5;color:#059669;">
                                    <svg class="icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                </span>
                                3. Sekolah & Tempat Praktik (DU/DI)
                            </div>
                        </div>

                        <div class="form-grid-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <!-- Sekolah -->
                            <div class="form-group">
                                <label class="form-label">Nama Sekolah <span class="required">*</span></label>
                                <input type="text" name="school_name" class="form-control" value="{{ old('school_name', $student->school_name) }}" placeholder="SMK Negeri 20 Jakarta" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat Sekolah</label>
                                <input type="text" name="school_address" class="form-control" value="{{ old('school_address', $student->school_address) }}" placeholder="Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kota Jaksel">
                            </div>

                            <!-- DU/DI -->
                            <div class="form-group">
                                <label class="form-label">Nama Tempat Praktik (DU/DI) <span class="required">*</span></label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $student->company_name) }}" placeholder="Kantor Lurah Bintaro" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Alamat Tempat Praktik</label>
                                <input type="text" name="company_address" class="form-control" value="{{ old('company_address', $student->company_address) }}" placeholder="Jl. B.C. Veteran Raya No. 1, RT 1/RW 3 Bintaro">
                            </div>

                            <!-- Guru Pembimbing & No HP -->
                            <div class="form-group">
                                <label class="form-label">Nama Guru Pembimbing <span class="required">*</span></label>
                                <input type="text" name="teacher_name" class="form-control" value="{{ old('teacher_name', $student->teacher_name) }}" placeholder="Kuswanto" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor HP Guru Pembimbing</label>
                                <input type="text" name="teacher_phone" class="form-control" value="{{ old('teacher_phone', $student->teacher_phone) }}" placeholder="0858-8192-1976">
                            </div>

                            <!-- Pembimbing Industri (Mentor) -->
                            <div class="form-group" style="grid-column:span 2;">
                                <label class="form-label">Pembimbing Industri (Mentor DU/DI)</label>
                                <input type="text" name="mentor_name" class="form-control" value="{{ old('mentor_name', $student->mentor_name ?: 'Sujatmo, S.Sos') }}" placeholder="Nama Pembimbing Industri">
                            </div>

                            <!-- Tanda Tangan / Paraf Mentor Industri -->
                            <div class="form-group" style="grid-column:span 2;">
                                <label class="form-label">Tanda Tangan / Paraf Pembimbing Industri</label>
                                <div style="display:flex;align-items:center;gap:1.25rem;background:#f8fafc;padding:0.85rem 1rem;border-radius:12px;border:1px solid #e2e8f0;">
                                    <div id="mentorSigPreviewBox" style="width:140px;height:65px;border:1px dashed #cbd5e1;border-radius:8px;background:#ffffff;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                        @if($student->mentor_signature_url)
                                            <img src="{{ $student->mentor_signature_url }}" id="mentorSigImg" alt="Tanda Tangan Mentor" style="max-height:55px;max-width:125px;object-fit:contain;mix-blend-mode:multiply;">
                                        @else
                                            <span id="mentorSigPlaceholder" style="font-size:0.75rem;color:#94a3b8;font-style:italic;">Belum ada TTD</span>
                                            <img src="" id="mentorSigImg" alt="Tanda Tangan Mentor" style="max-height:55px;max-width:125px;object-fit:contain;mix-blend-mode:multiply;display:none;">
                                        @endif
                                    </div>
                                    <div>
                                        <input type="hidden" name="mentor_signature_data" id="mentorSigData" value="">
                                        <input type="hidden" name="remove_mentor_signature" id="removeMentorSigInput" value="0">
                                        <input type="file" id="mentorSigFileInput" name="mentor_signature" accept="image/*" style="display:none;" onchange="handleMentorSigSelected(this)">
                                        
                                        <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                                            <label for="mentorSigFileInput" class="btn btn-secondary btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:0.35rem;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                                Upload Foto TTD
                                            </label>
                                            <button type="button" class="btn btn-outline-danger btn-sm" id="btnRemoveMentorSig" style="{{ $student->mentor_signature_url ? '' : 'display:none;' }}" onclick="removeMentorSignature()">
                                                Hapus
                                            </button>
                                        </div>
                                        <p style="font-size:0.72rem;color:var(--slate-500);margin-top:0.35rem;margin-bottom:0;">
                                            Otomatis transparan & tanpa latar foto saat dicetak ke Lembar 4 & 5.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="form-submit-row" style="text-align:right;margin-top:1.5rem;">
                        <button type="submit" class="btn btn-primary" style="padding:0.65rem 1.75rem;font-size:0.95rem;">
                            <svg class="icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            Simpan Perubahan Identitas
                        </button>
                    </div>
                </div>

                <!-- Sidebar Kanan: Pas Foto 3x4 & Kartu Status Berkas -->
                <div>
                    <!-- Kartu Pas Foto 3x4 -->
                    <div class="form-section-card" style="text-align:center;">
                        <h4 style="font-size:0.95rem;font-weight:800;color:var(--slate-800);margin-bottom:0.35rem;">Pas Foto Siswa (3x4)</h4>
                        <p style="font-size:0.75rem;color:var(--slate-500);margin-bottom:1rem;">Foto resmi berkas magang</p>

                        <!-- Box Foto 3x4 Presisi -->
                        <div id="photoPreviewBox" style="width:130px;height:173px;margin:0 auto 1.25rem;border:2px dashed #cbd5e1;border-radius:16px;overflow:hidden;background:#f8fafc;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.03);">
                            @if($student->photo_url)
                                <img src="{{ $student->photo_url }}" alt="Pas Foto {{ $student->name }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <div style="text-align:center;padding:1rem;">
                                    <svg style="width:36px;height:36px;color:#94a3b8;margin:0 auto 0.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                    <span style="font-size:0.75rem;font-weight:600;color:var(--slate-500);display:block;line-height:1.3;">Pas Foto 3x4<br>Belum Ada</span>
                                </div>
                            @endif
                        </div>

                        <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewShowPhoto(event)">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('photoInput').click()" style="width:100%;">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Ganti / Unggah Foto
                        </button>
                        <small style="display:block;font-size:0.75rem;color:var(--slate-400);margin-top:0.5rem;">JPG/PNG/WEBP maks 2MB</small>
                    </div>

                    <!-- Kartu Status Berkas & Sinkronisasi Cloud -->
                    <div class="form-section-card" style="background:#f8fafc;border-color:#e2e8f0;">
                        <h5 style="font-size:0.85rem;font-weight:800;color:var(--slate-800);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.4rem;">
                            <span style="width:8px;height:8px;border-radius:50%;background:#10b981;"></span>
                            Status Berkas
                        </h5>
                        <ul style="list-style:none;padding:0;margin:0;font-size:0.8rem;color:var(--slate-600);line-height:1.8;">
                            <li>&bull; <strong>Database:</strong> Supabase Cloud</li>
                            <li>&bull; <strong>Keamanan:</strong> RLS Enabled</li>
                            <li>&bull; <strong>Format:</strong> Sesuai Foto 2 Fisik</li>
                            <li>&bull; <strong>Terakhir Diubah:</strong> {{ $student->updated_at ? $student->updated_at->format('d/m/Y H:i') : 'Baru saja' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- VIEW 2: FORMAT BERKAS FISIK ASLI A4 (PERSIS FOTO 2) -->
    <div id="identitasPaperView" style="display:none;">
        <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-bottom:1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                <svg class="icon" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Lembar A4 (Print / PDF)
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="switchIdentitasMode('form')">
                Kembali ke Formulir Edit
            </button>
        </div>

        <div class="paper-sheet-a4">
            <!-- Kop / Header Berkas Fisik dengan Logo Resmi SMKN 20 & Jaya Raya -->
            <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:3px double #000000;padding-bottom:0.75rem;margin-bottom:1.75rem;">
                <img src="{{ asset('images/logo-jayaraya.png') }}" alt="Logo DKI Jakarta" style="height:65px;width:auto;object-fit:contain;">
                <div style="text-align:center;flex:1;padding:0 1rem;">
                    <div style="font-size:1rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">Pemerintah Provinsi Daerah Khusus Ibukota Jakarta</div>
                    <div style="font-size:0.9rem;font-weight:700;">DINAS PENDIDIKAN</div>
                    <div style="font-size:1.15rem;font-weight:900;letter-spacing:0.04em;">SMK NEGERI 20 JAKARTA</div>
                    <div style="font-size:0.75rem;font-style:italic;">Jl. Melati No. 24 RT 13/RW 10, Cilandak Barat, Cilandak, Jakarta Selatan</div>
                </div>
                <img src="{{ asset('images/logo-smkn20.png') }}" alt="Logo SMKN 20" style="height:65px;width:auto;object-fit:contain;">
            </div>

            <!-- Judul Halaman Sesuai Foto 2 -->
            <h2 style="text-align:center;font-size:1.35rem;font-weight:800;letter-spacing:0.08em;margin-bottom:2rem;text-transform:uppercase;">
                IDENTITAS PESERTA
            </h2>

            <!-- Daftar Identitas dengan Titik-Titik Sesuai Format Asli -->
            <table style="width:100%;font-size:1rem;border-collapse:separate;border-spacing:0 0.45rem;color:#000000;">
                <tr>
                    <td style="width:230px;vertical-align:top;">Nama Siswa</td>
                    <td style="width:20px;vertical-align:top;">:</td>
                    <td class="paper-dotted" style="font-weight:700;">{{ $student->name }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Nomor Induk Siswa</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->nis }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Tempat, Tanggal Lahir</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->birth_place ?: '-' }}{{ $student->birth_date ? ', ' . $student->birth_date : '' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Jenis Kelamin</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->gender ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Agama</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->religion ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Alamat</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->student_address ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Nomor HP</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->student_phone ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Nama Orang Tua</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted"></td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Ayah</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->father_name ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Nomor HP</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->father_phone ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Ibu</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->mother_name ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Nomor HP</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->mother_phone ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Alamat Orang Tua</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->parent_address ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Nama Sekolah</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->school_name ?: 'SMK Negeri 20 Jakarta' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Alamat</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->school_address ?: 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kota Jaksel' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Nama Tempat Praktik</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->company_name ?: 'Kantor Lurah Bintaro' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Alamat</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->company_address ?: 'Jl. B.C. Veteran Raya No. 1, RT 1/RW 3 Bintaro' }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;">Nama Guru Pembimbing</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->teacher_name ?: 'Kuswanto' }}</td>
                </tr>
                <tr>
                    <td style="padding-left:1.5rem;vertical-align:top;">Nomor HP</td>
                    <td style="vertical-align:top;">:</td>
                    <td class="paper-dotted">{{ $student->teacher_phone ?: '0858-8192-1976' }}</td>
                </tr>
            </table>

            <!-- Kolom Bawah Sesuai Foto 2: Kotak Pas Foto 3x4 di Kiri Bawah & Tanda Tangan di Kanan Bawah -->
            <div style="margin-top:2.5rem;display:flex;justify-content:space-between;align-items:flex-end;">
                <!-- Bingkai Pas Foto 3x4 Asli -->
                <div style="width:115px;height:155px;border:1.5px solid #000000;display:flex;align-items:center;justify-content:center;background:#ffffff;overflow:hidden;">
                    @if($student->photo_url)
                        <img src="{{ $student->photo_url }}" alt="Pas Foto" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <span style="font-size:0.8rem;text-align:center;line-height:1.4;">Pas Foto<br>3 x 4</span>
                    @endif
                </div>

                <!-- Tanda Tangan Resmi Sesuai Foto 2 -->
                <div style="text-align:center;min-width:240px;">
                    <div style="font-size:0.95rem;margin-bottom:0.25rem;">Jakarta, {{ $todayIndo }}</div>
                    <div style="font-size:0.95rem;font-weight:600;">Peserta Praktik,</div>
                    <div style="height:70px;"></div>
                    <div style="font-weight:700;font-size:1rem;border-bottom:1px solid #000000;display:inline-block;padding:0 1rem;">
                        {{ $student->name }}
                    </div>
                    <div style="font-size:0.85rem;margin-top:0.2rem;">NIS. {{ $student->nis }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PEMISAH HALAMAN 3: URAIAN KEGIATAN MAGANG -->
<div class="word-page-divider">
    <div class="word-page-divider-line"></div>
    <span class="word-page-divider-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:#2563eb;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Halaman 3 dari 5 &bull; Uraian Kegiatan (Jurnal Harian)
    </span>
    <div class="word-page-divider-line"></div>
</div>

@php
    $selectedSheetId = (request()->query('tab') !== 'presensi' ? request()->query('sheet') : null) ?? $student->activitySheets->first()->id ?? null;
    $currentSheet = $student->activitySheets->firstWhere('id', $selectedSheetId) ?: $student->activitySheets->first();
@endphp
<div class="tab-card word-page-section" id="page-3-uraian">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
        <div>
            <h3 style="font-size:1.25rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.01em;">Lembar 3: Uraian Kegiatan Magang (Jurnal Harian)</h3>
            <p style="font-size:0.85rem;color:var(--slate-500);margin-top:0.25rem;">Format berkas 10 baris per lembar. Gunakan tombol plus <strong>+</strong> untuk menambah slide / lembar berikutnya saat telah mencapai 10 kegiatan.</p>
        </div>
    </div>

    <!-- Slide Carousel Navigation Bar -->
    <div class="slide-toolbar">
        <div class="slide-toolbar-left">
            <span class="slide-toolbar-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:var(--primary);"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                Pilih Slide:
            </span>
            <div class="slide-pills">
                @foreach($student->activitySheets as $sheet)
                    <a href="{{ route('students.show', [$student->id, 'tab' => 'uraian', 'sheet' => $sheet->id]) }}#page-3-uraian" class="slide-pill {{ $sheet->id === $currentSheet->id ? 'active' : '' }}" onclick="preserveScrollPosition()">
                        Slide {{ $sheet->sheet_number }}
                        <span class="slide-pill-sub">({{ $sheet->month_name }})</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="slide-toolbar-actions">
            <!-- Tombol Tambah Lembar Baru dengan SweetAlert2 -->
            <button type="button" class="btn btn-primary btn-sm" onclick="promptAddSheetWithSwal()">
                <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Slide (+)
            </button>

            @if($student->activitySheets->count() > 1)
                <form action="{{ route('activity-sheets.destroy', [$student->id, $currentSheet->id]) }}" method="POST" onsubmit="preserveScrollPosition(); return confirmDelete(event, this, 'Hapus Lembar Slide {{ $currentSheet->sheet_number }}?', 'Data 10 baris kegiatan pada lembar {{ $currentSheet->month_name }} ini akan dihapus permanen.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus slide ini" style="display:inline-flex;align-items:center;justify-content:center;padding:0.45rem 0.65rem;">
                        <svg class="icon" viewBox="0 0 24 24" style="width:16px;height:16px;">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Form 10 Baris Uraian Kegiatan -->
    @if($currentSheet)
    <form action="{{ route('activity-sheets.update', [$student->id, $currentSheet->id]) }}" method="POST" enctype="multipart/form-data" onsubmit="preserveScrollPosition()">
        @csrf
        @method('PUT')

        <div class="sheet-meta-bar">
            <div class="sheet-meta-field">
                <span class="sheet-meta-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary);"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Bulan Kegiatan:
                </span>
                <input type="text" name="month_name" value="{{ old('month_name', $currentSheet->month_name) }}" class="form-control" style="width:260px;font-weight:600;" placeholder="Contoh: Oktober 2026" required>
            </div>
            <span class="badge badge-blue" style="font-weight:700;display:inline-flex;align-items:center;gap:0.35rem;padding:0.4rem 0.85rem;border-radius:8px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Lembar Ke-{{ $currentSheet->sheet_number }} (10 Baris)
            </span>
        </div>

        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table class="data-table" style="min-width:800px;width:100%;">
                <thead>
                    <tr>
                        <th style="width:44px;min-width:44px;text-align:center;">No.</th>
                        <th style="width:170px;min-width:160px;text-align:left;">Hari / Tanggal</th>
                        <th style="width:180px;min-width:170px;text-align:left;">Divisi / Bagian</th>
                        <th style="min-width:250px;text-align:left;">Uraian Pekerjaan</th>
                        <th style="width:200px;min-width:190px;text-align:left;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span>Pembimbing Industri</span>
                                <button type="button" class="btn-batch-paraf" onclick="openBatchParafModal('activity')" title="Terapkan paraf ke semua 10 baris">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                                    Semua
                                </button>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currentSheet->activityLogs as $log)
                        <tr>
                            <td style="text-align:center;font-weight:700;color:var(--slate-700);padding-top:0.95rem;">
                                {{ $log->row_index }}.
                                <input type="hidden" name="logs[{{ $log->id }}][id]" value="{{ $log->id }}">
                            </td>
                            <td>
                                <input type="text" name="logs[{{ $log->id }}][day_date]" value="{{ old("logs.{$log->id}.day_date", $log->day_date) }}" class="table-input" placeholder="e.g. Kamis, 01-10-2026">
                            </td>
                            <td>
                                <input type="text" name="logs[{{ $log->id }}][division]" value="{{ old("logs.{$log->id}.division", $log->division) }}" class="table-input" placeholder="e.g. Perpanjang Makam">
                            </td>
                            <td>
                                <textarea name="logs[{{ $log->id }}][job_description]" class="table-input" rows="2" placeholder="Uraian pekerjaan harian...">{{ old("logs.{$log->id}.job_description", $log->job_description) }}</textarea>
                            </td>
                            <td>
                                <div class="paraf-slot-container" data-row-id="{{ $log->id }}" data-type="log">
                                    <input type="hidden" name="logs[{{ $log->id }}][paraf_data]" id="paraf-data-log-{{ $log->id }}" value="">
                                    <input type="hidden" name="logs[{{ $log->id }}][remove_paraf]" id="remove-paraf-log-{{ $log->id }}" value="0">

                                    <input type="text" name="logs[{{ $log->id }}][supervisor_note]" value="{{ old("logs.{$log->id}.supervisor_note", $log->supervisor_note) }}" class="table-input" placeholder="Paraf/Catatan">

                                    <!-- Paraf Photo Preview & Upload Actions -->
                                    <div class="paraf-preview-box" id="paraf-box-log-{{ $log->id }}" style="{{ $log->paraf_url ? '' : 'display:none;' }}">
                                        <img src="{{ $log->paraf_url ?? '' }}" id="paraf-img-log-{{ $log->id }}" alt="Paraf Baris {{ $log->row_index }}" class="paraf-thumb-img">
                                        <div class="paraf-actions">
                                            <label for="paraf-file-log-{{ $log->id }}" class="btn-paraf-action" title="Ganti foto paraf">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            </label>
                                            <button type="button" class="btn-paraf-action btn-paraf-remove" onclick="removeRowParaf('log', {{ $log->id }})" title="Hapus foto paraf">
                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Upload Button (Shown when no paraf yet) -->
                                    <div id="paraf-upload-btn-log-{{ $log->id }}" style="{{ $log->paraf_url ? 'display:none;' : '' }}">
                                        <label for="paraf-file-log-{{ $log->id }}" class="btn-upload-paraf">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                            Upload Paraf
                                        </label>
                                    </div>

                                    <!-- Hidden File Input for this row -->
                                    <input type="file" id="paraf-file-log-{{ $log->id }}" accept="image/*" style="display:none;" onchange="handleParafFileSelected(this, 'log', {{ $log->id }})">

                                    <label class="approval-toggle">
                                        <input type="checkbox" name="logs[{{ $log->id }}][is_approved]" value="1" {{ $log->is_approved ? 'checked' : '' }} class="approval-toggle-checkbox">
                                        <span class="approval-toggle-indicator">
                                            <svg class="approval-check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        </span>
                                        <span class="approval-toggle-text">Disetujui</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="form-submit-row" style="margin-top:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <span style="font-size:0.85rem;color:var(--slate-500);">Tips: Jika kegiatan telah mencapai baris ke-10, klik tombol <strong>Tambah Slide Lembar Baru (+)</strong> di kanan atas.</span>
            <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:0.45rem;">
                <svg class="icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Lembar Ke-{{ $currentSheet->sheet_number }}
            </button>
        </div>
    </form>
    @endif
</div>

<!-- Modal Tambah Slide Lembar Baru -->
<div id="addSheetModal" class="modal-backdrop">
    <div class="modal-box">
        <h4 style="font-size:1.15rem;font-weight:800;margin-bottom:0.5rem;color:var(--slate-900);">Tambah Lembar Slide Kegiatan (+)</h4>
        <p style="font-size:0.85rem;color:var(--slate-500);margin-bottom:1.25rem;">Lembar baru dengan 10 baris kosong akan otomatis dibuat untuk melanjutkan uraian kegiatan magang siswa.</p>

        <form action="{{ route('activity-sheets.store', $student->id) }}" method="POST">
            @csrf
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Bulan / Periode Kegiatan</label>
                <input type="text" name="month_name" class="form-control" value="Oktober 2026 (Lembar Lanjutan)" required>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSheetModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Lembar Sekarang</button>
            </div>
        </form>
    </div>
</div>
<!-- PEMISAH HALAMAN 4: DAFTAR HADIR MAGANG -->
<div class="word-page-divider">
    <div class="word-page-divider-line"></div>
    <span class="word-page-divider-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:#2563eb;"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Halaman 4 dari 5 &bull; Daftar Hadir (Presensi Magang)
    </span>
    <div class="word-page-divider-line"></div>
</div>

@php
    $selectedAttId = (request()->query('tab') === 'presensi' ? request()->query('sheet') : request()->query('att_sheet')) ?? $student->attendanceSheets->first()->id ?? null;
    $currentAttSheet = $student->attendanceSheets->firstWhere('id', $selectedAttId) ?: $student->attendanceSheets->first();
@endphp
<div class="tab-card word-page-section" id="page-4-presensi">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
        <div>
            <h3 style="font-size:1.25rem;font-weight:800;color:var(--slate-900);">Lembar 4: Daftar Hadir Magang (Presensi Bulanan)</h3>
            <p style="font-size:0.85rem;color:var(--slate-500);">Daftar absensi harian dan rekapitulasi kehadiran (Jumlah Hadir, Sakit, Izin, Alpa) yang dihitung otomatis.</p>
        </div>
    </div>

    <!-- Presensi Month Toolbar -->
    <div class="slide-toolbar">
        <div class="slide-pills">
            <span style="font-size:0.85rem;font-weight:700;color:var(--slate-600);margin-right:0.25rem;">Pilih Bulan:</span>
            @foreach($student->attendanceSheets as $aSheet)
                <a href="{{ route('students.show', [$student->id, 'tab' => 'presensi', 'sheet' => $aSheet->id]) }}#page-4-presensi" class="slide-pill {{ $aSheet->id === $currentAttSheet->id ? 'active' : '' }}" onclick="preserveScrollPosition()">
                    Bulan {{ $aSheet->month_name }}
                </a>
            @endforeach
        </div>

        <div style="display:flex;gap:0.5rem;align-items:center;">
            <form id="formAddDay" action="{{ route('attendance-records.store', [$student->id, $currentAttSheet->id]) }}" method="POST" style="display:inline;" onsubmit="handleAsyncAddDay(event, this)">
                @csrf
                <button type="submit" id="btnAddDay" class="btn btn-secondary btn-sm" title="Tambah 1 hari kehadiran (opsional: Sabtu, Minggu, atau hari kerja tambahan)">
                    <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span id="btnAddDayText">Tambah Hari (+)</span>
                </button>
            </form>

            <button type="button" class="btn btn-primary btn-sm" onclick="promptAddAttSheetWithSwal()">
                <svg class="icon" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Bulan Presensi (+)
            </button>

            @if($student->attendanceSheets->count() > 1)
                <form action="{{ route('attendance-sheets.destroy', [$student->id, $currentAttSheet->id]) }}" method="POST" onsubmit="preserveScrollPosition(); return confirmDelete(event, this, 'Hapus Presensi Bulan {{ $currentAttSheet->month_name }}?', 'Seluruh catatan absensi harian pada bulan ini akan dihapus permanen.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus bulan ini" style="display:inline-flex;align-items:center;justify-content:center;padding:0.45rem 0.65rem;">
                        <svg class="icon" viewBox="0 0 24 24" style="width:16px;height:16px;">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($currentAttSheet)
    <form action="{{ route('attendance-sheets.update', [$student->id, $currentAttSheet->id]) }}" method="POST" enctype="multipart/form-data" onsubmit="preserveScrollPosition()">
        @csrf
        @method('PUT')

        <div class="sheet-meta-bar">
            <div class="sheet-meta-field">
                <span class="sheet-meta-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#16a34a;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Bulan Presensi:
                </span>
                <input type="text" name="month_name" value="{{ old('month_name', $currentAttSheet->month_name) }}" class="form-control" style="width:260px;font-weight:600;" placeholder="Contoh: Oktober 2026" required>
            </div>
            <span class="badge badge-green" id="attTargetDaysBadge" style="font-weight:700;display:inline-flex;align-items:center;gap:0.35rem;padding:0.4rem 0.85rem;border-radius:8px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                {{ $currentAttSheet->total_target_days }} Hari Presensi
            </span>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:45px;">No.</th>
                        <th rowspan="2" style="width:170px;">Hari, Tanggal</th>
                        <th rowspan="2" style="width:75px;">Hadir (H)</th>
                        <th colspan="3" style="width:150px;">Tidak Hadir</th>
                        <th rowspan="2">Keterangan</th>
                        <th rowspan="2" style="width:170px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span>Pembimbing</span>
                                <button type="button" class="btn-batch-paraf" onclick="openBatchParafModal('attendance')" title="Terapkan paraf ke seluruh hari">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                                    Semua
                                </button>
                            </div>
                        </th>
                        <th rowspan="2" style="width:40px;"></th>
                    </tr>
                    <tr>
                        <th style="width:50px;background:#fffbeb;color:#92400e;">S</th>
                        <th style="width:50px;background:#eff6ff;color:#1e40af;">I</th>
                        <th style="width:50px;background:#fef2f2;color:#991b1b;">A</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    @foreach($currentAttSheet->records as $rec)
                        <tr id="row-record-{{ $rec->id }}">
                            <td style="text-align:center;font-weight:700;color:var(--slate-800);" class="day-number-cell">
                                {{ $rec->day_number }}.
                                <input type="hidden" name="records[{{ $rec->id }}][id]" value="{{ $rec->id }}">
                            </td>
                            <td>
                                <input type="text" name="records[{{ $rec->id }}][day_date]" value="{{ old("records.{$rec->id}.day_date", $rec->day_date) }}" class="table-input" placeholder="Hari, DD-MM-YYYY">
                            </td>
                            <!-- Status Radio H, S, I, A -->
                            <td style="text-align:center;">
                                <input type="radio" name="records[{{ $rec->id }}][status]" value="H" {{ $rec->status === 'H' ? 'checked' : '' }} onchange="handleStatusChange({{ $rec->id }}, this)" title="Hadir">
                            </td>
                            <td style="text-align:center;background:#fffbeb;">
                                <input type="radio" name="records[{{ $rec->id }}][status]" value="S" {{ $rec->status === 'S' ? 'checked' : '' }} onchange="handleStatusChange({{ $rec->id }}, this)" title="Sakit">
                            </td>
                            <td style="text-align:center;background:#eff6ff;">
                                <input type="radio" name="records[{{ $rec->id }}][status]" value="I" {{ $rec->status === 'I' ? 'checked' : '' }} onchange="handleStatusChange({{ $rec->id }}, this)" title="Izin">
                            </td>
                            <td style="text-align:center;background:#fef2f2;">
                                <input type="radio" name="records[{{ $rec->id }}][status]" value="A" {{ $rec->status === 'A' ? 'checked' : '' }} onchange="handleStatusChange({{ $rec->id }}, this)" title="Alpa">
                            </td>
                            <td>
                                <input type="text" id="notes-{{ $rec->id }}" name="records[{{ $rec->id }}][notes]" value="{{ old("records.{$rec->id}.notes", $rec->notes) }}" class="table-input" placeholder="Keterangan">
                            </td>
                            <td>
                                <div class="paraf-slot-container" data-row-id="{{ $rec->id }}" data-type="rec">
                                    <input type="hidden" name="records[{{ $rec->id }}][paraf_data]" id="paraf-data-rec-{{ $rec->id }}" value="">
                                    <input type="hidden" name="records[{{ $rec->id }}][remove_paraf]" id="remove-paraf-rec-{{ $rec->id }}" value="0">

                                    <div class="paraf-preview-box" id="paraf-box-rec-{{ $rec->id }}" style="{{ $rec->paraf_url ? '' : 'display:none;' }}">
                                        <img src="{{ $rec->paraf_url ?? '' }}" id="paraf-img-rec-{{ $rec->id }}" alt="Paraf Hari {{ $rec->day_number }}" class="paraf-thumb-img" style="max-height:26px;">
                                        <div class="paraf-actions">
                                            <label for="paraf-file-rec-{{ $rec->id }}" class="btn-paraf-action" title="Ganti foto paraf">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            </label>
                                            <button type="button" class="btn-paraf-action btn-paraf-remove" onclick="removeRowParaf('rec', {{ $rec->id }})" title="Hapus foto paraf">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div id="paraf-upload-btn-rec-{{ $rec->id }}" style="{{ $rec->paraf_url ? 'display:none;' : '' }}">
                                        <label for="paraf-file-rec-{{ $rec->id }}" class="btn-upload-paraf btn-upload-paraf-sm">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                            Paraf
                                        </label>
                                    </div>

                                    <input type="file" id="paraf-file-rec-{{ $rec->id }}" accept="image/*" style="display:none;" onchange="handleParafFileSelected(this, 'rec', {{ $rec->id }})">

                                    <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.72rem;color:var(--slate-600);justify-content:center;margin-top:0.2rem;">
                                        <input type="checkbox" name="records[{{ $rec->id }}][is_signed]" value="1" {{ $rec->is_signed ? 'checked' : '' }}>
                                        Paraf
                                    </label>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($currentAttSheet->records->count() > 1)
                                    <button type="button" onclick="deleteAttendanceRecord({{ $student->id }}, {{ $currentAttSheet->id }}, {{ $rec->id }}, {{ $rec->day_number }})" title="Hapus hari ke-{{ $rec->day_number }}" style="background:none;border:none;cursor:pointer;color:#ef4444;padding:3px;display:inline-flex;align-items:center;justify-content:center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bagian Rekapitulasi Kehadiran (Sesuai Foto 4 - Smooth Elegant Card) -->
        <div class="att-recap-box" style="margin-top:2rem;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:20px;padding:1.75rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:2rem;box-shadow:0 4px 16px rgba(0,0,0,0.03);">
            <div style="flex:1;min-width:280px;">
                <h4 style="font-size:1rem;font-weight:800;color:var(--slate-800);margin-bottom:0.75rem;border-bottom:1px solid #cbd5e1;padding-bottom:0.4rem;">
                    Rekapitulasi Kehadiran
                </h4>
                <table style="width:100%;font-size:0.9rem;border-collapse:separate;border-spacing:0 0.4rem;">
                    <tr>
                        <td style="width:200px;color:var(--slate-700);">Jumlah Seharusnya Hadir</td>
                        <td style="width:20px;">:</td>
                        <td><strong id="liveTargetDays">{{ $currentAttSheet->total_target_days }}</strong> Hari</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-700);">Tidak Hadir (Sakit / Izin / Alpa)</td>
                        <td>:</td>
                        <td>
                            S: <strong id="liveSick">{{ $currentAttSheet->total_sick }}</strong> | 
                            I: <strong id="livePerm">{{ $currentAttSheet->total_permission }}</strong> | 
                            A: <strong id="liveAbs">{{ $currentAttSheet->total_absent }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-700);font-weight:700;">Jumlah Hadir</td>
                        <td>:</td>
                        <td><strong id="livePresent" style="color:var(--success);font-size:1.1rem;">{{ $currentAttSheet->total_present }}</strong> Hari</td>
                    </tr>
                </table>
            </div>

            <div style="text-align:center;min-width:220px;display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <span style="font-size:0.85rem;color:var(--slate-600);font-weight:600;">Pembimbing Industri</span>
                    <div style="height:60px;"></div>
                    <strong style="display:block;border-top:1px dotted #94a3b8;padding-top:0.35rem;font-size:0.9rem;">
                        {{ $student->mentor_name ?: '..........................................' }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="form-submit-row" style="margin-top:1.5rem;text-align:right;">
            <button type="submit" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Presensi & Perbarui Rekapitulasi
            </button>
        </div>
    </form>
    @endif
</div>

<!-- Modal Tambah Bulan Presensi -->
<div id="addAttModal" class="modal-backdrop">
    <div class="modal-box">
        <h4 style="font-size:1.15rem;font-weight:800;margin-bottom:0.5rem;color:var(--slate-900);">Tambah Lembar Bulan Presensi (+)</h4>
        <p style="font-size:0.85rem;color:var(--slate-500);margin-bottom:1.25rem;">Buat lembar daftar hadir baru untuk bulan berikutnya.</p>

        <form action="{{ route('attendance-sheets.store', $student->id) }}" method="POST">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Nama Bulan & Tahun</label>
                <input type="text" name="month_name" class="form-control" value="November 2026" required>
            </div>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.85rem;font-weight:600;margin-bottom:0.35rem;">Jumlah Hari dalam Bulan</label>
                <select name="days_count" class="form-control">
                    <option value="30" selected>30 Hari</option>
                    <option value="31">31 Hari</option>
                    <option value="28">28 Hari</option>
                    <option value="29">29 Hari</option>
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAttModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Lembar Presensi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: BATCH TERAPKAN PARAF KE SEMUA BARIS -->
<div id="batchParafModal" class="modal-backdrop">
    <div class="modal-box" style="max-width:440px;">
        <h4 style="font-size:1.15rem;font-weight:800;margin-bottom:0.35rem;color:var(--slate-900);">Terapkan Paraf ke Semua Baris</h4>
        <p style="font-size:0.85rem;color:var(--slate-500);margin-bottom:1.25rem;" id="batchParafDesc">
            Upload 1 foto paraf, otomatis diproses transparan & diterapkan ke seluruh baris lembar ini.
        </p>

        <div style="background:#f8fafc;border:2px dashed #cbd5e1;border-radius:12px;padding:1.5rem;text-align:center;margin-bottom:1.25rem;">
            <div id="batchPreviewBox" style="display:none;margin-bottom:1rem;">
                <img id="batchPreviewImg" src="" alt="Preview Paraf" style="max-height:65px;max-width:180px;object-fit:contain;mix-blend-mode:multiply;margin:0 auto;display:block;">
                <span style="display:inline-block;font-size:0.75rem;color:#16a34a;font-weight:700;margin-top:0.4rem;">&#10003; Berhasil diproses transparan & ringan</span>
            </div>

            <input type="file" id="batchParafFileInput" accept="image/*" style="display:none;" onchange="handleBatchParafFileSelected(this)">
            <label for="batchParafFileInput" class="btn btn-secondary" style="cursor:pointer;display:inline-flex;align-items:center;gap:0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Pilih Foto Paraf
            </label>
            <p style="font-size:0.75rem;color:var(--slate-400);margin-top:0.5rem;margin-bottom:0;">
                Kamera HP atau Galeri Foto (JPG/PNG/WEBP)
            </p>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('batchParafModal')">Batal</button>
            <button type="button" class="btn btn-primary" id="btnApplyBatch" onclick="applyBatchParaf()" disabled>
                Terapkan ke Semua
            </button>
        </div>
    </div>
</div>

<!-- PEMISAH HALAMAN 5: LAPORAN PENILAIAN MAGANG -->
<div class="word-page-divider">
    <div class="word-page-divider-line"></div>
    <span class="word-page-divider-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:#2563eb;"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
        Halaman 5 dari 5 &bull; Lembar Penilaian DU/DI
    </span>
    <div class="word-page-divider-line"></div>
</div>

@php
    $eval = $student->evaluation;
@endphp
<div class="tab-card word-page-section" id="page-5-penilaian">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;border-bottom:1px solid var(--slate-200);padding-bottom:1rem;">
        <div>
            <h3 style="font-size:1.25rem;font-weight:800;color:var(--slate-900);">Lembar 5: Laporan Penilaian Magang DU/DI</h3>
            <p style="font-size:0.85rem;color:var(--slate-500);">Penilaian aspek softskill dan teknis oleh Pembimbing Dunia Usaha & Dunia Industri (DUDI) dengan kalkulasi otomatis predikat.</p>
        </div>
    </div>

    <form action="{{ route('evaluations.update', $student->id) }}" method="POST" onsubmit="preserveScrollPosition()">
        @csrf
        @method('PUT')

        <!-- Ringkasan Identitas Penilaian Sesuai Foto 5 -->
        <table style="width:100%;max-width:700px;font-size:0.92rem;margin-bottom:1.5rem;border-collapse:separate;border-spacing:0 0.5rem;">
            <tr>
                <td style="width:200px;font-weight:600;">Nama Siswa</td>
                <td style="width:20px;">:</td>
                <td>{{ $student->name }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">NIS / NISN</td>
                <td>:</td>
                <td>{{ $student->nis }} @if($student->nisn) / {{ $student->nisn }} @endif</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Kelas / Program Keahlian</td>
                <td>:</td>
                <td>{{ $student->class }} / {{ $student->major }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Tempat Magang</td>
                <td>:</td>
                <td>{{ $student->company_name }}</td>
            </tr>
            <tr>
                <td style="font-weight:600;">Waktu Pelaksanaan</td>
                <td>:</td>
                <td>{{ $student->internship_period ?: '01 Oktober 2026 - 30 November 2026' }}</td>
            </tr>
        </table>

        <!-- Tabel Aspek Yang Dinilai (Foto 5) -->
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:2rem;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:60px;">No.</th>
                        <th rowspan="2">Aspek Yang Dinilai</th>
                        <th colspan="2" style="width:220px;">Penilaian</th>
                    </tr>
                    <tr>
                        <th style="width:110px;">Nilai (0-100)</th>
                        <th style="width:110px;">Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" style="background:#f1f5f9;font-weight:800;color:var(--slate-800);">SOFTSKILL</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;font-weight:700;">A</td>
                        <td>Menerapkan Softskill yang dibutuhkan dunia kerja.</td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" id="aspect_a" name="aspect_a_score" value="{{ old('aspect_a_score', $eval->aspect_a_score ?? 90) }}" class="form-control" style="text-align:center;font-weight:700;" oninput="updateEvaluationGrades()" required>
                        </td>
                        <td style="text-align:center;font-weight:800;font-size:1.1rem;color:var(--primary);" id="grade_a">
                            {{ $eval->aspect_a_grade ?? 'B' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;font-weight:700;">B</td>
                        <td>Menerapkan norma, Prosedur Operasional Standar (POS) dan Kesehatan Keselamatan Kerja (K3) yang ada pada dunia kerja.</td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" id="aspect_b" name="aspect_b_score" value="{{ old('aspect_b_score', $eval->aspect_b_score ?? 88) }}" class="form-control" style="text-align:center;font-weight:700;" oninput="updateEvaluationGrades()" required>
                        </td>
                        <td style="text-align:center;font-weight:800;font-size:1.1rem;color:var(--primary);" id="grade_b">
                            {{ $eval->aspect_b_grade ?? 'B' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;font-weight:700;">C</td>
                        <td>Menerapkan kompetensi teknis yang sudah dipelajari di sekolah dan/atau baru dipelajari pada dunia kerja sesuai konsentrasi keahlian.</td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" id="aspect_c" name="aspect_c_score" value="{{ old('aspect_c_score', $eval->aspect_c_score ?? 92) }}" class="form-control" style="text-align:center;font-weight:700;" oninput="updateEvaluationGrades()" required>
                        </td>
                        <td style="text-align:center;font-weight:800;font-size:1.1rem;color:var(--primary);" id="grade_c">
                            {{ $eval->aspect_c_grade ?? 'A' }}
                        </td>
                    </tr>
                    <tr style="background:#f8fafc;font-weight:800;">
                        <td colspan="2" style="text-align:right;padding-right:1.5rem;font-size:1rem;">NILAI RATA-RATA AKHIR:</td>
                        <td style="text-align:center;font-size:1.15rem;color:var(--success);" id="avg_score">
                            {{ number_format($eval->average_score ?? 90, 2) }}
                        </td>
                        <td style="text-align:center;font-size:1.25rem;color:var(--primary);font-weight:900;" id="final_grade">
                            {{ $eval->final_grade ?? 'B' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabel Keterangan Nilai & Kolom Pengesahan Tanda Tangan Sesuai Foto 5 -->
        <div class="eval-grid-layout" style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start;">
            <!-- Tabel Acuan Predikat -->
            <div>
                <strong style="display:block;margin-bottom:0.5rem;font-size:0.85rem;color:var(--slate-700);">Keterangan Nilai:</strong>
                <table class="data-table" style="max-width:380px;">
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

            <!-- Tanggal & Tanda Tangan Pembimbing Industri -->
            <div style="text-align:center;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:18px;padding:1.75rem;box-shadow:0 4px 16px rgba(0,0,0,0.02);">
                <div style="display:flex;align-items:center;justify-content:center;gap:0.4rem;margin-bottom:1rem;">
                    <input type="text" name="evaluation_date" class="form-control" style="width:230px;text-align:center;" value="{{ old('evaluation_date', $eval->evaluation_date ?? 'Jakarta, 30 November 2026') }}" required>
                </div>
                <span style="font-weight:600;font-size:0.9rem;color:var(--slate-700);">Pembimbing Dunia Industri</span>
                <div style="height:70px;"></div>
                <input type="text" name="mentor_name" class="form-control" style="text-align:center;font-weight:700;max-width:280px;margin:0 auto;" value="{{ old('mentor_name', $eval->mentor_name ?? $student->mentor_name ?? 'Sujatmo, S.Sos') }}" placeholder="Nama Pembimbing Industri">
            </div>
        </div>

        <div class="form-submit-row" style="margin-top:2rem;text-align:right;">
            <button type="submit" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Penilaian DU/DI
            </button>
        </div>
    </form>
</div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    // Simpan posisi scroll agar saat ganti slide / submit form layar tidak melompat ke atas
    function preserveScrollPosition() {
        try {
            sessionStorage.setItem('laporan_scroll_pos', window.pageYOffset.toString());
        } catch (e) {}
    }

    // Tangkap semua klik pada .slide-pill secara global
    document.addEventListener('click', function(e) {
        if (e.target.closest('.slide-pill')) {
            preserveScrollPosition();
        }
    });

    // Otomatis pulihkan posisi scroll saat halaman selesai dimuat
    (function initScrollRestoration() {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        function restoreScrollPosition() {
            try {
                const savedPos = sessionStorage.getItem('laporan_scroll_pos');
                if (savedPos !== null) {
                    sessionStorage.removeItem('laporan_scroll_pos');
                    const targetY = parseInt(savedPos, 10);
                    if (!isNaN(targetY)) {
                        window.scrollTo({
                            top: targetY,
                            behavior: 'instant'
                        });
                        return;
                    }
                }
            } catch (e) {}

            // Fallback: deteksi parameter query tab atau anchor hash pada URL
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            const hash = window.location.hash;

            let targetId = null;
            if (hash && document.querySelector(hash)) {
                targetId = hash.replace('#', '');
            } else if (tab === 'uraian') {
                targetId = 'page-3-uraian';
            } else if (tab === 'presensi') {
                targetId = 'page-4-presensi';
            } else if (tab === 'penilaian') {
                targetId = 'page-5-penilaian';
            } else if (tab === 'identitas') {
                targetId = 'page-2-identitas';
            }

            if (targetId) {
                const targetEl = document.getElementById(targetId);
                if (targetEl) {
                    const yOffset = -85;
                    const y = targetEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({
                        top: Math.max(0, y),
                        behavior: 'instant'
                    });
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', restoreScrollPosition);
        } else {
            restoreScrollPosition();
        }
        window.addEventListener('load', restoreScrollPosition);
    })();

    // Tab key to section ID mapping
    const wordTabMap = {
        'cover': 'page-1-cover',
        'identitas': 'page-2-identitas',
        'uraian': 'page-3-uraian',
        'presensi': 'page-4-presensi',
        'penilaian': 'page-5-penilaian'
    };
    const wordPageToTab = {
        'page-1-cover': 'cover',
        'page-2-identitas': 'identitas',
        'page-3-uraian': 'uraian',
        'page-4-presensi': 'presensi',
        'page-5-penilaian': 'penilaian'
    };

    // Smooth Scroll Jump to Word Document Page with Emil Kowalski animations & URL synchronization
    function jumpToWordPage(pageId, clickedEl, updateUrl = true) {
        const target = document.getElementById(pageId);
        if (!target) return;
        const yOffset = -80;
        const y = target.getBoundingClientRect().top + window.pageYOffset + yOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });

        document.querySelectorAll('.word-nav-item').forEach(item => item.classList.remove('active'));
        if (clickedEl) {
            clickedEl.classList.add('active');
        } else {
            const nav = document.querySelector(`.word-nav-item[data-target="${pageId}"]`);
            if (nav) nav.classList.add('active');
        }

        // Add subtle focus pulse animation to section
        document.querySelectorAll('.word-page-section').forEach(s => s.classList.remove('highlight-pulse'));
        target.classList.add('highlight-pulse');
        setTimeout(() => target.classList.remove('highlight-pulse'), 1200);

        const tabKey = wordPageToTab[pageId] || 'cover';
        if (updateUrl && window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabKey);
            window.history.replaceState(null, '', url.toString());
        }

        // Update student switcher pills to maintain active tab
        document.querySelectorAll('.switcher-pill[data-student-id]').forEach(pill => {
            const sid = pill.getAttribute('data-student-id');
            pill.href = `/students/${sid}?tab=${tabKey}`;
        });
    }

    // ScrollSpy: Otomatis deteksi halaman yang aktif saat scroll dokumen & sync URL
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.word-page-section');
        const navItems = document.querySelectorAll('.word-nav-item[data-target]');

        // Auto jump to requested tab on initial page load
        const urlParams = new URLSearchParams(window.location.search);
        const requestedTab = urlParams.get('tab') || '{{ $activeTab ?? "cover" }}';
        if (requestedTab && wordTabMap[requestedTab] && requestedTab !== 'cover') {
            const targetId = wordTabMap[requestedTab];
            const targetNav = document.querySelector(`.word-nav-item[data-target="${targetId}"]`);
            setTimeout(() => {
                jumpToWordPage(targetId, targetNav, false);
            }, 120);
        }

        window.addEventListener('scroll', function() {
            let currentSectionId = '';
            const scrollPosition = window.pageYOffset + 140;

            sections.forEach(sec => {
                const top = sec.offsetTop;
                const height = sec.offsetHeight;
                if (scrollPosition >= top && scrollPosition < top + height) {
                    currentSectionId = sec.getAttribute('id');
                }
            });

            if (currentSectionId) {
                const currentTab = wordPageToTab[currentSectionId];
                navItems.forEach(item => {
                    if (item.getAttribute('data-target') === currentSectionId) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                });

                if (currentTab && window.history && window.history.replaceState) {
                    const url = new URL(window.location.href);
                    if (url.searchParams.get('tab') !== currentTab) {
                        url.searchParams.set('tab', currentTab);
                        window.history.replaceState(null, '', url.toString());
                        document.querySelectorAll('.switcher-pill[data-student-id]').forEach(pill => {
                            const sid = pill.getAttribute('data-student-id');
                            pill.href = `/students/${sid}?tab=${currentTab}`;
                        });
                    }
                }
            }
        }, { passive: true });
    });

    function openModal(id) {
        document.getElementById(id)?.classList.add('show');
    }

    function closeModal(id) {
        document.getElementById(id)?.classList.remove('show');
    }

    // SweetAlert2: Tambah Slide Lembar Uraian Baru
    function promptAddSheetWithSwal() {
        Swal.fire({
            title: 'Tambah Slide Lembar Baru (+)',
            text: 'Masukkan nama bulan / keterangan lembar kegiatan lanjutan:',
            input: 'text',
            inputValue: 'Oktober 2026 (Lembar Lanjutan)',
            inputPlaceholder: 'Contoh: Oktober 2026 (Lembar 2)',
            showCancelButton: true,
            confirmButtonText: 'Buat Lembar (+)',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Nama bulan / keterangan lembar wajib diisi!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('activity-sheets.store', $student->id) }}";
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'month_name';
                input.value = result.value;
                form.appendChild(input);

                preserveScrollPosition();
                document.body.appendChild(form);
                Swal.fire({
                    title: 'Membuat Lembar Baru...',
                    text: 'Sedang menyiapkan lembar kegiatan baru.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                form.submit();
            }
        });
    }

    // SweetAlert2: Tambah Lembar Bulan Presensi Baru
    function promptAddAttSheetWithSwal() {
        Swal.fire({
            title: 'Tambah Lembar Presensi Baru (+)',
            html: `
                <div style="text-align:left;font-size:0.9rem;padding:0.5rem 0;">
                    <label style="display:block;font-weight:700;margin-bottom:0.35rem;color:#334155;">Nama Bulan & Tahun:</label>
                    <input id="swal-att-month" class="swal2-input" style="width:100%;margin:0 0 1rem 0;box-sizing:border-box;font-size:0.95rem;" value="November 2026" placeholder="Contoh: November 2026">
                    
                    <label style="display:block;font-weight:700;margin-bottom:0.35rem;color:#334155;">Jumlah Hari dalam Bulan:</label>
                    <select id="swal-att-days" class="swal2-select" style="width:100%;margin:0;display:block;box-sizing:border-box;font-size:0.95rem;">
                        <option value="30" selected>30 Hari (Default Sesuai Format Foto)</option>
                        <option value="31">31 Hari</option>
                        <option value="28">28 Hari</option>
                        <option value="29">29 Hari</option>
                    </select>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Buat Lembar Presensi (+)',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            preConfirm: () => {
                const month = document.getElementById('swal-att-month').value;
                const days = document.getElementById('swal-att-days').value;
                if (!month || !month.trim()) {
                    Swal.showValidationMessage('Nama bulan presensi wajib diisi!');
                    return false;
                }
                return { month: month.trim(), days: days };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('attendance-sheets.store', $student->id) }}";

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                const mInput = document.createElement('input');
                mInput.type = 'hidden';
                mInput.name = 'month_name';
                mInput.value = result.value.month;
                form.appendChild(mInput);

                const dInput = document.createElement('input');
                dInput.type = 'hidden';
                dInput.name = 'days_count';
                dInput.value = result.value.days;
                form.appendChild(dInput);

                preserveScrollPosition();
                document.body.appendChild(form);
                Swal.fire({
                    title: 'Membuat Lembar Presensi...',
                    text: 'Sedang menyiapkan lembar presensi baru.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                form.submit();
            }
        });
    }

    function previewShowPhoto(event) {
        const file = event.target.files[0];
        const box = document.getElementById('photoPreviewBox');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                box.innerHTML = `<img src="${e.target.result}" alt="Pas Foto" style="width:100%;height:100%;object-fit:cover;">`;
            }
            reader.readAsDataURL(file);
        }
    }

    // Kalkulasi Live Predikat Penilaian Magang
    function getGradeFromScore(score) {
        if (isNaN(score) || score === null) return '-';
        if (score >= 91) return 'A';
        if (score >= 81) return 'B';
        if (score >= 75) return 'C';
        return 'D';
    }

    function updateEvaluationGrades() {
        const a = parseFloat(document.getElementById('aspect_a')?.value || 0);
        const b = parseFloat(document.getElementById('aspect_b')?.value || 0);
        const c = parseFloat(document.getElementById('aspect_c')?.value || 0);

        const gradeA = getGradeFromScore(a);
        const gradeB = getGradeFromScore(b);
        const gradeC = getGradeFromScore(c);

        if (document.getElementById('grade_a')) document.getElementById('grade_a').textContent = gradeA;
        if (document.getElementById('grade_b')) document.getElementById('grade_b').textContent = gradeB;
        if (document.getElementById('grade_c')) document.getElementById('grade_c').textContent = gradeC;

        const avg = ((a + b + c) / 3).toFixed(2);
        const finalGrade = getGradeFromScore(parseFloat(avg));

        if (document.getElementById('avg_score')) document.getElementById('avg_score').textContent = avg;
        if (document.getElementById('final_grade')) document.getElementById('final_grade').textContent = finalGrade;
    }

    // Kalkulasi Live Presensi
    function calculateLiveAttendance() {
        const radios = document.querySelectorAll('input[type="radio"]:checked');
        let h = 0, s = 0, i = 0, a = 0;
        radios.forEach(r => {
            if (r.value === 'H') h++;
            else if (r.value === 'S') s++;
            else if (r.value === 'I') i++;
            else if (r.value === 'A') a++;
        });

        if (document.getElementById('livePresent')) document.getElementById('livePresent').textContent = h;
        if (document.getElementById('liveSick')) document.getElementById('liveSick').textContent = s;
        if (document.getElementById('livePerm')) document.getElementById('livePerm').textContent = i;
        if (document.getElementById('liveAbs')) document.getElementById('liveAbs').textContent = a;
    }

    // Handle perubahan status radio: jika Hadir (H) keterangan dikosongkan
    function handleStatusChange(recId, radio) {
        calculateLiveAttendance();
        const notesInput = document.getElementById('notes-' + recId);
        if (notesInput) {
            if (radio.value === 'H') {
                notesInput.value = '';
            }
            notesInput.placeholder = 'Keterangan';
        }
    }

    // Tambah 1 Hari Secara Asinkron (Cepat Tanpa Reload Halaman Penuh)
    async function handleAsyncAddDay(event, form) {
        event.preventDefault();
        const btn = document.getElementById('btnAddDay');
        const btnText = document.getElementById('btnAddDayText');
        const origText = btnText ? btnText.textContent : 'Tambah Hari (+)';
        if (btn) btn.disabled = true;
        if (btnText) btnText.textContent = 'Menambahkan...';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await res.json();
            if (data.success && data.record) {
                const rec = data.record;
                const tbody = document.getElementById('attendanceTableBody');
                if (tbody) {
                    const tr = document.createElement('tr');
                    tr.id = 'row-record-' + rec.id;
                    tr.style.background = '#f0fdf4';
                    tr.style.transition = 'background 1.5s ease';
                    tr.innerHTML = `
                        <td style="text-align:center;font-weight:700;color:var(--slate-800);" class="day-number-cell">
                            ${rec.day_number}.
                            <input type="hidden" name="records[${rec.id}][id]" value="${rec.id}">
                        </td>
                        <td>
                            <input type="text" name="records[${rec.id}][day_date]" value="${rec.day_date || ''}" class="table-input" placeholder="Hari, DD-MM-YYYY">
                        </td>
                        <td style="text-align:center;">
                            <input type="radio" name="records[${rec.id}][status]" value="H" checked onchange="handleStatusChange(${rec.id}, this)" title="Hadir">
                        </td>
                        <td style="text-align:center;background:#fffbeb;">
                            <input type="radio" name="records[${rec.id}][status]" value="S" onchange="handleStatusChange(${rec.id}, this)" title="Sakit">
                        </td>
                        <td style="text-align:center;background:#eff6ff;">
                            <input type="radio" name="records[${rec.id}][status]" value="I" onchange="handleStatusChange(${rec.id}, this)" title="Izin">
                        </td>
                        <td style="text-align:center;background:#fef2f2;">
                            <input type="radio" name="records[${rec.id}][status]" value="A" onchange="handleStatusChange(${rec.id}, this)" title="Alpa">
                        </td>
                        <td>
                            <input type="text" id="notes-${rec.id}" name="records[${rec.id}][notes]" value="" class="table-input" placeholder="Keterangan">
                        </td>
                        <td>
                            <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.75rem;color:var(--slate-600);justify-content:center;">
                                <input type="checkbox" name="records[${rec.id}][is_signed]" value="1" checked>
                                Paraf
                            </label>
                        </td>
                        <td style="text-align:center;">
                            <button type="button" onclick="deleteAttendanceRecord(${data.student_id}, ${data.sheet_id}, ${rec.id}, ${rec.day_number})" title="Hapus hari ke-${rec.day_number}" style="background:none;border:none;cursor:pointer;color:#ef4444;padding:3px;display:inline-flex;align-items:center;justify-content:center;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                    setTimeout(() => { tr.style.background = 'transparent'; }, 1200);
                }

                // Update indikator total hari
                if (document.getElementById('attTargetDaysBadge')) {
                    document.getElementById('attTargetDaysBadge').textContent = data.total_target_days + ' Hari';
                }
                if (document.getElementById('liveTargetDays')) {
                    document.getElementById('liveTargetDays').textContent = data.total_target_days;
                }
                calculateLiveAttendance();

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: data.message || 'Hari berhasil ditambahkan!'
                });
            } else {
                throw new Error(data.message || 'Gagal menambahkan hari.');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message || 'Terjadi kesalahan saat menambahkan hari.'
            });
        } finally {
            if (btn) btn.disabled = false;
            if (btnText) btnText.textContent = origText;
        }
    }

    // Konfirmasi & Hapus 1 Baris Presensi Secara Asinkron (Cepat Tanpa Reload)
    async function deleteAttendanceRecord(studentId, sheetId, recordId, dayNum) {
        const result = await Swal.fire({
            title: 'Hapus Hari ke-' + dayNum + '?',
            text: 'Baris hari ini akan dihapus dari daftar hadir.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const res = await fetch('/students/' + studentId + '/attendance-sheets/' + sheetId + '/records/' + recordId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await res.json();
                if (data.success) {
                    const row = document.getElementById('row-record-' + recordId);
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            row.remove();
                            // Re-index tampilan nomor urut 1, 2, 3...
                            const rows = document.querySelectorAll('#attendanceTableBody tr');
                            rows.forEach((r, idx) => {
                                const numCell = r.querySelector('.day-number-cell');
                                if (numCell) {
                                    const hiddenInput = numCell.querySelector('input[type="hidden"]');
                                    numCell.innerHTML = (idx + 1) + '.';
                                    if (hiddenInput) numCell.appendChild(hiddenInput);
                                }
                            });

                            if (document.getElementById('attTargetDaysBadge')) {
                                document.getElementById('attTargetDaysBadge').textContent = data.total_target_days + ' Hari';
                            }
                            if (document.getElementById('liveTargetDays')) {
                                document.getElementById('liveTargetDays').textContent = data.total_target_days;
                            }
                            calculateLiveAttendance();
                        }, 300);
                    }

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Baris kehadiran berhasil dihapus.'
                    });
                } else {
                    throw new Error(data.message || 'Gagal menghapus baris.');
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.message || 'Terjadi kesalahan saat menghapus baris.'
                });
            }
        }
    }

    // Toggle Tampilan Identitas (Formulir Terstruktur vs Format Berkas Fisik A4)
    function switchIdentitasMode(mode) {
        const formView = document.getElementById('identitasFormView');
        const paperView = document.getElementById('identitasPaperView');
        const btnForm = document.getElementById('btnViewForm');
        const btnPaper = document.getElementById('btnViewPaper');

        if (!formView || !paperView) return;

        if (mode === 'paper') {
            formView.style.display = 'none';
            paperView.style.display = 'block';
            if (btnPaper) btnPaper.className = 'btn btn-primary btn-sm';
            if (btnForm) btnForm.className = 'btn btn-secondary btn-sm';
        } else {
            paperView.style.display = 'none';
            formView.style.display = 'block';
            if (btnForm) btnForm.className = 'btn btn-primary btn-sm';
            if (btnPaper) btnPaper.className = 'btn btn-secondary btn-sm';
        }
    }

    // Custom Popover Dropdown Handlers (Anti-Kotak Kaku)
    let activeDropdownMenu = null;
    let activeDropdownTrigger = null;

    function toggleCustomDropdown(menuId, triggerId) {
        const menu = document.getElementById(menuId);
        const trigger = document.getElementById(triggerId);
        if (!menu || !trigger) return;

        if (menu.classList.contains('show')) {
            closeAllCustomDropdowns();
        } else {
            closeAllCustomDropdowns();
            menu.classList.add('show');
            trigger.classList.add('open');
            activeDropdownMenu = menu;
            activeDropdownTrigger = trigger;
        }
    }

    function closeAllCustomDropdowns() {
        document.querySelectorAll('.custom-dropdown-menu.show').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.custom-dropdown-trigger.open').forEach(t => t.classList.remove('open'));
        activeDropdownMenu = null;
        activeDropdownTrigger = null;
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-dropdown-container')) {
            closeAllCustomDropdowns();
        }
    });

    // Select Major from Custom Dropdown
    async function selectCustomMajor(major, className, field) {
        closeAllCustomDropdowns();

        // Update trigger text
        const textEl = document.getElementById('selectedMajorText');
        if (textEl) textEl.textContent = `${major} (${className})`;

        // Update active class on dropdown items
        document.querySelectorAll('.major-option').forEach(item => {
            const titleEl = item.querySelector('.item-title');
            if (titleEl && titleEl.textContent.trim() === major) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // Update trigger icon badge based on selected major color
        const iconEl = document.getElementById('majorTriggerIcon');
        if (iconEl) {
            if (className.includes('RPL')) {
                iconEl.innerHTML = '<span class="item-badge badge-rpl" style="font-size:0.68rem;padding:2px 6px;">RPL</span>';
            } else if (className.includes('MP')) {
                iconEl.innerHTML = '<span class="item-badge badge-mp" style="font-size:0.68rem;padding:2px 6px;">MP</span>';
            } else if (className.includes('BR')) {
                iconEl.innerHTML = '<span class="item-badge badge-br" style="font-size:0.68rem;padding:2px 6px;">BR</span>';
            } else if (className.includes('BD')) {
                iconEl.innerHTML = '<span class="item-badge badge-bd" style="font-size:0.68rem;padding:2px 6px;">BD</span>';
            } else {
                iconEl.innerHTML = '<span class="item-badge badge-custom" style="font-size:0.68rem;padding:2px 6px;">' + className + '</span>';
            }
        }

        await saveQuickCover({
            major: major,
            class: className,
            field_of_expertise: field
        });
    }

    // Select Year from Custom Dropdown
    async function selectCustomYear(year) {
        closeAllCustomDropdowns();

        // Update trigger text
        const textEl = document.getElementById('selectedYearText');
        if (textEl) textEl.textContent = year;

        // Update active class on dropdown items
        document.querySelectorAll('.year-option').forEach(item => {
            const valEl = item.querySelector('.year-val');
            if (valEl && valEl.textContent.trim() === year) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        await saveQuickCover({ academic_year: year });
    }

    // Dialog Input Kustom Jurusan
    async function promptCustomMajorDialog() {
        closeAllCustomDropdowns();

        const { value: formValues } = await Swal.fire({
            title: 'Kustom Jurusan & Kelas',
            html: `
                <div style="text-align:left;font-size:0.9rem;">
                    <label style="font-weight:700;display:block;margin-bottom:4px;color:#334155;">Nama Program Keahlian / Jurusan:</label>
                    <input id="swal_custom_major" class="swal2-input" style="width:100%;margin:0 0 12px 0;" placeholder="e.g. Bisnis Digital / Rekayasa Perangkat Lunak">
                    <label style="font-weight:700;display:block;margin-bottom:4px;color:#334155;">Nama Kelas:</label>
                    <input id="swal_custom_class" class="swal2-input" style="width:100%;margin:0 0 12px 0;" placeholder="e.g. XI BD / XI RPL">
                    <label style="font-weight:700;display:block;margin-bottom:4px;color:#334155;">Bidang Keahlian:</label>
                    <input id="swal_custom_field" class="swal2-input" style="width:100%;margin:0;" value="Bisnis Manajemen dan Teknologi">
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Terapkan & Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            preConfirm: () => {
                const m = document.getElementById('swal_custom_major').value.trim();
                const c = document.getElementById('swal_custom_class').value.trim();
                const f = document.getElementById('swal_custom_field').value.trim();
                if (!m || !c) {
                    Swal.showValidationMessage('Nama Jurusan dan Kelas wajib diisi!');
                    return false;
                }
                return { major: m, className: c, field: f || 'Bisnis Manajemen dan Teknologi' };
            }
        });

        if (formValues) {
            await selectCustomMajor(formValues.major, formValues.className, formValues.field);
        }
    }

    // Dialog Input Kustom Tahun Pelajaran
    async function promptCustomYearDialog() {
        closeAllCustomDropdowns();

        const { value: year } = await Swal.fire({
            title: 'Kustom Tahun Pelajaran / Angkatan',
            input: 'text',
            inputLabel: 'Masukkan Tahun Pelajaran (misal: 2027/2028):',
            inputPlaceholder: '2027/2028',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            inputValidator: (v) => {
                if (!v || !v.trim()) return 'Tahun pelajaran tidak boleh kosong!';
            }
        });

        if (year) {
            await selectCustomYear(year.trim());
        }
    }

    // Eksekusi Simpan Perubahan Cover Realtime
    async function saveQuickCover(payload) {
        if (!payload.major) {
            payload.major = '{{ $student->major }}';
            payload.class = '{{ $student->class }}';
            payload.field_of_expertise = '{{ $student->field_of_expertise }}';
        }

        if (!payload.academic_year) {
            payload.academic_year = '{{ $student->academic_year ?: '2026/2027' }}';
        }

        try {
            const resp = await fetch("{{ route('students.quick-cover', $student->id) }}", {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json();
            if (data.success) {
                if (document.getElementById('coverMajorDisplay')) document.getElementById('coverMajorDisplay').textContent = payload.major;
                if (document.getElementById('coverClassDisplay')) document.getElementById('coverClassDisplay').textContent = payload.class;
                if (document.getElementById('coverYearDisplay')) document.getElementById('coverYearDisplay').textContent = `TAHUN PELAJARAN ${payload.academic_year}`;
                if (document.getElementById('coverFieldDisplay')) document.getElementById('coverFieldDisplay').textContent = (payload.field_of_expertise || 'BISNIS MANAJEMEN DAN TEKNOLOGI').toUpperCase();

                const statusEl = document.getElementById('quickSaveStatus');
                if (statusEl) {
                    statusEl.style.display = 'inline-flex';
                    setTimeout(() => { statusEl.style.display = 'none'; }, 3000);
                }

                Toast.fire({
                    icon: 'success',
                    title: `Jurusan diperbarui: ${payload.major} (${payload.class})`
                });
            } else {
                Swal.fire('Gagal Menyimpan', data.message || 'Terjadi kesalahan.', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Kesalahan Jaringan', 'Gagal menghubungi server.', 'error');
        }
    }

    // ==========================================
    // PARAF OPTIMIZER & SEAMLESS INK PROCESSING
    // ==========================================
    window.ParafOptimizer = {
        optimizeImage: function(file, callback) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    let w = img.width;
                    let h = img.height;
                    const maxW = 320;
                    const maxH = 130;
                    if (w > maxW || h > maxH) {
                        const ratio = Math.min(maxW / w, maxH / h);
                        w = Math.round(w * ratio);
                        h = Math.round(h * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d', { willReadFrequently: true });
                    ctx.drawImage(img, 0, 0, w, h);

                    const imgData = ctx.getImageData(0, 0, w, h);
                    const data = imgData.data;

                    // Auto-transparency & ink contrast boost
                    for (let i = 0; i < data.length; i += 4) {
                        const r = data[i];
                        const g = data[i + 1];
                        const b = data[i + 2];
                        const lum = 0.299 * r + 0.587 * g + 0.114 * b;

                        if (lum > 185) {
                            data[i + 3] = 0; // Pure transparent background
                        } else if (lum > 150) {
                            const factor = (lum - 150) / 35;
                            data[i + 3] = Math.round((1 - factor) * 255);
                        } else {
                            data[i] = Math.max(0, r - 30);
                            data[i + 1] = Math.max(0, g - 30);
                            data[i + 2] = Math.max(0, b - 30);
                        }
                    }
                    ctx.putImageData(imgData, 0, 0);

                    let result = canvas.toDataURL('image/webp', 0.85);
                    if (!result.startsWith('data:image/webp')) {
                        result = canvas.toDataURL('image/png');
                    }
                    callback(result);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };

    window.handleParafFileSelected = function(input, type, id) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        window.ParafOptimizer.optimizeImage(file, function(base64) {
            const previewBox = document.getElementById(`paraf-box-${type}-${id}`);
            const previewImg = document.getElementById(`paraf-img-${type}-${id}`);
            const uploadBtn = document.getElementById(`paraf-upload-btn-${type}-${id}`);
            const dataInput = document.getElementById(`paraf-data-${type}-${id}`);
            const removeInput = document.getElementById(`remove-paraf-${type}-${id}`);

            if (previewImg) previewImg.src = base64;
            if (previewBox) previewBox.style.display = 'flex';
            if (uploadBtn) uploadBtn.style.display = 'none';
            if (dataInput) dataInput.value = base64;
            if (removeInput) removeInput.value = '0';

            Toast.fire({
                icon: 'success',
                title: 'Foto paraf berhasil diproses transparan & siap disimpan'
            });
        });
    };

    window.removeRowParaf = function(type, id) {
        const previewBox = document.getElementById(`paraf-box-${type}-${id}`);
        const previewImg = document.getElementById(`paraf-img-${type}-${id}`);
        const uploadBtn = document.getElementById(`paraf-upload-btn-${type}-${id}`);
        const dataInput = document.getElementById(`paraf-data-${type}-${id}`);
        const removeInput = document.getElementById(`remove-paraf-${type}-${id}`);
        const fileInput = document.getElementById(`paraf-file-${type}-${id}`);

        if (previewImg) previewImg.src = '';
        if (previewBox) previewBox.style.display = 'none';
        if (uploadBtn) uploadBtn.style.display = '';
        if (dataInput) dataInput.value = '';
        if (removeInput) removeInput.value = '1';
        if (fileInput) fileInput.value = '';

        Toast.fire({
            icon: 'info',
            title: 'Paraf baris ini ditandai untuk dihapus'
        });
    };

    // BATCH PARAF MODAL
    let currentBatchTarget = 'activity';
    let currentBatchOptimizedData = null;

    window.openBatchParafModal = function(target) {
        currentBatchTarget = target;
        currentBatchOptimizedData = null;
        document.getElementById('batchPreviewBox').style.display = 'none';
        document.getElementById('batchPreviewImg').src = '';
        document.getElementById('btnApplyBatch').disabled = true;
        document.getElementById('batchParafFileInput').value = '';

        const descEl = document.getElementById('batchParafDesc');
        if (target === 'activity') {
            descEl.textContent = 'Upload 1 foto paraf, otomatis diproses transparan & diterapkan ke seluruh 10 baris Uraian Kegiatan ini.';
        } else {
            descEl.textContent = 'Upload 1 foto paraf, otomatis diproses transparan & diterapkan ke seluruh hari presensi pada bulan ini.';
        }

        openModal('batchParafModal');
    };

    window.handleBatchParafFileSelected = function(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        window.ParafOptimizer.optimizeImage(file, function(base64) {
            currentBatchOptimizedData = base64;
            const previewBox = document.getElementById('batchPreviewBox');
            const previewImg = document.getElementById('batchPreviewImg');
            const applyBtn = document.getElementById('btnApplyBatch');

            previewImg.src = base64;
            previewBox.style.display = 'block';
            applyBtn.disabled = false;
        });
    };

    window.applyBatchParaf = function() {
        if (!currentBatchOptimizedData) return;

        const selector = currentBatchTarget === 'activity' 
            ? '.paraf-slot-container[data-type="log"]' 
            : '.paraf-slot-container[data-type="rec"]';

        const containers = document.querySelectorAll(selector);
        containers.forEach(container => {
            const rowId = container.dataset.rowId;
            const prefix = currentBatchTarget === 'activity' ? 'log' : 'rec';

            const previewBox = document.getElementById(`paraf-box-${prefix}-${rowId}`);
            const previewImg = document.getElementById(`paraf-img-${prefix}-${rowId}`);
            const uploadBtn = document.getElementById(`paraf-upload-btn-${prefix}-${rowId}`);
            const dataInput = document.getElementById(`paraf-data-${prefix}-${rowId}`);
            const removeInput = document.getElementById(`remove-paraf-${prefix}-${rowId}`);

            if (previewImg) previewImg.src = currentBatchOptimizedData;
            if (previewBox) previewBox.style.display = 'flex';
            if (uploadBtn) uploadBtn.style.display = 'none';
            if (dataInput) dataInput.value = currentBatchOptimizedData;
            if (removeInput) removeInput.value = '0';
        });

        closeModal('batchParafModal');
        Toast.fire({
            icon: 'success',
            title: `Paraf diterapkan ke semua baris ${currentBatchTarget === 'activity' ? 'kegiatan' : 'presensi'}! Klik Simpan untuk memperbarui.`
        });
    };

    // MENTOR SIGNATURE
    window.handleMentorSigSelected = function(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        window.ParafOptimizer.optimizeImage(file, function(base64) {
            const img = document.getElementById('mentorSigImg');
            const placeholder = document.getElementById('mentorSigPlaceholder');
            const removeBtn = document.getElementById('btnRemoveMentorSig');
            const dataInput = document.getElementById('mentorSigData');
            const removeInput = document.getElementById('removeMentorSigInput');

            if (img) {
                img.src = base64;
                img.style.display = 'block';
            }
            if (placeholder) placeholder.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'inline-flex';
            if (dataInput) dataInput.value = base64;
            if (removeInput) removeInput.value = '0';

            Toast.fire({
                icon: 'success',
                title: 'Tanda tangan mentor diproses transparan & siap disimpan'
            });
        });
    };

    window.removeMentorSignature = function() {
        const img = document.getElementById('mentorSigImg');
        const placeholder = document.getElementById('mentorSigPlaceholder');
        const removeBtn = document.getElementById('btnRemoveMentorSig');
        const dataInput = document.getElementById('mentorSigData');
        const removeInput = document.getElementById('removeMentorSigInput');
        const fileInput = document.getElementById('mentorSigFileInput');

        if (img) {
            img.src = '';
            img.style.display = 'none';
        }
        if (placeholder) placeholder.style.display = 'inline';
        if (removeBtn) removeBtn.style.display = 'none';
        if (dataInput) dataInput.value = '';
        if (removeInput) removeInput.value = '1';
        if (fileInput) fileInput.value = '';

        Toast.fire({
            icon: 'info',
            title: 'Tanda tangan mentor ditandai untuk dihapus'
        });
    };
</script>
@endpush
