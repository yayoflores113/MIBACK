<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'unique:tests,code'],
            'title' => ['required', 'string', 'max:255'],
            'version' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
