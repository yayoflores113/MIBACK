<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('duration')->nullable(); // ej: "6 meses"
            $table->enum('level', ['Principiante', 'Intermedio', 'Avanzado'])->default('Intermedio');
            $table->json('objectives')->nullable(); // Array de objetivos
            $table->json('requirements')->nullable(); // Array de requisitos
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Para ordenar las rutas
            $table->timestamps();
        });

        // Tabla pivot para relacionar learning_paths con courses
        Schema::create('learning_path_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_path_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Orden del curso en la ruta
            $table->timestamps();
            
            $table->unique(['learning_path_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_path_course');
        Schema::dropIfExists('learning_paths');
    }
};