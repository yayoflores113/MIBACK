<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
    // Agrega esta línea:
    \SocialiteProviders\Manager\ServiceProvider::class,
];
