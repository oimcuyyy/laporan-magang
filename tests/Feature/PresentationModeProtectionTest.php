<?php

namespace Tests\Feature;

use App\Models\Student;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentationModeProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_visitor_can_view_students_list_without_login(): void
    {
        $response = $this->get('/students');

        $response->assertStatus(200);
        $response->assertSee('Laporan Magang');
        $response->assertSee('Muhammad Rochimuloh');
    }

    public function test_public_visitor_can_view_student_details_and_print_without_login(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        $responseShow = $this->get("/students/{$student->id}?tab=cover");
        $responseShow->assertStatus(200);
        $responseShow->assertSee('LAPORAN');

        $responsePrint = $this->get("/students/{$student->id}/print");
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('IDENTITAS PESERTA');
    }

    public function test_unauthorized_data_mutation_is_immediately_blocked_with_401(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // 1. Coba hapus siswa tanpa otorisasi presenter -> Wajib 401
        $responseDelete = $this->delete("/students/{$student->id}");
        $responseDelete->assertStatus(401);

        // 2. Coba ubah nilai evaluasi tanpa otorisasi presenter -> Wajib 401
        $responseEval = $this->put("/students/{$student->id}/evaluation", [
            'aspect_a_score' => 100,
            'aspect_b_score' => 100,
            'aspect_c_score' => 100,
            'evaluation_date' => 'Jakarta, 31 Oktober 2026',
        ]);
        $responseEval->assertStatus(401);

        // 3. Coba tambah siswa baru tanpa otorisasi presenter -> Wajib 401
        $responseStore = $this->post('/students', [
            'name' => 'Hacker Tamu',
            'nis' => '99999',
            'class' => 'XII',
            'major' => 'RPL',
            'gender' => 'Laki-Laki',
            'school_name' => 'SMKN 20',
            'company_name' => 'Kantor',
            'teacher_name' => 'Guru',
        ]);
        $responseStore->assertStatus(401);
    }

    public function test_authorized_presenter_can_mutate_data_successfully(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);
        $key = config('services.api.key') ?: env('API_SECRET_KEY');

        $response = $this->withHeader('X-PRESENTER-KEY', $key)->put("/students/{$student->id}/evaluation", [
            'aspect_a_score' => 96,
            'aspect_b_score' => 94,
            'aspect_c_score' => 98,
            'evaluation_date' => 'Jakarta, 31 Oktober 2026',
            'mentor_name' => 'Pembimbing Presentasi',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluations', [
            'student_id' => $student->id,
            'aspect_a_score' => 96,
        ]);
    }

    public function test_secret_url_unlock_enables_session_mutations(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);
        $key = config('services.api.key') ?: env('API_SECRET_KEY');

        // Buka kunci via parameter ?unlock=
        $responseUnlock = $this->get('/?unlock='.$key);
        $responseUnlock->assertStatus(302);

        // Setelah unlock tersimpan di sesi browser presenter, mutasi diizinkan
        $responseEval = $this->withSession(['presenter_unlocked' => true])
            ->put("/students/{$student->id}/evaluation", [
                'aspect_a_score' => 97,
                'aspect_b_score' => 95,
                'aspect_c_score' => 99,
                'evaluation_date' => 'Jakarta, 31 Oktober 2026',
                'mentor_name' => 'Pembimbing Lapangan',
            ]);

        $responseEval->assertRedirect();
        $this->assertDatabaseHas('evaluations', [
            'student_id' => $student->id,
            'aspect_a_score' => 97,
        ]);
    }
}
