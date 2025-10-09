<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('subtitle', 120)->nullable()->after('name');   // "Para ti", "Individual"
            $table->string('cta_type', 20)->default('trial')->after('description'); // trial|subscribe|contact
            $table->string('cta_label', 60)->nullable()->after('cta_type');        // "Probar gratis", "Suscribirme"
            $table->boolean('is_featured')->default(false)->after('sort_order');   // para resaltar un plan
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'cta_type', 'cta_label', 'is_featured']);
        });
    }
};
