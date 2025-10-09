<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Monetización:
     * - plans: planes de suscripción (Free/Pro/…)
     * - subscriptions: suscripciones de usuarios a un plan
     * - payments: registros de pagos/intentos (Stripe)
     */
    public function up(): void
    {
        // PLANES
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // Nombre comercial del plan
            $table->string('slug')->unique();              // Identificador amigable
            $table->text('description')->nullable();       // Descripción en landing
            $table->unsignedInteger('price_cents')->default(0); // Precio en centavos (evita flotantes)
            $table->char('currency', 3)->default('MXN');   // Moneda (ISO 4217)
            $table->string('interval', 20)->default('month'); // month | year
            $table->json('features')->nullable();          // Lista de beneficios/limits
            $table->string('stripe_product_id')->nullable(); // IDs de Stripe para integración
            $table->string('stripe_price_id')->nullable();
            $table->boolean('is_active')->default(true);   // Permite desactivar un plan sin borrarlo
            $table->unsignedSmallInteger('trial_days')->default(0); // Días de prueba
            $table->unsignedSmallInteger('sort_order')->default(0); // Orden en la UI
            $table->timestamps();
            $table->softDeletes();
        });

        // SUSCRIPCIONES
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // Usuario suscrito
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete(); // Plan actual
            $table->string('stripe_customer_id')->nullable();     // Cliente en Stripe
            $table->string('stripe_subscription_id')->nullable(); // Suscripción en Stripe
            $table->string('status', 40)->default('incomplete');  // incomplete|active|past_due|canceled|unpaid
            $table->timestamp('trial_ends_at')->nullable();       // Fin de periodo de prueba
            $table->timestamp('current_period_start')->nullable(); // Inicio periodo actual
            $table->timestamp('current_period_end')->nullable();  // Fin periodo actual
            $table->timestamp('cancel_at')->nullable();           // Programada para cancelar en fecha
            $table->timestamp('canceled_at')->nullable();         // Cancelada en fecha (histórico)
            $table->json('meta')->nullable();                     // Datos auxiliares
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->unique(['user_id', 'stripe_subscription_id']); // Evita duplicados por usuario
        });

        // PAGOS
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Quien pagó
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete(); // Suscripción relacionada
            $table->string('stripe_payment_intent_id')->nullable(); // PaymentIntent de Stripe
            $table->string('stripe_session_id')->nullable();        // ID de Checkout Session
            $table->unsignedInteger('amount_cents')->default(0);    // Monto en centavos
            $table->char('currency', 3)->default('MXN');            // Moneda
            $table->string('status', 40)->default('pending');       // pending|succeeded|requires_action|canceled|failed
            $table->json('payload')->nullable();                    // Payload completo (webhook/intent)
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->unique(['stripe_payment_intent_id']);
            $table->unique(['stripe_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
