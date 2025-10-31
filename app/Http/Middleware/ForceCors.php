<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCors
{
    /**
     * Lista de dominios permitidos
     */
    protected array $allowedOrigins = [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://mifront-6stl.onrender.com',
        'https://mifront-1.onrender.com', // si tienes más dominios de frontend
    ];

    /**
     * Maneja la petición.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        // Si el origen está en la lista, lo permitimos
        $allowedOrigin = in_array($origin, $this->allowedOrigins) ? $origin : '';

        // Si es preflight (OPTIONS), respondemos sin ejecutar el controlador
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        // Para solicitudes normales, ejecutamos el controlador y agregamos headers CORS
        $response = $next($request);

        return $response
            ->header('Access-Control-Allow-Origin', $allowedOrigin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
