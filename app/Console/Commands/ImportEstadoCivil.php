<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use Illuminate\Console\Command;

class ImportEstadoCivil extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:estadocivil';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Estado Civil';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["sql_id" => 1, "nombre" => "SIN DATOS"],
            ["sql_id" => 2, "nombre" => "SOLTERO/A"],
            ["sql_id" => 3, "nombre" => "CASADO/A"],
            ["sql_id" => 4, "nombre" => "DIVORCIADO/A"],
            ["sql_id" => 5, "nombre" => "SEPARADO/A"],
            ["sql_id" => 6, "nombre" => "UNION DE HECHO"],
            ["sql_id" => 7, "nombre" => "VIUDO/A"],
            ["sql_id" => 8, "nombre" => "DIVORCIADO CONVIVIENTE"],
            ["sql_id" => 9, "nombre" => "VIUDO CONVIVIENTE"],
            ["sql_id" => 10, "nombre" => "SEPARADO DE HECHO"],
            ["sql_id" => 11, "nombre" => "SOLTERO CONVIVIENTE"],
            ["sql_id" => 12, "nombre" => "SEPARADO CONVIVIENTE"],
        ];


        foreach ($estados as $estado) {
            EstadoCivil::updateOrCreate(
                ['sql_id' => $estado['sql_id']],
                ['nombre' => $estado['nombre']]
            );
        }

        $this->info('Estado Civil importados exitosamente.');

        return 0;
    }
}
