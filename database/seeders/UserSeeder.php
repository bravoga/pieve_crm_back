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
        /*
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@sistema-cobranza.com',
            'password' => Hash::make('password'),
            'pin' => '123456',
            'role' => 'admin',
            'is_active' => true,
            // Campos LDAP simulados para usuario local
            'guid' => 'local-admin-guid',
            'domain' => 'local',
            'username' => 'admin',
        ]);

        // Crear llamadores de prueba (para el sistema de llamadas)
        $llamadores = [
            ['name' => 'Llamador 1', 'pin' => '111111', 'username' => 'llamador1'],
            ['name' => 'Llamador 2', 'pin' => '222222', 'username' => 'llamador2'],
            ['name' => 'Llamador 3', 'pin' => '333333', 'username' => 'llamador3'],
        ];

        foreach ($llamadores as $index => $llamador) {
            User::create([
                'name' => $llamador['name'],
                'email' => 'llamador' . ($index + 1) . '@sistema-cobranza.com',
                'password' => Hash::make('password'),
                'pin' => $llamador['pin'],
                'role' => 'llamador',
                'is_active' => true,
                // Campos LDAP simulados
                'guid' => 'local-llamador-' . ($index + 1) . '-guid',
                'domain' => 'local',
                'username' => $llamador['username'],
            ]);
        }

        // Crear cobradores de prueba
        $cobradores = [
            ['name' => 'Juan Pérez', 'pin' => '444444', 'username' => 'jperez'],
            ['name' => 'María García', 'pin' => '555555', 'username' => 'mgarcia'],
        ];

        foreach ($cobradores as $index => $cobrador) {
            User::create([
                'name' => $cobrador['name'],
                'email' => 'cobrador' . ($index + 1) . '@sistema-cobranza.com',
                'password' => Hash::make('password'),
                'pin' => $cobrador['pin'],
                'role' => 'cobrador',
                'is_active' => true,
                // Campos LDAP simulados
                'guid' => 'local-cobrador-' . ($index + 1) . '-guid',
                'domain' => 'local',
                'username' => $cobrador['username'],
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
            // Campos LDAP simulados
            'guid' => 'local-inactivo-guid',
            'domain' => 'local',
            'username' => 'inactivo',
        ]);
        */
    }
}
