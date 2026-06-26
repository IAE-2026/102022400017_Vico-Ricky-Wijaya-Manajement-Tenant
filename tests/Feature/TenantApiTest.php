<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantApiTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = '102022400017';

    /**
     * Test akses endpoint tanpa API key mengembalikan 401 atau 403.
     */
    public function test_tenant_list_requires_api_key(): void
    {
        $response = $this->getJson('/api/v1/tenants');

        $response->assertStatus(401)
                 ->assertJsonStructure(['status', 'message']);
    }

    /**
     * Test akses endpoint tenants dengan API key yang valid.
     */
    public function test_tenant_list_accessible_with_valid_api_key(): void
    {
        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey,
        ])->getJson('/api/v1/tenants');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data',
                     'meta',
                 ]);
    }

    /**
     * Test membuat tenant baru dengan data yang valid.
     */
    public function test_create_tenant_with_valid_data(): void
    {
        $tenantData = [
            'name'              => 'Budi Santoso',
            'email'             => 'budi@example.com',
            'phone'             => '081234567890',
            'id_number'         => '3201010101010001',
            'address'           => 'Jl. Sudirman No. 1, Jakarta',
            'occupation'        => 'Software Engineer',
            'emergency_contact' => '081234567891',
            'status'            => 'pending',
        ];

        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey,
        ])->postJson('/api/v1/tenants', $tenantData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'email',
                         'status',
                     ],
                 ]);
    }

    /**
     * Test mendapatkan tenant berdasarkan ID.
     */
    public function test_get_tenant_by_id(): void
    {
        // Buat tenant terlebih dahulu
        $createResponse = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey,
        ])->postJson('/api/v1/tenants', [
            'name'              => 'Siti Rahayu',
            'email'             => 'siti@example.com',
            'phone'             => '081298765432',
            'id_number'         => '3201010101010002',
            'address'           => 'Jl. Thamrin No. 2, Jakarta',
            'occupation'        => 'Dokter',
            'emergency_contact' => '081298765433',
            'status'            => 'pending',
        ]);

        $tenantId = $createResponse->json('data.id');

        // Ambil tenant berdasarkan ID
        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey,
        ])->getJson("/api/v1/tenants/{$tenantId}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $tenantId)
                 ->assertJsonPath('data.name', 'Siti Rahayu');
    }

    /**
     * Test mendapatkan tenant yang tidak ada mengembalikan 404.
     */
    public function test_get_nonexistent_tenant_returns_404(): void
    {
        $response = $this->withHeaders([
            'X-IAE-KEY' => $this->apiKey,
        ])->getJson('/api/v1/tenants/99999');

        $response->assertStatus(404);
    }
}
