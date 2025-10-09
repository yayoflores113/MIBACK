<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateCourseRequest extends FormRequest
{
    public const LEVELS = StoreCourseRequest::LEVELS;

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
        // 🔧 Mejora mínima: normalizar el parámetro de ruta (puede llegar como objeto o como string, p.ej. "2")
        $routeParam = $this->route('course') ?? $this->route('id');
        $id = is_object($routeParam)
            ? $routeParam->id
            : (is_numeric($routeParam) ? (int) $routeParam : null);

        // Mantener tu misma lógica: unique por career_id + slug.
        // Si no viene career_id en el payload, tomarlo del modelo atado (si es objeto).
        $cid = $this->integer('career_id');
        if (!$cid && is_object($routeParam) && isset($routeParam->career_id)) {
            $cid = (int) $routeParam->career_id;
        }

        return [
            'career_id'   => ['sometimes', 'integer', 'exists:careers,id'],
            'title'       => ['sometimes', 'string', 'max:255'],
            'slug'        => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')
                    ->where(fn($q) => $cid ? $q->where('career_id', $cid) : $q)
                    ->ignore($id),
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

            'card_image_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'card_image_base64' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^data:image\/(png|jpe?g|webp);base64,/',
            ],

            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
