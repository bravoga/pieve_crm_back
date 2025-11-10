<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoGrupoFamiliar;
use App\Models\TipoPago;
use App\Models\TipoPersona;
use App\Models\TipoSolicitud;
use Illuminate\Console\Command;

class ImportTipoGrupoFamiliares extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tiposgruposfamiliares';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa tipos de grupos familiares';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["id" => 1, "nombre" => "Sin Grupo",'descripcion'=>'No tiene grupo','sql_id'=>100],
            ["id" => 2, "nombre" => "Grupo A",'descripcion'=>'Titular, Padres, Hermanos','sql_id'=>200],
            ["id" => 3, "nombre" => "Grupo B",'descripcion'=>'Titular, Conyuge, Hijos','sql_id'=>300]
        ];


        foreach ($estados as $estado) {
            TipoGrupoFamiliar::updateOrCreate(
                ['nombre' => $estado['nombre']],
                ['descripcion' => $estado['descripcion'], 'sql_id' => $estado['sql_id']]
            );
        }


        $this->info('Tipos de Pagos grupos familiares importados exitosamente.');

        return 0;

    }
}
