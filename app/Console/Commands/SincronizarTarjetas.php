<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class SincronizarTarjetas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sincronizar:tarjetas {mes} {anio} {sucursal} {cartilla}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar tarjetas desde el procedimiento almacenado usp_tarjetas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mes = str_pad($this->argument('mes'), 2, '0', STR_PAD_LEFT);
        $anio = $this->argument('anio');
        $sucursal = $this->argument('sucursal');
        $cartilla = $this->argument('cartilla');
        
        // Crear el período en formato YYYY-MM
        $periodo = "{$anio}-{$mes}";

        $this->info("Sincronizando tarjetas para: {$mes}/{$anio} - Sucursal: {$sucursal} - Cartilla: {$cartilla}");
        $this->info("Período: {$periodo}");

        try {
            // Probar conexión primero
            $this->info('Probando conexión a sqlGPIEVE...');
            $connection = DB::connection('sqlGPIEVE');
            $connection->getPdo();
            $this->info('✓ Conexión exitosa a sqlGPIEVE');

            // Ejecutar procedimiento almacenado
            $this->info('Ejecutando procedimiento almacenado...');
            $results = $connection->select("EXEC usp_tarjetas ?, ?, ?, ?", [
                $mes, $anio, $sucursal, $cartilla
            ]);

            $this->info('✓ Procedimiento ejecutado. Registros obtenidos: ' . count($results));

            if (empty($results)) {
                $this->warn('No se obtuvieron registros del procedimiento almacenado');
                return 0;
            }

            // Mostrar estructura del primer registro
            $firstRecord = $results[0];
            $this->info('Estructura del primer registro:');
            foreach ($firstRecord as $key => $value) {
                $this->line("  {$key}: {$value}");
            }

            $procesados = 0;
            $exitosos = 0;
            $errores = 0;

            foreach ($results as $record) {
                $procesados++;
                
                try {
                    // Convertir objeto a array
                    $recordArray = (array) $record;
                    
                    // Verificar si el cliente ya existe en el mismo período
                    $clienteExistente = Cliente::where('certi', $record->certi)
                        ->where('periodo', $periodo)
                        ->first();
                    
                    if ($clienteExistente) {
                        $this->line("Actualizando cliente existente en período {$periodo}: {$record->certi}");
                        $clienteExistente->update([
                            'nombre' => $record->nombre ?? $clienteExistente->nombre,
                            'telefonos' => $record->Telefonos ?? $clienteExistente->telefonos,
                            'direccion' => $record->Direccion ?? $clienteExistente->direccion,
                            'importe' => (float) ($record->importe ?? 0),
                            'nbre_convenio' => $record->NbreConvenio ?? null,
                            'localidad' => $record->localidad ?? null,
                            'dni' => $record->dni ?? null,
                            'periodo' => $periodo, // Asignar el período actual
                        ]);
                    } else {
                        // Verificar si el cliente existe en otro período para logging
                        $clienteEnOtroPeriodo = Cliente::where('certi', $record->certi)->first();
                        if ($clienteEnOtroPeriodo) {
                            $this->line("Migrando cliente {$record->certi} desde período {$clienteEnOtroPeriodo->periodo} a período {$periodo}");
                        } else {
                            $this->line("Creando nuevo cliente para período {$periodo}: {$record->certi}");
                        }
                        Cliente::create([
                            'certi' => $record->certi,
                            'nombre' => $record->nombre,
                            'telefonos' => $record->Telefonos ?? null,
                            'direccion' => $record->Direccion,
                            'importe' => (float) ($record->importe ?? 0),
                            'nbre_convenio' => $record->NbreConvenio ?? null,
                            'localidad' => $record->localidad ?? null,
                            'dni' => $record->dni ?? null,
                            'periodo' => $periodo, // Asignar el período actual
                            'geocoding_status' => 'pending',
                        ]);
                    }
                    
                    $exitosos++;
                } catch (\Exception $e) {
                    $errores++;
                    $this->error("Error procesando registro {$procesados}: " . $e->getMessage());
                }
            }

            $this->info("\n=== Resumen de sincronización ===");
            $this->info("Registros procesados: {$procesados}");
            $this->info("Exitosos: {$exitosos}");
            $this->info("Errores: {$errores}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error en la sincronización: ' . $e->getMessage());
            return 1;
        }
    }
}