<?php

namespace App\Http\Controllers\Api;

use App\Models\University;
use App\Models\Career;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FrontController extends Controller
{
    /**
     * Listado de universidades públicas (antes: empresas)
     * GET /public/universities?quantity=10
     */
    public function universities(Request $request)
{
    // Lee la cantidad desde el segmento {quantity} de la ruta
    $limit = (int) ($request->route('quantity') ?? 10);
    if ($limit <= 0) {
        $limit = 10; // fallback seguro
    }

    // Construye el query y aplica el take correcto
    $data = University::query()
        ->orderByDesc('created_at')
        ->take($limit)
        ->get([
            'id',
            'name',
            'acronym',
            'description',
            'country',
            'state',
            'city',
            'logo_url',
            'slug',
        ]);

    return response()->json($data, 200);
}


    /**
     * Búsqueda de universidades por nombre
     * GET /public/universities/search?text=tec
     */
    public function search(Request $request)
    {
        $text = (string) $request->get('text', '');

        $data = University::query()
            ->where('name', 'like', $text . '%')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($data, 200);
    }

    /**
     * Listado de carreras (antes: categorias)
     * GET /public/careers
     */
    public function categorias()
    {
        $data = Career::query()
            ->with(['university']) // si la relación existe en el modelo
            ->get();

        return response()->json($data, 200);
    }

    /**
     * Detalle de una carrera por slug (antes: categoria($slug))
     * GET /public/careers/{slug}
     */
    public function categoria($slug)
    {
        $career = Career::query()
            ->where('slug', $slug)
            ->with(['university'])
            ->first();

        if (!$career) {
            return response()->json(['message' => 'Career not found'], 404);
        }

        // Cursos asociados a la carrera (si no hay relación, consulta directa por career_id)
        $courses = Course::query()
            ->where('career_id', $career->id)
            ->orderByDesc('popularity_score')
            ->get();

        return response()->json([
            'career'     => $career,
            'university' => $career->university,
            'courses'    => $courses,
        ], 200);
    }

    /**
     * NUEVO: Listado de cursos
     * GET /public/courses?quantity=12&text=python&level=Beginner&free=1
     */
    public function cursos(Request $request)
    {
        $q = Course::query();

        // Filtros opcionales y seguros con tu esquema
        if ($request->filled('text')) {
            $text = (string) $request->get('text');
            $q->where(function ($qq) use ($text) {
                $qq->where('title', 'like', '%' . $text . '%')
                   ->orWhere('topic', 'like', '%' . $text . '%')
                   ->orWhere('provider', 'like', '%' . $text . '%');
            });
        }

        if ($request->filled('level')) {
            $q->where('level', $request->get('level'));
        }

        if ($request->filled('free')) {
            $q->where('is_free', (int) $request->get('free') ? 1 : 0);
        }

        if ($request->filled('career_id')) {
            $q->where('career_id', (int) $request->get('career_id'));
        }

        // Relevancia por popularidad si existe, si no, por fecha
        $q->orderByRaw('CASE WHEN popularity_score IS NULL THEN 0 ELSE popularity_score END DESC')
          ->orderByDesc('created_at');

        if ($request->filled('quantity')) {
            $q->take((int) $request->get('quantity'));
        }

        // Incluir datos mínimos enlazados si tienes relaciones en el modelo
        $data = $q->with(['career.university'])->get();

        return response()->json($data, 200);
    }

    /**
     * NUEVO: Detalle de curso por slug
     * GET /public/courses/{slug}
     */
    public function curso($slug)
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->with(['career.university']) // si existen las relaciones
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // Otros cursos relacionados de la misma carrera (para "más como este")
        $related = Course::query()
            ->where('career_id', $course->career_id)
            ->where('id', '<>', $course->id)
            ->orderByDesc('popularity_score')
            ->take(8)
            ->get();

        return response()->json([
            'course'   => $course,
            'related'  => $related,
        ], 200);
    }

    /**
     * NUEVO: Detalle de universidad por slug
     * GET /public/universities/{slug}
     */
    public function universidad($slug)
    {
        $university = University::query()
            ->where('slug', $slug)
            ->first();

        if (!$university) {
            return response()->json(['message' => 'University not found'], 404);
        }

        // Carreras de la universidad
        $careers = Career::query()
            ->where('university_id', $university->id)
            ->get();

        // Cursos destacados de las carreras de esta universidad
        $courses = Course::query()
            ->whereIn('career_id', $careers->pluck('id'))
            ->orderByDesc('popularity_score')
            ->take(12)
            ->get();

        return response()->json([
            'university' => $university,
            'careers'    => $careers,
            'courses'    => $courses,
        ], 200);
    }
}
