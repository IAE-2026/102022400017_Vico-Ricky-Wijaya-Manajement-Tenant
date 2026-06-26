<?php

namespace Tests\Unit;

use App\Models\Tenant;
use PHPUnit\Framework\TestCase;

class TenantModelTest extends TestCase
{
    /**
     * Test bahwa fillable fields Tenant sudah benar.
     */
    public function test_tenant_fillable_fields(): void
    {
        $tenant = new Tenant();

        $expectedFillable = [
            'name',
            'email',
            'phone',
            'id_number',
            'address',
            'occupation',
            'emergency_contact',
            'document_path',
            'document_original_name',
            'status',
            'notes',
            'verified_at',
        ];

        $this->assertEquals($expectedFillable, $tenant->getFillable());
    }

    /**
     * Test bahwa hidden fields sudah benar.
     */
    public function test_tenant_hidden_fields(): void
    {
        $tenant = new Tenant();

        $this->assertContains('document_path', $tenant->getHidden());
        $this->assertContains('deleted_at', $tenant->getHidden());
    }

    /**
     * Test bahwa cast verified_at bertipe datetime.
     */
    public function test_tenant_casts_verified_at(): void
    {
        $tenant = new Tenant();
        $casts = $tenant->getCasts();

        $this->assertArrayHasKey('verified_at', $casts);
        $this->assertEquals('datetime', $casts['verified_at']);
    }
}
