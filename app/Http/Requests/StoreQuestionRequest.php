<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta si usas políticas de autorización
    }

    public function rules(): array
    {
        return [
            'test_id' => 'required|exists:tests,id',
            'text'    => 'required|string',
            'type'    => 'required|string|in:single_choice,multiple_choice,text',
            'order'   => 'nullable|integer|min:0',
        ];
    }
}
