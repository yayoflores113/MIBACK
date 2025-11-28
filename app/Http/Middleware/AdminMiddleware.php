<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'No autenticado. Por favor, inicia sesión.'
            ], 401);
        }

        if ($request->user()->role_id !== 1) {
            return response()->json([
                'message' => 'No autorizado. Se requieren permisos de administrador.',
                'user_role_id' => $request->user()->role_id
            ], 403);
        }

        return $next($request);
    }
}