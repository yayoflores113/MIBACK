<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Importante para flujo por BEARER TOKEN:
        // - No llamar $middleware->statefulApi()
        // - Mantener el grupo "api" sin CSRF

        // Puedes dejar CORS global (Laravel ya registra HandleCors de forma global).
        // Aquí no es necesario tocar nada más.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Aquí puedes registrar renderables si los necesitas
    })->create();
