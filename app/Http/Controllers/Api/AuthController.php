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
        $response = ["success" => false];

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response = ["error" => $validator->errors()];
            return response()->json($response, 200);
        }

        $input = $request->all();
        $input["password"] = bcrypt($input['password']);

        $user = User::create($input);
        $user->assignRole('user');

        $response["success"] = true;
        return response()->json($response, 200);
    }

    public function login(Request $request)
    {
        $response = ["success" => false];
        $response = ['message' => "Logueado"];

        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $response = ["error" => $validator->errors()];
            return response()->json($response, 200);
        }

        if (auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = auth()->user();
            $user->hasRole('user'); /// add rol 

            $response['token']   = $user->createToken("MI")->plainTextToken;
            $response['user']    = $user;
            $response['message'] = "Logueado";
            $response['success'] = true;
        } else {
            $response['message'] = "Correo o Contraseña incorrectos";
            $response['success'] = false;
        }

        return response()->json($response, 200);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(["message" => "Cerraste sesion"]);
    }

    /**
     * Redirige al proveedor OAuth (google, microsoft, github, etc.)
     */
    public function redirectToProvider(string $provider)
    {
        // Opcional: whitelist para evitar providers no configurados
        $allowed = ['google', 'microsoft'];
        abort_unless(in_array($provider, $allowed, true), 404);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Callback genérico: crea/vincula usuario y emite token Sanctum,
     * luego redirige al SPA con ?token=...&new=0|1 (o ?error=...)
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $providerId = $socialUser->getId();
            $name       = $socialUser->getName() ?: ($socialUser->user['name'] ?? 'Usuario');
            $email      = $socialUser->getEmail();

            // Si el proveedor no entrega email, generamos uno sintético estable
            if (!$email) {
                $email = "{$provider}-{$providerId}@no-email.local";
            }

            // Buscar por email y vincular provider/provider_id; si no existe, crear
            $wasNew = false;
            $user   = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name'        => $name,
                    'email'       => $email,
                    'provider'    => $provider,
                    'provider_id' => $providerId,
                    'password'    => bcrypt(str()->random(40)), // no se usa en social
                ]);
                $wasNew = true;

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user');
                }
            } else {
                // Garantiza la vinculación si aún no existe
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

            // Crea token personal de Sanctum y reenvía al SPA
            $token = $user->createToken('MI')->plainTextToken;

            $redirectBase = config('services.frontend_redirect'); // p.ej. http://localhost:5173/auth/callback
            $qs = http_build_query([
                'token'    => $token,
                'new'      => $wasNew ? 1 : 0,
                'provider' => $provider,
            ]);

            return redirect("{$redirectBase}?{$qs}");
        } catch (\Throwable $e) {
            \Log::error("{$provider} OAuth error", [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'query' => $request->query(),
            ]);

            $redirectBase = config('services.frontend_redirect');
            $qs = http_build_query([
                'error' => "{$provider}_auth_failed",
                'msg'   => 'No fue posible autenticar con ' . ucfirst($provider),
            ]);
            return redirect("{$redirectBase}?{$qs}");
        }
    }

    public function me(Request $request)
    {
        $user = $request->user()->loadMissing('roles:id,name'); // spatie/permission

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
