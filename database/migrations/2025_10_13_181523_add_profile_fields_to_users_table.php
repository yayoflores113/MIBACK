<?php
// database/migrations/2025_10_13_000001_add_profile_fields_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('name');
            $table->unsignedBigInteger('university_id')->nullable()->after('birth_date');
            $table->string('matricula', 30)->nullable()->after('university_id');
            $table->unsignedBigInteger('country_id')->nullable()->after('matricula');

            $table->foreign('university_id')->references('id')->on('universities')->nullOnDelete();
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();

            $table->index(['university_id']);
            $table->index(['country_id']);
            $table->index(['matricula']);
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn(['birth_date','university_id','matricula','country_id']);
        });
    }
};
