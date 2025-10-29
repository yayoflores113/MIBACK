<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // ✅ IMPORT CORRECTO

use Exception;

class NotificacionesController extends Controller
{
    /**
     * Obtener notificaciones para el usuario logueado
     */
    public function getNotificaciones(Request $request)
{
    $user = $request->user();
    Log::info('Usuario autenticado:', ['user' => $user]);

    if (!$user) {
        return response()->json(['message' => 'No autenticado'], 401);
    }

    $isSuper = $user->email === 'utm@gmail.com' || $user->id_rol == 3;

    $notificaciones = $isSuper 
        ? Notificacion::orderBy('id', 'desc')->get() 
        : Notificacion::where('id_usuario', $user->id)->orderBy('id', 'desc')->get();

    Log::info('Notificaciones obtenidas:', ['count' => $notificaciones->count()]);

    return response()->json($notificaciones);
}

    /**
     * Marcar notificación como leída
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        // Solo puede marcar sus propias notificaciones (o superusuario)
        if ($notificacion->id_usuario != $user->id && $user->email !== 'utm@gmail.com') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $notificacion->leido = true;
        $notificacion->save();

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    /**
     * Eliminar notificación
     */
    public function delete(Request $request, $id)
    {
        $user = $request->user();

        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }

        if ($notificacion->id_usuario != $user->id && $user->email !== 'utm@gmail.com') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $notificacion->delete();

        return response()->json(['message' => 'Notificación eliminada']);
    }





    /**
     * Enviar nueva notificación AL SUPERUSUARIO (id_rol = 3)
     */
    public function send(Request $request)
    {
        try {
            $validated = $request->validate([
                'mensaje' => 'required|string|max:255',
            ]);

            if (!auth()->check()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }

            $superusuario = User::where('id_rol', 3)
                ->orWhere('email', 'utm@gmail.com')
                ->first();

            if (!$superusuario) {
                Log::error('No se encontró el superusuario');
                return response()->json(['error' => 'No se encontró el superusuario'], 404);
            }

            $notificacion = Notificacion::create([
                'id_usuario' => $superusuario->id,
                'mensaje' => $validated['mensaje'],
                'leido' => false,
            ]);

            Log::info('Notificación creada para superusuario', [
                'superusuario_id' => $superusuario->id,
                'mensaje' => $validated['mensaje'],
                'creado_por' => auth()->user()->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificación enviada al superusuario',
                'data' => $notificacion
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Error al crear notificación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al crear la notificación',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar notificación como leída
     */
      
}