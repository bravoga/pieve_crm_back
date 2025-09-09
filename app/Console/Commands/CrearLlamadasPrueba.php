<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Llamada;
use App\Models\EstadoLlamada;
use Carbon\Carbon;

class CrearLlamadasPrueba extends Command
{
    protected $signature = 'llamadas:prueba {user_id=7} {cantidad=3}';
    protected $description = 'Crear llamadas de prueba para un usuario';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $cantidad = $this->argument('cantidad');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        $this->info("Creando llamadas para usuario: {$user->name} (ID: {$user->id})");
        
        // Obtener clientes asignados
        $clientes = Cliente::whereHas('asignacionLlamada', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('periodo', '2025-08')
              ->whereIn('estado', ['asignado', 'en_progreso']);
        })->limit($cantidad)->get();
        
        if ($clientes->isEmpty()) {
            $this->error("No hay clientes asignados al usuario");
            return;
        }
        
        $this->info("Clientes encontrados: " . $clientes->count());
        
        // Estado de llamada
        $estadoLlamada = EstadoLlamada::first();
        if (!$estadoLlamada) {
            $this->error("No hay estados de llamada configurados");
            return;
        }
        
        $llamadasCreadas = 0;
        
        foreach ($clientes as $cliente) {
            // Verificar si ya tiene llamada
            $existeLlamada = Llamada::where('cliente_id', $cliente->id)
                ->where('user_id', $user->id)
                ->whereRaw("DATE_FORMAT(fecha_llamada, '%Y-%m') = '2025-08'")
                ->exists();
                
            if ($existeLlamada) {
                $this->warn("Cliente {$cliente->nombre} (ID: {$cliente->id}) ya tiene llamada");
                continue;
            }
            
            // Crear llamada
            $fechaLlamada = Carbon::parse('2025-08-15 10:00:00');
            
            Llamada::create([
                'cliente_id' => $cliente->id,
                'user_id' => $user->id,
                'estado_llamada_id' => $estadoLlamada->id,
                'telefono_utilizado' => $cliente->telefonos ?: '123456789',
                'observaciones' => 'Llamada de prueba creada automáticamente',
                'fecha_llamada' => $fechaLlamada
            ]);
            
            $this->info("✓ Llamada creada - Cliente: {$cliente->nombre} (ID: {$cliente->id})");
            $llamadasCreadas++;
        }
        
        $this->info("Total de llamadas creadas: {$llamadasCreadas}");
        
        // Verificar conteo final
        $totalLlamadas = Llamada::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha_llamada, '%Y-%m') = '2025-08'")
            ->count();
            
        $this->info("Total llamadas del usuario en 2025-08: {$totalLlamadas}");
    }
}