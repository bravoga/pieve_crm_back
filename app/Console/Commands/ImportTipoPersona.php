<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoPersona;
use App\Models\TipoSolicitud;
use Illuminate\Console\Command;

class ImportTipoPersona extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tipospersonas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa tipos de personas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["id" => 1, "nombre" => "Titular"],
            ["id" => 2, "nombre" => "Responsable de Pago"],
            ["id" => 3, "nombre" => "Beneficiario"],
            ["id" => 4, "nombre" => "Integrante"],
        ];


        foreach ($estados as $estado) {
            TipoPersona::updateOrCreate(
                ['nombre' => $estado['nombre']]
            );
        }


        $this->info('Tipos Solicitudes importadas exitosamente.');

        return 0;

    }
}
