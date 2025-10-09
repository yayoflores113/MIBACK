<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * GET /users
     * Filtros: q (nombre/email), role (admin|user), created_from, created_to (YYYY-MM-DD)
     * Orden: sort (name|email|created_at), dir (asc|desc)
     * Paginado: per_page (10-100)
     */
    public function index(Request $request)
    {
        // Solo se necesita los IDs y nombres de usuarios con rol 'user', de admin no.
        $data = User::whereHas('roles', function ($q) {
            $q->where('name', 'user');
        })->get(['id', 'name', 'email']);
        return response()->json($data, 200);


        $q = User::query()->with('roles:id,name');

        if ($s = trim((string) $request->query('q', ''))) {
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($role = $request->query('role')) {
            // Requiere HasRoles (Spatie) en User
            $q->role($role);
        }

        if ($from = $request->query('created_from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('created_to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        $sortable = ['name', 'email', 'created_at'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'created_at';
        $dir  = strtolower($request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(10, min(100, $perPage));

        return response()->json(
            $q->orderBy($sort, $dir)->paginate($perPage)
        );
    }

    public function store(Request $request) {}

    /**
     * GET /users/{user}
     * Incluye roles, suscripciones y últimos pagos.
     */
    public function show(User $user)
    {
        $user->load([
            'roles:id,name',
            // Ajusta estas relaciones si las tienes definidas en User:
            'subscriptions.plan:id,name,slug',
            'payments' => function ($q) {
                $q->latest()->limit(5);
            },
        ]);

        return response()->json($user);
    }

    /**
     * PATCH/PUT /users/{user}
     * Actualiza datos básicos (nombre, email, contraseña).
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user->load('roles:id,name'));
    }

    /**
     * PATCH /users/{user}/roles
     * Actualiza roles (admin/user) respetando reglas de último admin.
     */
    public function updateRoles(UpdateUserRoleRequest $request, User $user)
    {
        $roles = collect($request->validated()['roles'])->unique()->values()->all();
        $user->syncRoles($roles);

        return response()->json($user->load('roles:id,name'));
    }

    /**
     * DELETE /users/{user}
     * Elimina usuario (revoca tokens antes).
     * Reglas:
     *  - No puedes borrarte a ti mismo.
     *  - No puedes borrar al último admin.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        if ($user->hasRole('admin')) {
            $adminsCount = User::role('admin')->count();
            if ($adminsCount <= 1) {
                return response()->json(['message' => 'No puedes eliminar al último administrador del sistema.'], 422);
            }
        }

        // Revoca todos los tokens del usuario
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Si el modelo usa SoftDeletes, esto será soft-delete; en caso contrario, hard-delete.
        $user->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * POST /users/{user}/deactivate
     * Desactiva usuario:
     *  - Si hay SoftDeletes en users: soft delete.
     *  - Si existe columna is_active: la pone en false.
     *  - Si no, devuelve 409 con sugerencia.
     * Reglas:
     *  - No puedes desactivarte a ti mismo.
     *  - No puedes desactivar al último admin.
     */
    public function deactivate(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'No puedes desactivar tu propio usuario.'], 422);
        }

        if ($user->hasRole('admin')) {
            $adminsCount = User::role('admin')->count();
            if ($adminsCount <= 1) {
                return response()->json(['message' => 'No puedes desactivar al último administrador del sistema.'], 422);
            }
        }

        // Revoca tokens
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        $usesSoftDeletes = in_array(
            'Illuminate\\Database\\Eloquent\\SoftDeletes',
            class_uses_recursive($user)
        );

        if ($usesSoftDeletes) {
            $user->delete();
            return response()->json(['deactivated' => true, 'mode' => 'soft_delete']);
        }

        if (Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->forceFill(['is_active' => false])->save();
            return response()->json(['deactivated' => true, 'mode' => 'flag_is_active']);
        }

        return response()->json([
            'deactivated' => false,
            'message' => 'No hay SoftDeletes ni columna is_active. Agrega SoftDeletes o un campo is_active para desactivar sin eliminar.',
        ], 409);
    }

    /**
     * POST /users/{user}/activate
     * Activa usuario (inverso de deactivate).
     */
    public function activate(Request $request, User $user)
    {
        $usesSoftDeletes = in_array(
            'Illuminate\\Database\\Eloquent\\SoftDeletes',
            class_uses_recursive($user)
        );

        if ($usesSoftDeletes && method_exists($user, 'restore')) {
            // Solo aplica si el usuario está soft-deleteado
            if (method_exists($user, 'trashed') && $user->trashed()) {
                $user->restore();
            }
            return response()->json(['activated' => true, 'mode' => 'soft_delete']);
        }

        if (Schema::hasColumn($user->getTable(), 'is_active')) {
            $user->forceFill(['is_active' => true])->save();
            return response()->json(['activated' => true, 'mode' => 'flag_is_active']);
        }

        return response()->json([
            'activated' => false,
            'message' => 'No hay SoftDeletes ni columna is_active. Agrega SoftDeletes o un campo is_active para activar/desactivar.',
        ], 409);
    }

    /**
     * POST /users/{user}/revoke-tokens
     * Revoca todos los tokens de acceso del usuario.
     */
    public function revokeTokens(User $user)
    {
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return response()->json(['revoked' => true]);
    }
}
