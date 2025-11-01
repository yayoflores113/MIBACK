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
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Middleware CORS personalizado primero
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceCors::class,
        ]);

        // ✅ Middleware de Laravel para estado de sesión y cookies
        $middleware->statefulApi();

        // Opcional: middleware oficial de Laravel CORS al final
        $middleware->api(append: [
            \Fruitcake\Cors\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
