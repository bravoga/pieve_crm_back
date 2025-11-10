<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoPago;
use App\Models\TipoPersona;
use App\Models\TipoSolicitud;
use Illuminate\Console\Command;

class ImportTipoPago extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tiposPagos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa tipos de pagos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["id" => 2, "nombre" => "Tarjeta de Crédito"],
            ["id" => 3, "nombre" => "Débito Bancario"],
            ["id" => 4, "nombre" => "Cobrador en Domicilio"],
            ["id" => 5, "nombre" => "Pago en Oficina"],
            ["id" => 6, "nombre" => "Descuento por Planilla"],
        ];


        foreach ($estados as $estado) {
            TipoPago::updateOrCreate(
                ['nombre' => $estado['nombre']]
            );
        }


        $this->info('Tipos de Pagos importadas exitosamente.');

        return 0;

    }
}
