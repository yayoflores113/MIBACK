<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            // Trayectoria de niveles: ["TSU","Ingeniería"]
            $table->json('levels')->nullable()->after('level');

            // Desglose de términos por nivel:
            // [{"level":"TSU","terms":6},{"level":"Ingeniería","terms":5}]
            $table->json('duration_terms')->nullable()->after('duration_months');

            // Unidad de término (por defecto “cuatrimestre”)
            $table->string('terms_unit', 20)->default('cuatrimestre')->after('duration_terms');
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn(['levels', 'duration_terms', 'terms_unit']);
        });
    }
};
