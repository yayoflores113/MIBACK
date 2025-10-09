<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Conecta Policies si quieres filtrar por dueño/rol
    }

    public function rules(): array
    {
        return [
            'started_at'     => ['sometimes', 'nullable', 'date'],
            // Evita cerrar antes de que haya empezado
            'finished_at'    => ['sometimes', 'nullable', 'date', 'after_or_equal:started_at'],
            'result_summary' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'started_at'     => 'inicio',
            'finished_at'    => 'fin',
            'result_summary' => 'resumen de resultados',
        ];
    }
}
