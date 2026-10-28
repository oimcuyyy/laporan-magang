import { ApiService } from './api.js';

let state = {
    students: [],
    activeStudentId: null,
    activeStudent: null,
    activeTab: 'cover',
    activeActivitySheetId: null,
    activeAttendanceSheetId: null,
};

// ==========================================
// SWEETALERT2 NOTIFICATION & DIALOG HELPERS
// ==========================================
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

function notifySuccess(title) {
    Toast.fire({
        icon: 'success',
        title: title
    });
}

function notifyError(title) {
    Toast.fire({
        icon: 'error',
        title: title
    });
}

// ==========================================
// RILOK OTOMATIS SETIAP 3 DETIK (SMART LIVE AUTO-SYNC)
// ==========================================
let spaReloadCountdown = 3;
let isSpaReloadActive = true; // Otomatis aktif dari web
let spaReloadInterval = null;

window.toggleSpaAutoReload = function() {
    isSpaReloadActive = !isSpaReloadActive;
    localStorage.setItem('spa_auto_reload', isSpaReloadActive ? 'true' : 'false');
    spaReloadCountdown = 3;
    updateSpaReloadUI();

    if (isSpaReloadActive) {
        Toast.fire({ icon: 'info', title: 'Rilok otomatis data setiap 3 detik DIAKTIFKAN' });
    } else {
        Toast.fire({ icon: 'info', title: 'Rilok otomatis DIJEDA' });
    }
};

function updateSpaReloadUI() {
    const btn = document.getElementById('btnSpaAutoReload');
    const dot = document.getElementById('spaReloadDot');
    const txt = document.getElementById('spaReloadText');
    if (!btn || !dot || !txt) return;

    if (isSpaReloadActive) {
        btn.style.borderColor = '#93c5fd';
        btn.style.background = '#eff6ff';
        btn.style.color = '#1e40af';
        dot.style.background = '#2563eb';
        txt.textContent = 'Rilok: ' + spaReloadCountdown + 's';
    } else {
        btn.style.borderColor = '#e2e8f0';
        btn.style.background = '#ffffff';
        btn.style.color = '#64748b';
        dot.style.background = '#94a3b8';
        txt.textContent = 'Rilok: Jeda';
    }
}

function startSpaReloadLoop() {
    if (spaReloadInterval) clearInterval(spaReloadInterval);
    spaReloadInterval = setInterval(async () => {
        if (!isSpaReloadActive) return;

        const activeEl = document.activeElement;
        const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');
        const isModalOpen = document.getElementById('wafModal')?.classList.contains('show') || document.querySelector('.swal2-shown');

        if (isTyping || isModalOpen) {
            const txt = document.getElementById('spaReloadText');
            if (txt) txt.textContent = 'Mengetik...';
            return;
        }

        spaReloadCountdown--;
        updateSpaReloadUI();

        if (spaReloadCountdown <= 0) {
            spaReloadCountdown = 3;
            if (state.activeStudentId) {
                try {
                    const res = await ApiService.getStudent(state.activeStudentId);
                    if (res.success && res.data) {
                        state.activeStudent = res.data;
                        renderStudentHeader(res.data);
                    }
                } catch (e) {
                    // silent fail
                }
            }
        }
    }, 1000);
}

// Inisialisasi Aplikasi
document.addEventListener('DOMContentLoaded', async () => {
    updateSpaReloadUI();
    startSpaReloadLoop();
    await loadStudents();
    await loadWafStatus();
    setInterval(loadWafStatus, 15000); // Polling status WAF setiap 15 detik
});

// ==========================================
// 1. DATA SISWA & SWITCHER
// ==========================================
async function loadStudents() {
    try {
        const res = await ApiService.getStudents();
        if (res.success && res.data.length > 0) {
            state.students = res.data;
            if (!state.activeStudentId || !res.data.some(s => s.id === state.activeStudentId)) {
                state.activeStudentId = res.data[0].id;
            }
            renderStudentSwitcher(res.data, res.can_add);
            await loadActiveStudent();
        }
    } catch (err) {
        console.error('Gagal memuat siswa:', err);
        notifyError('Koneksi ke backend gagal: ' + err.message);
    }
}

function renderStudentSwitcher(students, canAdd) {
    const container = document.getElementById('studentSwitcherContainer');
    let html = '';

    students.forEach((s, idx) => {
        const isActive = s.id === state.activeStudentId ? 'active' : '';
        html += `
            <button type="button" class="switcher-btn ${isActive}" onclick="selectStudent(${s.id})">
                <span style="font-weight:800;">#${idx + 1}</span> ${escapeHtml(s.name)} (${escapeHtml(s.nis)})
            </button>
        `;
    });

    if (canAdd) {
        html += `
            <button type="button" class="switcher-btn" style="border-style:dashed;color:var(--primary);font-weight:700;" onclick="promptAddStudent()">
                + Tambah Siswa (Maks 5)
            </button>
        `;
    }

    container.innerHTML = html;
}

window.selectStudent = async function(id) {
    state.activeStudentId = id;
    renderStudentSwitcher(state.students, state.students.length < 5);
    await loadActiveStudent();
};

// ==========================================
// 2. SISWA AKTIF & TABS
// ==========================================
async function loadActiveStudent() {
    try {
        const res = await ApiService.getStudent(state.activeStudentId);
        if (res.success) {
            state.activeStudent = res.data;
            renderStudentHeader(res.data);
            renderCoverTab(res.data);
            renderIdentityTab(res.data);
            renderActivityTab(res.data);
            renderAttendanceTab(res.data);
            renderEvaluationTab(res.data);
        }
    } catch (err) {
        console.error('Gagal memuat detail siswa:', err);
    }
}

function renderStudentHeader(s) {
    document.getElementById('activeStudentName').textContent = s.name;
    document.getElementById('activeStudentMeta').innerHTML = `
        NIS: <strong>${escapeHtml(s.nis)}</strong> &bull; 
        Kelas: <strong>${escapeHtml(s.class)}</strong> &bull; 
        Jurusan: <strong>${escapeHtml(s.major)}</strong> &bull; 
        Guru: <strong>${escapeHtml(s.teacher_name || '-')}</strong>
    `;

    const avatarBox = document.getElementById('studentAvatarBox');
    if (s.photo_url) {
        avatarBox.innerHTML = `<img src="${s.photo_url}" alt="Foto" style="width:100%;height:100%;object-fit:cover;">`;
    } else {
        avatarBox.innerHTML = `Foto 3x4`;
    }
}

