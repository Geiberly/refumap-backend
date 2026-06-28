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
        Schema::table('map_points', function (Blueprint $table) {
            $table->integer('spam_score')->default(0);
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
        });

        Schema::table('citizen_reports', function (Blueprint $table) {
            $table->integer('spam_score')->default(0);
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->dropColumn(['spam_score', 'risk_level']);
        });

        Schema::table('citizen_reports', function (Blueprint $table) {
            $table->dropColumn(['spam_score', 'risk_level']);
        });
    }
};
