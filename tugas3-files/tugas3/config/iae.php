<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IAE Central Configuration
    |--------------------------------------------------------------------------
    | Konfigurasi untuk integrasi dengan IAE Central (SSO, SOAP, AMQP)
    */

    'sso_url' => env('IAE_SSO_URL', 'https://iae-sso.virtualfri.id'),

    'api_key' => env('IAE_API_KEY', 'KEY-MHS-103'),

    'warga_email'    => env('IAE_WARGA_EMAIL', 'warga17@ktp.iae.id'),
    'warga_password' => env('IAE_WARGA_PASSWORD', 'KtpDigital2026!'),

    'team_id' => env('IAE_TEAM_ID', 'TEAM-08'),
];
