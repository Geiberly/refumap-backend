<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')->updateOrInsert(
            ['slug' => 'centro-acopio'],
            [
                'name' => 'Centro de Acopio',
                'icon' => '📦',
                'color' => '#14b8a6',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        $categoryId = DB::table('categories')->where('slug', 'centro-acopio')->value('id');

        if ($categoryId && DB::table('map_points')->where('category_id', $categoryId)->doesntExist()) {
            DB::table('categories')->where('id', $categoryId)->delete();
        }
    }
};
