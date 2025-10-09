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
            $table->string('career_url')->nullable()->after('name'); // filename de la imagen
            // (Opcional) si ya añadiste 'orden', ignora; si no:
            // $table->unsignedSmallInteger('orden')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn('career_url');
            // $table->dropIndex(['orden']); $table->dropColumn('orden'); // si lo agregaste aquí
        });
    }
};
