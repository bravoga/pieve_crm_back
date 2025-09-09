<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cliente;

class ContarClientesConEstados extends Command
{
    protected $signature = 'clientes:contar-estados {user_id=7} {periodo=2025-08}';
    protected $description = 'Contar clientes por estado de asignación';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $periodo = $this->argument('periodo');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        $this->info("Conteo de clientes por estado de asignación:");
        $this->info("Usuario: {$user->name} (ID: {$user->id})");
        $this->info("Período: {$periodo}");
        $this->info(str_repeat("=", 50));
        
        $estados = ['asignado', 'en_progreso', 'completado'];
        
        foreach ($estados as $estado) {
            $count = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo, $estado) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->where('estado', $estado);
            })->count();
            
            $this->info("Estado '{$estado}': {$count} clientes");
        }
        
        // Total sin estado 'completado' (filtro anterior)
        $sinCompletado = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
            $q->where('user_id', $user->id)
              ->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso']);
        })->count();
        
        $this->info(str_repeat("-", 50));
        $this->info("ANTERIOR (sin completado): {$sinCompletado} clientes");
        
        // Total con estado 'completado' (filtro nuevo)
        $conCompletado = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
            $q->where('user_id', $user->id)
              ->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
        })->count();
        
        $this->info("NUEVO (con completado): {$conCompletado} clientes");
        $this->info("Diferencia: " . ($conCompletado - $sinCompletado) . " clientes");
        
        // Verificar clientes con llamadas
        $this->info(str_repeat("-", 50));
        $clientesConLlamadas = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
            })
            ->whereHas('llamadas', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
            
        $this->info("Clientes con llamadas del usuario: {$clientesConLlamadas}");
    }
}