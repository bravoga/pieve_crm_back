<?php

namespace App\Console\Commands;


use App\Models\TipoCondicionFiscal;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportUsuario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:usuario';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa Usuario';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('ldap:import users gabrielbravo@grupopieve.com --no-interaction');

        $this->info('Usuario  importado exitosamente.');

        return 0;
    }
}
