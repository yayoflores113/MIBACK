<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Test vocacional:
     * - traits: rasgos/dimensiones (intereses, habilidades, estilos)
     * - tests: definición de un test (versión, activo)
     * - questions: preguntas del test
     * - answer_options: opciones por pregunta (pueden sumar a un rasgo)
     * - test_attempts: intentos de usuarios (o invitados)
     * - test_answers: respuestas por intento
     * - attempt_trait_scores: puntajes agregados por rasgo en un intento
     */
    public function up(): void
    {
        // RASGOS/DIMENSIONES
        Schema::create('traits', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();        // Código técnico (p.ej., RIASEC_R, ANALITICA)
            $table->string('name');                  // Nombre legible
            $table->text('description')->nullable(); // Descripción del rasgo
            $table->timestamps();
        });

        // DEFINICIÓN DEL TEST
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();        // Identificador del test (p.ej., MI_V1)
            $table->string('title');                 // Título del test
            $table->unsignedSmallInteger('version')->default(1); // Versión
            $table->boolean('is_active')->default(true); // Si está disponible para aplicar
            $table->text('description')->nullable(); // Descripción general
            $table->timestamps();
        });

        // PREGUNTAS
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete(); // A qué test pertenece
            $table->text('text');                          // Enunciado de la pregunta
            $table->string('type', 30)->default('single_choice'); // single_choice|multiple_choice|likert|text
            $table->unsignedSmallInteger('order')->default(0);    // Orden de presentación
            $table->timestamps();

            $table->index(['test_id', 'order']);
        });

        // OPCIONES DE RESPUESTA
        Schema::create('answer_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete(); // Pregunta a la que pertenece
            $table->text('text');                       // Texto de la opción
            $table->foreignId('trait_id')->nullable()->constrained()->nullOnDelete(); // Rasgo al que aporta (si aplica)
            $table->integer('score')->default(0);       // Puntuación que suma a ese rasgo
            $table->unsignedSmallInteger('order')->default(0); // Orden dentro de la pregunta
            $table->timestamps();

            $table->index(['question_id', 'order']);
        });

        // INTENTOS DE TEST
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();  // Test aplicado
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Usuario (o null si invitado)
            $table->timestamp('started_at')->nullable();   // Inicio del intento
            $table->timestamp('finished_at')->nullable();  // Fin del intento
            $table->json('result_summary')->nullable();    // Resumen de resultados (snapshot opcional)
            $table->timestamps();

            $table->index(['test_id', 'user_id']);
        });

        // RESPUESTAS (una fila por selección o valor)
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->cascadeOnDelete(); // Intento
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();               // Pregunta
            $table->foreignId('answer_option_id')->nullable()->constrained()->nullOnDelete(); // Opción elegida (si aplica)
            $table->integer('answer_value')->nullable();  // Para escalas Likert (1..5) u otro valor numérico
            $table->integer('score')->default(0);         // Puntaje derivado de la respuesta
            $table->timestamps();

            // Evita duplicidad (misma pregunta/opción dentro del mismo intento)
            $table->unique(['attempt_id', 'question_id', 'answer_option_id']);
            $table->index(['attempt_id', 'question_id']);
        });

        // PUNTAJES POR RASGO (agregado por intento)
        Schema::create('attempt_trait_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->cascadeOnDelete(); // Intento evaluado
            $table->foreignId('trait_id')->constrained()->cascadeOnDelete();                  // Rasgo evaluado
            $table->integer('score')->default(0);           // Puntuación acumulada
            $table->decimal('normalized_score', 5, 2)->nullable(); // Puntuación normalizada 0..100
            $table->timestamps();

            $table->unique(['attempt_id', 'trait_id']);     // 1 fila por rasgo en el intento
            $table->index(['trait_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_trait_scores');
        Schema::dropIfExists('test_answers');
        Schema::dropIfExists('test_attempts');
        Schema::dropIfExists('answer_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('tests');
        Schema::dropIfExists('traits');
    }
};
