<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MapPoint;
use Illuminate\Database\Seeder;

class CaracasHospitalsSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->where('slug', 'hospital')->first();

        if (!$category) {
            $this->command->error('No se encontro la categoria hospital.');
            return;
        }

        $hospitals = [
            [
                'name' => 'Hospital Universitario de Caracas',
                'address' => 'Ciudad Universitaria de Caracas',
                'latitude' => 10.4880180,
                'longitude' => -66.8905100,
                'description' => 'Hospital publico universitario.',
                'notes' => 'Verificar disponibilidad y estado operativo antes de trasladarse.',
            ],
            [
                'name' => 'Hospital Dr. Jose Maria Vargas',
                'address' => 'Cotiza, Caracas',
                'latitude' => 10.5197710,
                'longitude' => -66.9127780,
                'description' => 'Hospital publico general.',
                'notes' => 'Mantener como no verificado hasta revision operativa.',
            ],
            [
                'name' => 'Hospital de Ninos J. M. de los Rios',
                'address' => 'San Bernardino, Caracas',
                'latitude' => 10.5068470,
                'longitude' => -66.8926850,
                'description' => 'Hospital pediatrico.',
                'notes' => 'Centro pediatrico. Confirmar servicios disponibles.',
            ],
            [
                'name' => 'Hospital Domingo Luciani',
                'address' => 'El Llanito, Caracas',
                'latitude' => 10.4736210,
                'longitude' => -66.8070760,
                'description' => 'Hospital publico de alta demanda.',
                'notes' => 'Confirmar capacidad y estado operativo.',
            ],
            [
                'name' => 'Hospital Dr. Miguel Perez Carreno',
                'address' => 'La Yaguara, Caracas',
                'latitude' => 10.4701220,
                'longitude' => -66.9320360,
                'description' => 'Hospital publico de referencia.',
                'notes' => 'Confirmar triage y accesos habilitados.',
            ],
            [
                'name' => 'Hospital Militar Dr. Carlos Arvelo',
                'address' => 'San Martin, Caracas',
                'latitude' => 10.4918610,
                'longitude' => -66.9202560,
                'description' => 'Hospital militar.',
                'notes' => 'Verificar disponibilidad publica y admision.',
            ],
            [
                'name' => 'Hospital de Clinicas Caracas',
                'address' => 'San Bernardino, Caracas',
                'latitude' => 10.5013530,
                'longitude' => -66.8896240,
                'description' => 'Clinica privada.',
                'notes' => 'Verificar emergencia, disponibilidad y contacto.',
            ],
            [
                'name' => 'Policlinica Metropolitana',
                'address' => 'Caurimare, Caracas',
                'latitude' => 10.4698330,
                'longitude' => -66.8460240,
                'description' => 'Clinica privada.',
                'notes' => 'Verificar emergencia y disponibilidad.',
            ],
            [
                'name' => 'Instituto Medico La Floresta',
                'address' => 'La Floresta, Caracas',
                'latitude' => 10.4962680,
                'longitude' => -66.8620710,
                'description' => 'Centro medico privado.',
                'notes' => 'Confirmar acceso por emergencias.',
            ],
            [
                'name' => 'Clinica Sanatrix',
                'address' => 'Campo Alegre, Chacao',
                'latitude' => 10.4929620,
                'longitude' => -66.8571050,
                'description' => 'Clinica privada.',
                'notes' => 'Verificar admision de emergencias.',
            ],
            [
                'name' => 'Clinica Santiago de Leon',
                'address' => 'Av. Libertador, Caracas',
                'latitude' => 10.5008680,
                'longitude' => -66.8739930,
                'description' => 'Clinica privada.',
                'notes' => 'Confirmar estado operativo.',
            ],
            [
                'name' => 'Centro Medico Docente La Trinidad',
                'address' => 'La Trinidad, Caracas',
                'latitude' => 10.4318360,
                'longitude' => -66.8794430,
                'description' => 'Centro medico privado.',
                'notes' => 'Verificar disponibilidad y vias de acceso.',
            ],
        ];

        foreach ($hospitals as $hospital) {
            MapPoint::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name' => $hospital['name'],
                ],
                [
                    'address' => $hospital['address'],
                    'latitude' => $hospital['latitude'],
                    'longitude' => $hospital['longitude'],
                    'description' => $hospital['description'],
                    'city' => 'Caracas',
                    'state' => 'Distrito Capital',
                    'type' => 'hospital',
                    'status' => 'verified',
                    'source' => 'seed',
                    'notes' => $hospital['notes'],
                    'capacity_total' => null,
                    'capacity_available' => null,
                    'needs_geocoding' => false,
                    'emergency_available' => false,
                    'needs_supplies' => false,
                ]
            );
        }

        $this->command->info('✅ Hospitales de Caracas actualizados sin duplicados.');
    }
}
