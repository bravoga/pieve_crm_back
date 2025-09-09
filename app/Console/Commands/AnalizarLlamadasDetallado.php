<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Llamada;

class AnalizarLlamadasDetallado extends Command
{
    protected $signature = 'llamadas:analizar {user_id=7}';
    protected $description = 'Analizar detalladamente las llamadas y sus clientes por período';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        $this->info("Análisis detallado para usuario: {$user->name} (ID: {$user->id})");
        $this->info(str_repeat("=", 60));
        
        // Obtener todas las llamadas del usuario
        $llamadas = Llamada::where('user_id', $user->id)->with('cliente')->get();
        
        $this->info("Total llamadas del usuario: " . $llamadas->count());
        
        if ($llamadas->isEmpty()) {
            $this->warn("No hay llamadas para analizar");
            return;
        }
        
        // Agrupar por período de cliente
        $llamadasPorPeriodo = [];
        
        foreach ($llamadas as $llamada) {
            $periodo = $llamada->cliente->periodo ?? 'sin-periodo';
            
            if (!isset($llamadasPorPeriodo[$periodo])) {
                $llamadasPorPeriodo[$periodo] = [];
            }
            
            $llamadasPorPeriodo[$periodo][] = $llamada;
        }
        
        $this->info("\nAnálisis por período de cliente:");
        $this->info(str_repeat("-", 60));
        
        foreach ($llamadasPorPeriodo as $periodo => $llamadasDelPeriodo) {
            $this->info("\nPeríodo: {$periodo}");
            $this->info("Cantidad de llamadas: " . count($llamadasDelPeriodo));
            
            // Clientes únicos en este período
            $clientesUnicos = collect($llamadasDelPeriodo)->groupBy('cliente_id');
            $this->info("Clientes únicos llamados: " . $clientesUnicos->count());
            
            foreach ($clientesUnicos as $clienteId => $llamadasCliente) {
                $cliente = $llamadasCliente->first()->cliente;
                $this->info("  - Cliente ID {$clienteId}: {$cliente->nombre} ({$llamadasCliente->count()} llamadas)");
                
                foreach ($llamadasCliente as $llamada) {
                    $fecha = $llamada->fecha_llamada->format('Y-m-d H:i');
                    $this->info("    * Llamada ID {$llamada->id}: {$fecha}");
                }
            }
        }
        
        // Verificar asignaciones del usuario por período
        $this->info("\n" . str_repeat("=", 60));
        $this->info("Verificación de asignaciones:");
        
        foreach (array_keys($llamadasPorPeriodo) as $periodo) {
            if ($periodo === 'sin-periodo') continue;
            
            $clientesAsignados = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso']);
            })->count();
            
            $this->info("Período {$periodo}: {$clientesAsignados} clientes asignados al usuario");
        }
    }
}