<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug'],
            'description' => ['nullable', 'string'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'interval' => ['required', 'string', Rule::in(['month', 'year'])],
            'features' => ['nullable', 'array'],
            'stripe_product_id' => ['nullable', 'string', 'max:255'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'trial_days' => ['integer', 'min:0', 'max:365'],
            'sort_order' => ['integer', 'min:0', 'max:1000'],
            // New fields for the plan
            'subtitle'   => ['nullable', 'string', 'max:120'],
            'cta_type'   => ['required', 'string', Rule::in(['trial', 'subscribe', 'contact'])],
            'cta_label'  => ['nullable', 'string', 'max:60'],
            'is_featured' => ['boolean'],
        ];
    }
}
