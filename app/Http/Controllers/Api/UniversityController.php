<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUniversityRequest;
use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UniversityController extends Controller
{
    /** Listado con filtros básicos (país/estado/ciudad/búsqueda) */
    public function index()
    {
        $data = University::orderBy("orden")->get(['id', 'name', 'acronym', 'slug', 'country', 'state', 'city', 'website', 'description', 'established_year', 'logo_url']);
        return response()->json($data, 200);
    }

    /** Crear (acepta logo_base64 y guarda en disco public) */
    public function store(StoreUniversityRequest $request) // <- integrado
    {
        // Validar
        $data = new University($request->all());
        // upload image base64
        if ($request->logo_url) {
            $img = $request->logo_url;
            /// proceso para subir la imagen
            $folderPath = "/img/universidades/";
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = $folderPath . Str::slug($request->name) . '.' . $image_type;
            file_put_contents(public_path($file), $image_base64);
            $data->logo_url  =   Str::slug($request->name) . '.' . $image_type;
        }
        $data->slug = Str::slug($request->name);
        $data->save();
        return response()->json($data, 200);
    }

    /** Mostrar */
    public function show($id)
    {
        $data = University::find($id);
        return response()->json($data, 200);
    }

    /** Actualizar (también acepta logo_base64) */
    public function update(UpdateUniversityRequest $request, $id) // <- integrado
    {
        // validación ...
        $data = University::find($id);

        if ($request->file) {
            $img = $request->file;
            $folderPath = "/img/universidades/"; //path location
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            $file = $folderPath . Str::slug($request->name) . '.' . $image_type;
            file_put_contents(public_path($file), $image_base64);
            $data->logo_url  =   Str::slug($request->name) . '.' . $image_type;
        }

        $data->save();
        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = University::find($id);
        // tarea :: eliminar la imagen // unlink()
        $data->delete();
        return response()->json("Borrado", 200);
    }
}
