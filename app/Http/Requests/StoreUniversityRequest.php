<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Si viene base64 en logo_url (data:image/*;base64,...) => mover a logo_base64
        if (!empty($data['logo_url']) && is_string($data['logo_url']) && Str::startsWith($data['logo_url'], 'data:image/')) {
            $this->merge([
                'logo_base64' => $data['logo_url'],
                'logo_url' => null, // limpiar para no violar max:255
            ]);
        }

        // Autogenerar slug si no viene
        if (empty($data['slug']) && !empty($data['name'])) {
            $this->merge([
                'slug' => Str::slug($data['name']),
            ]);
        }
    }

    public function rules(): array
    {
        $currentYear = now()->year;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:universities,name'],
            'acronym' => ['nullable', 'string', 'max:50'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:universities,slug'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
            // opción A (URL) o B (base64)
            'logo_url' => ['nullable', 'url', 'max:255', 'required_without:logo_base64'],
            'logo_base64' => ['nullable', 'string', 'required_without:logo_url'],
            'established_year' => ['nullable', 'integer', 'min:1000', "max:$currentYear"],
            'orden' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
