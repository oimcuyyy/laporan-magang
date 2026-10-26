<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable();
            $table->string('class', 50)->default('XI');
            $table->string('major', 100)->default('Rekayasa Perangkat Lunak');
            $table->string('field_of_expertise', 100)->default('Bisnis Manajemen dan Teknologi');
            $table->string('academic_year', 30)->default('2026/2027')->nullable();
            $table->string('internship_period', 100)->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('birth_date', 50)->nullable();
            $table->string('gender', 20)->default('Laki-Laki');
            $table->string('religion', 30)->default('Islam');
            $table->text('student_address')->nullable();
            $table->string('student_phone', 30)->nullable();
            
            // Data Orang Tua
            $table->string('father_name', 100)->nullable();
            $table->string('father_phone', 30)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('mother_phone', 30)->nullable();
            $table->text('parent_address')->nullable();
            
            // Data Sekolah & Instansi DU/DI
            $table->string('school_name', 150)->default('SMK Negeri 20 Jakarta');
            $table->text('school_address')->nullable();
            $table->string('company_name', 150)->default('Kantor Lurah Bintaro');
            $table->text('company_address')->nullable();
            $table->string('teacher_name', 100)->default('Kuswanto');
            $table->string('teacher_phone', 30)->nullable();
            $table->string('mentor_name', 100)->nullable();
            $table->string('photo_path')->nullable();
            
            $table->timestamps();
        });

        Schema::create('activity_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->unsignedInteger('sheet_number')->default(1);
            $table->string('month_name', 50)->default('Oktober 2026');
            $table->timestamps();

            $table->unique(['student_id', 'sheet_number']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_sheet_id')->constrained('activity_sheets')->onDelete('cascade');
            $table->unsignedTinyInteger('row_index'); // 1-10
            $table->string('day_date', 50)->nullable(); // e.g. Kamis, 01-10-26
            $table->string('division', 100)->nullable(); // e.g. Perpanjang Makam
            $table->text('job_description')->nullable();
            $table->string('supervisor_note', 100)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['activity_sheet_id', 'row_index']);
        });

        Schema::create('attendance_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('month_name', 50)->default('Oktober 2026');
            $table->unsignedInteger('total_target_days')->default(30);
            $table->unsignedInteger('total_present')->default(0);
            $table->unsignedInteger('total_sick')->default(0);
            $table->unsignedInteger('total_permission')->default(0);
            $table->unsignedInteger('total_absent')->default(0);
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_sheet_id')->constrained('attendance_sheets')->onDelete('cascade');
            $table->unsignedTinyInteger('day_number'); // 1 to 30/31
            $table->string('day_date', 50)->nullable();
            $table->char('status', 1)->nullable(); // H, S, I, A
            $table->string('notes', 150)->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamps();

            $table->unique(['attendance_sheet_id', 'day_number']);
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->onDelete('cascade');
            $table->decimal('aspect_a_score', 5, 2)->nullable();
            $table->string('aspect_a_grade', 5)->nullable();
            $table->decimal('aspect_b_score', 5, 2)->nullable();
            $table->string('aspect_b_grade', 5)->nullable();
            $table->decimal('aspect_c_score', 5, 2)->nullable();
            $table->string('aspect_c_grade', 5)->nullable();
            $table->decimal('average_score', 5, 2)->nullable();
            $table->string('final_grade', 5)->nullable();
            $table->string('evaluation_date', 50)->default('Jakarta, 31 Oktober 2026');
            $table->string('mentor_name', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sheets');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('activity_sheets');
        Schema::dropIfExists('students');
    }
};
