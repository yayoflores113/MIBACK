<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /** Listar suscripciones (podría filtrarse por usuario autenticado) */
    public function index(Request $request)
    {
        $q = Subscription::query()->with('plan:id,name,slug');
        if ($uid = $request->integer('user_id')) {
            $q->where('user_id', $uid);
        }
        return response()->json($q->latest()->paginate(20));
    }

    /** Crear (normalmente se crea vía Stripe webhook; aquí queda por si administras manualmente) */
    public function store(StoreSubscriptionRequest $request)
    {
        $sub = Subscription::create($request->validated());
        return response()->json($sub, 201);
    }

    public function show(Subscription $subscription)
    {
        $subscription->load('plan:id,name,slug');
        return response()->json($subscription);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $subscription->update($request->validated());
        return response()->json($subscription);
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return response()->json(['deleted' => true]);
    }
}
