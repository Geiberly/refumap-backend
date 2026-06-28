<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE map_points DROP CONSTRAINT IF EXISTS map_points_source_check");
            DB::statement("ALTER TABLE map_points ADD CONSTRAINT map_points_source_check CHECK (source IN ('official', 'operator', 'citizen', 'seed', 'unverified'))");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE map_points MODIFY source ENUM('official', 'operator', 'citizen', 'seed', 'unverified') NOT NULL DEFAULT 'unverified'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE map_points DROP CONSTRAINT IF EXISTS map_points_source_check");
            DB::statement("ALTER TABLE map_points ADD CONSTRAINT map_points_source_check CHECK (source IN ('official', 'operator', 'citizen', 'unverified'))");
            DB::statement("UPDATE map_points SET source = 'unverified' WHERE source = 'seed'");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("UPDATE map_points SET source = 'unverified' WHERE source = 'seed'");
            DB::statement("ALTER TABLE map_points MODIFY source ENUM('official', 'operator', 'citizen', 'unverified') NOT NULL DEFAULT 'unverified'");
        }
    }
};
