<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\StoreRecommendationRequest;
use App\Http\Requests\UpdateRecommendationRequest;

use App\Models\Recommendation;
use App\Models\TestAttempt;
use App\Models\Career;
use App\Models\Course;
use App\Models\University;

class RecommendationController extends Controller
{
    /**
     * GET /admin/recommendations
     * (Opcional) Listado simple con filtros básicos.
     */
    public function index(Request $request)
    {
        $query = Recommendation::query()
            ->when($request->filled('entity_type'), fn($q) => $q->where('entity_type', $request->string('entity_type')))
            ->when($request->filled('title'), fn($q) => $q->where('title', 'like', '%'.$request->string('title').'%'));

        // puedes paginar o devolver todo según prefieras
        $data = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return response()->json($data, 200);
    }

    /**
     * POST /admin/recommendations
     * Crea una recomendación "admin" usando StoreRecommendationRequest.
     */
    public function store(StoreRecommendationRequest $request)
    {
        $payload = $request->validated();

        $rec = Recommendation::create([
            'title'       => $payload['title'],
            'entity_type' => $payload['entity_type'], // 'career' | 'course' | 'university'
            'entity_id'   => $payload['entity_id'],
            'weight'      => $payload['weight'] ?? null,
            'filters'     => $payload['filters'] ?? null, // array/json opcional
        ]);

        return response()->json($rec, 201);
    }

    /**
     * GET /admin/recommendations/{recommendation}
     */
    public function show(Recommendation $recommendation)
    {
        return response()->json($recommendation, 200);
    }

    /**
     * PUT/PATCH /admin/recommendations/{recommendation}
     * Actualiza usando UpdateRecommendationRequest.
     */
    public function update(UpdateRecommendationRequest $request, Recommendation $recommendation)
    {
        $payload = $request->validated();

        // Solo actualizamos lo que venga en el payload
        $recommendation->fill($payload);
        $recommendation->save();

        return response()->json($recommendation, 200);
    }

    /**
     * DELETE /admin/recommendations/{recommendation}
     */
    public function destroy(Recommendation $recommendation)
    {
        $recommendation->delete();
        return response()->json(['message' => 'Recommendation eliminada'], 200);
    }

    /**
     * GET /user/test-attempts/{testAttempt}/recommendations
     * Devuelve ranking personalizado en base al perfil normalizado del intento.
     *
     * NOTA:
     * - No altera tu pipeline de TestAttempt (ya acumulas/normalizas en finish).
     * - Solo lee result_summary y compara con trait_profile (JSON/array) de catálogos.
     * - Requiere que el intento pertenezca al usuario autenticado.
     */
    public function byAttempt(Request $request, TestAttempt $testAttempt)
    {
        // Validar ownership del intento
        $user = $request->user();
        if (!$user || $testAttempt->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Perfil normalizado guardado durante /finish (p.ej. ["analitico"=>0.8, "creativo"=>0.4, ...])
        $attemptProfile = $testAttempt->result_summary ?? [];
        if (empty($attemptProfile) || !is_array($attemptProfile)) {
            return response()->json([
                'message' => 'Attempt has no normalized profile yet. Finish the test first.'
            ], 422);
        }

        // Similitud tipo coseno (simple) sobre claves comunes
        $similarity = function (array $a, ?array $b): ?float {
            if (!$b || empty($b)) return null;
            $dot = 0.0; $na = 0.0; $nb = 0.0; $common = false;
            foreach ($a as $k => $va) {
                $vb = $b[$k] ?? null;
                $na += ($va * $va);
                if ($vb !== null) {
                    $dot += ($va * (float)$vb);
                    $common = true;
                }
            }
            foreach ($b as $vb) { $nb += ($vb * $vb); }
            if (!$common || $na == 0.0 || $nb == 0.0) return 0.0;
            return $dot / (sqrt($na) * sqrt($nb));
        };

        // Límites opcionales por query string
        $limitCareers = (int)($request->query('limit_careers', 10));
        $limitCourses = (int)($request->query('limit_courses', 10));
        $limitUniversities = (int)($request->query('limit_universities', 10));

        // Cargar catálogos; trait_profile puede ser JSON en DB → lo casteas en modelos si quieres
        $careers = Career::query()->select(['id','name','slug','area','level','trait_profile'])->get();
        $courses = Course::query()->select(['id','name','slug','level','trait_profile'])->get();
        $universities = University::query()->select(['id','name','slug','city','state','country','trait_profile'])->get();

        $rank = fn($rows) => collect($rows)
            ->map(function ($row) use ($attemptProfile, $similarity) {
                // Soportar JSON en string o array ya casteado
                $profile = is_array($row->trait_profile)
                    ? $row->trait_profile
                    : (json_decode($row->trait_profile ?? '[]', true) ?: []);

                $score = $similarity($attemptProfile, $profile);

                return [
                    'id'    => $row->id,
                    'name'  => $row->name ?? $row->title ?? '',
                    'slug'  => $row->slug ?? null,
                    'meta'  => [
                        'area'    => $row->area ?? null,
                        'level'   => $row->level ?? null,
                        'city'    => $row->city ?? null,
                        'state'   => $row->state ?? null,
                        'country' => $row->country ?? null,
                    ],
                    'score' => $score ?? 0.0,
                ];
            })
            ->filter(fn($r) => $r['score'] !== null)
            ->sortByDesc('score')
            ->values();

        return response()->json([
            'attempt_id'   => $testAttempt->id,
            'profile'      => $attemptProfile,
            'careers'      => $rank($careers)->take($limitCareers),
            'courses'      => $rank($courses)->take($limitCourses),
            'universities' => $rank($universities)->take($limitUniversities),
        ], 200);
    }
}
