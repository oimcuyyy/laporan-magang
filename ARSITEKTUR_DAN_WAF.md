# Arsitektur Terstruktur: Frontend, Backend & WAF (Laporan Magang)

Sistem **Laporan Magang SMK Negeri 20 Jakarta** telah dirancang dengan arsitektur terstruktur dan terpisah secara tegas (*Separation of Concerns*):

```
laporan-magang/
├── backend/                       # ARSITEKTUR BACKEND & REST API
│   ├── README.md                  # Dokumentasi Lengkap API & WAF
│   ├── app/Http/Controllers/Api/  # REST API Controllers (JSON Output)
│   │   ├── StudentApiController.php
│   │   ├── ActivitySheetApiController.php
│   │   ├── AttendanceSheetApiController.php
│   │   ├── EvaluationApiController.php
│   │   └── WafApiController.php
│   ├── app/Http/Middleware/       # Web Application Firewall (WAF)
│   │   └── WebProtectiveFirewall.php
│   ├── app/Models/                # Eloquent Models & Business Logic
│   └── routes/api.php             # Routing REST API v1
│
├── frontend/                      # ARSITEKTUR FRONTEND TERPISAH
│   ├── index.html                 # Single-Page Workspace Interface
│   ├── css/
│   │   └── style.css              # Design System & Styling
│   └── js/
│       ├── api.js                 # HTTP Client Fetch API ke Backend
│       └── app.js                 # State Management, Carousel Slide, & Auto-Recap
│
├── database/                      # SKEMA DATABASE
│   ├── supabase_schema.sql        # Skema SQL Supabase PostgreSQL (+ RLS & Index)
│   └── migrations/                # Database Migrations (Laravel 13)
```

---

## 🛡️ Web Application Firewall (WAF) Terintegrasi

Aplikasi dilengkapi modul firewall aplikasi web (*custom WAF*) di `app/Http/Middleware/WebProtectiveFirewall.php`:
1. **Inspeksi Berlapis**: Menguji parameter URL query, body data, request path, dan user-agent terhadap pola serangan siber.
2. **Aturan Proteksi**:
   - **SQL Injection (SQLi)**: Memblokir injeksi string, `UNION SELECT`, `OR 1=1`, blind boolean SQLi, dll.
   - **Cross-Site Scripting (XSS)**: Memblokir tag `<script>`, payload `javascript:`, event handler `onerror=`, `onload=`, pencurian cookie.
   - **Path Traversal / LFI**: Memblokir `../`, `..\`, `%2e%2e`, akses ke file sistem Windows/Linux.
   - **Remote Code Execution (RCE)**: Memblokir fungsi berbahaya PHP (`eval()`, `base64_decode()`, `system()`, `shell_exec()`).
   - **Bot & Scanner Defense**: Memblokir scanner otomatis (`sqlmap`, `nikto`, `wpscan`, `acunetix`, `nmap`).
3. **Respon Blokir 403 Forbidden**: Setiap serangan langsung dihentikan dan dicatat ke tabel `waf_logs`.
4. **Monitoring Real-Time**: Status dan telemetri serangan WAF dapat dipantau langsung via endpoint `/api/v1/waf/status` dan widget perisai di navbar frontend.

---

## 🌐 Akses Aplikasi

- **Frontend Terstruktur (SPA)**: `http://localhost:8000/spa`
- **Dashboard Rekap & Berkas Fisik**: `http://localhost:8000/students`
- **Mode Cetak Berkas Fisik A4**: `http://localhost:8000/students/1/print`
- **REST API Telemetri WAF**: `http://localhost:8000/api/v1/waf/status`
