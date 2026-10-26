# Web Aplikasi Laporan Magang &bull; SMK Negeri 20 Jakarta

Aplikasi web modern berbasis **Laravel 13** dan database **Supabase (PostgreSQL)** yang mendigitalkan seluruh berkas fisik rekapitulasi laporan kegiatan magang / PKL (Praktek Kerja Lapangan) kelas industri SMK Negeri 20 Jakarta.

Dibuat dengan mematuhi standar desain ketat (*UI Craft*, *Anti-Slop*, *Responsive Mobile*, dan *Security Hardening*), mendukung **1 sampai 5 siswa** dalam satu kelompok, penambahan slide uraian kegiatan dinamis per 10 baris, penambahan bulan presensi, fleksibilitas magang Sabtu/Minggu, kalkulasi rekapitulasi otomatis, upload pas foto siswa aman, serta mode cetak fisik A4 yang 100% presisi dengan berkas kertas aslinya.

---

## 5 Lembar Berkas Sesuai Dokumen Fisik Asli

1. **Lembar 1: Cover Laporan Kegiatan Kelas Industri**
   - Format judul resmi: *"LAPORAN KEGIATAN KELAS INDUSTRI - SMK NEGERI 20 JAKARTA"*
   - Bidang Keahlian: *"Bisnis Manajemen dan Teknologi"*
   - Dilengkapi grafis sudut geometris biru modern serta Logo Pemprov DKI Jakarta (Jaya Raya) dan Logo SMK Negeri 20 Jakarta beresolusi tinggi.
   - Pemilih Jurusan fleksibel: **RPL** (Rekayasa Perangkat Lunak), **MP 1 & MP 2** (Manajemen Perkantoran), **BR** (Bisnis Retail), **BD** (Bisnis Digital), atau Kustom.

2. **Lembar 2: Identitas Peserta Magang**
   - Data Pribadi Siswa: Nama, NIS, NISN, Tempat Tanggal Lahir, Jenis Kelamin, Agama, Alamat, No. HP.
   - Data Orang Tua: Nama Ayah, No. HP, Nama Ibu, No. HP, Alamat Orang Tua.
   - Data Sekolah: SMK Negeri 20 Jakarta, Alamat Sekolah.
   - Data Tempat Praktik (DU/DI): Nama Tempat (misal: Kantor Lurah Bintaro), Alamat Instansi.
   - Data Guru Pembimbing: Nama Guru (Kuswanto), No. HP.
   - Kotak Pas Foto 3x4 dengan fitur unggah aman, preview instan, dan penyimpanan storage.
   - Kolom tanda tangan pengesahan peserta.

3. **Lembar 3: Uraian Kegiatan Magang (Jurnal / Logbook Harian)**
   - Format standar 10 baris kegiatan per lembar/slide (No, Hari/Tanggal, Divisi/Bagian, Uraian Pekerjaan, Pembimbing Industri).
   - **Fitur Slide Dinamis & Tombol Plus (`+`)**: Ketika uraian kegiatan telah mencapai 10 baris, pengguna cukup menekan tombol **Tambah Slide Lembar Baru (+)** untuk melanjutkan ke lembar berikutnya tanpa batasan baris.

4. **Lembar 4: Daftar Hadir Magang (Presensi Bulanan)**
   - Tabel kehadiran harian dengan status:
     * **H** : Hadir
     * **S** : Sakit
     * **I** : Izin
     * **A** : Alpa
   - **Fleksibilitas Hari Magang**: Mendukung penambahan hari kerja mandiri (termasuk Sabtu dan Minggu bagi siswa yang magang di akhir pekan) dengan tombol **Tambah Hari (+)** secara asinkron (AJAX).
   - **Rekapitulasi Otomatis**: Menghitung secara real-time Jumlah Seharusnya Hadir, Jumlah Tidak Hadir (S, I, A), dan Total Hadir.
   - **Fitur Tombol Plus (`+`)**: Tombol untuk menambah lembar bulan presensi baru (misal: November 2026, Desember 2026).

5. **Lembar 5: Laporan Penilaian Magang DU/DI**
   - Penilaian oleh Pembimbing Dunia Usaha & Industri (DU/DI) mencakup 3 aspek:
     * **A**: Menerapkan Softskill yang dibutuhkan dunia kerja.
     * **B**: Menerapkan norma, POS, dan Kesehatan Keselamatan Kerja (K3).
     * **C**: Menerapkan kompetensi teknis yang dipelajari sesuai konsentrasi keahlian.
   - **Kalkulasi Otomatis Predikat**:
     * Nilai 91 - 100 : **A (Sangat Baik)**
     * Nilai 81 - 90  : **B (Baik)**
     * Nilai 75 - 80  : **C (Cukup Baik)**
     * Nilai < 75     : **D (Kurang)**
   - Menghitung rata-rata nilai akhir dan predikat gabungan secara otomatis.
   - Kolom tanda tangan resmi Pembimbing Industri.

