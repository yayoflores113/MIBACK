<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $q = Payment::query()->with('subscription:id,status,plan_id');
        if ($uid = $request->integer('user_id')) {
            $q->where('user_id', $uid);
        }
        if ($s = $request->string('status')->toString()) {
            $q->where('status', $s);
        }
        return response()->json($q->latest()->paginate(20));
    }

    public function store(StorePaymentRequest $request)
    {
        $p = Payment::create($request->validated());
        return response()->json($p, 201);
    }

    public function show(Payment $payment)
    {
        $payment->load('subscription:id,status,plan_id');
        return response()->json($payment);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());
        return response()->json($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(['deleted' => true]);
    }
}
