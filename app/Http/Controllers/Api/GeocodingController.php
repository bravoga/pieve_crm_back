<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    private GeocodingService $geocodingService;
    
    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }
    
    /**
     * Ejecutar geocodificación de direcciones pendientes
     */
    public function geocodificar(): JsonResponse
    {
        $resultado = $this->geocodingService->geocodificarPendientes();
        
        return response()->json([
            'message' => $resultado['message'],
            'geocodificacion' => [
                'procesados' => $resultado['procesados'],
                'exitosos' => $resultado['exitosos'],
                'errores' => $resultado['errores'],
                'errores_detalle' => array_slice($resultado['errores_detalle'] ?? [], 0, 10) // Solo primeros 10 errores
            ]
        ]);
    }
    
    /**
     * Obtener estadísticas de geocodificación
     */
    public function estadisticas(): JsonResponse
    {
        $estadisticas = $this->geocodingService->obtenerEstadisticas();
        
        return response()->json([
            'estadisticas' => $estadisticas
        ]);
    }
    
    /**
     * Geocodificar un cliente específico
     */
    public function geocodificarCliente(Cliente $cliente): JsonResponse
    {
        $resultado = $this->geocodingService->geocodificarDireccion($cliente);
        
        if ($resultado['success']) {
            return response()->json([
                'message' => $resultado['message'],
                'cliente' => [
                    'id' => $cliente->id,
                    'certi' => $cliente->certi,
                    'nombre' => $cliente->nombre,
                    'direccion' => $cliente->direccion,
                    'direccion_validada' => $cliente->direccion_validada,
                    'lat' => $cliente->lat,
                    'lng' => $cliente->lng,
                    'geocoding_status' => $cliente->geocoding_status
                ]
            ]);
        }
        
        return response()->json([
            'message' => $resultado['message']
        ], 400);
    }
    
    /**
     * Marcar cliente como geocodificado manualmente
     */
    public function marcarComoManual(Request $request, Cliente $cliente): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);
        
        $exito = $this->geocodingService->marcarComoManual(
            $cliente, 
            $request->lat, 
            $request->lng
        );
        
        if ($exito) {
            return response()->json([
                'message' => 'Cliente marcado como geocodificado manualmente',
                'cliente' => [
                    'id' => $cliente->id,
                    'certi' => $cliente->certi,
                    'nombre' => $cliente->nombre,
                    'lat' => $cliente->lat,
                    'lng' => $cliente->lng,
                    'geocoding_status' => $cliente->geocoding_status
                ]
            ]);
        }
        
        return response()->json([
            'message' => 'Error al marcar cliente como manual'
        ], 500);
    }
    
    /**
     * Resetear estado de geocodificación de un cliente
     */
    public function resetearEstado(Cliente $cliente): JsonResponse
    {
        try {
            $cliente->update([
                'lat' => null,
                'lng' => null,
                'direccion_validada' => null,
                'geocoding_status' => 'pending'
            ]);
            
            return response()->json([
                'message' => 'Estado de geocodificación reseteado',
                'cliente' => [
                    'id' => $cliente->id,
                    'certi' => $cliente->certi,
                    'geocoding_status' => $cliente->geocoding_status
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al resetear estado de geocodificación'
            ], 500);
        }
    }
}