<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crea una sesión de checkout de Stripe
     */
    public function createSession(Request $request)
    {
        try {
            $validated = $request->validate([
                'mode' => 'required|in:payment,subscription',
                'amount_cents' => 'required_if:mode,payment|integer|min:0',
                'currency' => 'required|string|size:3',
                'product_name' => 'required|string',
                'success_url' => 'required|url',
                'cancel_url' => 'required|url',
                'metadata' => 'nullable|array',
            ]);

            $metadata = $validated['metadata'] ?? [];
            $kind = $metadata['kind'] ?? 'unknown';

            $lineItems = [];

            if ($validated['mode'] === 'payment') {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => strtolower($validated['currency']),
                        'product_data' => [
                            'name' => $validated['product_name'],
                            'description' => $this->getProductDescription($kind, $metadata),
                        ],
                        'unit_amount' => (int)$validated['amount_cents'],
                    ],
                    'quantity' => 1,
                ];
            }

            $userId = auth()->id(); // Puede ser null
            $customerEmail = auth()->user()->email ?? null;

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => $validated['mode'],
                'success_url' => $validated['success_url'],
                'cancel_url' => $validated['cancel_url'],
                'metadata' => array_merge($metadata, [
                    'user_id' => $userId,
                ]),
                'customer_email' => $customerEmail,
            ]);

            // Crear orden pendiente
            $this->createPendingOrder($session, $validated, $metadata, $userId);

            return response()->json([
                'success' => true,
                'url' => $session->url,
                'session_id' => $session->id,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al crear la sesión de pago',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Checkout Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error inesperado',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook de Stripe
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'payment_intent.succeeded':
                Log::info('Payment succeeded: ' . $event->data->object->id);
                break;

            case 'payment_intent.payment_failed':
                Log::warning('Payment failed: ' . $event->data->object->id);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Maneja checkout completado
     */
    private function handleCheckoutCompleted($session)
    {
        $order = Order::where('stripe_session_id', $session->id)->first();

        if (!$order) {
            Log::warning('Order not found for session: ' . $session->id);
            return;
        }

        $order->update([
            'status' => 'completed',
            'stripe_payment_intent' => $session->payment_intent ?? null,
            'paid_at' => now(),
        ]);

        Log::info('Order completed: ' . $order->id);

        $this->activateAccess($order);
    }

    /**
     * Activa acceso según tipo de compra
     */
    private function activateAccess(Order $order)
    {
        $metadata = $order->metadata ?? [];
        $kind = $metadata['kind'] ?? null;
        $userId = $order->user_id;

        if (!$userId) {
            Log::warning('No user_id for order: ' . $order->id);
            return;
        }

        switch ($kind) {
            case 'plan':
                $this->activatePlanAccess($userId, $metadata);
                break;
            case 'course':
                $this->activateCourseAccess($userId, $metadata);
                break;
            case 'course_bundle':
                $this->activateCourseBundleAccess($userId, $metadata);
                break;
            default:
                Log::warning('Unknown kind for order: ' . $order->id);
        }
    }

    private function activatePlanAccess($userId, $metadata)
    {
        $planId = $metadata['plan_id'] ?? null;
        if (!$planId) {
            Log::warning('No plan_id in metadata');
            return;
        }

        $plan = Plan::find($planId);
        if (!$plan) {
            Log::warning('Plan not found: ' . $planId);
            return;
        }

        DB::table('user_subscriptions')->updateOrInsert(
            ['user_id' => $userId, 'plan_id' => $planId],
            [
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addMonth(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Log::info("Plan activated for user $userId: plan $planId");
    }

    private function activateCourseAccess($userId, $metadata)
    {
        $courseId = $metadata['course_id'] ?? null;
        if (!$courseId) {
            Log::warning('No course_id in metadata');
            return;
        }

        DB::table('user_courses')->insertOrIgnore([
            'user_id' => $userId,
            'course_id' => $courseId,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Course activated for user $userId: course $courseId");
    }

    private function activateCourseBundleAccess($userId, $metadata)
    {
        $courseIds = $metadata['course_ids'] ?? [];
        if (empty($courseIds)) {
            Log::warning('No course_ids in metadata');
            return;
        }

        foreach ($courseIds as $courseId) {
            DB::table('user_courses')->insertOrIgnore([
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info("Bundle activated for user $userId: " . count($courseIds) . " courses");
    }

    /**
     * Crea orden pendiente
     */
    private function createPendingOrder($session, $validated, $metadata, $userId = null)
    {
        $userId = $userId ?? 1; // Si no hay login, asignar usuario de prueba

        Order::create([
            'user_id' => $userId,
            'stripe_session_id' => $session->id,
            'amount_cents' => $validated['amount_cents'] ?? 0,
            'currency' => strtoupper($validated['currency']),
            'status' => 'pending',
            'metadata' => $metadata,
            'product_name' => $validated['product_name'],
        ]);

        Log::info("Orden creada para user_id: $userId, session: {$session->id}");
    }

    private function getProductDescription($kind, $metadata)
    {
        switch ($kind) {
            case 'plan':
                return 'Suscripción a plan premium';
            case 'course':
                return 'Acceso al curso';
            case 'course_bundle':
                return 'Bundle de cursos';
            default:
                return 'Compra';
        }
    }
}
