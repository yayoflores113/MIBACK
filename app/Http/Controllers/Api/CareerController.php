<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCareerRequest;
use App\Http\Requests\UpdateCareerRequest;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerController extends Controller
{
    /**
     * Listado con filtros y orden:
     * - id
     * - university_id
     * - name
     * - level (grado maximo de estudio de la carrera)
     * - levels (grados que tiene la carrera: licenciatura, TSU, ingenieria, maestría, doctorado)
     * - area
     * - duration_terms (duración en términos)
     * - terms_unit (unidad de tiempo de duración: cuatrimestre, semestre)
     * - modality (modalidad: presencial, online, mixta)
     * - description
     */
    public function index()
    {
        $data = Career::orderBy("orden")->get(['id','slug', 'university_id', 'name', 'level', 'levels', 'area', 'duration_terms', 'terms_unit', 'modality', 'description','career_url']);
        return response()->json($data, 200);
    }

    /** Crear (acepta logo_base64 y guarda en disco public) */
    public function store(StoreCareerRequest $request) // <- integrado
    {
        // Validar
        $data = new Career($request->all());
        // upload image base64
        if ($request->career_url) {
            $img = $request->career_url;
            /// proceso para subir la imagen
            $folderPath = "/img/carreras/";
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = $folderPath . Str::slug($request->nombre) . '.' . $image_type;
            file_put_contents(public_path($file), $image_base64);
            $data->career_url  =   Str::slug($request->nombre) . '.' . $image_type;
        }
        $data->slug = Str::slug($request->nombre);
        $data->save();
        return response()->json($data, 200);
    }

    /** Mostrar */
    public function show($id)
    {
        $data = Career::find($id);
        return response()->json($data, 200);
    }

    /** Actualizar (también acepta logo_base64) */
    public function update(UpdateCareerRequest $request, $id) // <- integrado
    {
        // validación ...
        $data = Career::find($id);

        if ($request->file) {
            $img = $request->file;
            $folderPath = "/img/carreras/"; //path location
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            $file = $folderPath . Str::slug($request->nombre) . '.' . $image_type;
            file_put_contents(public_path($file), $image_base64);
            $data->career_url  =   Str::slug($request->nombre) . '.' . $image_type;
        }

        $data->save();
        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = Career::find($id);
        // tarea :: eliminar la imagen // unlink()
        $data->delete();
        return response()->json("Borrado", 200);
    }
}
