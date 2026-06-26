<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test endpoint health check mengembalikan status success.
     */
    public function test_health_check_returns_success(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => 'success',
                     'message' => 'Tenant Service is running',
                 ])
                 ->assertJsonPath('data.service', 'Tenant-Service')
                 ->assertJsonPath('data.version', 'v1');
    }

    /**
     * Test endpoint yang tidak ada mengembalikan 404 JSON.
     */
    public function test_unknown_endpoint_returns_404_json(): void
    {
        $response = $this->getJson('/api/v1/endpoint-yang-tidak-ada');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => 'error',
                     'message' => 'Resource not found',
                 ]);
    }
}
