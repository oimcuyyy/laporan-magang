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
        $response->assertSee('Nama Siswa / Nama Anda');
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

    public function test_legitimate_web_user_can_edit_and_save_data_without_401(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Form edit penilaian dapat disimpan langsung secara normal
        $responseEval = $this->put("/students/{$student->id}/evaluation", [
            'aspect_a_score' => 96,
            'aspect_b_score' => 94,
            'aspect_c_score' => 98,
            'evaluation_date' => 'Jakarta, 30 November 2026',
            'mentor_name' => 'Pembimbing Industri',
        ]);

        $responseEval->assertRedirect();
        $this->assertDatabaseHas('evaluations', [
            'student_id' => $student->id,
            'aspect_a_score' => 96,
            'average_score' => 96.0,
            'final_grade' => 'A',
        ]);
    }

    public function test_data_manipulation_with_invalid_scores_is_blocked_by_validation(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Mencoba memanipulasi skor di luar batas wajar (misal: skor 999 atau negatif)
        $responseInvalid = $this->from("/students/{$student->id}?tab=penilaian")
            ->put("/students/{$student->id}/evaluation", [
                'aspect_a_score' => 999,
                'aspect_b_score' => -10,
                'aspect_c_score' => 80,
                'evaluation_date' => 'Jakarta, 30 November 2026',
            ]);

        $responseInvalid->assertSessionHasErrors(['aspect_a_score', 'aspect_b_score']);
    }

    public function test_tampering_url_query_tab_throws_401_with_warning(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Mengubah parameter tab di link URL menjadi tab palsu
        $response = $this->get("/students/{$student->id}?tab=hacker_link");

        $response->assertStatus(401);
        $response->assertSee('Jangan mengubah-ubah di web ini', false);
    }

    public function test_tampering_url_query_with_unexpected_param_throws_401_with_warning(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);

        // Mengubah link dengan menyisipkan parameter query liar
        $response = $this->get("/students/{$student->id}?admin=true");

        $response->assertStatus(401);
        $response->assertSee('Jangan mengubah-ubah di web ini', false);
    }

    public function test_tampering_url_student_id_to_non_existent_throws_401_with_warning(): void
    {
        // Mengubah link ID siswa ke ID yang tidak ada
        $response = $this->get('/students/9999');

        $response->assertStatus(401);
        $response->assertSee('Jangan mengubah-ubah di web ini', false);
    }

    public function test_tampering_url_to_random_unregistered_path_throws_401_with_warning(): void
    {
        // Mengubah link URL ke alamat acak
        $response = $this->get('/hacked-admin-panel');

        $response->assertStatus(401);
        $response->assertSee('Jangan mengubah-ubah di web ini', false);
    }

    public function test_cross_student_sheet_manipulation_is_rejected_with_401_and_warning(): void
    {
        $students = Student::take(2)->get();
        if ($students->count() < 2) {
            $student2 = Student::create([
                'name' => 'Siswa Kedua',
                'nis' => '14451',
                'class' => 'XI RPL',
                'major' => 'RPL',
                'gender' => 'Laki-Laki',
                'school_name' => 'SMKN 20',
                'company_name' => 'Kantor',
                'teacher_name' => 'Guru',
            ]);
        } else {
            $student2 = $students[1];
        }

        $student1 = $students[0];
        $sheetOfStudent1 = $student1->activitySheets()->first();
        $this->assertNotNull($sheetOfStudent1);

        // Mencoba mengupdate lembar milik Siswa 1 lewat rute Siswa 2 (IDOR / Manipulasi URL)
        $responseAttack = $this->put("/students/{$student2->id}/activity-sheets/{$sheetOfStudent1->id}", [
            'month_name' => 'Manipulated Month',
            'logs' => [],
        ]);

        $responseAttack->assertStatus(401);
        $responseAttack->assertSee('Jangan mengubah-ubah di web ini', false);
    }

    public function test_tampered_url_endpoint_returns_401_with_tampered_details(): void
    {
        $response = $this->get('/tampered-url?url=' . urlencode('http://localhost/students/1?tab=uraian#page-3-uraian/lock#wkwk') . '&detail=' . urlencode('Bagian akhir tautan link (#page-3-uraian/lock#wkwk) dimanipulasi'));

        $response->assertStatus(401);
        $response->assertSee('401 Akses Ditolak');
        $response->assertSee('Jangan mengubah-ubah di web ini', false);
        $response->assertSee('Bagian akhir tautan link (#page-3-uraian/lock#wkwk) dimanipulasi');
    }
}
