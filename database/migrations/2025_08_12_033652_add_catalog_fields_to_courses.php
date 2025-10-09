<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('topic', 120)->nullable()->after('provider');
            $table->string('level', 20)->nullable()->after('topic'); // 'todos','principiante','intermedio','experto'
            $table->decimal('rating_avg', 3, 2)->nullable()->after('level'); // 0.00 - 5.00
            $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');
            $table->unsignedInteger('popularity_score')->default(0)->after('rating_count'); // ej: inscripciones
            $table->boolean('is_free')->default(false)->after('is_premium');
            $table->integer('price_cents')->nullable()->after('is_free'); // opcional si no es gratis
            $table->date('published_at')->nullable()->after('price_cents');
            $table->string('card_image_url', 255)->nullable()->after('published_at'); // imagen pública de la card
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'topic',
                'level',
                'rating_avg',
                'rating_count',
                'popularity_score',
                'is_free',
                'price_cents',
                'published_at',
                'card_image_url'
            ]);
        });
    }
};
