<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreCourseRequest extends FormRequest
{
    // Enums suaves
    public const LEVELS = ['todos', 'principiante', 'intermedio', 'experto'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->filled('slug') && $this->filled('title')) {
            $this->merge(['slug' => Str::slug($this->string('title'))]);
        }
    }

    public function rules(): array
    {
        $cid = $this->integer('career_id');

        return [
            'career_id'   => ['required', 'integer', 'exists:careers,id'],
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->where(fn($q) => $q->where('career_id', $cid))
            ],
            'provider'    => ['sometimes', 'nullable', 'string', 'max:120'],
            'url'         => ['sometimes', 'nullable', 'url', 'max:255'],

            'topic'       => ['sometimes', 'nullable', 'string', 'max:120'],
            'level'       => ['sometimes', 'nullable', 'string', Rule::in(self::LEVELS)],
            'difficulty'  => ['sometimes', 'nullable', 'string', 'max:50'],

            'hours'       => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1000'],
            'is_premium'  => ['sometimes', 'boolean'],
            'is_free'     => ['sometimes', 'boolean'],
            'price_cents' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'rating_avg'    => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:5'],
            'rating_count'  => ['sometimes', 'nullable', 'integer', 'min:0'],
            'popularity_score' => ['sometimes', 'nullable', 'integer', 'min:0'],

            'published_at' => ['sometimes', 'nullable', 'date'],

            // Portada: puedes mandar URL pública o (en update) base64 aparte
            'card_image_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'card_image_base64' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^data:image\\/(png|jpe?g|webp);base64,/'
            ],

            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_image_base64.regex' => 'La imagen debe ser una data URL válida (png, jpg, jpeg o webp).',
            'level.in' => 'Nivel inválido. Valores permitidos: ' . implode(', ', self::LEVELS),
        ];
    }
}
