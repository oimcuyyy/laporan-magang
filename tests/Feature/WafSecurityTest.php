<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WafSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_waf_allows_legitimate_requests_and_adds_security_headers(): void
    {
        $response = $this->withHeader('X-API-KEY', 'smkn20-magang-api-key-2026')->get('/api/v1/students');
        $response->assertStatus(200);
        $response->assertHeader('X-WAF-Protection');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_waf_blocks_sql_injection(): void
    {
        $response = $this->get('/api/v1/students?search='.urlencode("' UNION SELECT 1,2,3,4,5 --"));
        $response->assertStatus(401);
        $response->assertHeader('X-WAF-Status', 'BLOCKED');
        $response->assertHeader('X-WAF-Reason', 'SQL_INJECTION');

        $this->assertDatabaseHas('waf_logs', [
            'threat_type' => 'SQL_INJECTION',
        ]);
    }

    public function test_waf_blocks_cross_site_scripting(): void
    {
        $response = $this->get('/api/v1/students?name='.urlencode('<script>alert("hacked")</script>'));
        $response->assertStatus(401);
        $response->assertHeader('X-WAF-Status', 'BLOCKED');
        $response->assertHeader('X-WAF-Reason', 'CROSS_SITE_SCRIPTING');

        $this->assertDatabaseHas('waf_logs', [
            'threat_type' => 'CROSS_SITE_SCRIPTING',
        ]);
    }

    public function test_waf_blocks_path_traversal(): void
    {
        $response = $this->get('/api/v1/students?file=../../../../windows/system32/cmd.exe');
        $response->assertStatus(401);
        $response->assertHeader('X-WAF-Status', 'BLOCKED');
        $response->assertHeader('X-WAF-Reason', 'PATH_TRAVERSAL');

        $this->assertDatabaseHas('waf_logs', [
            'threat_type' => 'PATH_TRAVERSAL',
        ]);
    }

    public function test_waf_blocks_malicious_scanner_bots(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'sqlmap/1.6#stable (https://sqlmap.org)',
        ])->get('/api/v1/students');

        $response->assertStatus(401);
        $response->assertHeader('X-WAF-Status', 'BLOCKED');
        $response->assertHeader('X-WAF-Reason', 'BAD_BOT');

        $this->assertDatabaseHas('waf_logs', [
            'threat_type' => 'BAD_BOT',
        ]);
    }

    public function test_waf_status_telemetry_endpoint(): void
    {
        $response = $this->withHeader('X-API-KEY', 'smkn20-magang-api-key-2026')->get('/api/v1/waf/status');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'waf_status' => 'ACTIVE',
        ]);
    }
}
