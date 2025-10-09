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
        Schema::table('users', function (Blueprint $table) {
            // Eliminar columna google_id
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropColumn('google_id');
            }

            // Agregar columnas genéricas
            $table->string('provider')->nullable()->after('password')->index();
            $table->string('provider_id')->nullable()->after('provider')->index();

            // (Opcional) índice compuesto para búsquedas rápidas
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Quitar los campos nuevos
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id']);

            // Restaurar google_id si necesitas rollback
            $table->string('google_id')->nullable()->index();
        });
    }
};
