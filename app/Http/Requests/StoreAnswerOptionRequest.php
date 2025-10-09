<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnswerOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustar si usas policies
    }

    public function rules(): array
    {
        return [
            'question_id' => 'required|exists:questions,id',
            'text'        => 'required|string|max:500',
            'trait_id'    => 'nullable|exists:vocational_traits,id',
            'score'       => 'required|integer|min:0',
            'order'       => 'nullable|integer|min:0',
        ];
    }
}
