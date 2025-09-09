<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\LlamadaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProbarEstadisticas extends Command
{
    protected $signature = 'estadisticas:probar {user_id=7} {periodo=2025-08}';
    protected $description = 'Probar endpoint de estadísticas simulando autenticación';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $periodo = $this->argument('periodo');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario {$userId} no encontrado");
            return;
        }
        
        // Simular autenticación
        Auth::login($user);
        
        $this->info("Probando estadísticas para usuario: {$user->name}");
        $this->info("Período: {$periodo}");
        $this->info(str_repeat("=", 50));
        
        // Crear request simulado
        $request = new Request(['periodo' => $periodo]);
        
        // Llamar al controlador
        $controller = new LlamadaController();
        $response = $controller->misEstadisticas($request);
        
        // Obtener datos de la respuesta
        $data = json_decode($response->getContent(), true);
        
        if ($data) {
            $this->info("✅ Endpoint funcionando correctamente");
            $this->info("");
            
            $resumen = $data['resumen'];
            $this->info("RESUMEN DEL PERÍODO:");
            $this->info("- Total asignados: {$resumen['total_asignados']}");
            $this->info("- Clientes llamados: {$resumen['clientes_llamados']}");
            $this->info("- Clientes pendientes: {$resumen['clientes_pendientes']}");
            $this->info("- Total llamadas: {$resumen['total_llamadas']}");
            $this->info("- Porcentaje progreso: {$resumen['porcentaje_progreso']}%");
            
            $this->info("");
            $this->info("LLAMADAS POR DÍA (últimos 7 días):");
            foreach ($data['llamadas_por_dia'] as $dia) {
                $this->info("- {$dia['fecha']}: {$dia['llamadas']} llamadas");
            }
        } else {
            $this->error("❌ Error en el endpoint");
        }
    }
}