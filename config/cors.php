<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*'],
    
    'allowed_methods' => ['*'],
    
    // Orígenes explícitos de Vite (tanto en producción como en local)
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    // Indica si las cookies y credenciales son soportadas
    'supports_credentials' => true,
];