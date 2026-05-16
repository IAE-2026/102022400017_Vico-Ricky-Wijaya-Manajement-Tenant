<?php

use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Tenant Management Service
| Standard Integration Contract (IAE-T2)
|--------------------------------------------------------------------------
| Semua route dilindungi oleh middleware CheckApiKey
| Header yang diperlukan: X-IAE-KEY: [NIM Mahasiswa]
*/

Route::prefix('v1')->middleware('check.api.key')->group(function () {

    // ─── Tenant Resource ────────────────────────────────────────────────────
    // [Collection] GET /api/v1/tenants - Mengambil daftar semua tenant
    Route::get('/tenants', [TenantController::class, 'index']);

    // [Resource] GET /api/v1/tenants/{id} - Mengambil data spesifik tenant
    Route::get('/tenants/{id}', [TenantController::class, 'show']);

    // [Action] POST /api/v1/tenants - Mendaftarkan penyewa baru
    Route::post('/tenants', [TenantController::class, 'store']);

    // [Action] PATCH /api/v1/tenants/{id}/verify - Admin verifikasi tenant
    Route::patch('/tenants/{id}/verify', [TenantController::class, 'verify']);
});

// Health check (tidak perlu API key)
Route::get('/v1/health', function () {
    return response()->json([
        'status'  => 'success',
        'message' => 'Tenant Service is running',
        'data'    => [
            'service' => 'Tenant-Service',
            'version' => 'v1',
            'time'    => now()->toIso8601String(),
        ],
        'meta'    => [
            'service_name' => 'Tenant-Service',
            'api_version'  => 'v1',
        ],
    ]);
});
