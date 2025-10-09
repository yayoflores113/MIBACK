<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('plan')?->id;
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('plans', 'slug')->ignore($id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'price_cents' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'interval' => ['sometimes', 'string', Rule::in(['month', 'year'])],
            'features' => ['sometimes', 'nullable', 'array'],
            'stripe_product_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stripe_price_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'trial_days' => ['sometimes', 'integer', 'min:0', 'max:365'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
            // New fields for UI
            'subtitle'   => ['sometimes', 'nullable', 'string', 'max:120'],
            'cta_type'   => ['sometimes', 'string', Rule::in(['trial', 'subscribe', 'contact'])],
            'cta_label'  => ['sometimes', 'nullable', 'string', 'max:60'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
