<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Career;
use Illuminate\Support\Str;

class UpdateCareerRequest extends FormRequest
{
    public const LEVELS     = StoreCareerRequest::LEVELS;
    public const MODALITIES = StoreCareerRequest::MODALITIES;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('file') && !$this->filled('career_url')) {
            $this->merge(['career_url' => $this->input('file')]);
        }
        if (!$this->filled('name') && $this->filled('nombre')) {
            $this->merge(['name' => $this->input('nombre')]);
        }
        if ($this->filled('div') && !$this->filled('division')) { 
            $this->merge(['division' => $this->input('div')]);
        }
        if (!$this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => \Illuminate\Support\Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        $routeParam = $this->route('career') ?? $this->route('id');
        $careerId   = $routeParam instanceof \App\Models\Career
            ? $routeParam->getKey()
            : (is_numeric($routeParam) ? (int) $routeParam : null);

        // university_id / division desde body o modelo
        $universityId = $this->input('university_id');
        $division     = $this->input('division');

        if (!$universityId && $routeParam instanceof \App\Models\Career) {
            $universityId = $routeParam->university_id;
        }
        if (!$division && $routeParam instanceof \App\Models\Career) {
            $division = $routeParam->division;
        }

        return [
            'university_id'   => ['sometimes', 'integer', 'exists:universities,id'],
            'name'            => ['sometimes', 'string', 'max:255'],
            'division'        => ['sometimes', 'nullable', 'string', 'max:120'], // <- NUEVO
            'slug'            => [
                'sometimes',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('careers', 'slug')
                    ->where(fn($q) => $q->where('university_id', $universityId)
                                        ->where('division', $division)
                                        ->whereNull('deleted_at'))
                    ->ignore($careerId),
            ],
            'level'           => ['sometimes', 'nullable', 'string', \Illuminate\Validation\Rule::in(self::LEVELS)],
            'levels'          => ['sometimes', 'array', 'min:1'],
            'levels.*'        => ['string', \Illuminate\Validation\Rule::in(self::LEVELS)],
            'modality'        => ['sometimes', 'nullable', 'string', \Illuminate\Validation\Rule::in(self::MODALITIES)],
            'area' => ['sometimes', 'nullable', 'string', 'max:120'],
            'duration_months' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:120'],
            'duration_terms'              => ['sometimes', 'array'],
            'duration_terms.*.level'      => ['required_with:duration_terms', 'string', \Illuminate\Validation\Rule::in(self::LEVELS)],
            'duration_terms.*.terms'      => ['required_with:duration_terms', 'integer', 'min:1', 'max:12'],
            'terms_unit'      => ['sometimes', 'string', 'in:cuatrimestre,semestre'],
            'description'     => ['sometimes', 'nullable', 'string'],
            'orden'           => ['sometimes', 'integer', 'min:0'],
            'career_url'      => ['sometimes', 'nullable', 'string', 'regex:/^data:image\\/(png|jpe?g|webp);base64,/'],
            'file'            => ['sometimes', 'nullable', 'string', 'regex:/^data:image\\/(png|jpe?g|webp);base64,/'],
        ];
    }
}
