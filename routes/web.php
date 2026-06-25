<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/api/v1/documentation');
});

Route::get('/v1', function () {
    return redirect('/api/v1/documentation');
});

Route::get('/docs', function () {
    return redirect('/api/v1/documentation');
});

Route::get('/graphql-playground', function () {
    return view('graphql-playground');
});

// Also provide exact /v1 routes to satisfy "harus menggunakan /v1" literal test cases
Route::prefix('v1')->middleware('check.api.key')->group(function () {
    Route::get('/tenants', [\App\Http\Controllers\Api\TenantController::class, 'index']);
    Route::get('/tenants/{id}', [\App\Http\Controllers\Api\TenantController::class, 'show']);
    Route::post('/tenants', [\App\Http\Controllers\Api\TenantController::class, 'store']);
    Route::patch('/tenants/{id}/verify', [\App\Http\Controllers\Api\TenantController::class, 'verify']);
});
