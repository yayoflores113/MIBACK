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
            $table->json('trait_profile')->nullable()->after('description');
            $table->index('slug'); // útil para tus vistas públicas
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->json('trait_profile')->nullable()->after('description');
            $table->index('slug');
        });
        Schema::table('universities', function (Blueprint $table) {
            $table->json('trait_profile')->nullable()->after('city');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn('trait_profile');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('trait_profile');
        });
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn('trait_profile');
        });
    }
};
