<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSecurityAndHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_api_endpoint_rejects_unauthenticated_request(): void
    {
        $response = $this->getJson('/api/v1/students');

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'code' => 401,
        ]);
        $response->assertJsonFragment([
            'message' => 'Unauthorized: Akses API ditolak. Masukkan API Key yang valid pada header X-API-KEY atau Authorization: Bearer <token>.',
        ]);
    }

    public function test_api_endpoint_rejects_invalid_api_key(): void
    {
        $response = $this->withHeader('X-API-KEY', 'wrong-key-123')->getJson('/api/v1/students');

        $response->assertStatus(401);
    }

    public function test_api_endpoint_rejects_api_key_in_query_parameter_for_security(): void
    {
        $validKey = config('services.api.key') ?: env('API_SECRET_KEY');
        $response = $this->getJson('/api/v1/students?api_key='.$validKey);

        $response->assertStatus(401);
    }

    public function test_api_endpoint_accepts_valid_x_api_key(): void
    {
        $validKey = config('services.api.key');
        $response = $this->withHeader('X-API-KEY', $validKey)->getJson('/api/v1/students');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'total_students',
            'data',
        ]);
    }

    public function test_api_endpoint_accepts_valid_bearer_token(): void
    {
        $validKey = config('services.api.key');
        $response = $this->withToken($validKey)->getJson('/api/v1/students');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'total_students',
            'data',
        ]);
    }

    public function test_web_health_endpoint_does_not_disclose_sensitive_stack_info(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'UP',
        ]);

        // Verifikasi tidak ada kebocoran driver database atau detail internal
        $data = $response->json();
        $this->assertArrayNotHasKey('driver', $data);
        $this->assertArrayNotHasKey('database', $data);
        $this->assertArrayNotHasKey('error', $data);
        $this->assertStringNotContainsStringIgnoringCase('pgsql', $response->getContent());
        $this->assertStringNotContainsStringIgnoringCase('postgres', $response->getContent());
        $this->assertStringNotContainsStringIgnoringCase('supabase', $response->getContent());
    }

    public function test_api_health_endpoint_does_not_disclose_sensitive_stack_info(): void
    {
        $response = $this->get('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'UP',
        ]);

        // Verifikasi tidak ada kebocoran driver database atau detail internal
        $data = $response->json();
        $this->assertArrayNotHasKey('driver', $data);
        $this->assertArrayNotHasKey('database', $data);
        $this->assertArrayNotHasKey('error', $data);
        $this->assertStringNotContainsStringIgnoringCase('pgsql', $response->getContent());
        $this->assertStringNotContainsStringIgnoringCase('postgres', $response->getContent());
        $this->assertStringNotContainsStringIgnoringCase('supabase', $response->getContent());
    }
}
