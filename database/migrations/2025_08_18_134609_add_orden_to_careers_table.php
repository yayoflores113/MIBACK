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
            // En MySQL puedes usar ->after('name') si quieres posición física.
            $table->unsignedSmallInteger('orden')->default(0)->index(); // ->after('name')

            // (Opcional) si ordenas dentro de cada universidad
            // $table->index(['university_id','orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            // Si el nombre del índice auto es careers_orden_index:
            // $table->dropIndex('careers_orden_index');
            $table->dropIndex(['orden']);
            $table->dropColumn('orden');
        });
    }
};