---

## Panduan Kloning & Pemasangan Web untuk Angkatan / Jurusan Lain

Aplikasi ini dibangun secara modular sehingga sangat mudah di-clone dan digunakan kembali untuk angkatan tahun pelajaran baru atau kelompok jurusan lain.

### 1. Salin / Clone Repositori dari GitHub

```bash
git clone https://github.com/<username>/<repo-name>.git laporan-magang
cd laporan-magang
```

### 2. Pasang Dependensi PHP (Composer)

```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)

Salin file contoh konfigurasi dan buat application key:

```bash
# Di Windows Command Prompt / PowerShell:
copy .env.example .env

# Di Linux / macOS:
cp .env.example .env

# Generate Application Key:
php artisan key:generate
```

### 4. Konfigurasi Database (Pilih Salah Satu)

#### Opsi A: Menggunakan SQLite Lokal (Instan & Tanpa Setup)
Ubah baris database di `.env`:
```env
DB_CONNECTION=sqlite
```
Lalu jalankan migrasi:
```bash
php artisan migrate --seed
```

#### Opsi B: Menggunakan Database Cloud Supabase (PostgreSQL)
Konfigurasikan kredensial Supabase Anda di `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com # Host database Supabase Anda
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.your_project_id
DB_PASSWORD=your_supabase_password
```
Jalankan skrip migrasi atau gunakan skema SQL yang telah disediakan pada `database/supabase_schema.sql`.

### 5. Jalankan Web Server Lokal

Untuk performa responsif dan menghindari koneksi terhambat, jalankan server dengan multi-worker:

```bash
php artisan serve
```

Buka peramban di: **http://127.0.0.1:8000**

---

## Fitur Desain & Tampilan Responsif Mobile (HP)

- **Mobile First Adaptation**: Tampilan formulir otomatis bertransformasi menjadi 1 kolom yang nyaman di layar smartphone (360px - 768px).
- **Sticky Pill Bar**: Panel navigasi 5 lembar ala Microsoft Word di laptop berubah menjadi *horizontal scrollable pill bar* melayang di HP, sehingga pengguna dapat berpindah lembar dokumen dengan cepat hanya menggunakan jempol tanpa scroll panjang.
- **Tabel Horisontal Mandiri**: Tabel jurnal uraian kegiatan dan presensi bulanan dibungkus wadah *touch-scroll* mandiri tanpa merusak lebar layar ponsel.
- **Bebas Emoji (*Zero Emoji Design*)**: Seluruh ikon menggunakan vektor SVG resmi beresolusi tinggi (Lucide & Tabler standard).

---

## Standar Keamanan & Proteksi API

1. **Autentikasi Endpoint API**: Seluruh endpoint data `/api/v1/*` dilindungi middleware `api.auth` menggunakan header `X-API-KEY`.
2. **Sanitasi Health Check**: Endpoint `/health` dan `/api/v1/health` hanya mengembalikan status `UP` atau `DOWN` tanpa membocorkan kredensial database, port, atau stack trace PDO (*Information Disclosure Prevention*).
3. **Web Application Firewall (WAF)**: Middleware `WebProtectiveFirewall` aktif memfilter serangan XSS, SQL Injection, dan Path Traversal.
4. **SweetAlert2 Feedback**: Seluruh form submit, konfirmasi penghapusan data, dan notifikasi sukses terintegrasi dengan dialog modal SweetAlert2.

---

## Panduan Mencetak Berkas Fisik A4

1. Klik tombol **Cetak 5 Lembar A4** pada navigasi berkas siswa atau tombol **Cetak Cover A4** pada halaman sampul.
2. Jendela preview cetak akan terbuka dengan tata letak berkas A4 yang presisi.
3. Tekan `Ctrl + P` (atau `Cmd + P` di Mac).
4. Pada jendela cetak peramban:
   - **Ukuran Kertas**: A4
   - **Margin**: Default atau None
   - **Opsi**: Centang *Background graphics* (Grafik latar belakang) agar warna tabel dan aksen resmi tercetak sempurna.

---

&copy; {{ date('Y') }} **SMK Negeri 20 Jakarta &bull; Program Magang Kelas Industri**
