<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Novedad;
use App\Models\Provincia;
use App\Models\Tarjeta;
use Illuminate\Console\Command;

class importTarjetas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tarjetas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Tarjetas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nombres = [
            "Naranja",
            "Credimás",
            "Shopping",
            "SuCredito",
            "MasterCard",
            "Visa",
            "Nevada",
            "Castillo",
            "Nativa"
        ];

        $novedades = [];

        foreach ($nombres as $nombre) {
            $novedades[] = [
                "nombre" => $nombre,
                "max_caracteres" => 16,
            ];
        }



        foreach ($novedades as $novedad) {
            Tarjeta::updateOrCreate(
                     ['nombre' => $novedad['nombre'],
                    'max_caracteres' => $novedad['max_caracteres'],
                   ]
            );
        }

        $this->info('Tarjetas importadas exitosamente.');

        return 0;
    }
}