// Tab 1: Cover
function renderCoverTab(s) {
    document.getElementById('coverSchoolName').textContent = (s.school_name || 'SMK NEGERI 20 JAKARTA').toUpperCase();
    document.getElementById('coverFieldExpertise').textContent = (s.field_of_expertise || 'BISNIS MANAJEMEN DAN TEKNOLOGI').toUpperCase();
    const yr = s.academic_year || '2026/2027';
    const yrEl = document.getElementById('coverAcademicYear');
    if (yrEl) yrEl.textContent = `TAHUN PELAJARAN ${yr}`;

    document.getElementById('coverStudentName').textContent = s.name;
    document.getElementById('coverStudentClass').textContent = s.class;
    document.getElementById('coverStudentMajor').textContent = s.major;
    document.getElementById('coverStudentPeriod').textContent = s.internship_period || '01 Oktober 2026 - 30 November 2026';

    // Sinkronkan nilai selector jurusan & angkatan
    const majorSel = document.getElementById('spaQuickMajorSelect');
    if (majorSel) {
        let matched = false;
        for (let opt of majorSel.options) {
            if (opt.value.startsWith(s.major + '|')) {
                majorSel.value = opt.value;
                matched = true;
                break;
            }
        }
        if (!matched) majorSel.value = 'CUSTOM';
    }

    const yrSel = document.getElementById('spaQuickYearSelect');
    if (yrSel) {
        let matched = false;
        for (let opt of yrSel.options) {
            if (opt.value === yr) {
                yrSel.value = opt.value;
                matched = true;
                break;
            }
        }
        if (!matched) yrSel.value = 'CUSTOM';
    }
}

// Tab 2: Identitas Peserta
function renderIdentityTab(s) {
    if (!s) return;
    // Form fields
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    setVal('id_name', s.name);
    setVal('id_nis', s.nis);
    setVal('id_nisn', s.nisn);
    setVal('id_birth', (s.birth_place ? s.birth_place + ', ' : '') + (s.birth_date || ''));
    setVal('id_gender', s.gender || 'Laki-Laki');
    setVal('id_religion', s.religion || 'Islam');
    setVal('id_student_phone', s.student_phone);
    setVal('id_student_address', s.student_address);
    setVal('id_father_name', s.father_name);
    setVal('id_father_phone', s.father_phone);
    setVal('id_mother_name', s.mother_name);
    setVal('id_mother_phone', s.mother_phone);
    setVal('id_parent_address', s.parent_address);
    setVal('id_school_name', s.school_name || 'SMK Negeri 20 Jakarta');
    setVal('id_school_address', s.school_address);
    setVal('id_company_name', s.company_name || 'Kantor Lurah Bintaro');
    setVal('id_company_address', s.company_address);
    setVal('id_teacher_name', s.teacher_name);
    setVal('id_teacher_phone', s.teacher_phone);
    setVal('id_mentor_name', s.mentor_name);

    // Photo preview
    const preview = document.getElementById('id_photo_preview');
    if (preview) {
        if (s.photo_url) {
            preview.innerHTML = `<img src="${s.photo_url}" style="width:100%;height:100%;object-fit:cover;">`;
        } else {
            preview.innerHTML = `<span style="font-size:0.75rem;color:var(--slate-400);">Pas Foto<br>3x4</span>`;
        }
    }

    // Populate Authentic A4 Paper Sheet Elements
    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '-'; };
    setText('paper_name', s.name);
    setText('paper_nis', s.nis);
    setText('paper_birth', (s.birth_place || 'Jakarta') + ', ' + (s.birth_date || '09 Desember 2008'));
    setText('paper_gender', s.gender || 'Laki-Laki');
    setText('paper_religion', s.religion || 'Islam');
    setText('paper_address', s.student_address || 'Jl. Damai IV Rt.002 / RW.002');
    setText('paper_phone', s.student_phone || '081314854567');
    setText('paper_father', s.father_name || 'Mubasir');
    setText('paper_father_phone', s.father_phone || '-');
    setText('paper_mother', s.mother_name || 'Rita Murni');
    setText('paper_mother_phone', s.mother_phone || '-');
    setText('paper_parent_address', s.parent_address || 'Jl. Damai IV');
    setText('paper_school', s.school_name || 'SMK Negeri 20 Jakarta');
    setText('paper_school_address', s.school_address || 'Jl. Melati No. 24 RT 13/RW 10, Cilandak Bar., Kota Jaksel');
    setText('paper_company', s.company_name || 'Kantor Lurah Bintaro');
    setText('paper_company_address', s.company_address || 'Jl. B.C. Veteran Raya No. 1, RT 1/RW 3 Bintaro');
    setText('paper_teacher', s.teacher_name || 'Kuswanto');
    setText('paper_teacher_phone', s.teacher_phone || '0858-8192-1976');
    setText('paper_sign_name', s.name);
    setText('paper_sign_nis', 'NIS. ' + (s.nis || '-'));

    const paperPhotoBox = document.getElementById('paper_photo_box');
    if (paperPhotoBox) {
        if (s.photo_url) {
            paperPhotoBox.innerHTML = `<img src="${s.photo_url}" style="width:100%;height:100%;object-fit:cover;">`;
        } else {
            paperPhotoBox.innerHTML = `<span style="font-size:0.8rem;text-align:center;">Pas Foto<br>3 x 4</span>`;
        }
    }
}

window.switchSpaIdentitasMode = function(mode) {
    const formView = document.getElementById('spaIdentitasFormView');
    const paperView = document.getElementById('spaIdentitasPaperView');
    const btnForm = document.getElementById('spaBtnViewForm');
    const btnPaper = document.getElementById('spaBtnViewPaper');

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
};

