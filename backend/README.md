# Backend Service & WAF Architecture &bull; Laporan Magang

Backend ini dibangun menggunakan **Laravel 13 (PHP 8.3.36)** dengan database **Supabase (PostgreSQL)**, dilindungi oleh lapisan **Web Application Firewall (WAF)** kustom berkinerja tinggi.

---

## 🛡️ Web Application Firewall (WAF) Specifications

File: `app/Http/Middleware/WebProtectiveFirewall.php`

WAF secara otomatis menginspeksi semua request HTTP sebelum mencapai controller aplikasi:

| Modul Ancaman | Pola yang Didisteksi | Tindakan |
|---|---|---|
| **SQL Injection (SQLi)** | `UNION SELECT`, `OR 1=1`, `sleep()`, `benchmark()`, `information_schema`, dll. | Blokir 403 Forbidden + Catat Log |
| **Cross-Site Scripting (XSS)** | `<script>`, `javascript:`, `onerror=`, `onload=`, `alert()`, `document.cookie` | Blokir 403 Forbidden + Catat Log |
| **Path Traversal / LFI** | `../`, `..\`, `%2e%2e`, `/etc/passwd`, `cmd.exe`, `powershell.exe` | Blokir 403 Forbidden + Catat Log |
| **Remote Code Execution (RCE)** | `eval()`, `base64_decode()`, `system()`, `passthru()`, `shell_exec()` | Blokir 403 Forbidden + Catat Log |
| **Bot & Scanner Defense** | `sqlmap`, `nikto`, `wpscan`, `acunetix`, `masscan`, `dirbuster`, `nmap` | Blokir 403 Forbidden + Catat Log |

Setiap insiden blokir disimpan di tabel `waf_logs` dan dapat dipantau secara real-time melalui endpoint:
`GET /api/v1/waf/status`

---

## 📡 REST API Endpoints (v1)

Semua endpoint mengembalikan respons terstandarisasi JSON:

### 1. Siswa (1 s/d 5 Siswa)
- `GET    /api/v1/students` : Mengambil daftar seluruh siswa & rekap kelompok
- `POST   /api/v1/students` : Menambah siswa baru (maksimal 5 siswa)
- `GET    /api/v1/students/{id}` : Mengambil data detail lengkap 5 lembar siswa
- `POST   /api/v1/students/{id}` : Update identitas & upload pas foto siswa
- `DELETE /api/v1/students/{id}` : Menghapus data siswa

### 2. Uraian Kegiatan Magang (Slide System)
- `POST   /api/v1/students/{studentId}/activity-sheets` : Tambah slide lembar kegiatan baru (`+`)
- `PUT    /api/v1/students/{studentId}/activity-sheets/{sheetId}` : Simpan 10 baris kegiatan
- `DELETE /api/v1/students/{studentId}/activity-sheets/{sheetId}` : Hapus slide lembar kegiatan

### 3. Presensi & Daftar Hadir
- `POST   /api/v1/students/{studentId}/attendance-sheets` : Tambah bulan presensi baru (`+`)
- `PUT    /api/v1/students/{studentId}/attendance-sheets/{sheetId}` : Simpan presensi & hitung ulang rekapitulasi otomatis
- `DELETE /api/v1/students/{studentId}/attendance-sheets/{sheetId}` : Hapus bulan presensi

### 4. Penilaian Magang DU/DI
- `PUT    /api/v1/students/{studentId}/evaluation` : Simpan nilai 3 aspek & hitung otomatis predikat (A/B/C/D)

### 5. Keamanan WAF
- `GET    /api/v1/waf/status` : Telemetri metrik proteksi dan riwayat ancaman yang diblokir
