<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use App\Models\AsignacionLlamada;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsignacionLlamadaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AsignacionLlamada::with(['cliente', 'llamador', 'asignadoPor']);
        
        if ($request->has('periodo')) {
            $query->byPeriodo($request->periodo);
        }
        
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }
        
        if ($request->has('estado')) {
            $query->byEstado($request->estado);
        }
        
        $asignaciones = $query->latest('fecha_asignacion')->paginate(20);
        
        return response()->json($asignaciones);
    }

    public function resumenAsignaciones(Request $request): JsonResponse
    {
        $periodo = $request->get('periodo', now()->format('Y-m'));
        $canal = $request->get('canal');
        
        // Total de clientes disponibles para llamar
        $clientesQuery = Cliente::paraLlamadas()
            ->byPeriodo($periodo)
            ->whereDoesntHave('asignacionLlamada', function ($query) use ($periodo) {
                $query->byPeriodo($periodo)
                      ->whereIn('estado', ['asignado', 'en_progreso']);
            });
            
        // Aplicar filtro de canal si se proporciona
        if ($canal) {
            $clientesQuery->where('nbre_convenio', $canal);
        }
            
        $clientesDisponibles = $clientesQuery->count();
            
        // Llamadores activos disponibles
        $llamadoresDisponibles = User::llamadores()->count();
        
        // Estadísticas de asignaciones actuales
        $estadisticas = AsignacionLlamada::byPeriodo($periodo)
            ->selectRaw('
                estado,
                COUNT(*) as total,
                COUNT(DISTINCT user_id) as llamadores_asignados
            ')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');
            
        return response()->json([
            'periodo' => $periodo,
            'clientes_disponibles' => $clientesDisponibles,
            'llamadores_disponibles' => $llamadoresDisponibles,
            'estadisticas' => $estadisticas,
            'puede_asignar' => $clientesDisponibles > 0 && $llamadoresDisponibles > 0
        ]);
    }

    public function asignarAutomatico(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'periodo' => 'required|string|size:7', // YYYY-MM
            'llamadores' => 'required|array|min:1',
            'llamadores.*' => 'exists:users,id',
            'fecha_vencimiento' => 'nullable|date|after:today',
            'notas' => 'nullable|string|max:1000',
            'canal' => 'nullable|string' // Filtro por canal (nbre_convenio)
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Validar que los usuarios sean llamadores activos
            $llamadores = User::whereIn('id', $request->llamadores)
                ->llamadores()
                ->get();
                
            if ($llamadores->count() !== count($request->llamadores)) {
                return response()->json([
                    'message' => 'Uno o más usuarios seleccionados no son llamadores activos'
                ], 422);
            }
            
            // Obtener clientes disponibles para asignar
            $clientesQuery = Cliente::paraLlamadas()
                ->byPeriodo($request->periodo)
                ->whereDoesntHave('asignacionLlamada', function ($query) use ($request) {
                    $query->byPeriodo($request->periodo)
                          ->whereIn('estado', ['asignado', 'en_progreso']);
                });
                
            // Aplicar filtro de canal si se proporciona
            if ($request->has('canal') && $request->canal) {
                $clientesQuery->where('nbre_convenio', $request->canal);
            }
            
            $clientes = $clientesQuery->get();
                
            if ($clientes->isEmpty()) {
                return response()->json([
                    'message' => 'No hay clientes disponibles para asignar en este período'
                ], 422);
            }
            
            // Dividir clientes entre llamadores
            $clientesPorLlamador = $clientes->count();
            $llamadoresCount = $llamadores->count();
            $clientesPorPersona = intval($clientesPorLlamador / $llamadoresCount);
            $clientesRestantes = $clientesPorLlamador % $llamadoresCount;
            
            $fechaVencimiento = $request->fecha_vencimiento ? 
                Carbon::parse($request->fecha_vencimiento) : 
                now()->addDays(7); // 7 días por defecto
            
            $asignacionesCreadas = [];
            $clienteIndex = 0;
            
            foreach ($llamadores as $index => $llamador) {
                // Calcular cuántos clientes asignar a este llamador
                $clientesParaEsteLlamador = $clientesPorPersona;
                if ($index < $clientesRestantes) {
                    $clientesParaEsteLlamador++; // Distribuir el resto
                }
                
                // Crear asignaciones para este llamador
                for ($i = 0; $i < $clientesParaEsteLlamador && $clienteIndex < $clientes->count(); $i++) {
                    $cliente = $clientes[$clienteIndex];
                    
                    $asignacion = AsignacionLlamada::create([
                        'cliente_id' => $cliente->id,
                        'user_id' => $llamador->id,
                        'asignado_por' => Auth::id(),
                        'periodo' => $request->periodo,
                        'estado' => 'asignado',
                        'fecha_asignacion' => now(),
                        'fecha_vencimiento' => $fechaVencimiento,
                        'notas' => $request->notas
                    ]);
                    
                    $asignacionesCreadas[] = $asignacion;
                    $clienteIndex++;
                }
            }
            
            DB::commit();
            
            // Preparar resumen de asignaciones
            $resumen = $llamadores->map(function ($llamador) use ($asignacionesCreadas) {
                $asignacionesDelLlamador = collect($asignacionesCreadas)
                    ->where('user_id', $llamador->id);
                    
                return [
                    'llamador' => [
                        'id' => $llamador->id,
                        'name' => $llamador->name
                    ],
                    'clientes_asignados' => $asignacionesDelLlamador->count(),
                    'asignaciones' => $asignacionesDelLlamador->values()
                ];
            });
            
            return response()->json([
                'message' => 'Asignación automática completada exitosamente',
                'resumen' => [
                    'total_clientes_asignados' => count($asignacionesCreadas),
                    'total_llamadores' => $llamadores->count(),
                    'periodo' => $request->periodo,
                    'fecha_vencimiento' => $fechaVencimiento->toDateString(),
                    'asignaciones_por_llamador' => $resumen
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error al realizar la asignación automática: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reasignar(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nuevo_llamador_id' => 'required|exists:users,id',
            'notas' => 'nullable|string|max:1000'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $asignacion = AsignacionLlamada::findOrFail($id);
        
        // Validar que el nuevo usuario sea un llamador activo
        $nuevoLlamador = User::find($request->nuevo_llamador_id);
        if (!$nuevoLlamador->isLlamador() || !$nuevoLlamador->activo) {
            return response()->json([
                'message' => 'El usuario seleccionado no es un llamador activo'
            ], 422);
        }
        
        $asignacion->update([
            'user_id' => $request->nuevo_llamador_id,
            'asignado_por' => Auth::id(),
            'notas' => $request->notas ?? $asignacion->notas,
            'estado' => 'asignado' // Reset estado si estaba en progreso
        ]);
        
        $asignacion->load(['cliente', 'llamador', 'asignadoPor']);
        
        return response()->json([
            'message' => 'Asignación reasignada exitosamente',
            'asignacion' => $asignacion
        ]);
    }

    public function cancelar(string $id): JsonResponse
    {
        $asignacion = AsignacionLlamada::findOrFail($id);
        
        if ($asignacion->estado === 'completado') {
            return response()->json([
                'message' => 'No se puede cancelar una asignación completada'
            ], 422);
        }
        
        $asignacion->cancelar();
        
        return response()->json([
            'message' => 'Asignación cancelada exitosamente'
        ]);
    }

    public function estadisticasLlamador(Request $request): JsonResponse
    {
        $userId = $request->get('user_id', Auth::id());
        $periodo = $request->get('periodo', now()->format('Y-m'));
        
        $estadisticas = AsignacionLlamada::byUser($userId)
            ->byPeriodo($periodo)
            ->selectRaw('
                estado,
                COUNT(*) as total
            ')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');
            
        // Calcular progreso
        $totalAsignadas = $estadisticas->sum('total');
        $completadas = $estadisticas->get('completado')?->total ?? 0;
        $enProgreso = $estadisticas->get('en_progreso')?->total ?? 0;
        $porcentajeCompleto = $totalAsignadas > 0 ? round(($completadas / $totalAsignadas) * 100, 2) : 0;
        
        return response()->json([
            'user_id' => $userId,
            'periodo' => $periodo,
            'total_asignadas' => $totalAsignadas,
            'completadas' => $completadas,
            'en_progreso' => $enProgreso,
            'pendientes' => $estadisticas->get('asignado')?->total ?? 0,
            'canceladas' => $estadisticas->get('cancelado')?->total ?? 0,
            'porcentaje_completo' => $porcentajeCompleto,
            'estadisticas' => $estadisticas
        ]);
    }

    public function llamadoresDisponibles(): JsonResponse
    {
        $llamadores = User::llamadores()
            ->select('id', 'name', 'email')
            ->get();
            
        return response()->json([
            'llamadores' => $llamadores
        ]);
    }
}
