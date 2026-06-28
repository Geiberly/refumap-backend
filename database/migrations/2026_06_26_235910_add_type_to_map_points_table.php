<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        Schema::table('map_points', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
        });

        if ($driver === 'pgsql') {
            DB::statement('UPDATE map_points SET type = categories.slug FROM categories WHERE categories.id = map_points.category_id');
            return;
        }

        DB::statement('UPDATE map_points SET type = (SELECT slug FROM categories WHERE categories.id = map_points.category_id)');
    }

    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
