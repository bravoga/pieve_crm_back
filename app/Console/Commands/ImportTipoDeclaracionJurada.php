<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoDeclaracionJurada;
use App\Models\TipoDeclaracionJuradaDetalle;
use App\Models\TipoPago;
use App\Models\TipoPersona;
use App\Models\TipoSolicitud;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportTipoDeclaracionJurada extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tiposdeclaracionjurada';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa tipos de declaracion jurada.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = [
            'nombre' => 'Declaracion Salud V1',
            'fecha' => Carbon::now(),
            'activo' => true
        ];

        // Corrección aquí: 'create' en lugar de 'created'
        $declaracion = TipoDeclaracionJurada::create($data);

        $detalles = [
            ['tipo_id' => $declaracion->id, 'texto' => 'DECLARACION JURADA DE SALUD PARA SER COMPLETADA POR SOLICITANTE','tipo' => 'titulo',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'El que suscribe la presente, declara bajo juramento que las respuestas que se consigan a continuación y sus ampliaciones y declaraciones, han sido completadas teniendo pleno conocimiento de que cualquier ocultamiento, falsedad o reticencia sobre su verdadero estado de salud o el de su grupo familiar, determinará que la afiliación sea nula.','tipo' => 'subtitulo',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => '¿Se siente Ud. y su grupo familiar en buen estado de salud?', 'tipo' => 'checkbox',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => '¿Tiene Ud. y/o su grupo familiar alguna dificultad para el desempeño de sus tareas laborales habituales?', 'tipo' => 'checkbox',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => '¿Ha sido tratado Ud. y/o su grupo familiar alguna vez, o está actualmente en tratamiento por las siguientes enfermedades?', 'tipo' => null, 'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'Cardiovasculares', 'tipo' => 'checkbox', 'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'Broncopulmonares', 'tipo' => 'checkbox','activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'Hipertension Arterial', 'tipo' => 'checkbox',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'Diabetes', 'tipo' => 'checkbox',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => '¿Tiene Ud. y/o su grupo familiar algún defecto físico o discapacidad?', 'tipo' => 'checkbox',  'activo' => true],
        ];

        foreach ($detalles as $detalle) {
            TipoDeclaracionJuradaDetalle::create(
                $detalle
            );
        }

        /////

        $data = [
            'nombre' => 'Declaracion Persona Expuesta Politicamente V1',
            'fecha' => Carbon::now(),
            'activo' => true
        ];

        // Corrección aquí: 'create' en lugar de 'created'
        $declaracion = TipoDeclaracionJurada::create($data);

        $detalles = [
            ['tipo_id' => $declaracion->id, 'texto' => 'Declaración Jurada sobre persona expuesta políticamente','tipo' => 'titulo',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'Resolución UIF N° 52/2012','tipo' => 'subtitulo',  'activo' => true],
            ['tipo_id' => $declaracion->id, 'texto' => 'Quien suscribe la presente declara bajo juramento que los datos consignados en la presente son correctos, completos y fiel expresión de la verdad, me encuentro incluido y/o alcanzado dentro de la Nomina de Funciones / Funcionarios de Personas  Expuestas Politicamente. Declaro bajo juramento la licitud de los fondos relacionados a esta operación en cumplimiento de la R 230/2011 de la UIF.', 'tipo' => 'checkbox',  'activo' => true],

        ];

        foreach ($detalles as $detalle) {
            TipoDeclaracionJuradaDetalle::create(
                $detalle
            );
        }


        $this->info('Tipos de declaracion juradas  importadas exitosamente.');

        return 0;

    }
}
