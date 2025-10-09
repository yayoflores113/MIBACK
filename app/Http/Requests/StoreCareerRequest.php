<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreCareerRequest extends FormRequest
{
    public const LEVELS     = ['TSU', 'Licenciatura', 'Ingeniería', 'Maestría', 'Doctorado'];
    public const MODALITIES = ['presencial', 'online', 'mixta'];
    public const AREAS      = [
        'Tecnologías de la Información',
        'Ingeniería',
        'Salud',
        'Negocios',
        'Ciencias Sociales',
        'Ciencias Naturales',
        'Artes y Humanidades',
        'Educación',
        'Derecho',
        'Arquitectura y Diseño'
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('logo_url') && !$this->filled('career_url')) {
            $this->merge(['career_url' => $this->input('logo_url')]);
        }
        if (!$this->filled('name') && $this->filled('nombre')) {
            $this->merge(['name' => $this->input('nombre')]);
        }
        // alias corto "div" -> "division"
        if ($this->filled('div') && !$this->filled('division')) {
            $this->merge(['division' => $this->input('div')]);
        }
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => \Illuminate\Support\Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        $uid = (int) $this->integer('university_id');
        $div = $this->input('division'); // puede ser null

        return [
            'university_id'   => ['required', 'integer', 'exists:universities,id'],
            'name'            => ['required', 'string', 'max:255'],
            'division'        => ['sometimes', 'nullable', 'string', 'max:120'],   // <- NUEVO
            'slug'            => [
                'required',
                'string',
                'max:255',
                // ahora uniqueness considera la división
                \Illuminate\Validation\Rule::unique('careers', 'slug')
                    ->where(fn($q) => $q->where('university_id', $uid)
                                         ->where('division', $div)),
            ],
            'level'           => ['sometimes', 'nullable', 'string', \Illuminate\Validation\Rule::in(self::LEVELS)],
            'levels'          => ['sometimes', 'array', 'min:1'],
            'levels.*'        => ['string', \Illuminate\Validation\Rule::in(self::LEVELS)],
            'modality'        => ['sometimes', 'nullable', 'string', \Illuminate\Validation\Rule::in(self::MODALITIES)],
            'area'            => ['sometimes', 'nullable', 'string', \Illuminate\Validation\Rule::in(self::AREAS)],
            'duration_months' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:120'],
            'duration_terms'              => ['sometimes', 'array'],
            'duration_terms.*.level'      => ['required_with:duration_terms', 'string', \Illuminate\Validation\Rule::in(self::LEVELS)],
            'duration_terms.*.terms'      => ['required_with:duration_terms', 'integer', 'min:1', 'max:12'],
            'terms_unit'      => ['sometimes', 'string', 'in:cuatrimestre,semestre'],
            'description'     => ['sometimes', 'nullable', 'string'],
            'career_url'      => ['sometimes', 'nullable', 'string', 'regex:/^data:image\\/(png|jpe?g|webp);base64,/'],
        ];
    }
}
