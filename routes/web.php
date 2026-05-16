<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'Tenant Management Service',
        'version' => 'v1',
        'docs'    => url('/api/documentation'),
        'graphql' => url('/graphql'),
        'health'  => url('/api/v1/health'),
    ]);
});

Route::get('/graphql-playground', function () {
    return view('graphql-playground');
});
