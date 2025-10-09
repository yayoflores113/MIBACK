<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // 🔹 Variables que envía tu frontend (cursos y planes)
        //    (coinciden con Course.jsx y Plan.jsx)
        $mode         = $request->input('mode', 'payment'); // normalmente "payment"
        $amountCents  = (int) $request->input('amount_cents', 0); // centavos
        $currency     = strtoupper($request->input('currency', 'MXN'));
        $productName  = $request->input('product_name', 'Producto');
        $description  = $request->input('description'); // opcional
        $successUrl   = $request->input('success_url', rtrim(env('APP_URL'), '/') . '/success');
        $cancelUrl    = $request->input('cancel_url',  rtrim(env('APP_URL'), '/') . '/cancel');
        $metadata     = (array) $request->input('metadata', []); // { kind: "course"|"plan", ... }

        if ($amountCents <= 0 || empty($currency) || empty($productName)) {
            return response()->json([
                'error' => 'Parámetros inválidos',
                'message' => 'amount_cents, currency y product_name son obligatorios'
            ], 422);
        }

        try {
            // 🔹 Checkout Session (misma estructura, variables adaptadas)
            $session = Session::create([
                'payment_method_types' => ['card', 'oxxo', 'link'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $productName,
                            'description' => $description ?? 'Sin descripción',
                        ],
                        'unit_amount' => $amountCents, // ya viene en centavos
                    ],
                    'quantity' => 1,
                ]],
                'mode' => $mode === 'subscription' ? 'subscription' : 'payment', // por ahora usas "payment"
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => array_merge([
                    // 🔹 Campos útiles para distinguir en tu backend
                    // kind: "course" | "plan"
                    'kind' => $metadata['kind'] ?? 'product',
                ], $metadata),
            ]);

            return response()->json(['url' => $session->url], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear la sesión de pago',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
