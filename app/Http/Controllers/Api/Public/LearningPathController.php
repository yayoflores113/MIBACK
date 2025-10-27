<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use Illuminate\Http\Request;

class LearningPathController extends Controller
{
    /**
     * Obtener todas las rutas de aprendizaje activas
     */
    public function index(Request $request)
    {
        try {
            $quantity = $request->input('quantity', 120);
            
            $learningPaths = LearningPath::active()
                ->ordered()
                ->with(['courses' => function($query) {
                    $query->select('courses.id', 'courses.title', 'courses.slug', 'courses.description');
                }])
                ->take($quantity)
                ->get()
                ->map(function ($path) {
                    return [
                        'id' => $path->id,
                        'title' => $path->title,
                        'slug' => $path->slug,
                        'description' => $path->description,
                        'image' => $path->image,
                        'duration' => $path->duration,
                        'level' => $path->level,
                        'courses_count' => $path->courses->count(),
                        'created_at' => $path->created_at,
                        'updated_at' => $path->updated_at,
                    ];
                });

            return response()->json($learningPaths, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las rutas de aprendizaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una ruta de aprendizaje por slug
     */
    public function show($slug)
    {
        try {
            $learningPath = LearningPath::where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (!$learningPath) {
                return response()->json([
                    'message' => 'Ruta de aprendizaje no encontrada'
                ], 404);
            }

            // Cargar cursos con todos los campos necesarios
            $courses = $learningPath->courses()
                ->select(
                    'courses.id',
                    'courses.title',
                    'courses.slug',
                    'courses.description',
                    'courses.card_image_url',
                    'courses.hours',
                    'courses.difficulty',
                    'courses.level',
                    'courses.rating_avg',
                    'courses.rating_count',
                    'courses.is_free',
                    'courses.is_premium',
                    'courses.price_cents',
                    'courses.provider',
                    'courses.topic'
                )
                ->orderBy('learning_path_course.order')
                ->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'title' => $course->title,
                        'slug' => $course->slug,
                        'description' => $course->description,
                        'card_image_url' => $course->card_image_url,
                        'hours' => $course->hours,
                        'difficulty' => $course->difficulty ?? $course->level,
                        'rating_avg' => $course->rating_avg,
                        'rating_count' => $course->rating_count,
                        'is_free' => (bool) $course->is_free,
                        'is_premium' => (bool) $course->is_premium,
                        'price_cents' => $course->price_cents,
                        'provider' => $course->provider,
                        'topic' => $course->topic,
                    ];
                });

            // Calcular estadísticas
            $totalCourses = $courses->count();
            $freeCourses = $courses->where('is_free', true)->count();
            $premiumCourses = $courses->where('is_premium', true)->count();
            $totalHours = $courses->sum('hours');

            $stats = [
                'total_courses' => $totalCourses,
                'free_courses' => $freeCourses,
                'premium_courses' => $premiumCourses,
                'total_hours' => $totalHours,
            ];

            return response()->json([
                'data' => [
                    'id' => $learningPath->id,
                    'title' => $learningPath->title,
                    'slug' => $learningPath->slug,
                    'description' => $learningPath->description,
                    'subtitle' => $learningPath->description, // Puedes agregar un campo subtitle en la BD
                    'image' => $learningPath->image,
                    'icon' => '🚀', // Puedes agregar un campo icon en la BD
                    'duration' => $learningPath->duration,
                    'level' => $learningPath->level,
                    'difficulty' => strtolower($learningPath->level), // principiante, intermedio, avanzado
                    'estimated_hours' => $totalHours,
                    'goal' => $learningPath->description, // Puedes agregar un campo goal en la BD
                    'objectives' => $learningPath->objectives ?? [],
                    'requirements' => $learningPath->requirements ?? [],
                    'courses' => $courses,
                    'created_at' => $learningPath->created_at,
                    'updated_at' => $learningPath->updated_at,
                ],
                'stats' => $stats
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error en LearningPathController@show: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al obtener la ruta de aprendizaje',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}