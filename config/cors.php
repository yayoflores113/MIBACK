<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://mifront-6stl.onrender.com',
        'https://mifront-1.onrender.com',  // ✅ Agrega esta línea
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];
