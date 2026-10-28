<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParafUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function createSampleStudent(): Student
    {
        $student = Student::create([
            'name' => 'Ahmad Faiz',
            'nis' => '14451',
            'nisn' => '0081234567',
            'class' => 'XI RPL 1',
            'major' => 'Rekayasa Perangkat Lunak',
            'school_name' => 'SMK Negeri 20 Jakarta',
            'company_name' => 'PT Transisi Digital',
            'teacher_name' => 'Kuswanto, S.Pd',
            'mentor_name' => 'Sujatmo, S.Sos',
            'gender' => 'Laki-Laki',
        ]);

        $sheet = $student->activitySheets()->create([
            'sheet_number' => 1,
            'month_name' => 'Oktober 2026',
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $sheet->activityLogs()->create([
                'row_index' => $i,
                'day_date' => "Hari ke-{$i}",
                'division' => 'Pengembangan Web',
                'job_description' => 'Membuat fitur upload paraf',
            ]);
        }

        $attSheet = $student->attendanceSheets()->create([
            'month_name' => 'Oktober 2026',
            'total_target_days' => 5,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $attSheet->records()->create([
                'day_number' => $i,
                'day_date' => "2026-10-0{$i}",
                'status' => 'H',
            ]);
        }
        $attSheet->recalculateTotals();

        $student->evaluation()->create([
            'evaluation_date' => 'Jakarta, 30 November 2026',
            'mentor_name' => 'Sujatmo, S.Sos',
        ]);

        return $student;
    }

    public function test_activity_log_paraf_can_be_saved_and_rendered_on_print(): void
    {
        $student = $this->createSampleStudent();
        $sheet = $student->activitySheets()->first();
        $log1 = $sheet->activityLogs()->where('row_index', 1)->first();

        // 1x1 transparent png data url
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'month_name' => 'Oktober 2026',
            'logs' => [
                [
                    'id' => $log1->id,
                    'day_date' => 'Senin, 05-10-2026',
                    'division' => 'IT Software',
                    'job_description' => 'Implementasi upload foto paraf transparan',
                    'supervisor_note' => 'Bagus',
                    'is_approved' => 1,
                    'paraf_data' => $fakeBase64,
                ],
            ],
        ];

        $response = $this->put(route('activity-sheets.update', [$student->id, $sheet->id]), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('students.show', ['student' => $student->id, 'tab' => 'uraian', 'sheet' => $sheet->id]));

        $log1->refresh();
        $this->assertNotNull($log1->paraf_path);
        $this->assertNotNull($log1->paraf_url);

        // Verify print view renders the paraf image with seamless print classes
        $printRes = $this->get(route('students.print', $student->id));
        $printRes->assertStatus(200);
        $printRes->assertSee('print-paraf-img');
        $printRes->assertSee($log1->paraf_url, false);
    }

    public function test_activity_log_paraf_can_be_removed(): void
    {
        $student = $this->createSampleStudent();
        $sheet = $student->activitySheets()->first();
        $log1 = $sheet->activityLogs()->where('row_index', 1)->first();

        $log1->update(['paraf_path' => 'data:image/png;base64,fake-data']);
        $this->assertNotNull($log1->paraf_path);

        $payload = [
            'month_name' => 'Oktober 2026',
            'logs' => [
                [
                    'id' => $log1->id,
                    'day_date' => 'Senin, 05-10-2026',
                    'division' => 'IT Software',
                    'job_description' => 'Hapus paraf',
                    'supervisor_note' => null,
                    'is_approved' => 0,
                    'remove_paraf' => 1,
                ],
            ],
        ];

        $response = $this->put(route('activity-sheets.update', [$student->id, $sheet->id]), $payload);
        $response->assertSessionHasNoErrors();

        $log1->refresh();
        $this->assertNull($log1->paraf_path);
    }

    public function test_attendance_record_paraf_can_be_saved_and_rendered(): void
    {
        $student = $this->createSampleStudent();
        $attSheet = $student->attendanceSheets()->first();
        $rec1 = $attSheet->records()->where('day_number', 1)->first();

        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'month_name' => 'Oktober 2026',
            'records' => [
                [
                    'id' => $rec1->id,
                    'day_date' => 'Senin, 05-10-2026',
                    'status' => 'H',
                    'is_signed' => 1,
                    'paraf_data' => $fakeBase64,
                ],
            ],
        ];

        $response = $this->put(route('attendance-sheets.update', [$student->id, $attSheet->id]), $payload);
        $response->assertSessionHasNoErrors();

        $rec1->refresh();
        $this->assertNotNull($rec1->paraf_path);
        $this->assertNotNull($rec1->paraf_url);

        // Verify print view renders paraf
        $printRes = $this->get(route('students.print', $student->id));
        $printRes->assertStatus(200);
        $printRes->assertSee($rec1->paraf_url, false);
    }

    public function test_student_mentor_signature_can_be_uploaded_and_rendered_on_print(): void
    {
        $student = $this->createSampleStudent();
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $payload = [
            'name' => $student->name,
            'nis' => $student->nis,
            'school_name' => $student->school_name,
            'company_name' => $student->company_name,
            'teacher_name' => $student->teacher_name,
            'mentor_name' => 'Sujatmo, S.Sos',
            'gender' => 'Laki-Laki',
            'mentor_signature_data' => $fakeBase64,
        ];

        $response = $this->put(route('students.update', $student->id), $payload);
        $response->assertSessionHasNoErrors();

        $student->refresh();
        $this->assertNotNull($student->mentor_signature_path);
        $this->assertNotNull($student->mentor_signature_url);

        $printRes = $this->get(route('students.print', $student->id));
        $printRes->assertStatus(200);
        $printRes->assertSee('print-mentor-signature-img');
        $printRes->assertSee($student->mentor_signature_url, false);
    }

    public function test_attendance_destroy_sheet_returns_redirect(): void
    {
        $student = $this->createSampleStudent();
        // create a second sheet so count > 1
        $sheet2 = $student->attendanceSheets()->create([
            'month_name' => 'November 2026',
            'total_target_days' => 5,
        ]);

        $response = $this->delete(route('attendance-sheets.destroy', [$student->id, $sheet2->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('students.show', ['student' => $student->id, 'tab' => 'presensi']));
        $this->assertDatabaseMissing('attendance_sheets', ['id' => $sheet2->id]);
    }

    public function test_first_uploaded_paraf_becomes_default_and_autofills_subsequent_days(): void
    {
        $student = $this->createSampleStudent();
        $sheet = $student->activitySheets()->first();
        $log1 = $sheet->activityLogs()->where('row_index', 1)->first();
        $log2 = $sheet->activityLogs()->where('row_index', 2)->first();

        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        // Siswa hanya upload di baris 1
        $payload = [
            'month_name' => 'Oktober 2026',
            'logs' => [
                [
                    'id' => $log1->id,
                    'day_date' => 'Senin, 05-10-2026',
                    'division' => 'IT Software',
                    'job_description' => 'Membuat fitur paraf hari pertama',
                    'paraf_data' => $fakeBase64,
                ],
            ],
        ];

        $response = $this->put(route('activity-sheets.update', [$student->id, $sheet->id]), $payload);
        $response->assertSessionHasNoErrors();

        // 1. Student otomatis punya default_paraf_path
        $student->refresh();
        $this->assertNotNull($student->default_paraf_path);
        $this->assertNotNull($student->default_paraf_url);

        // 2. Baris ke-2 (hari kedua) otomatis langsung punya foto paraf tanpa perlu upload lagi!
        $log2->refresh();
        $this->assertTrue($log2->is_using_default_paraf);
        $this->assertEquals($student->default_paraf_url, $log2->paraf_url);

        // 3. Daftar hadir / presensi otomatis langsung punya foto paraf pada status Hadir!
        $attSheet = $student->attendanceSheets()->first();
        $rec1 = $attSheet->records()->where('day_number', 1)->first();
        $this->assertTrue($rec1->is_using_default_paraf);
        $this->assertEquals($student->default_paraf_url, $rec1->paraf_url);
    }

    public function test_master_paraf_can_be_saved_via_dedicated_endpoint(): void
    {
        $student = $this->createSampleStudent();
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->post(route('students.master-paraf', $student->id), [
            'default_paraf_data' => $fakeBase64,
            'apply_to_all_existing' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $student->refresh();
        $this->assertNotNull($student->default_paraf_path);

        $log1 = $student->activitySheets()->first()->activityLogs()->first();
        $this->assertNotNull($log1->paraf_url);
    }

    public function test_explicit_removal_marks_none_and_does_not_inherit_default(): void
    {
        $student = $this->createSampleStudent();
        $student->update(['default_paraf_path' => 'data:image/png;base64,master-sample']);

        $sheet = $student->activitySheets()->first();
        $log1 = $sheet->activityLogs()->where('row_index', 1)->first();
        $log2 = $sheet->activityLogs()->where('row_index', 2)->first();

        // User menghapus paraf di baris 1
        $payload = [
            'month_name' => 'Oktober 2026',
            'logs' => [
                [
                    'id' => $log1->id,
                    'day_date' => 'Senin, 05-10-2026',
                    'division' => 'IT Software',
                    'job_description' => 'Khusus baris ini tidak ada paraf',
                    'remove_paraf' => 1,
                ],
            ],
        ];

        $response = $this->put(route('activity-sheets.update', [$student->id, $sheet->id]), $payload);
        $response->assertSessionHasNoErrors();

        $log1->refresh();
        $this->assertEquals('none', $log1->paraf_path);
        $this->assertNull($log1->paraf_url);

        // Baris ke-2 tetap menggunakan default paraf
        $log2->refresh();
        $this->assertNotNull($log2->paraf_url);
    }
}
