<?php

namespace Tests\Feature;

use App\Models\Student;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanMagangTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_homepage_redirects_to_students(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/students');
    }

    public function test_students_index_displays_list(): void
    {
        $response = $this->get('/students');
        $response->assertStatus(200);
        $response->assertSee('Laporan Magang');
        $response->assertSee('Muhammad Rochimuloh');
    }

    public function test_student_show_displays_five_sheets(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Test Cover Tab
        $responseCover = $this->get("/students/{$student->id}?tab=cover");
        $responseCover->assertStatus(200);
        $responseCover->assertSee('LAPORAN');
        $responseCover->assertSee('KEGIATAN KELAS INDUSTRI');

        // Test Identitas Tab
        $responseIdentitas = $this->get("/students/{$student->id}?tab=identitas");
        $responseIdentitas->assertStatus(200);
        $responseIdentitas->assertSee('Identitas Peserta Magang');

        // Test Uraian Kegiatan Tab
        $responseUraian = $this->get("/students/{$student->id}?tab=uraian");
        $responseUraian->assertStatus(200);
        $responseUraian->assertSee('Uraian Kegiatan Magang');

        // Test Presensi Tab
        $responsePresensi = $this->get("/students/{$student->id}?tab=presensi");
        $responsePresensi->assertStatus(200);
        $responsePresensi->assertSee('Daftar Hadir Magang');

        // Test Penilaian Tab
        $responsePenilaian = $this->get("/students/{$student->id}?tab=penilaian");
        $responsePenilaian->assertStatus(200);
        $responsePenilaian->assertSee('Laporan Penilaian Magang DU/DI');
    }

    public function test_print_page_renders_cleanly(): void
    {
        $student = Student::first();
        $response = $this->get("/students/{$student->id}/print");
        $response->assertStatus(200);
        $response->assertSee('IDENTITAS PESERTA');
        $response->assertSee('URAIAN KEGIATAN MAGANG');
        $response->assertSee('DAFTAR HADIR MAGANG');
        $response->assertSee('LAPORAN PENILAIAN MAGANG');
    }

    public function test_add_activity_sheet_and_calculate_evaluation(): void
    {
        $student = Student::first();

        // Test tambah slide lembar kegiatan (+) langsung dari web tanpa hambatan 401
        $responseSheet = $this->post("/students/{$student->id}/activity-sheets", [
            'month_name' => 'November 2026',
        ]);
        $responseSheet->assertRedirect();

        $this->assertDatabaseHas('activity_sheets', [
            'student_id' => $student->id,
            'month_name' => 'November 2026',
        ]);

        // Test update penilaian dan otomatis hitung predikat secara aman
        $responseEval = $this->put("/students/{$student->id}/evaluation", [
            'aspect_a_score' => 95,
            'aspect_b_score' => 92,
            'aspect_c_score' => 96,
            'evaluation_date' => 'Jakarta, 31 Oktober 2026',
            'mentor_name' => 'Pembimbing Lapangan',
        ]);
        $responseEval->assertRedirect();

        $this->assertDatabaseHas('evaluations', [
            'student_id' => $student->id,
            'aspect_a_grade' => 'A',
            'final_grade' => 'A',
        ]);
    }
}
