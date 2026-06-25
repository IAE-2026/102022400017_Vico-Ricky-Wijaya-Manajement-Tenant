<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AMQP Publisher Service — Modul 3: Message Broker Publisher
 * Mengirim event notification ke RabbitMQ IAE Central secara asinkron
 */
class AmqpPublisherService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('iae.sso_url', 'https://iae-sso.virtualfri.id');
    }

    /**
     * Publish event ke RabbitMQ melalui IAE Central API
     * POST /api/v1/messages/publish
     *
     * @param string $bearerToken  JWT token dari SSO
     * @param string $eventType    Nama event (misal: TenantRegistered, TenantVerified)
     * @param array  $payload      Data event dalam format JSON
     */
    public function publish(string $bearerToken, string $eventType, array $payload): array
    {
        $message = [
            'event'      => $eventType,
            'service'    => 'Tenant-Service',
            'team'       => 'TEAM-08',
            'timestamp'  => now()->toIso8601String(),
            'payload'    => $payload,
        ];

        Log::info('[AMQP] Publishing event', [
            'event'   => $eventType,
            'service' => 'Tenant-Service',
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$bearerToken}",
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/v1/messages/publish", $message);

            if ($response->successful()) {
                Log::info('[AMQP] Event berhasil dipublish', [
                    'event'  => $eventType,
                    'status' => $response->status(),
                ]);

                return [
                    'success'    => true,
                    'event'      => $eventType,
                    'status'     => $response->status(),
                    'response'   => $response->json(),
                ];
            }

            Log::warning('[AMQP] Publish gagal', [
                'event'  => $eventType,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Publish gagal',
                'status'  => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('[AMQP] Exception saat publish', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish event TenantRegistered ketika penyewa baru mendaftar
     */
    public function publishTenantRegistered(string $bearerToken, array $tenantData): array
    {
        return $this->publish($bearerToken, 'TenantRegistered', [
            'tenant_id'   => $tenantData['id'] ?? null,
            'name'        => $tenantData['name'] ?? null,
            'email'       => $tenantData['email'] ?? null,
            'status'      => 'pending',
            'action'      => 'tenant_registered',
            'description' => 'Penyewa baru telah mendaftarkan diri dan menunggu verifikasi.',
        ]);
    }

    /**
     * Publish event TenantVerified ketika admin memverifikasi penyewa
     */
    public function publishTenantVerified(string $bearerToken, array $tenantData): array
    {
        return $this->publish($bearerToken, 'TenantVerified', [
            'tenant_id'       => $tenantData['id'] ?? null,
            'name'            => $tenantData['name'] ?? null,
            'email'           => $tenantData['email'] ?? null,
            'status'          => $tenantData['status'] ?? 'verified',
            'contract_number' => $tenantData['contract_number'] ?? null,
            'action'          => 'tenant_verified',
            'description'     => 'Penyewa telah diverifikasi oleh admin dan kontrak telah disetujui.',
        ]);
    }
}
