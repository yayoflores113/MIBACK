<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Protege este endpoint con role:admin en rutas; aquí lo dejamos abierto.
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user'); // requiere Route Model Binding: /users/{user}
        $id   = $user?->id;

        return [
            'name'                  => ['sometimes', 'string', 'max:100'],
            'email'                 => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password'              => ['sometimes', 'string', 'min:8', 'confirmed'], // requiere password_confirmation si viene
            // Si tienes más campos de perfil, añádelos aquí (teléfono, avatar_url, etc.)
        ];
    }

    public function attributes(): array
    {
        return [
            'name'     => 'nombre',
            'email'    => 'correo',
            'password' => 'contraseña',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'     => 'Este correo ya está en uso.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ];
    }
}
