<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestAttemptRequest;
use App\Http\Requests\UpdateTestAttemptRequest;
use App\Http\Requests\AnswerTestAttemptRequest;
use App\Models\{TestAttempt, TestAnswer, Question, AnswerOption, AttemptTraitScore};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TestAttemptController extends Controller
{
    /** Listar intentos (filtrable por user_id o test_id) */
    public function index(Request $request)
    {
        $q = TestAttempt::query()->with('test:id,title,version');

        if ($uid = $request->integer('user_id')) $q->where('user_id', $uid);
        if ($tid = $request->integer('test_id')) $q->where('test_id', $tid);

        return response()->json($q->latest()->paginate(20));
    }

    /** Iniciar intento */
    public function store(StoreTestAttemptRequest $request)
    {
        $data = $request->validated();
        $attempt = TestAttempt::create([
            'test_id'    => $data['test_id'],
            'user_id'    => $data['user_id'] ?? null,
            'started_at' => Carbon::now(),
        ]);

        return response()->json($attempt, 201);
    }

    /** Ver intento + respuestas + puntajes */
    public function show(TestAttempt $testAttempt)
    {
        $testAttempt->load(['answers', 'traitScores', 'test:id,title,version']);
        return response()->json($testAttempt);
    }

    /** Actualizar campos del intento (parcial) */
    public function update(UpdateTestAttemptRequest $request, TestAttempt $testAttempt)
    {
        // Nota: esto NO recalcula puntajes. Para cerrar y calcular usa finish().
        $testAttempt->update($request->validated());
        return response()->json($testAttempt);
    }

    /** Registrar/actualizar respuesta a una pregunta */
    public function answer(AnswerTestAttemptRequest $request, TestAttempt $testAttempt)
    {
        $data = $request->validated();

        // Validar pertenencia de la pregunta al test del intento
        $question = Question::where('id', $data['question_id'])
            ->where('test_id', $testAttempt->test_id)
            ->first();
        if (!$question) {
            return response()->json(['message' => 'La pregunta no pertenece a este test'], 422);
        }

        $score = 0;
        $answerOptionId = $data['answer_option_id'] ?? null;
        if ($answerOptionId) {
            $option = AnswerOption::find($answerOptionId);
            if (!$option || $option->question_id != $question->id) {
                return response()->json(['message' => 'La opción no corresponde a la pregunta'], 422);
            }
            $score = (int) $option->score;
        }

        // upsert: evita violar la unique (attempt_id, question_id, answer_option_id)
        $answer = TestAnswer::updateOrCreate(
            [
                'attempt_id'       => $testAttempt->id,
                'question_id'      => $question->id,
                'answer_option_id' => $answerOptionId,
            ],
            [
                'answer_value' => $data['answer_value'] ?? null,
                'score'        => $score,
            ]
        );

        return response()->json($answer);
    }

    /** Finalizar intento y calcular puntajes por rasgo */
    public function finish(TestAttempt $testAttempt)
    {
        if ($testAttempt->finished_at) {
            return response()->json(['message' => 'El intento ya está finalizado'], 422);
        }

        DB::transaction(function () use ($testAttempt) {
            // Agregado por rasgo a partir de las opciones elegidas
            $sums = DB::table('test_answers as ta')
                ->join('answer_options as ao', 'ao.id', '=', 'ta.answer_option_id')
                ->select('ao.trait_id', DB::raw('SUM(ta.score) as total'))
                ->where('ta.attempt_id', $testAttempt->id)
                ->whereNotNull('ao.trait_id')
                ->groupBy('ao.trait_id')
                ->get();

            // Elimina previos y crea nuevos puntajes
            AttemptTraitScore::where('attempt_id', $testAttempt->id)->delete();

            $max = max($sums->pluck('total')->all() ?: [1]); // evita div/0
            foreach ($sums as $row) {
                AttemptTraitScore::create([
                    'attempt_id'       => $testAttempt->id,
                    'trait_id'         => $row->trait_id,
                    'score'            => (int) $row->total,
                    'normalized_score' => round(($row->total / $max) * 100, 2),
                ]);
            }

            // Resumen simple: top 3 rasgos
            $top = AttemptTraitScore::with('trait:id,code,name')
                ->where('attempt_id', $testAttempt->id)
                ->orderByDesc('normalized_score')
                ->limit(3)
                ->get(['trait_id', 'score', 'normalized_score']);

            $testAttempt->result_summary = [
                'top_traits' => $top->map(function ($t) {
                    return [
                        'trait_id'   => $t->trait_id,
                        'code'       => $t->trait->code ?? null,
                        'name'       => $t->trait->name ?? null,
                        'score'      => $t->score,
                        'normalized' => $t->normalized_score,
                    ];
                })->values(),
            ];
            $testAttempt->finished_at = now();
            $testAttempt->save();
        });

        $testAttempt->load(['traitScores.trait:id,code,name']);
        return response()->json($testAttempt);
    }

    public function destroy(TestAttempt $testAttempt)
    {
        $testAttempt->delete();
        return response()->json(['deleted' => true]);
    }
}
