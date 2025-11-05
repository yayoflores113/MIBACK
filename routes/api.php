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
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\MetabaseController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\Api\DailyExerciseController;
use App\Http\Controllers\Api\Public\LearningPathController;
use App\Http\Controllers\Api\V1\CheckoutController;

// ========================================
// 🔐 AUTH (públicas)
// ========================================
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// OAuth con middleware 'web' para tener sesión
Route::middleware('web')->group(function () {
    Route::get('auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])
        ->whereIn('provider', ['google', 'microsoft']);
    Route::get('auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])
        ->whereIn('provider', ['google', 'microsoft']);
});

// Auth protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']); 
});

// ========================================
// 🌍 PÚBLICAS
// ========================================

// Países
Route::get('public/countries', [CountryController::class, 'index']);

// Tests (solo lectura)
Route::get('public/tests/active', [TestController::class, 'active'])->name('public.tests.active');
Route::apiResource('public/tests', TestController::class)->only(['index', 'show'])->names('public.tests');
Route::apiResource('public/questions', QuestionController::class)->only(['index', 'show'])->names('public.questions');
Route::apiResource('public/answer-options', AnswerOptionController::class)->only(['index', 'show'])->names('public.answer-options');

// Universidades
Route::get('public/universities/{quantity}', [FrontController::class, 'universities'])->whereNumber('quantity');
Route::get('public/universities/{slug}', [FrontController::class, 'universidad'])->where('slug', '^[a-z0-9-]+$');

// Carreras
Route::get('public/careers/{quantity}', [FrontController::class, 'categorias'])->whereNumber('quantity');
Route::get('public/careers/{slug}', [FrontController::class, 'categoria'])->where('slug', '^[a-z0-9-]+$');

// Cursos
Route::get('public/courses', [FrontController::class, 'cursos']);
Route::get('public/courses/{slug}', [FrontController::class, 'curso'])->where('slug', '^[a-z0-9-]+$');

// Planes
Route::get('public/plans', [PlanController::class, 'index']);
Route::get('public/plans/{slug}', [PlanController::class, 'showBySlug'])->where('slug', '^[a-z0-9-]+$');

// Learning Paths
Route::get('public/learning-paths', [LearningPathController::class, 'index'])->name('public.learning-paths.index');
Route::get('public/learning-paths/{slug}', [LearningPathController::class, 'show'])->where('slug', '^[a-z0-9-]+$')->name('public.learning-paths.show');

// Catch-all: DEBE IR AL FINAL
Route::get('public/{slug}', [FrontController::class, 'categoria'])->where('slug', '^[a-z0-9-]+$');

// ========================================
// 👤 USER (protegidas)
// ========================================

Route::middleware('auth:sanctum')->group(function () {
    // Favoritos
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites/{favorite}', [FavoriteController::class, 'destroy']);
    
    // Stripe Checkout
    Route::post('checkout', [StripeController::class, 'checkout']);
    
    // Metabase
    Route::get('metabase/dashboard/{id}', [MetabaseController::class, 'getDashboardUrl']);
    
    // Daily Exercises
    Route::get('user/daily-exercise/today', [DailyExerciseController::class, 'getTodayExercise'])->name('user.daily-exercise.today');
    Route::post('user/daily-exercise/submit', [DailyExerciseController::class, 'submitAnswer'])->name('user.daily-exercise.submit');
    Route::get('user/streak', [DailyExerciseController::class, 'getUserStreak'])->name('user.streak');
    Route::get('user/daily-exercise/history', [DailyExerciseController::class, 'getHistory'])->name('user.daily-exercise.history');
    
    // Test Attempts
    Route::apiResource('user/test-attempts', TestAttemptController::class)
        ->only(['index', 'store', 'show', 'destroy'])->names('user.test-attempts');
    Route::post('user/test-attempts/{testAttempt}/answer', [TestAttemptController::class, 'answer'])
        ->name('user.test-attempts.answer');
    Route::post('user/test-attempts/{testAttempt}/finish', [TestAttemptController::class, 'finish'])
        ->name('user.test-attempts.finish');
    
    // Recomendaciones
    Route::get('user/test-attempts/{testAttempt}/recommendations', [RecommendationController::class, 'byAttempt'])
        ->name('user.test-attempts.recommendations');
    
    // Suscripciones y Pagos
    Route::apiResource('user/subscriptions', SubscriptionController::class)->only(['index', 'show'])->names('user.subscriptions');
    Route::apiResource('user/payments', PaymentController::class)->only(['index', 'show'])->names('user.payments');
    
    // User info
    Route::get('user', fn(Request $request) => $request->user());
});

// ========================================
// 🔧 ADMIN (protegidas + rol admin)
// ========================================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Daily Exercises
    Route::apiResource('daily-exercises', DailyExerciseController::class)->names('admin.daily-exercises');
    
    // Catálogos
    Route::apiResource('universities', UniversityController::class)->names('admin.universities');
    Route::apiResource('careers', CareerController::class)->names('admin.careers');
    Route::apiResource('courses', CourseController::class)->names('admin.courses');
    Route::apiResource('plans', PlanController::class)->names('admin.plans');
    
    // Tests
    Route::apiResource('tests', TestController::class)->names('admin.tests');
    Route::apiResource('questions', QuestionController::class)->names('admin.questions');
    Route::apiResource('answer-options', AnswerOptionController::class)->names('admin.answer-options');
    
    // Recomendaciones
    Route::apiResource('recommendations', RecommendationController::class)->names('admin.recommendations');
    
    // Suscripciones y Pagos
    Route::apiResource('subscriptions', SubscriptionController::class)->only(['store', 'update', 'destroy'])->names('admin.subscriptions');
    Route::apiResource('payments', PaymentController::class)->only(['store', 'update', 'destroy'])->names('admin.payments');
    
    // Usuarios
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'update', 'destroy'])->names('admin.users');
    Route::patch('users/{user}/roles', [UserController::class, 'updateRoles'])->name('admin.users.update-roles');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('admin.users.activate');
    Route::post('users/{user}/revoke-tokens', [UserController::class, 'revokeTokens'])->name('admin.users.revoke-tokens');
});

// ========================================
// 💳 CHECKOUT & WEBHOOKS
// ========================================

Route::post('checkout', [CheckoutController::class, 'createSession']);

// Webhook de Stripe (sin auth, sin CSRF) - Debe estar en web.php o aquí sin middleware
Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->post('stripe/webhook', [CheckoutController::class, 'webhook']);
