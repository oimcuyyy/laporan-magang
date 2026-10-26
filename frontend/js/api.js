/**
 * Frontend API Service &bull; Laporan Magang
 * Terkoneksi secara bersih ke backend Laravel REST API & WAF
 */

const API_BASE = '/api/v1';

function getAuthHeaders(custom = {}) {
    const key = localStorage.getItem('api_key') || window.APP_API_KEY || 'smkn20-magang-api-key-2026';
    const headers = { 'Accept': 'application/json', ...custom };
    if (key) {
        headers['X-API-KEY'] = key;
    }
    return headers;
}

export const ApiService = {
    // 1. Siswa
    async getStudents() {
        const res = await fetch(`${API_BASE}/students`, {
            headers: getAuthHeaders()
        });
        return res.json();
    },

    async getStudent(id) {
        const res = await fetch(`${API_BASE}/students/${id}`, {
            headers: getAuthHeaders()
        });
        return res.json();
    },

    async createStudent(formData) {
        const res = await fetch(`${API_BASE}/students`, {
            method: 'POST',
            body: formData,
            headers: getAuthHeaders()
        });
        return res.json();
    },

    async updateStudent(id, formData) {
        const res = await fetch(`${API_BASE}/students/${id}`, {
            method: 'POST',
            body: formData,
            headers: getAuthHeaders()
        });
        return res.json();
    },

    async deleteStudent(id) {
        const res = await fetch(`${API_BASE}/students/${id}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });
        return res.json();
    },

    // 2. Uraian Kegiatan (Slide & 10 Baris)
    async addActivitySheet(studentId, monthName) {
        const res = await fetch(`${API_BASE}/students/${studentId}/activity-sheets`, {
            method: 'POST',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ month_name: monthName })
        });
        return res.json();
    },

    async updateActivitySheet(studentId, sheetId, payload) {
        const res = await fetch(`${API_BASE}/students/${studentId}/activity-sheets/${sheetId}`, {
            method: 'PUT',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(payload)
        });
        return res.json();
    },

    async deleteActivitySheet(studentId, sheetId) {
        const res = await fetch(`${API_BASE}/students/${studentId}/activity-sheets/${sheetId}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });
        return res.json();
    },

    // 3. Presensi & Daftar Hadir
    async addAttendanceSheet(studentId, monthName, daysCount) {
        const res = await fetch(`${API_BASE}/students/${studentId}/attendance-sheets`, {
            method: 'POST',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ month_name: monthName, days_count: daysCount })
        });
        return res.json();
    },

    async updateAttendanceSheet(studentId, sheetId, payload) {
        const res = await fetch(`${API_BASE}/students/${studentId}/attendance-sheets/${sheetId}`, {
            method: 'PUT',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(payload)
        });
        return res.json();
    },

    async deleteAttendanceSheet(studentId, sheetId) {
        const res = await fetch(`${API_BASE}/students/${studentId}/attendance-sheets/${sheetId}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });
        return res.json();
    },

    // 4. Penilaian DU/DI
    async updateEvaluation(studentId, payload) {
        const res = await fetch(`${API_BASE}/students/${studentId}/evaluation`, {
            method: 'PUT',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(payload)
        });
        return res.json();
    },

    // 5. WAF Security Status
    async getWafStatus() {
        const res = await fetch(`${API_BASE}/waf/status`, {
            headers: getAuthHeaders()
        });
        return res.json();
    }
};
