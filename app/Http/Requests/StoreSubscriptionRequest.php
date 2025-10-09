<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'stripe_customer_id' => ['nullable', 'string', 'max:255'],
            'stripe_subscription_id' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:incomplete,active,past_due,canceled,unpaid'],
            'trial_ends_at' => ['nullable', 'date'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end' => ['nullable', 'date'],
            'cancel_at' => ['nullable', 'date'],
            'canceled_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
