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
        Schema::table('universities', function (Blueprint $table) {
            // Si usas MySQL puedes poner ->after('name') si quieres posición física.
            $table->unsignedSmallInteger('orden')->default(0)->index(); // ->after('name')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Si el índice se creó automático, puedes usar el nombre:
            // $table->dropIndex('universities_orden_index');
            $table->dropIndex(['orden']);
            $table->dropColumn('orden');
        });
    }
};
