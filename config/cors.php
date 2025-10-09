<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Orígenes explícitos de Vite en local
    'allowed_origins' => [
        'http://localhost:5173',    
        'http://127.0.0.1:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5174',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Para BEARER tokens no hace falta enviar cookies:
    'supports_credentials' => false,

];
