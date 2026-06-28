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
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->boolean('needs_geocoding')->default(false)->after('longitude');
            $table->boolean('emergency_available')->default(false)->after('has_power_charging');
            $table->boolean('needs_supplies')->default(false)->after('emergency_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'needs_geocoding', 'emergency_available', 'needs_supplies']);
        });
    }
};
