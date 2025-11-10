<?php

namespace App\Console\Commands;

use App\Models\EstadoCivil;
use App\Models\Provincia;
use App\Models\TipoVinculo;
use Illuminate\Console\Command;

class ImportVinculos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:vinculos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Vinculos';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $estados = [
            ["sql_id" => 1, "nombre" => "Titular", "orden" => 1],
            ["sql_id" => 2, "nombre" => "Conyuge", "orden" => 2],
            ["sql_id" => 10, "nombre" => "Suegro/a", "orden" => null],
            ["sql_id" => 18, "nombre" => "Tio Abuelo", "orden" => null],
            ["sql_id" => 7, "nombre" => "Hermano/a", "orden" => null],
            ["sql_id" => 15, "nombre" => "Nuera", "orden" => null],
            ["sql_id" => 4, "nombre" => "Padre", "orden" => 4],
            ["sql_id" => 12, "nombre" => "Sobrino/a", "orden" => null],
            ["sql_id" => 20, "nombre" => "Adherente", "orden" => null],
            ["sql_id" => 9, "nombre" => "Primo/a", "orden" => null],
            ["sql_id" => 17, "nombre" => "Hijastro/a", "orden" => null],
            ["sql_id" => 6, "nombre" => "Nieto/a", "orden" => null],
            ["sql_id" => 14, "nombre" => "Yerno", "orden" => null],
            ["sql_id" => 3, "nombre" => "Hijo/a", "orden" => 3],
            ["sql_id" => 11, "nombre" => "Cuñado/a", "orden" => null],
            ["sql_id" => 19, "nombre" => "Responsable", "orden" => null],
            ["sql_id" => 8, "nombre" => "A cargo", "orden" => null],
            ["sql_id" => 16, "nombre" => "Tío/a", "orden" => null],
            ["sql_id" => 5, "nombre" => "Madre", "orden" => null],
            ["sql_id" => 13, "nombre" => "Concubino/a", "orden" => null],
        ];

        foreach ($estados as $estado) {
            TipoVinculo::updateOrCreate(
                ['sql_id' => $estado['sql_id']],
                [
                    'nombre' => $estado['nombre'],
                    'orden' => $estado['orden']
                ]
            );
        }

        $this->info('Estados Civiles importados exitosamente.');

        return 0;
    }
}
