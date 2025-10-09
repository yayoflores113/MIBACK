<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /** Listado público de planes activos (ordenados para la UI) */
    public function index()
    {
        // Obtiene todos los registros activos ordenados por sort_order
        $plans = Plan::orderBy('sort_order', 'asc')->get();

        // Devuelve la respuesta en formato JSON
        return response()->json($plans);
    }

public function showBySlug(string $slug)
{
    $plan = \App\Models\Plan::query()
        ->where('slug', $slug)
        ->where('is_active', true)
        ->select([
            'id','name','subtitle','slug','description',
            'price_cents','currency','interval',
            'trial_days','features',
            'cta_type','cta_label',
            'is_featured'
        ])
        ->first();

    if (!$plan) {
        return response()->json(['message' => 'Plan not found'], 404);
    }

    return response()->json($plan);
}


    /** Crear (para admin) */
    public function store(StorePlanRequest $request)
    {
        $plan = Plan::create($request->validated());
        return response()->json($plan, 201);
    }

    public function show(Plan $plan)
    {
        return response()->json($plan);
    }

    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());
        return response()->json($plan);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return response()->json(['deleted' => true]);
    }
}
