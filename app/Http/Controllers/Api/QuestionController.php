<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    // Listar preguntas (público y admin)
    public function index(Request $request)
    {
        // Eager load de answerOptions
        $query = Question::with('answerOptions')
            ->when($request->test_id, fn($q) => $q->where('test_id', $request->test_id))
            ->orderBy('order');

        return response()->json($query->get(), 200);
    }

    // Guardar nueva pregunta
    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $question->load('answerOptions'),
        ], 201);
    }

    // Mostrar una pregunta por id
    public function show(Question $question)
    {
        return response()->json(
            $question->load('answerOptions'),
            200
        );
    }

    // Actualizar pregunta
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $question->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $question->load('answerOptions'),
        ], 200);
    }

    // Eliminar pregunta
    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(['success' => true], 200);
    }
}
