<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | GraphQL Playground route name
    |--------------------------------------------------------------------------
    | Route name yang digunakan untuk mengakses GraphQL Playground.
    | Playground dapat diakses di: /api/v1/graphql-playground
    */
    'route_name' => 'graphql-playground',

    /*
    |--------------------------------------------------------------------------
    | Route configuration
    |--------------------------------------------------------------------------
    | Prefix untuk route Playground agar accessible di /api/v1/graphql-playground
    */
    'route' => [
        'prefix' => 'api/v1',
        'domain' => env('GRAPHQL_PLAYGROUND_DOMAIN', null),
        'middleware' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default GraphQL endpoint
    |--------------------------------------------------------------------------
    | Endpoint yang digunakan oleh Playground untuk mengirim query GraphQL
    */
    'endpoint' => env('APP_URL', 'http://localhost:8000') . '/api/v1/graphql',

    /*
    |--------------------------------------------------------------------------
    | Control Playground availability
    |--------------------------------------------------------------------------
    */
    'enabled' => env('GRAPHQL_PLAYGROUND_ENABLED', true),
];
