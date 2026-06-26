<?php

use Illuminate\Support\Facades\Route;

// Redirect root ke Swagger UI
Route::get('/', function () {
    return redirect('/docs');
});

// Route /docs sudah dihandle otomatis oleh package l5-swagger.

// GraphQL Playground - dihandle oleh package mll-lab/laravel-graphql-playground
// Route ini terdaftar otomatis oleh package via config/graphql-playground.php
// yang mengarah ke /graphql-playground
