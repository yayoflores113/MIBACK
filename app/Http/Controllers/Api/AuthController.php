<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Registro con TOKEN
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('user');
        }

        // 🔥 Crear token de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Cargar roles
        if (method_exists($user, 'loadMissing')) {
            $user->loadMissing('roles:id,name');
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token,
            'rol' => 'user',
        ], 201);
    }

    /**
    * Login con EMAIL/PASSWORD usando TOKENS (no sesiones)
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar usuario por email
        $user = User::where('email', $request->email)->first();

            // Verificar contraseña
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Correo o Contraseña incorrectos'
            ], 401);
        }

        // 🔥 Crear token de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Obtener el rol usando Spatie
        $rol = 'user'; // valor por defecto
        if (method_exists($user, 'getRoleNames')) {
            $roleNames = $user->getRoleNames();
            $rol = $roleNames->first() ?? 'user';
        }

        // Cargar roles
        if (method_exists($user, 'loadMissing')) {
            $user->loadMissing('roles:id,name');
        }

        return response()->json([
            'success' => true,
            'message' => 'Logueado exitosamente',
            'user' => $user,
            'token' => $token,
            'rol' => $rol,
        ], 200);
    }

    /**
     * Logout (Elimina el token actual)
     */
    public function logout(Request $request)
    {
        // Eliminar el token actual del usuario autenticado
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }

    /**
     * Redirige al proveedor OAuth (Google, Microsoft)
     */
    public function redirectToProvider(string $provider)
    {
        $allowed = ['google', 'microsoft'];

        if (!in_array($provider, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no permitido'
            ], 404);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Callback OAuth - Retorna TOKEN (no sesión)
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $providerId = $socialUser->getId();
            $name       = $socialUser->getName() ?: ($socialUser->user['name'] ?? 'Usuario');
            $email      = $socialUser->getEmail();

            // Si no hay email, generar uno sintético
            if (!$email) {
                $email = "{$provider}-{$providerId}@no-email.local";
            }

            // Buscar o crear usuario
            $wasNew = false;
            $user   = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name'        => $name,
                    'email'       => $email,
                    'provider'    => $provider,
                    'provider_id' => $providerId,
                    'password'    => bcrypt(str()->random(40)),
                ]);
                $wasNew = true;

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user');
                }
            } else {
                // Actualizar provider si no existe
                $needsSave = false;
                if (!$user->provider) {
                    $user->provider = $provider;
                    $needsSave = true;
                }
                if (!$user->provider_id) {
                    $user->provider_id = $providerId;
                    $needsSave = true;
                }
                if ($needsSave) {
                    $user->save();
                }
            }

            // 🔥 Crear token de Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Obtener rol
            $rol = 'user';
            if (method_exists($user, 'getRoleNames')) {
                $roleNames = $user->getRoleNames();
                $rol = $roleNames->first() ?? 'user';
            }

            // Cargar roles
            if (method_exists($user, 'loadMissing')) {
                $user->loadMissing('roles:id,name');
            }

            // Redirigir al frontend con el token
            $frontendUrl = env('FRONTEND_URL', 'https://mifront-1.onrender.com');
            
            // Codificar datos para pasarlos de forma segura
            $userData = base64_encode(json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'provider' => $provider,
                'new' => $wasNew,
                'rol' => $rol,
                'token' => $token, // 🔥 IMPORTANTE: Incluir el token
            ]));

            return redirect("{$frontendUrl}/auth/callback?data={$userData}");

        } catch (\Throwable $e) {
            \Log::error("OAuth {$provider} error", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $frontendUrl = env('FRONTEND_URL', 'https://mifront-1.onrender.com');

            return redirect("{$frontendUrl}/login?error=auth_failed&provider={$provider}");
        }
    }


    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        // Cargar roles si usas Spatie
        if (method_exists($user, 'loadMissing')) {
            $user->loadMissing('roles:id,name');
        }

        // Obtener rol
        $rol = 'user';
        if (method_exists($user, 'getRoleNames')) {
            $roleNames = $user->getRoleNames();
            $rol = $roleNames->first() ?? 'user';
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->map(fn($r) => [
                    'id'   => $r->id,
                    'name' => $r->name,
                ])->values(),
            ],
            'rol' => $rol,
        ], 200);
    }
}