window.handleIdentitySubmit = async function(e) {
    e.preventDefault();
    const s = state.activeStudent;
    if (!s) return;

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('name', document.getElementById('id_name').value);
    formData.append('nis', document.getElementById('id_nis').value);
    formData.append('nisn', document.getElementById('id_nisn')?.value || '');
    formData.append('gender', document.getElementById('id_gender').value);
    formData.append('religion', document.getElementById('id_religion').value);
    formData.append('student_address', document.getElementById('id_student_address').value);
    formData.append('student_phone', document.getElementById('id_student_phone').value);
    formData.append('father_name', document.getElementById('id_father_name').value);
    formData.append('father_phone', document.getElementById('id_father_phone').value);
    formData.append('mother_name', document.getElementById('id_mother_name').value);
    formData.append('mother_phone', document.getElementById('id_mother_phone').value);
    formData.append('parent_address', document.getElementById('id_parent_address').value);
    formData.append('school_name', document.getElementById('id_school_name').value);
    formData.append('school_address', document.getElementById('id_school_address').value);
    formData.append('company_name', document.getElementById('id_company_name').value);
    formData.append('company_address', document.getElementById('id_company_address').value);
    formData.append('teacher_name', document.getElementById('id_teacher_name').value);
    formData.append('teacher_phone', document.getElementById('id_teacher_phone').value);
    formData.append('mentor_name', document.getElementById('id_mentor_name').value);

    const fileInput = document.getElementById('id_photo_file');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('photo', fileInput.files[0]);
    }

    try {
        const res = await ApiService.updateStudent(s.id, formData);
        if (res.success) {
            notifySuccess('Identitas peserta berhasil disimpan!');
            await loadStudents();
        } else {
            notifyError('Gagal: ' + (res.message || 'Terjadi kesalahan'));
        }
    } catch (err) {
        notifyError('Error: ' + err.message);
    }
};

window.handlePhotoUpload = function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (evt) => {
            const preview = document.getElementById('id_photo_preview');
            if (preview) preview.innerHTML = `<img src="${evt.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
            const paperBox = document.getElementById('paper_photo_box');
            if (paperBox) paperBox.innerHTML = `<img src="${evt.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(file);
    }
};

