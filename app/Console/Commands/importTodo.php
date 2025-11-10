<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\NovedadesSeeder;
use Database\Seeders\NotificacionesSeeder;

class ImportTodo extends Command
{
    protected $signature = 'import:setup {--force : Force the operation to run without confirmation}';
    protected $description = 'Importar todos los datos necesarios';

    /**
     * Comandos de importación (artisan) a ejecutar.
     */
    protected array $commands = [
        'import:vinculos',
        'import:tarjetas',
        'import:bancos',
        'import:condicionfiscal',
        'import:convenios',
        'import:estadocivil',
        'import:paises',
        'import:sucursales',
        'import:localidades',
        'import:tiposdeclaracionjurada',
        'import:tiposgruposfamiliares',
        'import:tiposlogs',
        'import:tiposPagos',
        'import:tipospersonas',
        'import:tiposolicitudes',
        'import:tiposolicitudestado',
        'import:vniculos',
         'import:usuario',
    ];

    /**
     * Seeders a ejecutar al final del proceso.
     */
    protected array $seeders = [
        NovedadesSeeder::class,
        NotificacionesSeeder::class,
    ];

    public function handle()
    {
        $this->info('');
        $this->info('╔═════════════════════════════════════════╗');
        $this->info('║         Importación de Datos SETUP      ║');
        $this->info('╚═════════════════════════════════════════╝');
        $this->info('');

        if (
            !$this->option('force')
            && !$this->confirm('Esta operación borrará todos los datos existentes. ¿Desea continuar?')
        ) {
            $this->warn('Operación cancelada.');
            return 1;
        }

        $this->warn('⚠️  ADVERTENCIA: Se borrarán todos los datos existentes.');
        $this->info('');

        // Si querés reiniciar todo, descomentá:
         Artisan::call('migrate:fresh', $this->option('force') ? ['--force' => true] : []);

        $totalPasos = count($this->commands) + count($this->seeders);
        $bar = $this->output->createProgressBar($totalPasos);
        $bar->start();

        // 1) Ejecutar comandos de importación
        foreach ($this->commands as $command) {
            $this->info("\n Ejecutando: {$command}");
            Artisan::call($command, $this->option('force') ? ['--force' => true] : []);
            $this->info(' ' . trim(Artisan::output()));
            $bar->advance();
        }

        // 2) Ejecutar seeders requeridos
        foreach ($this->seeders as $seederClass) {
            $this->info("\n Ejecutando seeder: {$seederClass}");
            Artisan::call('db:seed', array_filter([
                '--class' => $seederClass,
                '--force' => $this->option('force') ? true : null,
            ]));
            $this->info(' ' . trim(Artisan::output()));
            $bar->advance();
        }

        $bar->finish();

        $this->info('');
        $this->info('');
        $this->info('╔═════════════════════════════════════════╗');
        $this->info('║    ¡Importación completada con éxito!   ║');
        $this->info('╚═════════════════════════════════════════╝');

        return 0;
    }
}
