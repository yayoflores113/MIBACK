<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyExercise;
use App\Models\UserExerciseAttempt;
use App\Models\UserStreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DailyExerciseController extends Controller
{
    /**
     * Obtener el ejercicio del día
     */
    public function getTodayExercise(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        // Verificar si ya completó el ejercicio de hoy
        $completedToday = UserExerciseAttempt::byUser($user->id)
            ->forDate($today)
            ->exists();

        if ($completedToday) {
            return response()->json([
                'completed' => true,
                'message' => 'Ya completaste el ejercicio de hoy. ¡Regresa mañana para un nuevo desafío!'
            ]);
        }

        // Obtener un ejercicio aleatorio para hoy
        $exercise = DailyExercise::forToday()
           ->with('course:id,title')
            ->inRandomOrder()
            ->first();

        if (!$exercise) {
            return response()->json([
                'completed' => false,
                'exercise' => null,
                'message' => 'No hay ejercicios disponibles hoy.'
            ], 404);
        }

        // No enviar la solución al frontend
        return response()->json([
            'completed' => false,
            'exercise' => [
                'id' => $exercise->id,
                'title' => $exercise->title,
                'description' => $exercise->description,
                'type' => $exercise->type,
                'content' => $exercise->content,
                'difficulty' => $exercise->difficulty,
                'points' => $exercise->points,
               'course' => $exercise->course->title,
            ]
        ]);
    }

    /**
     * Enviar respuesta del ejercicio
     */
    public function submitAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exercise_id' => 'required|exists:daily_exercises,id',
            'answer' => 'required|string',
            'time_spent' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $exercise = DailyExercise::findOrFail($request->exercise_id);
        $today = now()->toDateString();

        // Verificar si ya completó este ejercicio hoy
        $alreadyCompleted = UserExerciseAttempt::where('user_id', $user->id)
            ->where('exercise_id', $exercise->id)
            ->where('completed_date', $today)
            ->exists();

        if ($alreadyCompleted) {
            return response()->json([
                'message' => 'Ya completaste este ejercicio.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Validar respuesta
            $isCorrect = $exercise->validateAnswer($request->answer);
            
            // Calcular puntos
            $pointsEarned = $isCorrect ? $exercise->points : floor($exercise->points * 0.3);

            // Guardar intento
            $attempt = UserExerciseAttempt::create([
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'user_answer' => $request->answer,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'time_spent' => $request->time_spent,
                'completed_date' => $today,
            ]);

            // Actualizar o crear racha del usuario
            $streak = UserStreak::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'total_exercises_completed' => 0,
                    'total_points' => 0,
                ]
            );

            $streak->updateStreak();
            $streak->addPoints($pointsEarned);

            DB::commit();

            return response()->json([
                'correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'solution' => $isCorrect ? null : $exercise->solution,
                'message' => $isCorrect 
                    ? '¡Excelente! Tu respuesta es correcta.'
                    : 'No es la respuesta correcta, pero ganas puntos por el esfuerzo.',
                'streak' => [
                    'current' => $streak->current_streak,
                    'longest' => $streak->longest_streak,
                    'total_points' => $streak->total_points,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar la respuesta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener racha del usuario
     */
    public function getUserStreak(Request $request)
    {
        $user = $request->user();
        
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_exercises_completed' => 0,
                'total_points' => 0,
            ]
        );

        return response()->json([
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'total_exercises_completed' => $streak->total_exercises_completed,
            'total_points' => $streak->total_points,
            'last_completion_date' => $streak->last_completion_date,
        ]);
    }

    /**
     * Historial de ejercicios completados
     */
    public function getHistory(Request $request)
    {
        $user = $request->user();
        
        $history = UserExerciseAttempt::with('exercise:id,title,type,difficulty,points')
            ->byUser($user->id)
            ->orderBy('completed_date', 'desc')
            ->paginate(20);

        return response()->json($history);
    }

    // ========================================
    // MÉTODOS ADMIN
    // ========================================

    /**
     * ADMIN: Listar todos los ejercicios
     */
    public function index(Request $request)
    {
        $query = DailyExercise::with('course:id,name');

        // Filtros opcionales
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $exercises = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($exercises);
    }

    /**
     * ADMIN: Crear ejercicio
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:code,theory,problem,quiz',
            'content' => 'required',
            'solution' => 'required|string',
            'difficulty' => 'required|integer|min:1|max:5',
            'points' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'available_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exercise = DailyExercise::create($request->all());

        return response()->json([
            'message' => 'Ejercicio creado exitosamente',
            'exercise' => $exercise
        ], 201);
    }

    /**
     * ADMIN: Mostrar ejercicio
     */
    public function show($id)
    {
        $exercise = DailyExercise::with('course')->findOrFail($id);
        return response()->json($exercise);
    }

    /**
     * ADMIN: Actualizar ejercicio
     */
    public function update(Request $request, $id)
    {
        $exercise = DailyExercise::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'course_id' => 'sometimes|exists:courses,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|in:code,theory,problem,quiz',
            'content' => 'sometimes',
            'solution' => 'sometimes|string',
            'difficulty' => 'sometimes|integer|min:1|max:5',
            'points' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'available_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exercise->update($request->all());

        return response()->json([
            'message' => 'Ejercicio actualizado exitosamente',
            'exercise' => $exercise
        ]);
    }

    /**
     * ADMIN: Eliminar ejercicio
     */
    public function destroy($id)
    {
        $exercise = DailyExercise::findOrFail($id);
        $exercise->delete();

        return response()->json([
            'message' => 'Ejercicio eliminado exitosamente'
        ]);
    }
}