<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Llamada;

class VerificarDatosPeriodo extends Command
{
    protected $signature = 'datos:periodo {user_id=7} {periodo=2025-08}';
    protected $description = 'Verificar la relación entre clientes por período y sus llamadas';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $periodo = $this->argument('periodo');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        $this->info("Analizando datos para usuario: {$user->name} (ID: {$user->id}) - Período: {$periodo}");
        
        // Clientes asignados del período
        $clientesPeriodo = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
            $q->where('user_id', $user->id)
              ->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso']);
        })->get();
        
        $this->info("Clientes asignados del período {$periodo}: " . $clientesPeriodo->count());
        
        // Verificar si estos clientes tienen llamadas
        $clientesConLlamadas = 0;
        $clientesSinLlamadas = 0;
        
        foreach ($clientesPeriodo->take(10) as $cliente) {
            $llamadas = Llamada::where('user_id', $user->id)
                ->where('cliente_id', $cliente->id)
                ->get();
                
            if ($llamadas->count() > 0) {
                $this->info("  ✓ Cliente {$cliente->id} ({$cliente->nombre}) - {$llamadas->count()} llamadas");
                $clientesConLlamadas++;
                
                foreach ($llamadas as $llamada) {
                    $this->info("    - Llamada ID {$llamada->id}: {$llamada->fecha_llamada}");
                }
            } else {
                $this->info("  ○ Cliente {$cliente->id} ({$cliente->nombre}) - sin llamadas");
                $clientesSinLlamadas++;
            }
        }
        
        if ($clientesPeriodo->count() > 10) {
            $this->info("  ... (mostrando solo los primeros 10 clientes)");
        }
        
        // Conteos totales
        $totalClientesConLlamadas = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso']);
            })
            ->whereHas('llamadas', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
            
        $totalClientesSinLlamadas = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso']);
            })
            ->whereDoesntHave('llamadas', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
        
        $this->info("\nResumen:");
        $this->info("- Clientes del período {$periodo} CON llamadas: {$totalClientesConLlamadas}");
        $this->info("- Clientes del período {$periodo} SIN llamadas: {$totalClientesSinLlamadas}");
        $this->info("- Total clientes del período: " . ($totalClientesConLlamadas + $totalClientesSinLlamadas));
    }
}