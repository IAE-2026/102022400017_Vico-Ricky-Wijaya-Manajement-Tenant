<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SSO Service — Modul 1: Federated SSO
 * Menangani autentikasi ke IAE Central SSO dan validasi JWT
 */
class SsoService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('iae.sso_url', 'https://iae-sso.virtualfri.id');
        $this->apiKey  = config('iae.sso_api_key', 'KEY-MHS-103'); // Key untuk M2M login
    }

    /**
     * Login sebagai Warga (user) menggunakan email & password
     * POST /api/v1/auth/token
     */
    public function loginAsWarga(string $email, string $password): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/api/v1/auth/token", [
                    'email'    => $email,
                    'password' => $password,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('[SSO] Warga login berhasil', ['email' => $email]);
                return [
                    'success' => true,
                    'token'   => $data['token'] ?? $data['access_token'] ?? null,
                    'data'    => $data,
                ];
            }

            Log::warning('[SSO] Warga login gagal', [
                'email'  => $email,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Login gagal',
                'status'  => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('[SSO] Exception saat login', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Login sebagai M2M (Machine to Machine) menggunakan API Key
     * POST /api/v1/auth/token
     */
    public function loginAsM2M(): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/api/v1/auth/token", [
                    'api_key' => $this->apiKey,
                ]);

            if ($response->successful()) {
                $data  = $response->json();
                $token = $data['token'] ?? $data['access_token'] ?? null;

                // Cache token selama 50 menit (JWT biasanya 60 menit)
                if ($token) {
                    Cache::put('iae_m2m_token', $token, now()->addMinutes(50));
                }

                Log::info('[SSO] M2M login berhasil');
                return [
                    'success' => true,
                    'token'   => $token,
                    'data'    => $data,
                ];
            }

            Log::warning('[SSO] M2M login gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'M2M login gagal',
                'status'  => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('[SSO] Exception M2M login', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Ambil JWKS (public keys) untuk verify JWT
     * GET /api/v1/auth/jwks
     */
    public function getJwks(): array
    {
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/api/v1/auth/jwks");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'message' => 'Gagal ambil JWKS'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Decode JWT payload (tanpa verifikasi signature — untuk mapping lokal)
     */
    public function decodeJwtPayload(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) return null;

            $payload = base64_decode(str_pad(
                strtr($parts[1], '-_', '+/'),
                strlen($parts[1]) % 4,
                '='
            ));

            return json_decode($payload, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Ambil atau refresh M2M token dari cache
     */
    public function getM2MToken(): ?string
    {
        $cached = Cache::get('iae_m2m_token');
        if ($cached) return $cached;

        $result = $this->loginAsM2M();
        return $result['success'] ? $result['token'] : null;
    }
}
