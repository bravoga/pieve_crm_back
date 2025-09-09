<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@sistema-cobranza.com',
            'password' => Hash::make('password'),
            'pin' => '123456',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Crear cobradores de prueba
        $cobradores = [
            ['name' => 'Juan Pérez', 'pin' => '111111'],
            ['name' => 'María García', 'pin' => '222222'],
            ['name' => 'Carlos López', 'pin' => '333333'],
            ['name' => 'Ana Rodríguez', 'pin' => '444444'],
            ['name' => 'Luis Martínez', 'pin' => '555555'],
        ];

        foreach ($cobradores as $index => $cobrador) {
            User::create([
                'name' => $cobrador['name'],
                'email' => 'cobrador' . ($index + 1) . '@sistema-cobranza.com',
                'password' => Hash::make('password'),
                'pin' => $cobrador['pin'],
                'role' => 'cobrador',
                'is_active' => true,
            ]);
        }

        // Crear un usuario inactivo para pruebas
        User::create([
            'name' => 'Usuario Inactivo',
            'email' => 'inactivo@sistema-cobranza.com',
            'password' => Hash::make('password'),
            'pin' => '999999',
            'role' => 'cobrador',
            'is_active' => false,
        ]);
    }
}