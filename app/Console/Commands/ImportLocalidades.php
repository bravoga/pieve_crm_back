<?php

namespace App\Console\Commands;

use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Console\Command;

class ImportLocalidades extends Command
{
    protected $signature = 'import:localidades';
    protected $description = 'Importa las localidades desde un archivo CSV';

    public function handle()
    {
        ini_set('memory_limit', '512M');


        $path = public_path('localidades.csv');
        if (!file_exists($path)) {
            $this->error('El archivo localidades.csv no existe en la carpeta public.');
            return 1;
        }

        $file = fopen($path, 'r');
        while (($data = fgetcsv($file, 1000, ';')) !== FALSE) {
            $codigo = $data[0];
            $provinciaNombre = $data[1];
            $localidadNombre = $data[2];

            $provincia = Provincia::firstOrCreate(['nombre' => $provinciaNombre]);

            Localidad::updateOrCreate(
                ['nombre' => $localidadNombre, 'provincia_id' => $provincia->id]
            );
        }

        fclose($file);
        $this->info('Localidades importadas exitosamente.');

        return 0;
    }
}
