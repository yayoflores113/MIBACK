<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo académico:
     * - universities: universidades disponibles
     * - careers: carreras asociadas a una universidad
     * - courses: cursos asociados a una carrera (pueden ser internos o de terceros)
     */
    public function up(): void
    {
        // UNIVERSIDADES
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Nombre oficial de la universidad
            $table->string('acronym', 50)->nullable();    // Siglas (p.ej., UNAM)
            $table->string('slug')->unique();             // Identificador amigable en URLs
            $table->string('country', 80)->nullable();    // País
            $table->string('state', 80)->nullable();      // Estado/Provincia
            $table->string('city', 80)->nullable();       // Ciudad
            $table->string('website')->nullable();        // Sitio web oficial
            $table->text('description')->nullable();      // Descripción breve
            $table->string('logo_url')->nullable();       // URL del logo
            $table->unsignedSmallInteger('established_year')->nullable(); // Año de fundación
            $table->timestamps();
            $table->softDeletes();
        });

        // CARRERAS
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete(); // FK a universities
            $table->string('name');                       // Nombre de la carrera
            $table->string('slug');                       // Identificador amigable en URLs
            $table->string('level', 50)->nullable();      // Nivel: licenciatura, ingeniería, maestría, etc.
            $table->string('area', 100)->nullable();      // Área/Disciplina (p.ej., Ingeniería, Salud)
            $table->unsignedSmallInteger('duration_months')->nullable(); // Duración estimada en meses
            $table->string('modality', 30)->nullable();   // presencial | en_linea | mixta
            $table->longText('description')->nullable();  // Descripción detallada
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['university_id', 'slug']);    // Evita slugs repetidos dentro de la misma universidad
            $table->index(['university_id', 'name']);     // Búsquedas/filtrados frecuentes
        });

        // CURSOS
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->cascadeOnDelete(); // FK a careers
            $table->string('title');                     // Título del curso
            $table->string('slug');                      // Identificador amigable en URLs
            $table->string('provider')->nullable();      // Proveedor (Coursera, edX, Propio, etc.)
            $table->string('url')->nullable();           // Enlace al curso si es externo
            $table->string('difficulty', 30)->nullable(); // básico | intermedio | avanzado
            $table->unsignedInteger('hours')->nullable(); // Duración estimada en horas
            $table->boolean('is_premium')->default(false); // true si solo es accesible con plan
            $table->longText('description')->nullable(); // Descripción del contenido
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['career_id', 'slug']);       // Evita duplicados por carrera
            $table->index(['career_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
        Schema::dropIfExists('careers');
        Schema::dropIfExists('universities');
    }
};
