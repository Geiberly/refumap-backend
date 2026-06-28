<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MapPoint;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@refumap.local')->first();
        $operator = User::where('email', 'operador@refumap.local')->first();

        // Usar coordenadas de Caracas, Venezuela como ejemplo
        $points = [
            // REFUGIOS
            [
                'category_slug'      => 'refugio',
                'name'               => 'Refugio Escuela Experimental de Venezuela',
                'description'        => 'Escuela habilitada como refugio temporal. Cuenta con agua y baños.',
                'address'            => 'Av. México, Bellas Artes',
                'latitude'           => 10.505000,
                'longitude'          => -66.900000,
                'status'             => 'active',
                'source'             => 'official',
                'capacity_total'     => 200,
                'capacity_available' => 85,
                'accepts_children'   => true,
                'accepts_elderly'    => true,
                'accepts_pets'       => false,
                'has_water'          => true,
                'has_food'           => true,
                'has_medicine'       => false,
                'has_power_charging' => false,
                'urgency_level'      => 2,
                'notes'              => 'Horario: 24 horas. Traer documentos de identificación.',
            ],
            [
                'category_slug'      => 'refugio',
                'name'               => 'Gimnasio Vertical de Chacao',
                'description'        => 'Refugio temporal con capacidad para familias numerosas.',
                'address'            => 'Bello Campo, Chacao',
                'latitude'           => 10.495000,
                'longitude'          => -66.850000,
                'status'             => 'full',
                'source'             => 'operator',
                'capacity_total'     => 150,
                'capacity_available' => 0,
                'accepts_children'   => true,
                'accepts_elderly'    => true,
                'accepts_pets'       => true,
                'has_water'          => true,
                'has_food'           => true,
                'has_medicine'       => false,
                'has_power_charging' => true,
                'urgency_level'      => 3,
                'notes'              => 'SATURADO. Redirigir a Escuela Experimental.',
            ],
            [
                'category_slug'      => 'refugio',
                'name'               => 'Centro Comunitario Los Palos Grandes',
                'description'        => 'Refugio con área especial para mascotas.',
                'address'            => 'Tercera Avenida de Los Palos Grandes',
                'latitude'           => 10.500000,
                'longitude'          => -66.840000,
                'status'             => 'active',
                'source'             => 'official',
                'capacity_total'     => 300,
                'capacity_available' => 180,
                'accepts_children'   => true,
                'accepts_elderly'    => true,
                'accepts_pets'       => true,
                'has_water'          => true,
                'has_food'           => true,
                'has_medicine'       => true,
                'has_power_charging' => true,
                'urgency_level'      => 1,
                'notes'              => 'Área especial para mascotas en patio trasero.',
            ],
            // HOSPITALES
            [
                'category_slug'      => 'hospital',
                'name'               => 'Hospital Clínico Universitario',
                'description'        => 'Hospital público operando al 70% de capacidad. Urgencias disponibles.',
                'address'            => 'Ciudad Universitaria, UCV',
                'latitude'           => 10.485000,
                'longitude'          => -66.885000,
                'status'             => 'active',
                'source'             => 'official',
                'capacity_total'     => 400,
                'capacity_available' => 120,
                'accepts_children'   => true,
                'accepts_elderly'    => true,
                'accepts_pets'       => false,
                'has_water'          => true,
                'has_food'           => false,
                'has_medicine'       => true,
                'has_power_charging' => false,
                'contact_phone'      => '0212-123-4567',
                'urgency_level'      => 2,
                'notes'              => 'Urgencias: entrada lateral norte. Traer tipo de sangre si conoce.',
            ],
            [
                'category_slug'      => 'hospital',
                'name'               => 'Clínica El Ávila',
                'description'        => 'Clínica operativa con médicos voluntarios.',
                'address'            => '6ta Transversal de Altamira',
                'latitude'           => 10.510000,
                'longitude'          => -66.845000,
                'status'             => 'active',
                'source'             => 'operator',
                'capacity_total'     => 80,
                'capacity_available' => 30,
                'accepts_children'   => true,
                'accepts_elderly'    => true,
                'accepts_pets'       => false,
                'has_water'          => true,
                'has_food'           => false,
                'has_medicine'       => true,
                'has_power_charging' => false,
                'contact_phone'      => '0212-987-6543',
                'urgency_level'      => 2,
                'notes'              => 'Atención prioritaria a heridos leves.',
            ],
            // AGUA
            [
                'category_slug'      => 'agua',
                'name'               => 'Distribución de Agua - Plaza Altamira',
                'description'        => 'Pipas de agua potable. Distribución cada 3 horas.',
                'address'            => 'Plaza Francia, Altamira',
                'latitude'           => 10.497500,
                'longitude'          => -66.848000,
                'status'             => 'active',
                'source'             => 'official',
                'capacity_total'     => null,
                'capacity_available' => null,
                'has_water'          => true,
                'has_food'           => false,
                'urgency_level'      => 3,
                'notes'              => 'Pipas disponibles 6am-8pm. Llevar recipiente limpio.',
            ],
            [
                'category_slug'      => 'agua',
                'name'               => 'Cisterna Petare',
                'description'        => 'Agua filtrada disponible. Trae tu recipiente.',
                'address'            => 'Redoma de Petare',
                'latitude'           => 10.482000,
                'longitude'          => -66.805000,
                'status'             => 'active',
                'source'             => 'citizen',
                'capacity_total'     => null,
                'capacity_available' => null,
                'has_water'          => true,
                'urgency_level'      => 2,
                'notes'              => 'Información no verificada. Confirmar antes de acudir.',
            ],
            // COMIDA
            [
                'category_slug'      => 'comida',
                'name'               => 'Cocina Comunitaria Parque del Este',
                'description'        => 'Comida caliente disponible. 3 tiempos al día.',
                'address'            => 'Parque Generalísimo Francisco de Miranda',
                'latitude'           => 10.494000,
                'longitude'          => -66.833000,
                'status'             => 'active',
                'source'             => 'official',
                'capacity_total'     => null,
                'capacity_available' => null,
                'has_water'          => true,
                'has_food'           => true,
                'urgency_level'      => 2,
                'notes'              => 'Desayuno 8am, Almuerzo 1pm, Cena 6pm.',
            ],
            [
                'category_slug'      => 'comida',
                'name'               => 'Centro de Acopio La Carlota',
                'description'        => 'Distribución de víveres: latas, agua, pan.',
                'address'            => 'Base Aérea Generalísimo Francisco de Miranda',
                'latitude'           => 10.485000,
                'longitude'          => -66.840000,
                'status'             => 'active',
                'source'             => 'operator',
                'has_food'           => true,
                'has_water'          => true,
                'urgency_level'      => 2,
                'notes'              => 'Máximo 5 artículos por familia.',
            ],
            // MEDICINAS
            [
                'category_slug'      => 'medicinas',
                'name'               => 'Sede Central Cruz Roja Venezolana',
                'description'        => 'Medicamentos básicos gratuitos para afectados.',
                'address'            => 'La Candelaria',
                'latitude'           => 10.505500,
                'longitude'          => -66.900500,
                'status'             => 'active',
                'source'             => 'official',
                'has_medicine'       => true,
                'contact_phone'      => '0212-555-1234',
                'urgency_level'      => 3,
                'notes'              => 'Presentar condición médica o receta si tiene.',
            ],
            // CARGA ELÉCTRICA
            [
                'category_slug'      => 'carga-electrica',
                'name'               => 'Punto de Carga - Biblioteca Central UCV',
                'description'        => 'Múltiples contactos disponibles. Generador propio.',
                'address'            => 'Plaza Cubierta, UCV',
                'latitude'           => 10.488000,
                'longitude'          => -66.890000,
                'status'             => 'active',
                'source'             => 'official',
                'has_power_charging' => true,
                'urgency_level'      => 1,
                'notes'              => 'Disponible 7am-9pm. Máximo 1 hora por dispositivo.',
            ],
            // ZONA PELIGROSA
            [
                'category_slug'      => 'zona-peligrosa',
                'name'               => 'Zona de Riesgo - Edificios El Paraíso',
                'description'        => 'Varios edificios con daños estructurales graves. No ingresar.',
                'address'            => 'Avenida Páez, El Paraíso',
                'latitude'           => 10.485000,
                'longitude'          => -66.930000,
                'status'             => 'danger',
                'source'             => 'official',
                'urgency_level'      => 4,
                'notes'              => 'PELIGRO CRÍTICO: No ingresar. Área acordonada por protección civil.',
            ],
            // EDIFICIO COLAPSADO
            [
                'category_slug'      => 'edificio-colapsado',
                'name'               => 'Edificio Colapsado - Los Chaguaramos',
                'description'        => 'Edificio de 6 pisos colapsado. Equipos de rescate en sitio.',
                'address'            => 'Avenida Universitaria, Los Chaguaramos',
                'latitude'           => 10.480000,
                'longitude'          => -66.885000,
                'status'             => 'danger',
                'source'             => 'official',
                'urgency_level'      => 4,
                'notes'              => 'Brigadas de rescate trabajando. Mantener distancia de 200m.',
            ],
            // ATENCIÓN ESPECIAL
            [
                'category_slug'      => 'atencion-especial',
                'name'               => 'Centro de Atención Especializada Chacao',
                'description'        => 'Espacio prioritario para adultos mayores y personas con discapacidad.',
                'address'            => 'Sede Salud Chacao',
                'latitude'           => 10.498000,
                'longitude'          => -66.855000,
                'status'             => 'active',
                'source'             => 'official',
                'accepts_elderly'    => true,
                'has_water'          => true,
                'has_food'           => true,
                'has_medicine'       => true,
                'urgency_level'      => 2,
                'notes'              => 'Personal médico presente. Prioritario para mayores de 65 y personas con discapacidad.',
            ],
        ];

        foreach ($points as $pointData) {
            $slug = $pointData['category_slug'];
            unset($pointData['category_slug']);

            $category = Category::where('slug', $slug)->first();
            if (!$category) continue;

            MapPoint::firstOrCreate(
                ['name' => $pointData['name'], 'latitude' => $pointData['latitude']],
                array_merge($pointData, [
                    'category_id'      => $category->id,
                    'type'             => $category->slug,
                    'created_by'       => $admin?->id,
                    'updated_by'       => $admin?->id,
                    'last_verified_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Puntos del mapa creados: ' . count($points));
    }
}