// ==========================================
// 3. TAB 3: URAIAN KEGIATAN (SLIDE SYSTEM 10 BARIS)
// ==========================================
function renderActivityTab(s) {
    const sheets = s.activity_sheets || [];
    if (!state.activeActivitySheetId && sheets.length > 0) {
        state.activeActivitySheetId = sheets[0].id;
    }

    const currentSheet = sheets.find(sh => sh.id === state.activeActivitySheetId) || sheets[0];

    // Render Slide Pills
    const pillsContainer = document.getElementById('activitySlidePills');
    let pillsHtml = '';
    sheets.forEach(sh => {
        const isActive = sh.id === (currentSheet ? currentSheet.id : null) ? 'active' : '';
        pillsHtml += `
            <button type="button" class="btn btn-secondary btn-sm ${isActive}" onclick="selectActivitySlide(${sh.id})" style="${isActive ? 'background:var(--primary);color:#fff;border-color:var(--primary-dark);' : ''}">
                Slide ${sh.sheet_number} (${escapeHtml(sh.month_name)})
            </button>
        `;
    });
    pillsContainer.innerHTML = pillsHtml;

    // Render Table
    const content = document.getElementById('activitySheetContent');
    if (!currentSheet) {
        content.innerHTML = '<p style="padding:1.5rem;color:var(--slate-500);">Belum ada lembar kegiatan. Klik tombol Tambah Slide Lembar Baru (+).</p>';
        return;
    }

    const logs = currentSheet.activity_logs || [];
    let rowsHtml = '';
    logs.forEach(log => {
        rowsHtml += `
            <tr>
                <td style="text-align:center;font-weight:700;">${log.row_index}.</td>
                <td><input type="text" id="act_date_${log.id}" class="table-field" value="${escapeHtml(log.day_date || '')}" placeholder="Kamis, 01-10-26"></td>
                <td><input type="text" id="act_div_${log.id}" class="table-field" value="${escapeHtml(log.division || '')}" placeholder="Perpanjang Makam"></td>
                <td><textarea id="act_desc_${log.id}" class="table-field" rows="2" placeholder="Uraian kegiatan...">${escapeHtml(log.job_description || '')}</textarea></td>
                <td>
                    <input type="text" id="act_note_${log.id}" class="table-field" value="${escapeHtml(log.supervisor_note || '')}" placeholder="Paraf/Catatan">
                    <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.75rem;margin-top:0.25rem;">
                        <input type="checkbox" id="act_app_${log.id}" ${log.is_approved ? 'checked' : ''}> Disetujui
                    </label>
                </td>
            </tr>
        `;
    });

    content.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;background:#f8fafc;padding:0.6rem 1rem;border-radius:var(--radius-sm);border:1px solid var(--slate-200);flex-wrap:wrap;gap:0.75rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <label style="font-weight:700;font-size:0.9rem;">Bulan :</label>
                <input type="text" id="act_month_name" value="${escapeHtml(currentSheet.month_name)}" class="table-field" style="border:1px solid #cbd5e1;width:240px;">
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span style="font-weight:700;font-size:0.85rem;color:var(--primary);">Lembar Ke-${currentSheet.sheet_number} (10 Baris)</span>
                ${sheets.length > 1 ? `
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteActivitySheet(${currentSheet.id}, ${currentSheet.sheet_number})" title="Hapus slide lembar ini">
                        Hapus Slide Ini
                    </button>
                ` : ''}
            </div>
        </div>
        <table class="sheet-table">
            <thead>
                <tr>
                    <th style="width:45px;">No.</th>
                    <th style="width:160px;">Hari / Tanggal</th>
                    <th style="width:180px;">Divisi / Bagian</th>
                    <th>Uraian Pekerjaan</th>
                    <th style="width:160px;">Pembimbing Industri</th>
                </tr>
            </thead>
            <tbody>${rowsHtml}</tbody>
        </table>
        <div style="margin-top:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <span style="font-size:0.85rem;color:var(--slate-500);">Jika telah mencapai baris ke-10, klik tombol <strong>+ Tambah Slide Lembar Baru</strong> di kanan atas.</span>
            <button type="button" class="btn btn-primary" onclick="saveActivitySheet(${currentSheet.id})">Simpan Lembar Ke-${currentSheet.sheet_number}</button>
        </div>
    `;
}

window.selectActivitySlide = function(sheetId) {
    state.activeActivitySheetId = sheetId;
    renderActivityTab(state.activeStudent);
};

window.saveActivitySheet = async function(sheetId) {
    const s = state.activeStudent;
    const currentSheet = (s.activity_sheets || []).find(sh => sh.id === sheetId);
    if (!currentSheet) return;

    const logsPayload = (currentSheet.activity_logs || []).map(log => {
        const parafDataEl = document.getElementById(`act_paraf_data_${log.id}`);
        const removeParafEl = document.getElementById(`act_remove_paraf_${log.id}`);
        return {
            id: log.id,
            day_date: document.getElementById(`act_date_${log.id}`)?.value || '',
            division: document.getElementById(`act_div_${log.id}`)?.value || '',
            job_description: document.getElementById(`act_desc_${log.id}`)?.value || '',
            supervisor_note: document.getElementById(`act_note_${log.id}`)?.value || '',
            is_approved: document.getElementById(`act_app_${log.id}`)?.checked || false,
            paraf_data: parafDataEl ? parafDataEl.value : null,
            remove_paraf: removeParafEl ? (removeParafEl.value === '1') : false,
        };
    });

    const monthName = document.getElementById('act_month_name').value;

    try {
        const res = await ApiService.updateActivitySheet(s.id, sheetId, {
            month_name: monthName,
            logs: logsPayload
        });
        if (res.success) {
            notifySuccess('Lembar uraian kegiatan berhasil disimpan!');
            await loadActiveStudent();
        } else {
            notifyError('Gagal: ' + res.message);
        }
    } catch (err) {
        notifyError('Error: ' + err.message);
    }
};

// SweetAlert2: Tambah Slide Lembar Uraian Baru
window.promptAddActivitySheet = async function() {
    const { value: month } = await Swal.fire({
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
        inputValidator: (val) => {
            if (!val || !val.trim()) return 'Nama bulan / keterangan lembar wajib diisi!';
        }
    });

    if (month) {
        try {
            const res = await ApiService.addActivitySheet(state.activeStudentId, month.trim());
            if (res.success) {
                notifySuccess(res.message || 'Slide lembar baru berhasil dibuat!');
                state.activeActivitySheetId = res.data.id;
                await loadActiveStudent();
            } else {
                notifyError('Gagal: ' + res.message);
            }
        } catch (err) {
            notifyError('Error: ' + err.message);
        }
    }
};

// SweetAlert2: Hapus Slide Uraian
window.deleteActivitySheet = async function(sheetId, sheetNum) {
    const result = await Swal.fire({
        title: `Hapus Slide ${sheetNum}?`,
        text: 'Data 10 baris kegiatan pada lembar slide ini akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Slide!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
    });

    if (result.isConfirmed) {
        try {
            const res = await ApiService.deleteActivitySheet(state.activeStudentId, sheetId);
            if (res.success) {
                notifySuccess(res.message || 'Slide berhasil dihapus');
                state.activeActivitySheetId = null;
                await loadActiveStudent();
            } else {
                notifyError('Gagal: ' + res.message);
            }
        } catch (err) {
            notifyError('Error: ' + err.message);
        }
    }
};

// ==========================================
// 4. TAB 4: PRESENSI & REKAP OTOMATIS
// ==========================================
function renderAttendanceTab(s) {
    const sheets = s.attendance_sheets || [];
    if (!state.activeAttendanceSheetId && sheets.length > 0) {
        state.activeAttendanceSheetId = sheets[0].id;
    }

    const currentSheet = sheets.find(sh => sh.id === state.activeAttendanceSheetId) || sheets[0];

    // Pills
    const pillsContainer = document.getElementById('attendanceMonthPills');
    let pillsHtml = '';
    sheets.forEach(sh => {
        const isActive = sh.id === (currentSheet ? currentSheet.id : null) ? 'active' : '';
        pillsHtml += `
            <button type="button" class="btn btn-secondary btn-sm ${isActive}" onclick="selectAttendanceMonth(${sh.id})" style="${isActive ? 'background:var(--primary);color:#fff;border-color:var(--primary-dark);' : ''}">
                Bulan ${escapeHtml(sh.month_name)}
            </button>
        `;
    });
    pillsContainer.innerHTML = pillsHtml;

    const content = document.getElementById('attendanceSheetContent');
    if (!currentSheet) {
        content.innerHTML = '<p style="padding:1.5rem;color:var(--slate-500);">Belum ada lembar presensi. Klik tombol Tambah Bulan Presensi (+).</p>';
        return;
    }

    const records = currentSheet.records || [];
    let rowsHtml = '';
    records.forEach(rec => {
        const isAfterCutoff = rec.day_number > 26;
        const isWeekend = (rec.day_date || '').toLowerCase().includes('sabtu') || (rec.day_date || '').toLowerCase().includes('minggu');
        const rowBg = isAfterCutoff ? 'background:#fcfcfc;' : (isWeekend ? 'background:#f8fafc;' : '');

        rowsHtml += `
            <tr style="${rowBg}">
                <td style="text-align:center;font-weight:600;color:${isAfterCutoff ? 'var(--slate-400)' : 'var(--slate-800)'};">${rec.day_number}.</td>
                <td><input type="text" id="att_date_${rec.id}" class="table-field" style="${isAfterCutoff ? 'color:var(--slate-500);' : ''}" value="${escapeHtml(rec.day_date || '')}" placeholder="Hari, DD-MM-YYYY"></td>
                <td style="text-align:center;"><input type="radio" name="status_${rec.id}" value="H" ${rec.status === 'H' ? 'checked' : ''} onchange="liveRecapAttendance()" title="Hadir"></td>
                <td style="text-align:center;background:#fffbeb;"><input type="radio" name="status_${rec.id}" value="S" ${rec.status === 'S' ? 'checked' : ''} onchange="liveRecapAttendance()" title="Sakit"></td>
                <td style="text-align:center;background:#eff6ff;"><input type="radio" name="status_${rec.id}" value="I" ${rec.status === 'I' ? 'checked' : ''} onchange="liveRecapAttendance()" title="Izin"></td>
                <td style="text-align:center;background:#fef2f2;"><input type="radio" name="status_${rec.id}" value="A" ${rec.status === 'A' ? 'checked' : ''} onchange="liveRecapAttendance()" title="Alpa"></td>
                <td>
                    <div style="display:flex;align-items:center;gap:0.4rem;">
                        ${isAfterCutoff ? '<span style="font-size:0.7rem;padding:0.15rem 0.4rem;background:#f1f5f9;color:#64748b;border-radius:4px;white-space:nowrap;font-weight:600;">Belum Berjalan</span>' : ''}
                        ${isWeekend ? '<span style="font-size:0.7rem;padding:0.15rem 0.4rem;background:#fef3c7;color:#92400e;border-radius:4px;white-space:nowrap;font-weight:600;">Akhir Pekan</span>' : ''}
                        <input type="text" id="att_note_${rec.id}" class="table-field" value="${escapeHtml(rec.notes || '')}" placeholder="${isAfterCutoff ? 'Belum Berjalan (Setelah 26 Okt)' : 'Keterangan...'}">
                    </div>
                </td>
            </tr>
        `;
    });

    content.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;background:#f8fafc;padding:0.6rem 1rem;border-radius:var(--radius-sm);border:1px solid var(--slate-200);flex-wrap:wrap;gap:0.75rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <label style="font-weight:700;font-size:0.9rem;">Bulan :</label>
                <input type="text" id="att_month_name" value="${escapeHtml(currentSheet.month_name)}" class="table-field" style="border:1px solid #cbd5e1;width:240px;">
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span style="font-weight:700;color:var(--success);">${currentSheet.total_target_days} Hari Kalender</span>
                ${sheets.length > 1 ? `
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteAttendanceSheet(${currentSheet.id}, '${escapeHtml(currentSheet.month_name)}')" title="Hapus lembar presensi bulan ini">
                        Hapus Presensi Bulan Ini
                    </button>
                ` : ''}
            </div>
        </div>
        <table class="sheet-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:40px;">No.</th>
                    <th rowspan="2" style="width:160px;">Hari, Tanggal</th>
                    <th rowspan="2" style="width:65px;">Hadir (H)</th>
                    <th colspan="3" style="width:140px;">Tidak Hadir</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th style="width:45px;background:#f1f5f9;">S</th>
                    <th style="width:45px;background:#f1f5f9;">I</th>
                    <th style="width:45px;background:#f1f5f9;">A</th>
                </tr>
            </thead>
            <tbody>${rowsHtml}</tbody>
        </table>

        <!-- Box Rekapitulasi Otomatis (Sesuai Foto 4) -->
        <div style="margin-top:1.5rem;background:#f8fafc;border:2px solid #cbd5e1;border-radius:var(--radius-md);padding:1.25rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
            <div>
                <h4 style="font-size:0.95rem;font-weight:800;color:var(--slate-800);margin-bottom:0.5rem;border-bottom:1px solid #cbd5e1;padding-bottom:0.35rem;">Rekapitulasi Kehadiran</h4>
                <p style="font-size:0.88rem;color:var(--slate-700);">Jumlah Seharusnya Hadir: <strong>${currentSheet.total_target_days} Hari</strong></p>
                <p style="font-size:0.88rem;color:var(--slate-700);margin-top:0.25rem;">Tidak Hadir: S: <strong id="recap_s">${currentSheet.total_sick}</strong> | I: <strong id="recap_i">${currentSheet.total_permission}</strong> | A: <strong id="recap_a">${currentSheet.total_absent}</strong></p>
                <p style="font-size:0.95rem;color:var(--success);font-weight:700;margin-top:0.4rem;">Jumlah Hadir: <span id="recap_h" style="font-size:1.15rem;">${currentSheet.total_present}</span> Hari</p>
            </div>
            <div style="text-align:right;">
                <button type="button" class="btn btn-primary" onclick="saveAttendanceSheet(${currentSheet.id})">Simpan Presensi</button>
            </div>
        </div>
    `;
}

