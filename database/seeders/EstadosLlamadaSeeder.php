<?php

namespace Database\Seeders;

use App\Models\EstadoLlamada;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadosLlamadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [

            [
                'nombre' => 'Sin Contacto',
                'descripcion' => 'El número no corresponde al cliente',
                'color' => '#dc3545',
                'orden' => 2
            ],
            [
                'nombre' => 'Número Equivocado',
                'descripcion' => 'El número no corresponde al cliente',
                'color' => '#dc3545',
                'orden' => 2
            ],
            [
                'nombre' => 'Está al Día',
                'descripcion' => 'Cliente informa que está al día con sus pagos',
                'color' => '#28a745',
                'orden' => 3
            ],
            [
                'nombre' => 'Va a Pagar',
                'descripcion' => 'Cliente confirma que realizará el pago',
                'color' => '#007bff',
                'orden' => 4
            ],
            [
                'nombre' => 'No le interesa',
                'descripcion' => 'Se logró contactar al cliente exitosamente',
                'color' => '#17a2b8',
                'orden' => 5
            ],
            [
                'nombre' => 'Promete Pagar',
                'descripcion' => 'Cliente promete realizar el pago próximamente',
                'color' => '#fd7e14',
                'orden' => 6
            ],
            [
                'nombre' => 'No Puede Pagar',
                'descripcion' => 'Cliente manifiesta no poder pagar actualmente',
                'color' => '#6f42c1',
                'orden' => 7
            ],

            [
            'nombre' => 'A Visitar',
            'descripcion' => 'Cliente manifiesta no poder pagar actualmente',
            'color' => '#6f42c1',
            'orden' => 7
        ],

            [
            'nombre' => 'Nuevo Afiliado',
            'descripcion' => 'Cliente manifiesta no poder pagar actualmente',
            'color' => '#6f42c1',
            'orden' => 7
        ]
        ];

        foreach ($estados as $estado) {
            EstadoLlamada::updateOrCreate(
                ['nombre' => $estado['nombre']],
                $estado
            );
        }
    }
}
