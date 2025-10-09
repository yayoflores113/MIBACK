<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['sometimes', 'nullable', 'exists:plans,id'],
            'stripe_customer_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stripe_subscription_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:incomplete,active,past_due,canceled,unpaid'],
            'trial_ends_at' => ['sometimes', 'nullable', 'date'],
            'current_period_start' => ['sometimes', 'nullable', 'date'],
            'current_period_end' => ['sometimes', 'nullable', 'date'],
            'cancel_at' => ['sometimes', 'nullable', 'date'],
            'canceled_at' => ['sometimes', 'nullable', 'date'],
            'meta' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
