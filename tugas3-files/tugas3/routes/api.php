<?php

use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Tenant Management Service
| Standard Integration Contract (IAE-T2) + IAE Tugas 3
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('check.api.key')->group(function () {

    // ─── Tenant Resource (Tugas 2) ──────────────────────────────────────
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::get('/tenants/{id}', [TenantController::class, 'show']);
    Route::post('/tenants', [TenantController::class, 'store']);
    Route::patch('/tenants/{id}/verify', [TenantController::class, 'verify']);

    // ─── Integration Routes (Tugas 3) ───────────────────────────────────

    // Modul 1: SSO
    Route::prefix('integration')->group(function () {
        Route::post('/sso/login', [IntegrationController::class, 'ssoLogin']);
        Route::post('/sso/m2m', [IntegrationController::class, 'ssoM2MLogin']);

        // Modul 2: SOAP Audit
        Route::post('/soap/audit', [IntegrationController::class, 'sendSoapAudit']);
        Route::get('/soap/logs', [IntegrationController::class, 'getSoapLogs']);

        // Modul 3: AMQP Publisher
        Route::post('/amqp/publish', [IntegrationController::class, 'publishAmqp']);

        // Full Flow Orchestration
        Route::post('/full-flow/tenant-verified', [IntegrationController::class, 'fullFlowTenantVerified']);
    });
});

// Health check
Route::get('/v1/health', function () {
    return response()->json([
        'status'  => 'success',
        'message' => 'Tenant Service is running',
        'data'    => [
            'service' => 'Tenant-Service',
            'version' => 'v1',
            'time'    => now()->toIso8601String(),
            'modules' => ['REST-API', 'GraphQL', 'SSO', 'SOAP-Audit', 'AMQP-Publisher'],
        ],
        'meta' => [
            'service_name' => 'Tenant-Service',
            'api_version'  => 'v1',
        ],
    ]);
});
