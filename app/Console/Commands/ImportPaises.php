<?php

namespace App\Console\Commands;

use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Console\Command;

class ImportPaises extends Command
{
    protected $signature = 'import:paises';
    protected $description = 'Importa paises desde un archivo CSV';

    public function handle()
    {
        ini_set('memory_limit', '512M');

        $path = public_path('paises.csv');
        if (!file_exists($path)) {
            $this->error('El archivo paises.csv no existe en la carpeta public.');
            return 1;
        }

        $file = fopen($path, 'r');
        while (($data = fgetcsv($file, 1000, ';')) !== FALSE) {
            $nombre = $data[0];
            $pais = Pais::firstOrCreate(['nombre' => $nombre]);
        }

        fclose($file);
        $this->info('Paises importadas exitosamente.');

        return 0;
    }
}
