<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'      => 'Refugio',
                'slug'      => 'refugio',
                'icon'      => '🏠',
                'color'     => '#2563eb',
                'is_active' => true,
            ],
            [
                'name'      => 'Hospital',
                'slug'      => 'hospital',
                'icon'      => '🏥',
                'color'     => '#dc2626',
                'is_active' => true,
            ],
            [
                'name'      => 'Agua Potable',
                'slug'      => 'agua',
                'icon'      => '💧',
                'color'     => '#0891b2',
                'is_active' => true,
            ],
            [
                'name'      => 'Comida',
                'slug'      => 'comida',
                'icon'      => '🍽️',
                'color'     => '#16a34a',
                'is_active' => true,
            ],
            [
                'name'      => 'Centro de Acopio',
                'slug'      => 'centro-acopio',
                'icon'      => '📦',
                'color'     => '#14b8a6',
                'is_active' => true,
            ],
            [
                'name'      => 'Medicinas',
                'slug'      => 'medicinas',
                'icon'      => '💊',
                'color'     => '#7c3aed',
                'is_active' => true,
            ],
            [
                'name'      => 'Carga Eléctrica',
                'slug'      => 'carga-electrica',
                'icon'      => '🔌',
                'color'     => '#d97706',
                'is_active' => true,
            ],
            [
                'name'      => 'Zona Peligrosa',
                'slug'      => 'zona-peligrosa',
                'icon'      => '⚠️',
                'color'     => '#991b1b',
                'is_active' => true,
            ],
            [
                'name'      => 'Vía Bloqueada',
                'slug'      => 'via-bloqueada',
                'icon'      => '🚧',
                'color'     => '#78350f',
                'is_active' => true,
            ],
            [
                'name'      => 'Edificio Colapsado',
                'slug'      => 'edificio-colapsado',
                'icon'      => '🏚️',
                'color'     => '#374151',
                'is_active' => true,
            ],
            [
                'name'      => 'Atención Especial',
                'slug'      => 'atencion-especial',
                'icon'      => '❤️',
                'color'     => '#be185d',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ Categorías creadas: ' . count($categories));
    }
}
