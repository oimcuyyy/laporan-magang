<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TestSupabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supabase:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uji koneksi ke Supabase Cloud (REST API & PostgreSQL Connection)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info("   PENGUJIAN KONEKSI SUPABASE LAPORAN MAGANG   ");
        $this->info("=================================================");

        $url = env('SUPABASE_URL', 'https://mpbeyneczxznhsaqnwxg.supabase.co');
        $key = env('SUPABASE_KEY', 'sb_publishable_RwCdV78MsUPVxVnJwG6tUw_nXtx0wPc');
        $host = env('DB_HOST', 'aws-0-ap-northeast-2.pooler.supabase.com');
        $port = env('DB_PORT', '6543');
        $user = env('DB_USERNAME', 'postgres.mpbeyneczxznhsaqnwxg');
        $pass = env('DB_PASSWORD', '');

        // 1. Uji REST API Supabase
        $this->line("\n[1/2] Menguji Supabase REST API Endpoint...");
        try {
            $response = Http::withHeaders([
                'apikey' => $key,
                'Authorization' => "Bearer {$key}",
            ])->timeout(15)->get("{$url}/rest/v1/students?select=*");

            if ($response->successful()) {
                $this->info("   [OK] REST API Terhubung & Tabel 'students' aktif di Supabase!");
                $data = $response->json();
                $this->line("   Jumlah data siswa di Supabase: " . count($data));
            } else {
                $status = $response->status();
                $body = $response->json();
                if (isset($body['code']) && $body['code'] === 'PGRST205') {
                    $this->warn("   [INFO] Otentikasi Supabase Sukses, namun tabel database belum dibuat.");
                    $this->line("   Silakan buka SQL Editor Supabase dan jalankan isi file: database/supabase_schema.sql");
                } else {
                    $this->warn("   [HTTP {$status}] Respons: " . ($body['message'] ?? 'Tidak diketahui'));
                }
            }
        } catch (\Exception $e) {
            $this->error("   [GAGAL] Gagal menghubungi REST API: " . $e->getMessage());
        }

        // 2. Uji Direct PostgreSQL Connection (PDO)
        $this->line("\n[2/2] Menguji Direct PostgreSQL Pooler Connection...");
        $this->line("   Host: {$host}:{$port}");
        $this->line("   User: {$user}");

        if (empty($pass) || str_contains($pass, 'MASUKKAN_PASSWORD') || str_contains($pass, 'YOUR_PASSWORD')) {
            $this->warn("   [PERHATIAN] Password database masih berupa placeholder di file .env");
            $this->line("   Silakan koreksi DB_PASSWORD di .env dengan password database Supabase Anda.");
            return 0;
        }

        try {
            $dsn = "pgsql:host={$host};port={$port};dbname=postgres;sslmode=require";
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $this->info("   [SUKSES] Berhasil terhubung langsung ke PostgreSQL Supabase via PDO!");

            $stmt = $pdo->query("SELECT version()");
            $version = $stmt->fetchColumn();
            $this->line("   Server Version: " . substr($version, 0, 45) . "...");
            
            $this->info("\n[OK] Database Supabase Anda telah 100% siap digunakan!");
        } catch (\Exception $e) {
            $this->error("   [GAGAL KONEKSI PDO] " . $e->getMessage());
            $this->line("   Tips: Periksa kembali DB_PASSWORD Anda di file .env atau pastikan port (6543/5432) dapat diakses.");
        }

        return 0;
    }
}
