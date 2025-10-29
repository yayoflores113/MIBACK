<?php
return [
<<<<<<< HEAD

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*'],

=======
    'paths' => ['api/*', 'api/v1/*', 'sanctum/csrf-cookie'],
    
>>>>>>> 1cf2757 (Mis cambios para MIBACK)
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],
    
    'allowed_origins_patterns' => [],
<<<<<<< HEAD

    'allowed_headers' => ['*'],

=======
    
    'allowed_headers' => ['*'],
    
>>>>>>> 1cf2757 (Mis cambios para MIBACK)
    'exposed_headers' => [],
    
    'max_age' => 0,
<<<<<<< HEAD

    'supports_credentials' => true,

];
=======
    
    'supports_credentials' => true, // Cambia a true para Sanctum
];
>>>>>>> 1cf2757 (Mis cambios para MIBACK)
