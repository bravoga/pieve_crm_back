<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\Sucursal;
use Illuminate\Console\Command;

class ImportSucursales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sucursales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Sucursal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["nombre" => "Oran", "localidad_id" => 73],
            ["nombre" => "Tartagal", "localidad_id" => 83],
            ["nombre" => "Salta", "localidad_id" => 43],
            ["nombre" => "Metan", "localidad_id" => 63],
            ["nombre" => "GralGuemes", "localidad_id" => 60],
            ["nombre" => "Guemes", "localidad_id" => 60],
            ["nombre" => "Valle", "localidad_id" => 47],
            ["nombre" => "ANTA", "localidad_id" => 91],
            ["nombre" => "Cafayate", "localidad_id" => 58],
            ["nombre" => "CACHI", "localidad_id" => 52],
            ["nombre" => "Rosario de Lerma", "localidad_id" => 47],
            ["nombre" => "Chicoana", "localidad_id" => 56],
            ["nombre" => "Carril", "localidad_id" => 54],
            ["nombre" => "La Merced", "localidad_id" => 89],
            ["nombre" => "Coronel moldes", "localidad_id" => 55],
            ["nombre" => "Campo Quijano", "localidad_id" => 48],
            ["nombre" => "Cerrillos", "localidad_id" => 46],
            ["nombre" => "San Antonio de los cobres", "localidad_id" => 51],
            ["nombre" => "J.V. Gonzalez", "localidad_id" => 66],
            ["nombre" => "Rosario la Frontera", "localidad_id" => 42]
        ];



        foreach ($estados as $estado) {
            Sucursal::updateOrCreate(
                [
                    'nombre' => $estado['nombre'],
                    'localidad_id' => $estado['localidad_id'],

                ]
            );
        }

        $this->info('Sucursales importados exitosamente.');

        return 0;
    }
}
