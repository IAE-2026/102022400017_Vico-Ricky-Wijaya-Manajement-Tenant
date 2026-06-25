<?php

use Illuminate\Support\Facades\Route;

// Redirect root ke Swagger UI
Route::get('/', function () {
    return redirect('/api/v1/documentation');
});

Route::get('/docs', function () {
    return redirect('/api/v1/documentation');
});

// GraphQL Playground - dihandle oleh package mll-lab/laravel-graphql-playground
// Route ini terdaftar otomatis oleh package via config/graphql-playground.php
// yang mengarah ke /api/v1/graphql-playground
