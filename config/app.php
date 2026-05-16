<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    'name' => env('APP_NAME', 'Tenant Service'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id',
    'fallback_locale' => 'en',
    'faker_locale' => 'id_ID',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],

    /*
    |--------------------------------------------------------------------------
    | IAE API Key (Standard Integration Contract)
    |--------------------------------------------------------------------------
    | API key untuk autentikasi sesuai dokumen Standard Integration Contract
    | IAE-T2. Nilai diambil dari .env: IAE_API_KEY=[NIM Mahasiswa]
    */
    'iae_api_key' => env('IAE_API_KEY'),

    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];
