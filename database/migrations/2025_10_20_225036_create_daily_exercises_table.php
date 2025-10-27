<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['code', 'theory', 'problem', 'quiz']);
            $table->json('content');
            $table->text('solution');
            $table->integer('difficulty')->default(1);
            $table->integer('points')->default(10);
            $table->boolean('is_active')->default(true);
            $table->date('available_date')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'is_active']);
            $table->index('available_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_exercises');
    }
};