<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private const BATCH_SIZE = 50;
    private const GOOGLE_MAPS_API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';
    
    private $apiKey;
    
    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }
    
    /**
     * Geocodifica direcciones pendientes en lotes
     */
    public function geocodificarPendientes(): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => 'Google Maps API key no configurada'
            ];
        }
        
        // Obtener período actual desde configuración
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor');
        if (!$periodoActual) {
            $periodoActual = now()->format('Y-m');
        }

        $clientesPendientes = Cliente::where('geocoding_status', 'pending')
            ->where('tipo_contacto', 'visita')
            ->where('periodo', $periodoActual)
            ->limit(self::BATCH_SIZE)
            ->get();
            
        if ($clientesPendientes->isEmpty()) {
            return [
                'success' => true,
                'message' => "No hay clientes pendientes de geocodificación (período: {$periodoActual}, tipo: visita)",
                'procesados' => 0
            ];
        }
        
        $procesados = 0;
        $exitosos = 0;
        $errores = 0;
        $erroresDetalle = [];
        
        foreach ($clientesPendientes as $cliente) {
            try {
                $resultado = $this->geocodificarDireccion($cliente);
                $procesados++;
                
                if ($resultado['success']) {
                    $exitosos++;
                } else {
                    $errores++;
                    $erroresDetalle[] = [
                        'cliente_id' => $cliente->id,
                        'certi' => $cliente->certi,
                        'direccion' => $cliente->direccion,
                        'error' => $resultado['message']
                    ];
                }
                
                // Pausa entre requests para respetar rate limits
                usleep(50000); // 50ms (optimizado para lotes más grandes)
                
            } catch (\Exception $e) {
                $errores++;
                $erroresDetalle[] = [
                    'cliente_id' => $cliente->id,
                    'certi' => $cliente->certi,
                    'direccion' => $cliente->direccion,
                    'error' => $e->getMessage()
                ];
                
                Log::error('Error geocodificando cliente', [
                    'cliente_id' => $cliente->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return [
            'success' => true,
            'message' => "Geocodificación completada (período: {$periodoActual}, tipo: visita): {$exitosos} exitosos, {$errores} errores de {$procesados} procesados",
            'procesados' => $procesados,
            'exitosos' => $exitosos,
            'errores' => $errores,
            'errores_detalle' => $erroresDetalle
        ];
    }
    
    /**
     * Geocodifica una dirección específica
     */
    public function geocodificarDireccion(Cliente $cliente): array
    {
        try {
            $direccionCompleta = $this->construirDireccionCompleta($cliente);
            
            $response = Http::timeout(10)->get(self::GOOGLE_MAPS_API_URL, [
                'address' => $direccionCompleta,
                'key' => $this->apiKey,
                'region' => 'co', // Colombia
                'language' => 'es'
            ]);
            
            if (!$response->successful()) {
                $cliente->update(['geocoding_status' => 'failed']);
                return [
                    'success' => false,
                    'message' => 'Error en la respuesta de Google Maps API'
                ];
            }
            
            $data = $response->json();
            
            if ($data['status'] !== 'OK' || empty($data['results'])) {
                $cliente->update(['geocoding_status' => 'failed']);
                return [
                    'success' => false,
                    'message' => "Google Maps: {$data['status']}"
                ];
            }
            
            $resultado = $data['results'][0];
            $coordenadas = $resultado['geometry']['location'];
            $direccionFormateada = $resultado['formatted_address'];
            
            // Actualizar cliente con coordenadas
            $cliente->update([
                'lat' => $coordenadas['lat'],
                'lng' => $coordenadas['lng'],
                'direccion_validada' => $direccionFormateada,
                'geocoding_status' => 'validated'
            ]);
            
            return [
                'success' => true,
                'message' => 'Geocodificación exitosa',
                'coordenadas' => $coordenadas,
                'direccion_formateada' => $direccionFormateada
            ];
            
        } catch (\Exception $e) {
            $cliente->update(['geocoding_status' => 'failed']);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Construye la dirección completa para geocodificación
     */
    private function construirDireccionCompleta(Cliente $cliente): string
    {
        $direccion = $cliente->direccion;
        
        // Agregar localidad si existe
        if ($cliente->localidad) {
            $direccion .= ', ' . $cliente->localidad;
        }
        
        // Agregar provincia (por defecto "Salta")
        if ($cliente->provincia) {
            $direccion .= ', ' . $cliente->provincia;
        }
        
        // Agregar país (por defecto "Argentina")
        if ($cliente->pais) {
            $direccion .= ', ' . $cliente->pais;
        }
        
        return trim($direccion);
    }
    
    /**
     * Obtiene estadísticas de geocodificación
     */
    public function obtenerEstadisticas(): array
    {
        // Obtener período actual desde configuración
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor');
        if (!$periodoActual) {
            $periodoActual = now()->format('Y-m');
        }

        // Filtrar solo clientes del período actual con tipo_contacto = 'visita'
        $queryBase = Cliente::where('periodo', $periodoActual)
            ->where('tipo_contacto', 'visita');

        return [
            'total' => (clone $queryBase)->count(),
            'pendientes' => (clone $queryBase)->where('geocoding_status', 'pending')->count(),
            'validados' => (clone $queryBase)->where('geocoding_status', 'validated')->count(),
            'manuales' => (clone $queryBase)->where('geocoding_status', 'manual')->count(),
            'fallidos' => (clone $queryBase)->where('geocoding_status', 'failed')->count(),
        ];
    }
    
    /**
     * Marca un cliente como geocodificado manualmente
     */
    public function marcarComoManual(Cliente $cliente, float $lat, float $lng): bool
    {
        try {
            $cliente->update([
                'lat' => $lat,
                'lng' => $lng,
                'geocoding_status' => 'manual'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error marcando cliente como manual', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}