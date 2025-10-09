<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Protege este endpoint con role:admin en rutas; aquí lo dejamos abierto.
        return true;
    }

    public function rules(): array
    {
        return [
            'roles'   => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(['admin', 'user'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'roles' => 'roles',
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Debes indicar al menos un rol.',
            'roles.*.in'     => 'Rol inválido. Solo se permiten: admin, user.',
        ];
    }

    /**
     * Reglas de negocio:
     * - No permitir quitar el rol admin si es el ÚLTIMO admin.
     * - Evitar que un admin se quite a sí mismo el rol admin si dejaría al sistema sin admins.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            /** @var \App\Models\User|null $target */
            $target = $this->route('user'); // /users/{user}
            if (! $target) {
                return;
            }

            $incoming = collect($this->input('roles', []))->unique()->values();
            $remueveAdmin = $target->hasRole('admin') && ! $incoming->contains('admin');

            if (! $remueveAdmin) {
                return;
            }

            // ¿Cuántos admins hay actualmente?
            $adminsCount = User::role('admin')->count();

            // 1) No permitir dejar al sistema sin admins
            if ($adminsCount <= 1) {
                $v->errors()->add('roles', 'No puedes quitar el rol de administrador: sería el último administrador del sistema.');
                return;
            }

            // 2) Opcional: evita que un admin se auto-quite el rol admin si solo quedan 1 o 2 admins
            // (especialmente útil si estás editando tu propio usuario)
            if ($this->user() && $this->user()->id === $target->id && $adminsCount <= 1) {
                $v->errors()->add('roles', 'No puedes quitarte a ti mismo el rol de administrador cuando quedarías sin admins.');
            }
        });
    }
}
