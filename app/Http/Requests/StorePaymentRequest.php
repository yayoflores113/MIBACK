<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'subscription_id' => ['nullable', 'exists:subscriptions,id'],
            'stripe_payment_intent_id' => ['nullable', 'string', 'max:255'],
            'stripe_session_id' => ['nullable', 'string', 'max:255'],
            'amount_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', 'string', 'in:pending,succeeded,requires_action,canceled,failed'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