window.selectAttendanceMonth = function(id) {
    state.activeAttendanceSheetId = id;
    renderAttendanceTab(state.activeStudent);
};

window.liveRecapAttendance = function() {
    const checked = document.querySelectorAll('input[type="radio"]:checked');
    let h = 0, s = 0, i = 0, a = 0;
    checked.forEach(r => {
        if (r.value === 'H') h++;
        else if (r.value === 'S') s++;
        else if (r.value === 'I') i++;
        else if (r.value === 'A') a++;
    });
    if (document.getElementById('recap_h')) document.getElementById('recap_h').textContent = h;
    if (document.getElementById('recap_s')) document.getElementById('recap_s').textContent = s;
    if (document.getElementById('recap_i')) document.getElementById('recap_i').textContent = i;
    if (document.getElementById('recap_a')) document.getElementById('recap_a').textContent = a;
};

window.saveAttendanceSheet = async function(sheetId) {
    const s = state.activeStudent;
    const currentSheet = (s.attendance_sheets || []).find(sh => sh.id === sheetId);
    if (!currentSheet) return;

    const recordsPayload = (currentSheet.records || []).map(rec => {
        const checkedRadio = document.querySelector(`input[name="status_${rec.id}"]:checked`);
        const parafDataEl = document.getElementById(`att_paraf_data_${rec.id}`);
        const removeParafEl = document.getElementById(`att_remove_paraf_${rec.id}`);
        return {
            id: rec.id,
            day_date: document.getElementById(`att_date_${rec.id}`)?.value || '',
            status: checkedRadio ? checkedRadio.value : null,
            notes: document.getElementById(`att_note_${rec.id}`)?.value || '',
            paraf_data: parafDataEl ? parafDataEl.value : null,
            remove_paraf: removeParafEl ? (removeParafEl.value === '1') : false,
        };
    });

    const monthName = document.getElementById('att_month_name').value;

    try {
        const res = await ApiService.updateAttendanceSheet(s.id, sheetId, {
            month_name: monthName,
            records: recordsPayload
        });
        if (res.success) {
            notifySuccess('Presensi dan rekapitulasi berhasil disimpan!');
            await loadActiveStudent();
        } else {
            notifyError('Gagal: ' + res.message);
        }
    } catch (err) {
        notifyError('Error: ' + err.message);
    }
};

// SweetAlert2: Tambah Lembar Presensi Baru
window.promptAddAttendanceSheet = async function() {
    const { value: formValues } = await Swal.fire({
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
            const m = document.getElementById('swal-att-month').value;
            const d = document.getElementById('swal-att-days').value;
            if (!m || !m.trim()) {
                Swal.showValidationMessage('Nama bulan wajib diisi!');
                return false;
            }
            return { month: m.trim(), days: parseInt(d, 10) };
        }
    });

    if (formValues) {
        try {
            const res = await ApiService.addAttendanceSheet(state.activeStudentId, formValues.month, formValues.days);
            if (res.success) {
                notifySuccess(res.message || 'Lembar presensi baru berhasil dibuat!');
                state.activeAttendanceSheetId = res.data.id;
                await loadActiveStudent();
            } else {
                notifyError('Gagal: ' + res.message);
            }
        } catch (err) {
            notifyError('Error: ' + err.message);
        }
    }
};

// SweetAlert2: Hapus Lembar Presensi
window.deleteAttendanceSheet = async function(sheetId, monthName) {
    const result = await Swal.fire({
        title: `Hapus Presensi Bulan ${monthName}?`,
        text: 'Seluruh riwayat absensi harian pada bulan ini akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Presensi!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
    });

    if (result.isConfirmed) {
        try {
            const res = await ApiService.deleteAttendanceSheet(state.activeStudentId, sheetId);
            if (res.success) {
                notifySuccess(res.message || 'Lembar presensi berhasil dihapus');
                state.activeAttendanceSheetId = null;
                await loadActiveStudent();
            } else {
                notifyError('Gagal: ' + res.message);
            }
        } catch (err) {
            notifyError('Error: ' + err.message);
        }
    }
};

