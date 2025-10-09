<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnswerOptionRequest;
use App\Http\Requests\UpdateAnswerOptionRequest;
use App\Models\AnswerOption;
use Illuminate\Http\Request;

class AnswerOptionController extends Controller
{
    // Listado de opciones
    public function index(Request $request)
    {
        $query = AnswerOption::with('question')
            ->when($request->question_id, fn($q) => $q->where('question_id', $request->question_id))
            ->orderBy('order');

        return response()->json($query->get(), 200);
    }

    // Crear nueva opción
    public function store(StoreAnswerOptionRequest $request)
    {
        $option = AnswerOption::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $option->load('question'),
        ], 201);
    }

    // Mostrar una opción
    public function show(AnswerOption $answerOption)
    {
        return response()->json($answerOption->load('question'), 200);
    }

    // Actualizar opción
    public function update(UpdateAnswerOptionRequest $request, AnswerOption $answerOption)
    {
        $answerOption->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $answerOption->load('question'),
        ], 200);
    }

    // Eliminar opción
    public function destroy(AnswerOption $answerOption)
    {
        $answerOption->delete();

        return response()->json(['success' => true], 200);
    }
}
