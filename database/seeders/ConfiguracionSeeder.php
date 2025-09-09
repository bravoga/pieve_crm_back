<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('configuracion')->insert([
            [
                'clave' => 'periodo_actual',
                'valor' => '2025-08',
                'descripcion' => 'Período actual de trabajo para las llamadas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
