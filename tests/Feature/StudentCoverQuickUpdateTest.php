<?php

namespace Tests\Feature;

use App\Models\Student;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCoverQuickUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_quick_update_cover_via_web_route_returns_json_response_successfully(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);
        $key = config('services.api.key') ?: env('API_SECRET_KEY');

        $response = $this->withHeader('X-PRESENTER-KEY', $key)->patchJson(route('students.quick-cover', $student->id), [
            'major' => 'Bisnis Retail',
            'class' => 'XI BR',
            'academic_year' => '2026/2027',
            'field_of_expertise' => 'Bisnis Manajemen dan Teknologi',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Jurusan, Kelas, dan Tahun Pelajaran berhasil diperbarui!',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'major' => 'Bisnis Retail',
            'class' => 'XI BR',
        ]);
    }

    public function test_clone_student_via_web_route_succeeds(): void
    {
        $student = Student::first();
        $this->assertNotNull($student);
        $key = config('services.api.key') ?: env('API_SECRET_KEY');

        $response = $this->withHeader('X-PRESENTER-KEY', $key)->postJson(route('students.clone', $student->id), [
            'major' => 'Bisnis Digital',
            'class' => 'XI BD',
            'academic_year' => '2027/2028',
            'name' => 'Siswa Kloning Baru',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('students', [
            'major' => 'Bisnis Digital',
            'class' => 'XI BD',
            'academic_year' => '2027/2028',
        ]);
    }
}
