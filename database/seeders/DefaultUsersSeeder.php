<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin por defecto
        User::updateOrCreate(
            ['email' => 'admin@pieve.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('admin123'),
                'pin' => '000000',
                'role' => 'admin',
                'activo' => true
            ]
        );

        // Cobradores de ejemplo
        $cobradores = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@pieve.com',
                'pin' => '123456'
            ],
            [
                'name' => 'María García',
                'email' => 'maria.garcia@pieve.com',
                'pin' => '123457'
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos.lopez@pieve.com',
                'pin' => '123458'
            ]
        ];

        foreach ($cobradores as $cobrador) {
            User::updateOrCreate(
                ['email' => $cobrador['email']],
                [
                    'name' => $cobrador['name'],
                    'password' => Hash::make('cobrador123'),
                    'pin' => $cobrador['pin'],
                    'role' => 'cobrador',
                    'activo' => true
                ]
            );
        }

        // Llamadores de ejemplo
        $llamadores = [
            [
                'name' => 'Ana Rodríguez',
                'email' => 'ana.rodriguez@pieve.com',
                'pin' => '234567'
            ],
            [
                'name' => 'Luis Martínez',
                'email' => 'luis.martinez@pieve.com',
                'pin' => '234568'
            ],
            [
                'name' => 'Carmen Fernández',
                'email' => 'carmen.fernandez@pieve.com',
                'pin' => '234569'
            ],
            [
                'name' => 'Roberto Silva',
                'email' => 'roberto.silva@pieve.com',
                'pin' => '234570'
            ]
        ];

        foreach ($llamadores as $llamador) {
            User::updateOrCreate(
                ['email' => $llamador['email']],
                [
                    'name' => $llamador['name'],
                    'password' => Hash::make('llamador123'),
                    'pin' => $llamador['pin'],
                    'role' => 'llamador',
                    'activo' => true
                ]
            );
        }

        $this->command->info('Usuarios por defecto creados:');
        $this->command->info('- Admin: admin@pieve.com / admin123 (PIN: 000000)');
        $this->command->info('- Cobradores: {name}@pieve.com / cobrador123');
        $this->command->info('- Llamadores: {name}@pieve.com / llamador123');
    }
}
