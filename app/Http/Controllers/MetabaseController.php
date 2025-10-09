<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MetabaseController extends Controller
{
    public function getDashboardUrl($dashboardId)
    {
        $metabaseSecret = env('METABASE_SECRET_KEY');
        $metabaseSiteUrl = env('METABASE_SITE_URL');
        
        // Validar configuración
        if (!$metabaseSecret || !$metabaseSiteUrl) {
            return response()->json([
                'error' => 'Metabase no está configurado correctamente'
            ], 500);
        }
        
        // Payload con estructura correcta
        $payload = [
            "resource" => [
                "dashboard" => (int)$dashboardId
            ],
            "params" => (object)[], // IMPORTANTE: Debe ser un objeto vacío
            "exp" => time() + (60 * 10)
        ];
        
        try {
            // Generar token JWT
            $token = JWT::encode($payload, $metabaseSecret, 'HS256');
            
            // Construir URL
            $embedUrl = $metabaseSiteUrl . "/embed/dashboard/" . $token . "#bordered=true&titled=true";
            
            return response()->json([
                'url' => $embedUrl,
                'expires_at' => time() + (60 * 10),
                'dashboard_id' => $dashboardId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar token: ' . $e->getMessage()
            ], 500);
        }
    }
}
