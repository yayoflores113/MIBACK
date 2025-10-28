<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
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

        // Autenticar automáticamente después del registro
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'rol' => 'user',
            'token' => null, // Añadido para consistencia
        ], 201);
    }

    /**
    * Login con EMAIL/PASSWORD (Sanctum Stateful - NO tokens)
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

        // Intentar autenticar
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Obtener el rol usando Spatie
            $rol = 'user'; // valor por defecto
            if (method_exists($user, 'getRoleNames')) {
                $roleNames = $user->getRoleNames();
                $rol = $roleNames->first() ?? 'user';
            }

            return response()->json([
                'success' => true,
                'message' => 'Logueado exitosamente',
                'user' => $user,
                'rol' => $rol,
                'token' => null, // Esto evita el "undefined" en frontend
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Correo o Contraseña incorrectos'
        ], 401);
    }

    /**
     * Logout (Invalida la sesión)
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

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
     * Callback OAuth - Autentica por SESIÓN (NO tokens)
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

            // CRÍTICO: Autenticar por SESIÓN (Sanctum Stateful)
            Auth::login($user);
            $request->session()->regenerate();

            // Obtener rol
            $rol = 'user';
            if (method_exists($user, 'getRoleNames')) {
                $roleNames = $user->getRoleNames();
                $rol = $roleNames->first() ?? 'user';
            }

            // CAMBIO IMPORTANTE: Redirigir a una ruta específica del frontend
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            
            // Codificar datos del usuario en base64 para pasarlos de forma segura
            $userData = base64_encode(json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'provider' => $provider,
                'new' => $wasNew,
                'rol' => $rol
            ]));

            return redirect("{$frontendUrl}/auth/callback?data={$userData}");

        } catch (\Throwable $e) {
            \Log::error("OAuth {$provider} error", [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            
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
        ], 200);
    }
}