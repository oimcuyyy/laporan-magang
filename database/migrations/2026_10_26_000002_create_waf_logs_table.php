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
        Schema::create('waf_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('method', 10);
            $table->text('request_url');
            $table->string('threat_type', 50); // SQL_INJECTION, XSS, PATH_TRAVERSAL, RCE, BAD_BOT
            $table->string('severity', 20)->default('HIGH'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->text('matched_pattern');
            $table->text('blocked_payload')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waf_logs');
    }
};
