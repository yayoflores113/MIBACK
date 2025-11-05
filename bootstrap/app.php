<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1', // ✅ Prefijo completo aquí
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Middleware CORS personalizado
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceCors::class,
        ]);
        
        // ✅ Excluir rutas API del CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
            '/api/*',
        ]);
        
        // ✅ Middleware web para OAuth (Google, Microsoft)
        $middleware->web(append: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
