<?php

namespace App\Console\Commands;


use App\Models\TipoCondicionFiscal;
use Illuminate\Console\Command;

class importCondicionFiscal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:condicionfiscal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Condiciones Fiscales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estados = [
            ["sql_id" => 1, "nombre" => "Consumidor Final"],
            ["sql_id" => 2, "nombre" => "Responsable Inscripto"],
        ];


        foreach ($estados as $estado) {
            TipoCondicionFiscal::updateOrCreate(
            // ['sql_id' => $estado['sql_id']],
                ['nombre' => $estado['nombre']]
            );
        }

        $this->info('Condicion Fiscal importado exitosamente.');

        return 0;
    }
}
