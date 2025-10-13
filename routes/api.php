<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\AnswerOptionController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\TestAttemptController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FrontController;
use App\Http\Controllers\MetabaseController;
use App\Http\Controllers\StripeController;

Route::prefix('v1')->group(function () {

    // =======================
    // PUBLIC
    // =======================

    // ::auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    // Paises
    Route::get('/public/countries', [CountryController::class, 'index']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
    Route::middleware('auth:sanctum')->get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Metabase
    Route::get('/metabase/dashboard/{id}', [MetabaseController::class, 'getDashboardUrl']);

    // Stripe 
    Route::post('/checkout', [StripeController::class, 'checkout']);

    // ::tests (solo lectura) — PONER ANTES DEL CATCH-ALL
    Route::get('/public/tests/active', [TestController::class, 'active'])->name('public.tests.active');
    Route::apiResource('/public/tests',          TestController::class)->only(['index', 'show'])->names('public.tests');
    Route::apiResource('/public/questions',      QuestionController::class)->only(['index', 'show'])->names('public.questions');
    Route::apiResource('/public/answer-options', AnswerOptionController::class)->only(['index', 'show'])->names('public.answer-options');

    // catálogo publico
    // Universidades
    Route::get('/public/universities/{quantity}', [FrontController::class, 'universities'])->whereNumber('quantity');
    Route::get('/public/universities/{slug}',     [FrontController::class, 'universidad'])->where('slug', '^[a-z0-9-]+$');

    // Carreras
    Route::get('/public/careers/{quantity}', [FrontController::class, 'categorias'])->whereNumber('quantity');
    Route::get('/public/careers/{slug}',     [FrontController::class, 'categoria'])->where('slug', '^[a-z0-9-]+$');

    // Cursos
    Route::get('/public/courses',        [FrontController::class, 'cursos']);
    Route::get('/public/courses/{slug}', [FrontController::class, 'curso'])->where('slug', '^[a-z0-9-]+$');

    // Planes
    Route::get('/public/plans',        [PlanController::class, 'index']);
    Route::get('/public/plans/{slug}', [PlanController::class, 'showBySlug'])->where('slug', '^[a-z0-9-]+$');

    // Catch-all: DEBE IR AL FINAL PARA NO INTERCEPTAR RUTAS REALES
    Route::get('/public/{slug}', [FrontController::class, 'categoria'])->where('slug', '^[a-z0-9-]+$');

    // =======================
    // PRIVATE (auth:sanctum)
    // =======================

    // ::user test attempts
    Route::apiResource('user/test-attempts', TestAttemptController::class)
        ->only(['index', 'store', 'show', 'destroy'])->names('user.test-attempts');

    Route::post('user/test-attempts/{testAttempt}/answer', [TestAttemptController::class, 'answer'])
        ->name('user.test-attempts.answer');
    Route::post('user/test-attempts/{testAttempt}/finish', [TestAttemptController::class, 'finish'])
        ->name('user.test-attempts.finish');

    // Recomendaciones por intento
    Route::get('user/test-attempts/{testAttempt}/recommendations', [RecommendationController::class, 'byAttempt'])
        ->name('user.test-attempts.recommendations');

    // ::subs y pagos (user)
    Route::apiResource('user/subscriptions', SubscriptionController::class)->only(['index', 'show'])->names('user.subscriptions');
    Route::apiResource('user/payments',      PaymentController::class)->only(['index', 'show'])->names('user.payments');

    // =======================
    // ADMIN
    // =======================
    Route::apiResource('admin/universities', UniversityController::class)->names('admin.universities');
    Route::apiResource('admin/careers',      CareerController::class)->names('admin.careers');
    Route::apiResource('admin/courses',      CourseController::class)->names('admin.courses');
    Route::apiResource('admin/plans',        PlanController::class)->names('admin.plans');

    Route::apiResource('admin/tests',          TestController::class)->names('admin.tests');
    Route::apiResource('admin/questions',      QuestionController::class)->names('admin.questions');
    Route::apiResource('admin/answer-options', AnswerOptionController::class)->names('admin.answer-options');

    Route::apiResource('admin/recommendations', RecommendationController::class)->names('admin.recommendations');

    Route::apiResource('admin/subscriptions', SubscriptionController::class)->only(['store', 'update', 'destroy'])->names('admin.subscriptions');
    Route::apiResource('admin/payments',      PaymentController::class)->only(['store', 'update', 'destroy'])->names('admin.payments');

    Route::apiResource('admin/users', UserController::class)->only(['index', 'show', 'update', 'destroy'])->names('admin.users');
    Route::patch('admin/users/{user}/roles',        [UserController::class, 'updateRoles'])->name('admin.users.update-roles');
    Route::post('admin/users/{user}/deactivate',    [UserController::class, 'deactivate'])->name('admin.users.deactivate');
    Route::post('admin/users/{user}/activate',      [UserController::class, 'activate'])->name('admin.users.activate');
    Route::post('admin/users/{user}/revoke-tokens', [UserController::class, 'revokeTokens'])->name('admin.users.revoke-tokens');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
