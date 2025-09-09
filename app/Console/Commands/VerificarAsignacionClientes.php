<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cliente;

class VerificarAsignacionClientes extends Command
{
    protected $signature = 'clientes:verificar-asignacion {user_id=7} {periodo=2025-08}';
    protected $description = 'Verificar asignación específica de clientes con llamadas';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $periodo = $this->argument('periodo');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        // IDs de clientes que tienen llamadas del usuario
        $clientesConLlamadas = [2, 3, 6, 7, 9, 10];
        
        $this->info("Verificando asignación de clientes con llamadas:");
        $this->info("Usuario: {$user->name} (ID: {$user->id})");
        $this->info("Período: {$periodo}");
        $this->info(str_repeat("=", 80));
        
        foreach ($clientesConLlamadas as $clienteId) {
            $cliente = Cliente::find($clienteId);
            
            if (!$cliente) {
                $this->error("Cliente ID {$clienteId} no encontrado");
                continue;
            }
            
            $this->info("\nCliente ID {$clienteId}: {$cliente->nombre}");
            $this->info("- Período del cliente: {$cliente->periodo}");
            $this->info("- Tipo de contacto: {$cliente->tipo_contacto}");
            
            // Verificar asignación
            $asignacion = $cliente->asignacionLlamada()
                ->where('user_id', $user->id)
                ->where('periodo', $periodo)
                ->whereIn('estado', ['asignado', 'en_progreso'])
                ->first();
                
            if ($asignacion) {
                $this->info("✓ ASIGNADO - Estado: {$asignacion->estado}");
            } else {
                $this->error("✗ NO ASIGNADO o estado incorrecto");
                
                // Buscar cualquier asignación para este cliente
                $cualquierAsignacion = $cliente->asignacionLlamada()
                    ->where('user_id', $user->id)
                    ->first();
                    
                if ($cualquierAsignacion) {
                    $this->warn("  Tiene asignación pero: período={$cualquierAsignacion->periodo}, estado={$cualquierAsignacion->estado}");
                } else {
                    $this->warn("  No tiene ninguna asignación a este usuario");
                }
            }
            
            // Verificar si cumple con los filtros base
            $cumpleFiltros = $cliente->whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso']);
            })->exists();
            
            $this->info("- Cumple filtros base: " . ($cumpleFiltros ? "SÍ" : "NO"));
        }
        
        $this->info("\n" . str_repeat("=", 80));
        $this->info("Resumen de filtros que deberían aplicar en misClientesParaLlamar:");
        $this->info("1. Cliente debe tener asignación al usuario {$user->id}");
        $this->info("2. Asignación debe ser del período {$periodo}");
        $this->info("3. Estado de asignación: 'asignado' o 'en_progreso'");
        $this->info("4. Cliente debe tener llamadas del usuario {$user->id}");
    }
}