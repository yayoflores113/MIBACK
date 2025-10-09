<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscription_id' => ['sometimes', 'nullable', 'exists:subscriptions,id'],
            'stripe_payment_intent_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stripe_session_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'amount_cents' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'status' => ['sometimes', 'string', 'in:pending,succeeded,requires_action,canceled,failed'],
            'payload' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
