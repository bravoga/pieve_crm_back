<?php

namespace App\Console\Commands;

use App\Models\Beneficio;
use App\Models\BeneficioSucursal;
use App\Models\Convenio;
use App\Models\ConvenioSQL;
use App\Models\EstadoCivil;
use App\Models\Novedad;
use App\Models\Provincia;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportConvenios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:convenios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Convenios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar si el convenio "Sin Convenio" ya existe
        Convenio::firstOrCreate(
            [
                'id' => 1, // Aseguramos que el ID sea 1
            ],
            [
                'contratante_id' => 0,
                'nombre' => 'Sin Convenio'
            ]
        );

        // Conectar y seleccionar datos de la conexión SQL Server
        $convenios = DB::connection('sqlGPIEVEOPE')->select("SELECT * FROM dbo.v_ConvenioSalud");

        // Iterar sobre cada convenio
        foreach ($convenios as $convenio) {
            // Usar updateOrCreate para actualizar o crear cada registro en la base de datos
            Convenio::updateOrCreate(
                [
                    'contratante_id' => $convenio->IdContratanteCP,
                ],
                [
                    'nombre' => $convenio->NombreEmp
                ]
            );
        }

        $this->info('Convenios importados exitosamente.');

        return 0;
    }
}
