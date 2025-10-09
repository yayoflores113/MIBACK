<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')
                ->constrained('test_attempts')
                ->cascadeOnDelete();

            // Crea recommendable_type, recommendable_id y su índice compuesto automáticamente.
            $table->morphs('recommendable');

            $table->decimal('score', 5, 2)->default(0); // 0..100
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['attempt_id', 'score']); // este sí lo dejamos
        });

        // Quitar este bloque porque duplica el índice creado por morphs:
        // Schema::table('recommendations', function (Blueprint $table) {
        //     $table->index(['recommendable_type', 'recommendable_id']);
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
