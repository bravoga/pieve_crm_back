<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'certi' => 'CLI001',
                'nombre' => 'Juan Carlos Pérez',
                'telefonos' => '3001234567',
                'direccion' => 'Calle 123 # 45-67, Barrio Centro, Bogotá',
                'importe' => 150000,
                'geocoding_status' => 'validated',
                'lat' => 4.60971,
                'lng' => -74.08175,
            ],
            [
                'certi' => 'CLI002',
                'nombre' => 'María Elena García',
                'telefonos' => '3109876543',
                'direccion' => 'Carrera 15 # 25-30, Barrio La Candelaria, Bogotá',
                'importe' => 85000,
                'geocoding_status' => 'validated',
                'lat' => 4.59707,
                'lng' => -74.07525,
            ],
            [
                'certi' => 'CLI003',
                'nombre' => 'Carlos Alberto López',
                'telefonos' => '3205551234',
                'direccion' => 'Avenida 68 # 40-20, Barrio Modelo, Bogotá',
                'importe' => 200000,
                'geocoding_status' => 'validated',
                'lat' => 4.64134,
                'lng' => -74.10453,
            ],
            [
                'certi' => 'CLI004',
                'nombre' => 'Ana Patricia Rodríguez',
                'telefonos' => '3151112222',
                'direccion' => 'Calle 26 # 13-28, Barrio Santa Fe, Bogotá',
                'importe' => 95000,
                'geocoding_status' => 'validated',
                'lat' => 4.60897,
                'lng' => -74.07209,
            ],
            [
                'certi' => 'CLI005',
                'nombre' => 'Luis Fernando Martínez',
                'telefonos' => '3186667777',
                'direccion' => 'Transversal 20 # 50-15, Barrio Chapinero, Bogotá',
                'importe' => 125000,
                'geocoding_status' => 'validated',
                'lat' => 4.63421,
                'lng' => -74.06187,
            ],
            [
                'certi' => 'CLI006',
                'nombre' => 'Sandra Milena Vargas',
                'telefonos' => '3123334444',
                'direccion' => 'Diagonal 39 # 20-50, Barrio Teusaquillo, Bogotá',
                'importe' => 175000,
                'geocoding_status' => 'validated',
                'lat' => 4.63011,
                'lng' => -74.08756,
            ],
            [
                'certi' => 'CLI007',
                'nombre' => 'Roberto José Herrera',
                'telefonos' => '3008889999',
                'direccion' => 'Calle 72 # 11-45, Barrio Zona Rosa, Bogotá',
                'importe' => 300000,
                'geocoding_status' => 'validated',
                'lat' => 4.66109,
                'lng' => -74.05987,
            ],
            [
                'certi' => 'CLI008',
                'nombre' => 'Diana Carolina Sánchez',
                'telefonos' => '3145551111',
                'direccion' => 'Avenida Caracas # 35-10, Barrio San Luis, Bogotá',
                'importe' => 110000,
                'geocoding_status' => 'validated',
                'lat' => 4.62456,
                'lng' => -74.07234,
            ],
            [
                'certi' => 'CLI009',
                'nombre' => 'Andrés Felipe Torres',
                'telefonos' => '3172223333',
                'direccion' => 'Calle 45 # 24-80, Barrio La Soledad, Bogotá',
                'importe' => 80000,
                'geocoding_status' => 'validated',
                'lat' => 4.63786,
                'lng' => -74.08334,
            ],
            [
                'certi' => 'CLI010',
                'nombre' => 'Claudia Patricia Morales',
                'telefonos' => '3194445555',
                'direccion' => 'Carrera 30 # 45-25, Barrio Ciudad Universitaria, Bogotá',
                'importe' => 220000,
                'geocoding_status' => 'validated',
                'lat' => 4.63567,
                'lng' => -74.08901,
            ],
            [
                'certi' => 'CLI011',
                'nombre' => 'Miguel Ángel Castro',
                'telefonos' => '3166667777',
                'direccion' => 'Calle 19 # 5-30, Barrio Macarena, Bogotá',
                'importe' => 90000,
                'geocoding_status' => 'pending',
                'lat' => null,
                'lng' => null,
            ],
            [
                'certi' => 'CLI012',
                'nombre' => 'Esperanza del Carmen Silva',
                'telefonos' => '3088889999',
                'direccion' => 'Transversal 25 # 65-40, Barrio Chicó Norte, Bogotá',
                'importe' => 280000,
                'geocoding_status' => 'pending',
                'lat' => null,
                'lng' => null,
            ],
        ];

        foreach ($clientes as $clienteData) {
            Cliente::create($clienteData);
        }
    }
}