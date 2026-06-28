<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminName = env('SEED_ADMIN_NAME', 'Admin Principal');
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@refumap.local');
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'admin1234');
        
        $operatorName = env('SEED_OPERATOR_NAME', 'Operador Principal');
        $operatorEmail = env('SEED_OPERATOR_EMAIL', 'operador@refumap.local');
        $operatorPassword = env('SEED_OPERATOR_PASSWORD', 'operador1234');

        if (app()->environment('production')) {
            if ($adminPassword === 'admin1234' || $adminPassword === 'password' || $operatorPassword === 'operador1234' || $operatorPassword === 'password') {
                $this->command->error('Las contraseñas de seed son inseguras para producción. Por favor, configura SEED_ADMIN_PASSWORD y SEED_OPERATOR_PASSWORD en el .env');
                return;
            }
        }

        // Administrador
        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'      => $adminName,
                'password'  => Hash::make($adminPassword),
                'role'      => 'admin',
                'status'    => 'approved',
            ]
        );

        // Operador
        User::firstOrCreate(
            ['email' => $operatorEmail],
            [
                'name'      => $operatorName,
                'password'  => Hash::make($operatorPassword),
                'role'      => 'operator',
                'status'    => 'approved',
                'organization' => 'Protección Civil',
            ]
        );

        $this->command->info("✅ Usuarios creados:\n- Admin: $adminEmail\n- Operador: $operatorEmail");
    }
}