// ==========================================
// 5. TAB 5: PENILAIAN DU/DI
// ==========================================
function renderEvaluationTab(s) {
    const ev = s.evaluation || {};
    document.getElementById('eval_a').value = ev.aspect_a_score || 95;
    document.getElementById('eval_b').value = ev.aspect_b_score || 90;
    document.getElementById('eval_c').value = ev.aspect_c_score || 92;
    document.getElementById('eval_date').value = ev.evaluation_date || 'Jakarta, 31 Oktober 2026';
    document.getElementById('eval_mentor').value = ev.mentor_name || s.mentor_name || 'Sujatmo, S.Sos';

    window.updateEvalGrades();
}

window.updateEvalGrades = function() {
    const a = parseFloat(document.getElementById('eval_a').value) || 0;
    const b = parseFloat(document.getElementById('eval_b').value) || 0;
    const c = parseFloat(document.getElementById('eval_c').value) || 0;

    const grade = (score) => {
        if (score >= 91) return 'A';
        if (score >= 81) return 'B';
        if (score >= 75) return 'C';
        return 'D';
    };

    document.getElementById('grade_a').textContent = grade(a);
    document.getElementById('grade_b').textContent = grade(b);
    document.getElementById('grade_c').textContent = grade(c);

    const avg = ((a + b + c) / 3).toFixed(2);
    document.getElementById('eval_avg').textContent = avg;
    document.getElementById('eval_final_grade').textContent = grade(parseFloat(avg));
};

window.handleEvaluationSubmit = async function(e) {
    e.preventDefault();
    const s = state.activeStudent;
    if (!s) return;

    const payload = {
        aspect_a_score: parseFloat(document.getElementById('eval_a').value),
        aspect_b_score: parseFloat(document.getElementById('eval_b').value),
        aspect_c_score: parseFloat(document.getElementById('eval_c').value),
        evaluation_date: document.getElementById('eval_date').value,
        mentor_name: document.getElementById('eval_mentor').value
    };

    try {
        const res = await ApiService.updateEvaluation(s.id, payload);
        if (res.success) {
            notifySuccess('Penilaian DU/DI berhasil disimpan!');
            await loadActiveStudent();
        } else {
            notifyError('Gagal: ' + res.message);
        }
    } catch (err) {
        notifyError('Error: ' + err.message);
    }
};

// ==========================================
// 6. WAF STATUS & TELEMETRY
// ==========================================
async function loadWafStatus() {
    try {
        const res = await ApiService.getWafStatus();
        if (res.success) {
            document.getElementById('wafBlockedCount').textContent = `${res.statistics.total_blocked} Blocked`;

            let incidentsHtml = '';
            (res.recent_blocked_incidents || []).forEach(inc => {
                incidentsHtml += `
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:0.75rem;border-radius:6px;margin-bottom:0.5rem;font-size:0.8rem;">
                        <div style="display:flex;justify-content:space-between;font-weight:700;">
                            <span style="color:#b91c1c;">[${escapeHtml(inc.threat_type)}] (${escapeHtml(inc.severity)})</span>
                            <span style="color:#64748b;">${escapeHtml(inc.created_at)}</span>
                        </div>
                        <p style="font-family:monospace;color:#334155;margin-top:0.25rem;word-break:break-all;">Target: ${escapeHtml(inc.request_url)}</p>
                        <p style="color:#64748b;margin-top:0.15rem;">Pola: <code>${escapeHtml(inc.matched_pattern)}</code> &bull; IP: ${escapeHtml(inc.ip_address)}</p>
                    </div>
                `;
            });

            document.getElementById('wafModalBody').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                    <div style="background:#ecfdf5;border:1px solid #a7f3d0;padding:1rem;border-radius:8px;text-align:center;">
                        <span style="font-size:0.8rem;color:#065f46;font-weight:700;">Status Engine</span>
                        <h4 style="color:#059669;font-size:1.1rem;margin-top:2px;">ACTIVE &bull; SHIELD ON</h4>
                    </div>
                    <div style="background:#fef2f2;border:1px solid #fecaca;padding:1rem;border-radius:8px;text-align:center;">
                        <span style="font-size:0.8rem;color:#991b1b;font-weight:700;">Total Serangan Diblokir</span>
                        <h4 style="color:#dc2626;font-size:1.4rem;margin-top:2px;">${res.statistics.total_blocked}</h4>
                    </div>
                </div>
                <h5 style="font-size:0.85rem;font-weight:700;color:var(--slate-700);margin-bottom:0.5rem;">Riwayat Serangan Terakhir yang Ditahan WAF:</h5>
                ${incidentsHtml || '<p style="font-size:0.85rem;color:var(--slate-500);">Belum ada serangan berbahaya.</p>'}
            `;
        }
    } catch (err) {
        console.error('Gagal mengambil status WAF:', err);
    }
}

window.toggleWafModal = function() {
    const modal = document.getElementById('wafModal');
    modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
};

// ==========================================
// 7. TAB SWITCHING
// ==========================================
window.switchTab = function(tabName) {
    state.activeTab = tabName;
    ['cover', 'identitas', 'uraian', 'presensi', 'penilaian'].forEach(t => {
        const sec = document.getElementById(`tab-${t}`);
        if (sec) sec.style.display = (t === tabName) ? 'block' : 'none';
    });

    document.querySelectorAll('.tab-item').forEach((btn, idx) => {
        const names = ['cover', 'identitas', 'uraian', 'presensi', 'penilaian'];
        btn.classList.toggle('active', names[idx] === tabName);
    });
};

// ==========================================
// 8. SWEETALERT2: TAMBAH SISWA (1-5 SISWA)
// ==========================================
window.promptAddStudent = async function() {
    if (state.students.length >= 5) {
        Swal.fire({
            icon: 'info',
            title: 'Batas Siswa Tercapai',
            text: 'Maksimal 5 siswa dalam satu berkas laporan magang.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    const { value: formValues } = await Swal.fire({
        title: 'Tambah Siswa Baru (1-5 Siswa)',
        html: `
            <div style="text-align:left;font-size:0.88rem;display:flex;flex-direction:column;gap:0.75rem;padding:0.5rem 0;">
                <div>
                    <label style="font-weight:700;display:block;margin-bottom:0.25rem;color:#334155;">Nama Lengkap Siswa:</label>
                    <input id="swal_name" class="swal2-input" style="width:100%;margin:0;box-sizing:border-box;font-size:0.92rem;" placeholder="Contoh: Muhammad Bintang">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <div>
                        <label style="font-weight:700;display:block;margin-bottom:0.25rem;color:#334155;">Nomor Induk Siswa (NIS):</label>
                        <input id="swal_nis" class="swal2-input" style="width:100%;margin:0;box-sizing:border-box;font-size:0.92rem;" placeholder="e.g. 10245">
                    </div>
                    <div>
                        <label style="font-weight:700;display:block;margin-bottom:0.25rem;color:#334155;">Kelas:</label>
                        <input id="swal_class" class="swal2-input" style="width:100%;margin:0;box-sizing:border-box;font-size:0.92rem;" value="XI RPL">
                    </div>
                </div>
                <div>
                    <label style="font-weight:700;display:block;margin-bottom:0.25rem;color:#334155;">Program Keahlian / Jurusan:</label>
                    <input id="swal_major" class="swal2-input" style="width:100%;margin:0;box-sizing:border-box;font-size:0.92rem;" value="Rekayasa Perangkat Lunak">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <div>
                        <label style="font-weight:700;display:block;margin-bottom:0.25rem;color:#334155;">Guru Pembimbing:</label>
                        <input id="swal_teacher" class="swal2-input" style="width:100%;margin:0;box-sizing:border-box;font-size:0.92rem;" value="Kuswanto, M.Kom">
                    </div>
                    <div>
                        <label style="font-weight:700;display:block;margin-bottom:0.25rem;color:#334155;">Jenis Kelamin:</label>
                        <select id="swal_gender" class="swal2-select" style="width:100%;margin:0;display:block;box-sizing:border-box;font-size:0.92rem;">
                            <option value="Laki-Laki" selected>Laki-Laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan Siswa (+)',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        preConfirm: () => {
            const name = document.getElementById('swal_name').value;
            const nis = document.getElementById('swal_nis').value;
            const sClass = document.getElementById('swal_class').value;
            const major = document.getElementById('swal_major').value;
            const teacher = document.getElementById('swal_teacher').value;
            const gender = document.getElementById('swal_gender').value;

            if (!name || !name.trim()) {
                Swal.showValidationMessage('Nama siswa wajib diisi!');
                return false;
            }
            if (!nis || !nis.trim()) {
                Swal.showValidationMessage('NIS siswa wajib diisi!');
                return false;
            }
            return { name: name.trim(), nis: nis.trim(), sClass, major, teacher, gender };
        }
    });

    if (formValues) {
        const formData = new FormData();
        formData.append('name', formValues.name);
        formData.append('nis', formValues.nis);
        formData.append('class', formValues.sClass);
        formData.append('major', formValues.major);
        formData.append('gender', formValues.gender);
        formData.append('school_name', 'SMK Negeri 20 Jakarta');
        formData.append('company_name', 'Kantor Lurah Bintaro');
        formData.append('teacher_name', formValues.teacher);

        try {
            const res = await ApiService.createStudent(formData);
            if (res.success) {
                notifySuccess('Siswa baru berhasil ditambahkan!');
                state.activeStudentId = res.data.id;
                await loadStudents();
            } else {
                notifyError('Gagal: ' + res.message);
            }
        } catch (err) {
            notifyError('Error: ' + err.message);
        }
    }
};

