<?php

namespace App\Console\Commands;

use App\Models\Banco;
use App\Models\EstadoCivil;
use App\Models\Novedad;
use App\Models\Provincia;
use App\Models\Tarjeta;
use Illuminate\Console\Command;

class importBanco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:bancos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Bancos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nombres = [
        ["Bnaco Macro", 15],
        ["Banco Frances", 22],
        ["Banco Sudameris", 22],
        ["Banco Nación", 14],
        ["Banco Mas Ventas", 8],
        ["Banco Salta", 15],
        ["Convenio 11105(SERRA)", 15],
    ];

        $novedades = [];

        foreach ($nombres as $nombre) {
            $novedades[] = [
                "nombre" => $nombre[0],
                "max_caracteres" => $nombre[1],
            ];
        }



        foreach ($novedades as $novedad) {
            Banco::updateOrCreate(
                     ['nombre' => $novedad['nombre'],
                    'max_caracteres' => $novedad['max_caracteres'],
                   ]
            );
        }

        $this->info('Bancos importados exitosamente.');

        return 0;
    }
}
