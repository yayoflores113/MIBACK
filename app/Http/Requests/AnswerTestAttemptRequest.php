<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerTestAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'exists:questions,id'],
            'answer_option_id' => ['nullable', 'exists:answer_options,id'],
            'answer_value' => ['nullable', 'integer'],
        ];
    }
}
