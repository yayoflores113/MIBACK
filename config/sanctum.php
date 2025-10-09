<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains (solo si usaras cookies "stateful")
    |--------------------------------------------------------------------------
    */
    'stateful' => explode(',', (string) env('SANCTUM_STATEFUL_DOMAINS', '')),

    /*
    |--------------------------------------------------------------------------
    | Expiración de tokens (opcional)
    |--------------------------------------------------------------------------
    */
    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'authenticate_session' => null,
        'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
