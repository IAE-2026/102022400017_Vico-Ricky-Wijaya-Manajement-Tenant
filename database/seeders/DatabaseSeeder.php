<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat 5 tenant contoh
        $tenants = [
            [
                'name'              => 'Budi Santoso',
                'email'             => 'budi.santoso@example.com',
                'phone'             => '081234567890',
                'id_number'         => '3374010101990001',
                'address'           => 'Jl. Merdeka No.1, Bandung',
                'occupation'        => 'Software Engineer',
                'emergency_contact' => '081299999999',
                'status'            => 'verified',
                'verified_at'       => now(),
            ],
            [
                'name'              => 'Siti Rahayu',
                'email'             => 'siti.rahayu@example.com',
                'phone'             => '081234567891',
                'id_number'         => '3374020202990002',
                'address'           => 'Jl. Sudirman No.5, Jakarta',
                'occupation'        => 'Teacher',
                'emergency_contact' => '081288888888',
                'status'            => 'pending',
                'verified_at'       => null,
            ],
            [
                'name'              => 'Ahmad Fauzi',
                'email'             => 'ahmad.fauzi@example.com',
                'phone'             => '081234567892',
                'id_number'         => '3374030303990003',
                'address'           => 'Jl. Pahlawan No.10, Surabaya',
                'occupation'        => 'Businessman',
                'emergency_contact' => '081277777777',
                'status'            => 'verified',
                'verified_at'       => now()->subDays(10),
            ],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::create($data);

            // Generate contract for each tenant
            $contractStatus = match($tenant->status) {
                'verified' => 'approved',
                default    => 'draft',
            };

            Contract::create([
                'tenant_id'       => $tenant->id,
                'contract_number' => 'DRAFT-' . strtoupper(Str::random(8)) . '-' . $tenant->id,
                'status'          => $contractStatus,
                'terms'           => 'Kontrak sewa unit hunian. Semua ketentuan berlaku sesuai peraturan yang berlaku.',
                'approved_at'     => $contractStatus === 'approved' ? now() : null,
            ]);
        }

        $this->command->info('Seeder selesai! ' . count($tenants) . ' tenant telah dibuat.');
    }
}
