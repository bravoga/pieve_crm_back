<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Llamada;

class VerificarLlamadas extends Command
{
    protected $signature = 'llamadas:verificar {user_id=7}';
    protected $description = 'Verificar llamadas existentes para un usuario';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        $this->info("Verificando llamadas para usuario: {$user->name} (ID: {$user->id})");
        
        // Obtener todas las llamadas del usuario
        $llamadas = Llamada::where('user_id', $user->id)->get();
        
        $this->info("Total llamadas encontradas: " . $llamadas->count());
        
        foreach ($llamadas as $llamada) {
            $fechaFormateada = $llamada->fecha_llamada->format('Y-m-d H:i:s');
            $periodoFormato = $llamada->fecha_llamada->format('Y-m');
            
            $this->info("ID: {$llamada->id} | Cliente: {$llamada->cliente_id} | Fecha: {$fechaFormateada} | Período: {$periodoFormato}");
        }
        
        // Probar query específica para 2025-08
        $periodo = '2025-08';
        $llamadasPeriodo = Llamada::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha_llamada, '%Y-%m') = ?", [$periodo])
            ->count();
            
        $this->info("Llamadas en período {$periodo}: {$llamadasPeriodo}");
        
        // Probar con diferentes formatos
        $llamadasPeriodo2 = Llamada::where('user_id', $user->id)
            ->whereYear('fecha_llamada', 2025)
            ->whereMonth('fecha_llamada', 8)
            ->count();
            
        $this->info("Llamadas en 2025-08 (usando whereYear/Month): {$llamadasPeriodo2}");
    }
}