<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);        // p.ej. "México"
            $table->char('code', 2);            // ISO-2: "MX", "ES", "CO"
            $table->timestamps();

            $table->unique(['name']);
            $table->unique(['code']);
            $table->index(['name']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('countries');
    }
};