// Helper: Escape HTML string to prevent injection
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Live Switcher Jurusan & Angkatan (Frontend SPA)
window.handleSpaMajorSelection = async function(val) {
    if (!state.currentStudent) return;

    if (val === 'CUSTOM') {
        const { value: formValues } = await Swal.fire({
            title: 'Kustom Jurusan & Kelas',
            html: `
                <div style="text-align:left;font-size:0.9rem;">
                    <label style="font-weight:700;display:block;margin-bottom:4px;">Nama Program Keahlian / Jurusan:</label>
                    <input id="spa_swal_custom_major" class="swal2-input" style="width:100%;margin:0 0 12px 0;" placeholder="e.g. Bisnis Digital / Rekayasa Perangkat Lunak">
                    <label style="font-weight:700;display:block;margin-bottom:4px;">Nama Kelas:</label>
                    <input id="spa_swal_custom_class" class="swal2-input" style="width:100%;margin:0 0 12px 0;" placeholder="e.g. XI BD / XI RPL">
                    <label style="font-weight:700;display:block;margin-bottom:4px;">Bidang Keahlian:</label>
                    <input id="spa_swal_custom_field" class="swal2-input" style="width:100%;margin:0;" value="Bisnis Manajemen dan Teknologi">
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Terapkan & Simpan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const m = document.getElementById('spa_swal_custom_major').value.trim();
                const c = document.getElementById('spa_swal_custom_class').value.trim();
                const f = document.getElementById('spa_swal_custom_field').value.trim();
                if (!m || !c) {
                    Swal.showValidationMessage('Nama Jurusan dan Kelas wajib diisi!');
                    return false;
                }
                return { major: m, className: c, field: f || 'Bisnis Manajemen dan Teknologi' };
            }
        });

        if (formValues) {
            await saveSpaCover({
                major: formValues.major,
                class: formValues.className,
                field_of_expertise: formValues.field
            });
        }
        return;
    }

    const [major, className, field] = val.split('|');
    await saveSpaCover({
        major: major,
        class: className,
        field_of_expertise: field
    });
};

window.handleSpaYearSelection = async function(val) {
    if (!state.currentStudent) return;

    if (val === 'CUSTOM') {
        const { value: year } = await Swal.fire({
            title: 'Kustom Tahun Pelajaran / Angkatan',
            input: 'text',
            inputLabel: 'Masukkan Tahun Pelajaran (misal: 2027/2028):',
            inputPlaceholder: '2027/2028',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            inputValidator: (v) => {
                if (!v || !v.trim()) return 'Tahun pelajaran tidak boleh kosong!';
            }
        });

        if (year) {
            await saveSpaCover({ academic_year: year.trim() });
        }
        return;
    }

    await saveSpaCover({ academic_year: val });
};

async function saveSpaCover(payload) {
    if (!state.currentStudent) return;

    const majorSelect = document.getElementById('spaQuickMajorSelect');
    const yearSelect = document.getElementById('spaQuickYearSelect');

    if (!payload.major) {
        const curVal = (majorSelect ? majorSelect.value : '').split('|');
        payload.major = curVal[0] || state.currentStudent.major;
        payload.class = curVal[1] || state.currentStudent.class;
        payload.field_of_expertise = curVal[2] || state.currentStudent.field_of_expertise || 'Bisnis Manajemen dan Teknologi';
    }

    if (!payload.academic_year) {
        payload.academic_year = (yearSelect && yearSelect.value !== 'CUSTOM') ? yearSelect.value : (state.currentStudent.academic_year || '2026/2027');
    }

    try {
        const res = await fetch(`/api/v1/students/${state.currentStudent.id}/cover`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success) {
            Object.assign(state.currentStudent, data.data);
            renderCoverTab(state.currentStudent);
            notifySuccess(`Jurusan & Angkatan diperbarui: ${payload.major} (${payload.class})`);
        } else {
            notifyError('Gagal: ' + data.message);
        }
    } catch (err) {
        console.error(err);
        notifyError('Gagal menyimpan: ' + err.message);
    }
}

// Modal Kloning Siswa SPA
window.openSpaCloneModal = async function() {
    if (!state.currentStudent) return;

    const { value: cloneData } = await Swal.fire({
        title: 'Kloning untuk Angkatan / Jurusan Baru',
        html: `
            <div style="text-align:left;font-size:0.88rem;color:#334155;">
                <p style="margin-bottom:0.75rem;color:#64748b;">Duplikasi berkas lengkap (Cover, Identitas, Uraian, Presensi, Penilaian) ke siswa baru untuk angkatan / jurusan lain.</p>
                <label style="font-weight:700;display:block;margin-bottom:4px;">Nama Siswa Baru:</label>
                <input id="spa_clone_name" class="swal2-input" style="width:100%;margin:0 0 10px 0;" placeholder="e.g. Nama Siswa Angkatan Baru">
                
                <label style="font-weight:700;display:block;margin-bottom:4px;">Jurusan / Program Keahlian:</label>
                <select id="spa_clone_major" class="swal2-input" style="width:100%;margin:0 0 10px 0;height:42px;font-size:0.9rem;" onchange="syncSpaCloneClass(this.value)">
                    <option value="Manajemen Perkantoran 1|XI MP 1">Manajemen Perkantoran 1 (XI MP 1)</option>
                    <option value="Manajemen Perkantoran 2|XI MP 2">Manajemen Perkantoran 2 (XI MP 2)</option>
                    <option value="Bisnis Retail|XI BR">Bisnis Retail (XI BR)</option>
                    <option value="Bisnis Digital|XI BD">Bisnis Digital (XI BD)</option>
                    <option value="Rekayasa Perangkat Lunak|XI RPL">Rekayasa Perangkat Lunak (XI RPL)</option>
                </select>

                <label style="font-weight:700;display:block;margin-bottom:4px;">Kelas:</label>
                <input id="spa_clone_class" class="swal2-input" style="width:100%;margin:0 0 10px 0;" value="XI MP 1">

                <label style="font-weight:700;display:block;margin-bottom:4px;">Tahun Pelajaran (Angkatan):</label>
                <input id="spa_clone_year" class="swal2-input" style="width:100%;margin:0;" value="2027/2028">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Kloning Sekarang',
        cancelButtonText: 'Batal',
        didOpen: () => {
            window.syncSpaCloneClass = function(val) {
                const parts = val.split('|');
                document.getElementById('spa_clone_class').value = parts[1] || 'XI';
            };
        },
        preConfirm: () => {
            const name = document.getElementById('spa_clone_name').value.trim();
            const majorVal = document.getElementById('spa_clone_major').value.split('|')[0];
            const cls = document.getElementById('spa_clone_class').value.trim();
            const yr = document.getElementById('spa_clone_year').value.trim();
            if (!name) {
                Swal.showValidationMessage('Nama siswa baru wajib diisi!');
                return false;
            }
            return { name, major: majorVal, class: cls, academic_year: yr };
        }
    });

    if (cloneData) {
        Swal.fire({
            title: 'Sedang Mengkloning...',
            text: 'Membuat berkas lengkap dan lembar kegiatan siap pakai.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(`/api/v1/students/${state.currentStudent.id}/clone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(cloneData)
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Kloning Berhasil!',
                    text: data.message,
                    confirmButtonText: 'Buka Profil Siswa Baru'
                }).then(async () => {
                    state.activeStudentId = data.data.id;
                    await loadStudents();
                });
            } else {
                Swal.fire('Gagal Kloning', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Kesalahan Server', 'Gagal memproses kloning.', 'error');
        }
    }
};

window.openCloneGuideModal = function() {
    Swal.fire({
        title: '🚀 Panduan Kloning Web untuk Angkatan Lain',
        html: `
            <div style="text-align:left;font-size:0.86rem;color:#334155;line-height:1.6;">
                <p style="margin-bottom:0.75rem;">Web ini dirancang <strong>modular & siap kloning</strong> untuk dipakai oleh angkatan berikutnya maupun jurusan lain di SMK Negeri 20 Jakarta:</p>
                <ol style="padding-left:1.25rem;margin-bottom:1rem;">
                    <li style="margin-bottom:0.5rem;"><strong>Salin / Clone Folder Web:</strong>
                        <code style="display:block;background:#f1f5f9;padding:4px 8px;border-radius:4px;margin-top:2px;">git clone &lt;repo-url&gt; laporan-magang-angkatan-baru</code>
                    </li>
                    <li style="margin-bottom:0.5rem;"><strong>Install Dependensi:</strong>
                        <code style="display:block;background:#f1f5f9;padding:4px 8px;border-radius:4px;margin-top:2px;">composer install && npm install</code>
                    </li>
                    <li style="margin-bottom:0.5rem;"><strong>Konfigurasi Environment (.env):</strong>
                        <code style="display:block;background:#f1f5f9;padding:4px 8px;border-radius:4px;margin-top:2px;">copy .env.example .env && php artisan key:generate</code>
                    </li>
                    <li style="margin-bottom:0.5rem;"><strong>Hubungkan Database:</strong> Bisa menggunakan Supabase PostgreSQL atau SQLite lokal instan (<code style="background:#f1f5f9;padding:2px 4px;">DB_CONNECTION=sqlite</code>).</li>
                    <li style="margin-bottom:0.5rem;"><strong>Jalankan Web Server:</strong>
                        <code style="display:block;background:#f1f5f9;padding:4px 8px;border-radius:4px;margin-top:2px;">php artisan serve</code>
                    </li>
                </ol>
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:0.75rem;color:#065f46;font-size:0.82rem;">
                    💡 <strong>Tips:</strong> Anda bisa langsung mengganti Jurusan (MP 1, MP 2, BR, BD, RPL) dan Tahun Pelajaran dari tombol dropdown di Cover tanpa harus coding ulang!
                </div>
            </div>
        `,
        width: 600,
        confirmButtonText: 'Tutup Panduan'
    });
};
