<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /*
     * - 'title' => El nombre del curso.
     * - 'provider' => La entidad o persona que ofrece el curso.
     * - 'url' => Enlace directo al curso en la página web.
     * -'topic' => Área o categoría principal del curso (por ejemplo: "Desarrollo web", "Marketing digital").
     * 'level' => El nivel de dificultad del curso ('todos','principiante','intermedio','experto').
     * 'difficulty' => Refleja la complejidad o reto que representa el curso.
     * 'hours' => La duración estimada del curso en horas.
     * 'is_premium' => Indica si el curso es de pago o tiene contenido exclusivo para usuarios premium.
     * 'is_free' => Indica si el curso es completamente gratuito.
     * 'price_cents' => El precio del curso en céntimos de la moneda (por ejemplo, 999 significa 9.99 unidades de la moneda).
     * 'rating_avg' => La calificación promedio que los estudiantes le han dado al curso (de 1 a 5 estrellas).
     * 'rating_count' => Número total de calificaciones dadas al curso.
     * 'popularity_score' => Puntaje de popularidad, basado en métricas como el número de inscripciones o interacciones con el curso.
     * 'published_at' => Fecha en la que el curso fue publicado en la plataforma.
     * 'card_image_url' => URL de la imagen que se utiliza como portada o miniatura del curso en la página.
     * 'description' => Breve descripción o resumen del contenido del curso.
     */
    public function index()
    {
        $data = Course::orderBy("orden")->get([
            'id',
            'career_id',
            'title',
            'provider',
            'topic',
            'level',
            'rating_avg',
            'rating_count',
            'popularity_score',
            'url',
            'difficulty',
            'hours',
            'is_premium',
            'is_free',
            'price_cents',
            'published_at',
            'description',
            'card_image_url',
        ]);
        return response()->json($data, 200);
    }

    public function store(StoreCourseRequest $request)
    {
        $data = new Course($request->all());

        $baseName = $request->title ?? $request->nombre ?? 'curso';

        // Acepta base64 en card_image_url (si la mandas así)
        if ($request->card_image_url && str_contains($request->card_image_url, ';base64,')) {
            $img = $request->card_image_url;

            $folderRel = "/img/cursos/";
            $folderAbs = public_path($folderRel);
            if (!is_dir($folderAbs)) {
                @mkdir($folderAbs, 0755, true);
            }

            $parts = explode(';base64,', $img, 2);
            $meta  = $parts[0] ?? '';
            $raw   = $parts[1] ?? null;

            if ($raw && str_contains($meta, 'image/')) {
                $typeParts  = explode('image/', $meta, 2);
                $image_type = $typeParts[1] ?? 'png';
                $binary     = base64_decode($raw);

                $filename = Str::slug($baseName) . '.' . $image_type;
                file_put_contents($folderAbs . $filename, $binary);

                $data->card_image_url = $filename;
            }
        }

        $data->slug = Str::slug($baseName);
        $data->save();

        return response()->json($data, 200);
    }

    public function show($id)
    {
        $data = Course::find($id);
        return response()->json($data, 200);
    }

    public function update(UpdateCourseRequest $request, $id)
    {
        $data = Course::find($id);

        // Compat: 'file' o 'card_image_url' en base64
        $img = $request->file ?: $request->card_image_url;

        if ($img && str_contains($img, ';base64,')) {
            $folderRel = "/img/cursos/";
            $folderAbs = public_path($folderRel);
            if (!is_dir($folderAbs)) {
                @mkdir($folderAbs, 0755, true);
            }

            $parts = explode(';base64,', $img, 2);
            $meta  = $parts[0] ?? '';
            $raw   = $parts[1] ?? null;

            if ($raw && str_contains($meta, 'image/')) {
                $typeParts  = explode('image/', $meta, 2);
                $image_type = $typeParts[1] ?? 'png';
                $binary     = base64_decode($raw);

                $baseName = $request->title ?? $request->nombre ?? ($data->title ?? 'curso');
                $filename = Str::slug($baseName) . '.' . $image_type;

                file_put_contents($folderAbs . $filename, $binary);
                $data->card_image_url = $filename;
            }
        }
        $data->update($request->validated());

        $data->save();
        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = Course::find($id);
        // tarea :: eliminar la imagen // unlink()
        $data->delete();
        return response()->json("Borrado", 200);
    }
}
