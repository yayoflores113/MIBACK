<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'test_id' => 'sometimes|exists:tests,id',
            'text'    => 'sometimes|string',
            'type'    => 'sometimes|string|in:single_choice,multiple_choice,text',
            'order'   => 'nullable|integer|min:0',
        ];
    }
}
