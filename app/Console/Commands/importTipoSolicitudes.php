<?php

namespace App\Console\Commands;

use App\Models\Banco;
use App\Models\EstadoCivil;
use App\Models\Novedad;
use App\Models\Provincia;
use App\Models\Tarjeta;
use App\Models\TipoSolicitud;
use Illuminate\Console\Command;

class importTipoSolicitudes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tiposolicitudes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Tipo de Solicitudes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Definir los nombres con un formato adecuado
        $nombres = [
            ["nombre" => "Alta Nueva","certificado"=>false],
            ["nombre" => "Alta Parcial","certificado"=>true],
            ["nombre" => "Cambio de Plan","certificado"=>true],
            ["nombre" => "Pase a Débito","certificado"=>true],
        ];

        // Iterar sobre cada novedad y actualizar o crear en la base de datos
        foreach ($nombres as $novedad) {
            TipoSolicitud::updateOrCreate(
                ['nombre' => $novedad['nombre'],'certificado'=>$novedad['certificado']],
                ['activo' => true],
            );
        }

        $this->info('Tipos de Solicitud importados exitosamente.');

        return 0;
    }
}
