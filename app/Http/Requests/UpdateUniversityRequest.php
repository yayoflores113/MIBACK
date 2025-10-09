<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Si el front manda base64 en logo_url (data:image/*;base64,...) lo movemos a logo_base64
        if (!empty($data['logo_url']) && is_string($data['logo_url']) && Str::startsWith($data['logo_url'], 'data:image/')) {
            $this->merge([
                'logo_base64' => $data['logo_url'],
                'logo_url' => null,
            ]);
        }

        // Autogenerar slug si no viene y tenemos name
        if (empty($data['slug']) && !empty($data['name'])) {
            $this->merge([
                'slug' => Str::slug($data['name']),
            ]);
        }
    }

    public function rules(): array
    {
        // Soporta binding por modelo o por id plano
        $routeParam = $this->route('university');
        $id = is_object($routeParam) ? $routeParam->id : $routeParam;

        $currentYear = now()->year;

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('universities', 'name')->ignore($id)],
            'acronym' => ['sometimes', 'nullable', 'string', 'max:50'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('universities', 'slug')->ignore($id)],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            // Si no cambias imagen, puedes omitir ambos campos (thanks a "sometimes")
            'logo_url' => ['sometimes', 'nullable', 'url', 'max:255', 'required_without:logo_base64'],
            'logo_base64' => ['sometimes', 'nullable', 'string', 'required_without:logo_url'],
            'established_year' => ['sometimes', 'nullable', 'integer', 'min:1000', "max:$currentYear"],
            'orden' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
