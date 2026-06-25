<?php

use App\Http\Middleware\CheckApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.api.key' => CheckApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Resource not found',
                'errors'  => null,
            ], 404);
        });

        $exceptions->render(function (\Throwable $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                    'errors'  => null,
                ], 500);
            }
        });
    })->create();
