<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeocodingService;

class GeocodeClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocode:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending geocoding for clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de geocodificación...');

        $geocodingService = new GeocodingService();
        $resultado = $geocodingService->geocodificarPendientes();

        if ($resultado['success']) {
            $this->info('✅ ' . $resultado['message']);
            if (isset($resultado['procesados'])) {
                $this->line('Clientes procesados: ' . $resultado['procesados']);
            }
            if (isset($resultado['exitosos'])) {
                $this->line('Geocodificaciones exitosas: ' . $resultado['exitosos']);
            }
            if (isset($resultado['errores'])) {
                $this->line('Errores: ' . $resultado['errores']);
            }
        } else {
            $this->error('❌ ' . $resultado['message']);
        }

        return $resultado['success'] ? Command::SUCCESS : Command::FAILURE;
    }
}
