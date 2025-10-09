<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnswerOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => 'sometimes|exists:questions,id',
            'text'        => 'sometimes|string|max:500',
            'trait_id'    => 'nullable|exists:vocational_traits,id',
            'score'       => 'sometimes|integer|min:0',
            'order'       => 'nullable|integer|min:0',
        ];
    }
}
