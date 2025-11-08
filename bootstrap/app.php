<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\LogApiActivity::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payment/callback',
            'bot-api/*',
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle CSRF token mismatch for Inertia requests
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
                // For Inertia requests, return a proper JSON response
                if ($request->header('X-Inertia')) {
                    return response()->json([
                        'message' => 'CSRF token mismatch. Please refresh the page.',
                    ], 419);
                }
            }
            return $response;
        });
    })->create();
