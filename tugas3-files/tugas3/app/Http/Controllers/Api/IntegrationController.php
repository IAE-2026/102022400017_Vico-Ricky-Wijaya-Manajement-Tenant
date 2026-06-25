<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SsoUser;
use App\Models\SoapAuditLog;
use App\Services\AmqpPublisherService;
use App\Services\SoapAuditService;
use App\Services\SsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(name="Integration", description="Endpoint integrasi Tugas 3 (SSO, SOAP, AMQP)")
 */
class IntegrationController extends Controller
{
    public function __construct(
        protected SsoService          $ssoService,
        protected SoapAuditService    $soapAuditService,
        protected AmqpPublisherService $amqpPublisher
    ) {}

    // =========================================================
    // MODUL 1: SSO
    // =========================================================

    /**
     * @OA\Post(
     *     path="/integration/sso/login",
     *     summary="[Modul 1] Login ke IAE SSO sebagai Warga",
     *     description="Login menggunakan email dan password warga ke IAE Central SSO. Mengembalikan JWT token dan memetakan user ke roles lokal.",
     *     tags={"Integration"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="warga17@ktp.iae.id"),
     *             @OA\Property(property="password", type="string", example="KtpDigital2026!")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login berhasil, JWT token dikembalikan"),
     *     @OA\Response(response=401, description="Kredensial tidak valid")
     * )
     */
    public function ssoLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->ssoService->loginAsWarga($request->email, $request->password);

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'SSO login gagal',
                'errors'  => null,
            ], 401);
        }

        // Decode JWT payload untuk mapping ke roles lokal
        $jwtPayload = $this->ssoService->decodeJwtPayload($result['token']);

        // Simpan/update user SSO di database lokal
        $localUser = null;
        if ($jwtPayload) {
            $localUser = SsoUser::updateOrCreate(
                ['email' => $jwtPayload['email'] ?? $request->email],
                [
                    'name'        => $jwtPayload['name'] ?? $jwtPayload['sub'] ?? $request->email,
                    'sso_subject' => $jwtPayload['sub'] ?? null,
                    'role'        => $this->mapSsoRole($jwtPayload['role'] ?? $jwtPayload['roles'] ?? 'warga'),
                    'jwt_payload' => json_encode($jwtPayload),
                    'last_login'  => now(),
                ]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'SSO login berhasil',
            'data'    => [
                'access_token' => $result['token'],
                'token_type'   => 'Bearer',
                'jwt_payload'  => $jwtPayload,
                'local_user'   => $localUser,
            ],
            'meta' => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'module'       => 'SSO-Federated',
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/integration/sso/m2m",
     *     summary="[Modul 1] Login M2M ke IAE SSO",
     *     description="Login Machine-to-Machine menggunakan API Key untuk keperluan service-to-service.",
     *     tags={"Integration"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\Response(response=200, description="M2M token berhasil didapat")
     * )
     */
    public function ssoM2MLogin(Request $request): JsonResponse
    {
        $result = $this->ssoService->loginAsM2M();

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'M2M login gagal',
                'errors'  => null,
            ], 401);
        }

        $jwtPayload = $this->ssoService->decodeJwtPayload($result['token']);

        return response()->json([
            'status'  => 'success',
            'message' => 'M2M SSO login berhasil',
            'data'    => [
                'access_token' => $result['token'],
                'token_type'   => 'Bearer',
                'jwt_payload'  => $jwtPayload,
            ],
            'meta' => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'module'       => 'SSO-M2M',
            ],
        ], 200);
    }

    // =========================================================
    // MODUL 2: SOAP AUDIT
    // =========================================================

    /**
     * @OA\Post(
     *     path="/integration/soap/audit",
     *     summary="[Modul 2] Kirim SOAP Audit ke IAE Central",
     *     description="Mengirim log transaksi kritis dalam format SOAP/XML ke server IAE. Menyimpan ReceiptNumber dari response.",
     *     tags={"Integration"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bearer_token","activity_name","log_data"},
     *             @OA\Property(property="bearer_token", type="string", description="JWT token dari SSO"),
     *             @OA\Property(property="activity_name", type="string", example="TenantVerified"),
     *             @OA\Property(property="log_data", type="object", example={"tenant_id":1,"action":"verified"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="SOAP audit berhasil, ReceiptNumber dikembalikan"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function sendSoapAudit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bearer_token'  => 'required|string',
            'activity_name' => 'required|string|max:100',
            'log_data'      => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->soapAuditService->sendAudit(
            $request->bearer_token,
            $request->activity_name,
            $request->log_data
        );

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'SOAP audit gagal',
                'errors'  => null,
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'SOAP audit berhasil dikirim',
            'data'    => [
                'receipt_number' => $result['receipt_number'],
                'activity_name'  => $request->activity_name,
                'team_id'        => 'TEAM-08',
                'status_code'    => $result['status_code'],
            ],
            'meta' => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'module'       => 'SOAP-Audit',
            ],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/integration/soap/logs",
     *     summary="[Modul 2] Lihat riwayat SOAP Audit Log",
     *     tags={"Integration"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\Response(response=200, description="Daftar audit log")
     * )
     */
    public function getSoapLogs(): JsonResponse
    {
        $logs = SoapAuditLog::orderByDesc('created_at')->take(20)->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data retrieved successfully',
            'data'    => $logs,
            'meta'    => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'module'       => 'SOAP-Audit',
            ],
        ], 200);
    }

    // =========================================================
    // MODUL 3: AMQP PUBLISHER
    // =========================================================

    /**
     * @OA\Post(
     *     path="/integration/amqp/publish",
     *     summary="[Modul 3] Publish Event ke RabbitMQ",
     *     description="Mengirim event notification dalam format JSON ke RabbitMQ IAE Central secara asinkron.",
     *     tags={"Integration"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"bearer_token","event_type","payload"},
     *             @OA\Property(property="bearer_token", type="string", description="JWT token dari SSO"),
     *             @OA\Property(property="event_type", type="string", example="TenantRegistered"),
     *             @OA\Property(property="payload", type="object", example={"tenant_id":1,"name":"Budi"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Event berhasil dipublish ke RabbitMQ")
     * )
     */
    public function publishAmqp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bearer_token' => 'required|string',
            'event_type'   => 'required|string|max:100',
            'payload'      => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $result = $this->amqpPublisher->publish(
            $request->bearer_token,
            $request->event_type,
            $request->payload
        );

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Publish gagal',
                'errors'  => null,
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Event berhasil dipublish ke RabbitMQ',
            'data'    => $result,
            'meta'    => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'module'       => 'AMQP-Publisher',
            ],
        ], 200);
    }

    // =========================================================
    // FULL FLOW — SSO + SOAP + AMQP dalam satu endpoint
    // =========================================================

    /**
     * @OA\Post(
     *     path="/integration/full-flow/tenant-verified",
     *     summary="[Full Flow] Verifikasi Tenant + SOAP Audit + AMQP Publish",
     *     description="Orkestrasi 3 lapis: Login SSO → Kirim SOAP Audit → Publish ke RabbitMQ. Dipanggil otomatis saat admin memverifikasi tenant.",
     *     tags={"Integration"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tenant_id"},
     *             @OA\Property(property="tenant_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Full flow berhasil")
     * )
     */
    public function fullFlowTenantVerified(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|integer|exists:tenants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $tenant = \App\Models\Tenant::with('latestContract')->find($request->tenant_id);

        // Step 1: Login SSO M2M
        $ssoResult = $this->ssoService->loginAsM2M();
        if (!$ssoResult['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => 'SSO login gagal: ' . ($ssoResult['message'] ?? ''),
                'errors'  => null,
            ], 500);
        }
        $token = $ssoResult['token'];

        // Step 2: Kirim SOAP Audit (transaksi kritis)
        $soapResult = $this->soapAuditService->sendAudit(
            $token,
            'TenantVerified',
            [
                'tenant_id'       => $tenant->id,
                'tenant_name'     => $tenant->name,
                'tenant_email'    => $tenant->email,
                'status'          => $tenant->status,
                'contract_number' => $tenant->latestContract?->contract_number,
                'verified_at'     => $tenant->verified_at?->toIso8601String(),
                'team'            => 'TEAM-08',
            ]
        );

        // Step 3: Publish ke RabbitMQ
        $amqpResult = $this->amqpPublisher->publishTenantVerified($token, [
            'id'              => $tenant->id,
            'name'            => $tenant->name,
            'email'           => $tenant->email,
            'status'          => $tenant->status,
            'contract_number' => $tenant->latestContract?->contract_number,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Full flow berhasil dieksekusi',
            'data'    => [
                'sso_token_obtained' => true,
                'soap_audit'         => [
                    'success'        => $soapResult['success'],
                    'receipt_number' => $soapResult['receipt_number'] ?? null,
                ],
                'amqp_publish' => [
                    'success' => $amqpResult['success'],
                    'event'   => 'TenantVerified',
                ],
                'tenant' => [
                    'id'     => $tenant->id,
                    'name'   => $tenant->name,
                    'status' => $tenant->status,
                ],
            ],
            'meta' => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'module'       => 'Full-Flow-Orchestration',
            ],
        ], 200);
    }

    /**
     * Map role dari SSO ke role lokal
     */
    private function mapSsoRole(mixed $ssoRole): string
    {
        if (is_array($ssoRole)) {
            $ssoRole = implode(',', $ssoRole);
        }

        $role = strtolower((string) $ssoRole);

        if (str_contains($role, 'admin')) return 'admin';
        if (str_contains($role, 'warga')) return 'warga';
        if (str_contains($role, 'tenant')) return 'tenant';

        return 'warga';
    }
}
