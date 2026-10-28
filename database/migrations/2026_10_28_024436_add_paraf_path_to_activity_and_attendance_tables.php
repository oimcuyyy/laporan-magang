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
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('activity_logs', 'paraf_path')) {
                    $table->text('paraf_path')->nullable()->after('supervisor_note');
                }
            });
        }

        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_records', 'paraf_path')) {
                    $table->text('paraf_path')->nullable()->after('is_signed');
                }
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (! Schema::hasColumn('students', 'mentor_signature_path')) {
                    $table->text('mentor_signature_path')->nullable()->after('photo_path');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (Schema::hasColumn('activity_logs', 'paraf_path')) {
                    $table->dropColumn('paraf_path');
                }
            });
        }

        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_records', 'paraf_path')) {
                    $table->dropColumn('paraf_path');
                }
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'mentor_signature_path')) {
                    $table->dropColumn('mentor_signature_path');
                }
            });
        }
    }
};
