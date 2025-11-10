<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoSolicitud;
use App\Models\TipoSolicitudEstado;
use Illuminate\Console\Command;

class ImportTipoSolicitudEstado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tiposolicitudestado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa tipos de solicitudes Estados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["id" => 1, "nombre" => "Borrador"],
            ["id" => 2, "nombre" => "Observada"],
            ["id" => 3, "nombre" => "En revision"],
            ["id" => 4, "nombre" => "Finalizada"],
        ];


        foreach ($estados as $estado) {
            TipoSolicitudEstado::updateOrCreate(
                ['nombre' => $estado['nombre']]
            );
        }


        $this->info('Tipos Solicitudes estados importadas exitosamente.');

        return 0;

    }
}
