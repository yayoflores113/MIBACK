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
        Schema::table('careers', function (Blueprint $table) {
            // Nueva columna 'division' (DIV)
            $table->string('division', 120)->nullable()->after('area');
            $table->index('division', 'careers_division_index');

            // Reemplazar UNIQUE actual (university_id, slug) por (university_id, division, slug)
            $table->dropUnique('careers_university_id_slug_unique'); // existe hoy en tu DB
            $table->unique(['university_id','division','slug'], 'careers_university_id_division_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            // Restaurar índice único original
            $table->dropUnique('careers_university_id_division_slug_unique');
            $table->unique(['university_id','slug'], 'careers_university_id_slug_unique');

            // Quitar índice/columna
            $table->dropIndex('careers_division_index');
            $table->dropColumn('division');
        });
    }
};
