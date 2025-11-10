<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoLog;
use App\Models\TipoPago;
use App\Models\TipoPersona;
use App\Models\TipoSolicitud;
use Illuminate\Console\Command;

class ImportTipoLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tiposlogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa tipos de logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["id" => 1, "nombre" => "Inicio Sesion"],
            ["id" => 2, "nombre" => "Consulta Novedades"],
            ["id" => 3, "nombre" => "Descarga Credencial Certificado"],
            ["id" => 4, "nombre" => "Consulta Comprobantes Certificado"],
            ["id" => 5, "nombre" => "Consulta Beneficios"],
            ["id" => 6, "nombre" => "Vefificacion de Alta Certificado"],
            ["id" => 7, "nombre" => "Construye Validacion de Datos"],
            ["id" => 8, "nombre" => "Validacion Registro Exitosa"],
            ["id" => 9, "nombre" => "Validacion Registro Incorrecta"],
            ["id" => 10, "nombre" => "Credencial enviada por Whatsapp"],
        ];


        foreach ($estados as $estado) {
            TipoLog::updateOrCreate(
                ['nombre' => $estado['nombre']]
            );
        }


        $this->info('Tipos de Logs importadas exitosamente.');

        return 0;

    }
}
