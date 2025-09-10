<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Llamada;
use App\Models\EstadoLlamada;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LlamadaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Llamada::with(['cliente', 'user', 'estadoLlamada']);
        
        if ($request->has('periodo')) {
            $query->byPeriodo($request->periodo);
        }
        
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }
        
        if ($request->has('fecha')) {
            $query->byFecha($request->fecha);
        }
        
        if ($request->has('estado_llamada_id')) {
            $query->where('estado_llamada_id', $request->estado_llamada_id);
        }
        
        $llamadas = $query->latest('fecha_llamada')->paginate(20);
        
        return response()->json($llamadas);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
            'estado_llamada_id' => 'required|exists:estados_llamada,id',
            'telefono_utilizado' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'fecha_llamada' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = Auth::user();
        
        $llamada = Llamada::create([
            'cliente_id' => $request->cliente_id,
            'user_id' => $user->id,
            'estado_llamada_id' => $request->estado_llamada_id,
            'telefono_utilizado' => $request->telefono_utilizado,
            'observaciones' => $request->observaciones,
            'fecha_llamada' => $request->fecha_llamada ?? now(),
        ]);
        
        // Si es un llamador, marcar la asignación como completada
        if ($user->isLlamador()) {
            $cliente = Cliente::find($request->cliente_id);
            $asignacion = $cliente->asignacionLlamada()
                ->where('user_id', $user->id)
                ->whereIn('estado', ['asignado', 'en_progreso'])
                ->first();
                
            if ($asignacion) {
                $asignacion->marcarComoCompletado();
            }
        }
        
        $llamada->load(['cliente', 'user', 'estadoLlamada']);
        
        return response()->json($llamada, 201);
    }

    public function show(string $id): JsonResponse
    {
        $llamada = Llamada::with(['cliente', 'user', 'estadoLlamada'])->findOrFail($id);
        
        return response()->json($llamada);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $llamada = Llamada::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'estado_llamada_id' => 'sometimes|exists:estados_llamada,id',
            'telefono_utilizado' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'fecha_llamada' => 'sometimes|date',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $llamada->update($request->only([
            'estado_llamada_id',
            'telefono_utilizado',
            'observaciones',
            'fecha_llamada'
        ]));
        
        $llamada->load(['cliente', 'user', 'estadoLlamada']);
        
        return response()->json($llamada);
    }

    public function destroy(string $id): JsonResponse
    {
        $llamada = Llamada::findOrFail($id);
        $llamada->delete();
        
        return response()->json(['message' => 'Llamada eliminada exitosamente']);
    }
    
    public function clientesParaLlamar(Request $request): JsonResponse
    {
        $user = Auth::user();
        $periodoDefecto = Configuracion::obtenerValor('periodo_actual', now()->format('Y-m'));
        $periodo = $request->has('periodo') ? $request->periodo : $periodoDefecto;
        
        $query = Cliente::with(['llamadas' => function($q) {
            $q->latest('fecha_llamada')->limit(1);
        }]);
        
        // Si es un llamador, solo mostrar sus clientes asignados
        if ($user->isLlamador()) {
            $query->whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso']);
            });
        } else {
            // Para admin y cobradores, mostrar todos los clientes para llamadas
            $query->paraLlamadas()->byPeriodo($periodo);
        }
        
        if ($request->has('buscar')) {
            $query->buscar($request->buscar);
        }
        
        // Filtro por estado de llamada (si fue llamado o está pendiente)
        if ($request->has('estado_llamada')) {
            if ($request->estado_llamada === 'llamado') {
                // Solo clientes que tienen al menos una llamada
                $query->whereHas('llamadas');
            } elseif ($request->estado_llamada === 'pendiente') {
                // Solo clientes que NO tienen llamadas
                $query->whereDoesntHave('llamadas');
            }
        }
        
        // Filtro por usuario llamador asignado
        if ($request->has('usuario_id')) {
            $query->whereHas('asignacionLlamada', function($q) use ($request, $periodo) {
                $q->where('user_id', $request->usuario_id)
                  ->where('periodo', $periodo);
            });
        }
        
        $perPage = $request->get('per_page', 20); // Por defecto 20, pero permite personalización
        $clientes = $query->paginate($perPage);
        
        return response()->json($clientes);
    }
    
    public function estadosLlamada(): JsonResponse
    {
        $estados = EstadoLlamada::activo()->ordenado()->get();
        
        return response()->json($estados);
    }
    
    public function usuariosLlamadores(): JsonResponse
    {
        $usuarios = \App\Models\User::llamadores()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
        
        return response()->json($usuarios);
    }
    
    public function periodoActual(): JsonResponse
    {
        $periodo = Configuracion::obtenerValor('periodo_actual', now()->format('Y-m'));
        
        return response()->json(['periodo' => $periodo]);
    }
    
    public function tomarCliente(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|exists:clientes,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = Auth::user();
        $cliente = Cliente::with(['llamadas', 'asignacionLlamada'])->findOrFail($request->cliente_id);
        
        // Si es un llamador, verificar que tiene asignado este cliente
        if ($user->isLlamador()) {
            $asignacion = $cliente->asignacionLlamada()
                ->where('user_id', $user->id)
                ->whereIn('estado', ['asignado', 'en_progreso'])
                ->first();
                
            if (!$asignacion) {
                return response()->json([
                    'message' => 'Este cliente no está asignado a ti o ya fue completado'
                ], 422);
            }
            
            // Marcar asignación como en progreso
            if ($asignacion->estado === 'asignado') {
                $asignacion->marcarComoEnProgreso();
            }
        }
        
        $ultimaLlamada = $cliente->llamadas()
            ->where('user_id', $user->id)
            ->whereDate('fecha_llamada', today())
            ->first();
            
        if ($ultimaLlamada) {
            return response()->json([
                'message' => 'Ya realizaste una llamada a este cliente hoy',
                'cliente' => $cliente,
                'ultima_llamada' => $ultimaLlamada
            ], 422);
        }
        
        return response()->json(['cliente' => $cliente]);
    }
    
    /**
     * Obtener clientes para llamar específicamente para el usuario llamador logueado
     * Este endpoint siempre filtra por el usuario actual, sin pasar user_id
     */
    public function misClientesParaLlamar(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea un llamador
        if (!$user->isLlamador()) {
            return response()->json([
                'message' => 'Solo los llamadores pueden acceder a este endpoint'
            ], 403);
        }
        
        $periodoDefecto = Configuracion::obtenerValor('periodo_actual', now()->format('Y-m'));
        $periodo = $request->has('periodo') ? $request->periodo : $periodoDefecto;
        
        // Debug logging
        \Log::info('Debug mis-clientes-para-llamar', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'periodo' => $periodo,
            'filtros' => $request->only(['buscar', 'estado_llamada', 'per_page', 'page'])
        ]);
        
        // Primero, veamos cuántas asignaciones tiene el usuario
        $totalAsignaciones = \App\Models\AsignacionLlamada::where('user_id', $user->id)
            ->where('periodo', $periodo)
            ->whereIn('estado', ['asignado', 'en_progreso'])
            ->count();
            
        \Log::info('Total asignaciones del usuario', ['count' => $totalAsignaciones]);
        
        // Veamos cuántas llamadas ha hecho el usuario (en total, no por período de fecha)
        $totalLlamadas = \App\Models\Llamada::where('user_id', $user->id)->count();
        \Log::info('Total llamadas del usuario (todas)', ['count' => $totalLlamadas]);
        
        // Llamadas del usuario para clientes de este período específico
        $llamadasClientesPeriodo = \App\Models\Llamada::where('user_id', $user->id)
            ->whereHas('cliente', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })
            ->count();
        \Log::info('Llamadas del usuario para clientes del período', ['periodo' => $periodo, 'count' => $llamadasClientesPeriodo]);
        
        $query = Cliente::with(['llamadas' => function($q) {
            $q->latest('fecha_llamada')->limit(1);
        }]);
        
        // Filtrar SIEMPRE por los clientes asignados al usuario logueado
        $query->whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
            $q->where('user_id', $user->id)
              ->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso', 'completado']); // Incluir completados
        });
        
        // Contar clientes base antes de filtros adicionales
        $clientesBase = $query->count();
        \Log::info('Clientes asignados base', ['count' => $clientesBase]);
        
        if ($request->has('buscar') && !empty($request->buscar)) {
            $query->buscar($request->buscar);
        }
        
        // Contar antes de aplicar filtro de estado
        $clientesAntesDelFiltro = $query->count();
        \Log::info('Clientes antes del filtro de estado', ['count' => $clientesAntesDelFiltro]);
        
        // Filtro por estado de llamada (si fue llamado o está pendiente)
        // Usar la misma lógica que en clientesParaLlamar() para consistencia
        if ($request->filled('estado_llamada')) {
            \Log::info('Aplicando filtro de estado', ['estado' => $request->estado_llamada, 'usuario' => $user->id, 'periodo' => $periodo]);
            
            if ($request->estado_llamada === 'llamado') {
                // Solo clientes que tienen al menos una llamada del usuario actual
                $query->whereHas('llamadas', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
                \Log::info('Filtro aplicado: clientes CON llamadas del usuario');
            } elseif ($request->estado_llamada === 'pendiente') {
                // Solo clientes que NO tienen llamadas del usuario actual
                $query->whereDoesntHave('llamadas', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
                \Log::info('Filtro aplicado: clientes SIN llamadas del usuario');
            }
            
            // Contar después del filtro
            $clientesDespuesDelFiltro = $query->count();
            \Log::info('Clientes después del filtro de estado', ['count' => $clientesDespuesDelFiltro]);
        } else {
            \Log::info('Sin filtro de estado aplicado');
        }
        
        $perPage = $request->get('per_page', 20);
        $clientes = $query->paginate($perPage);
        
        \Log::info('Resultado final', ['total' => $clientes->total()]);
        
        return response()->json($clientes);
    }
    
    /**
     * Obtener el historial de llamadas específicamente del usuario llamador logueado
     */
    public function misLlamadas(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea un llamador
        if (!$user->isLlamador()) {
            return response()->json([
                'message' => 'Solo los llamadores pueden acceder a este endpoint'
            ], 403);
        }
        
        $query = Llamada::with(['cliente', 'estadoLlamada'])
            ->where('user_id', $user->id); // Filtrar SIEMPRE por el usuario logueado
        
        if ($request->has('periodo')) {
            $query->byPeriodo($request->periodo);
        }
        
        if ($request->has('fecha')) {
            $query->byFecha($request->fecha);
        }
        
        if ($request->has('estado_llamada_id')) {
            $query->where('estado_llamada_id', $request->estado_llamada_id);
        }
        
        $llamadas = $query->latest('fecha_llamada')->paginate(20);
        
        return response()->json($llamadas);
    }
    
    /**
     * Obtener estadísticas de progreso del usuario llamador para el período
     */
    public function misEstadisticas(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea un llamador
        if (!$user->isLlamador()) {
            return response()->json([
                'message' => 'Solo los llamadores pueden acceder a este endpoint'
            ], 403);
        }
        
        $periodoDefecto = Configuracion::obtenerValor('periodo_actual', now()->format('Y-m'));
        $periodo = $request->has('periodo') ? $request->periodo : $periodoDefecto;
        
        // Total de clientes asignados al usuario en el período
        $totalAsignados = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
            $q->where('user_id', $user->id)
              ->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
        })->count();
        
        // Clientes llamados (con al menos una llamada)
        $clientesLlamados = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
                $q->where('user_id', $user->id)
                  ->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
            })
            ->whereHas('llamadas', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count();
            
        // Clientes pendientes (sin llamadas)
        $clientesPendientes = $totalAsignados - $clientesLlamados;
        
        // Total de llamadas realizadas por el usuario para clientes del período
        $totalLlamadas = Llamada::where('user_id', $user->id)
            ->whereHas('cliente', function($q) use ($periodo) {
                $q->where('periodo', $periodo);
            })->count();
            
        // Porcentaje de progreso
        $porcentajeProgreso = $totalAsignados > 0 ? round(($clientesLlamados / $totalAsignados) * 100, 1) : 0;
        
        // Llamadas por día (últimos 7 días)
        $llamadasPorDia = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $count = Llamada::where('user_id', $user->id)
                ->whereHas('cliente', function($q) use ($periodo) {
                    $q->where('periodo', $periodo);
                })
                ->whereDate('fecha_llamada', $fecha)
                ->count();
                
            $llamadasPorDia[] = [
                'fecha' => $fecha,
                'llamadas' => $count
            ];
        }
        
        return response()->json([
            'periodo' => $periodo,
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->name
            ],
            'resumen' => [
                'total_asignados' => $totalAsignados,
                'clientes_llamados' => $clientesLlamados,
                'clientes_pendientes' => $clientesPendientes,
                'total_llamadas' => $totalLlamadas,
                'porcentaje_progreso' => $porcentajeProgreso
            ],
            'llamadas_por_dia' => $llamadasPorDia
        ]);
    }
    
    /**
     * Obtener estadísticas generales para administradores
     */
    public function estadisticasGenerales(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea administrador
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Solo los administradores pueden acceder a este endpoint'
            ], 403);
        }
        
        $periodoDefecto = Configuracion::obtenerValor('periodo_actual', now()->format('Y-m'));
        $periodo = $request->has('periodo') ? $request->periodo : $periodoDefecto;
        
        // Total de clientes asignados en el período
        $totalAsignados = Cliente::whereHas('asignacionLlamada', function($q) use ($periodo) {
            $q->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
        })->count();
        
        // Clientes llamados (con al menos una llamada)
        $clientesLlamados = Cliente::whereHas('asignacionLlamada', function($q) use ($periodo) {
                $q->where('periodo', $periodo)
                  ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
            })
            ->whereHas('llamadas')
            ->count();
            
        // Clientes pendientes (sin llamadas)
        $clientesPendientes = $totalAsignados - $clientesLlamados;
        
        // Total de llamadas realizadas para el período
        $totalLlamadas = Llamada::whereHas('cliente', function($q) use ($periodo) {
            $q->where('periodo', $periodo);
        })->count();
        
        // Calcular porcentaje de progreso
        $porcentajeProgreso = $totalAsignados > 0 ? round(($clientesLlamados / $totalAsignados) * 100, 1) : 0;
        
        // Estadísticas por llamador - Modificado para SQL Server
        $llamadoresQuery = User::where('role', 'llamador')
            ->withCount([
                'asignacionesLlamadas as total_asignados' => function($q) use ($periodo) {
                    $q->where('periodo', $periodo)
                      ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
                },
                'llamadas as total_llamadas' => function($q) use ($periodo) {
                    $q->whereHas('cliente', function($subQ) use ($periodo) {
                        $subQ->where('periodo', $periodo);
                    });
                }
            ]);
            
        // Para SQL Server, filtrar después de obtener los resultados
        $estadisticasPorLlamador = $llamadoresQuery->get()
            ->filter(function($llamador) {
                return $llamador->total_asignados > 0;
            })
            ->map(function($llamador) {
                $clientesLlamados = Cliente::whereHas('asignacionLlamada', function($q) use ($llamador) {
                        $q->where('user_id', $llamador->id)
                          ->where('periodo', request('periodo', now()->format('Y-m')))
                          ->whereIn('estado', ['asignado', 'en_progreso', 'completado']);
                    })
                    ->whereHas('llamadas', function($q) use ($llamador) {
                        $q->where('user_id', $llamador->id);
                    })->count();
                    
                $porcentaje = $llamador->total_asignados > 0 ? 
                    round(($clientesLlamados / $llamador->total_asignados) * 100, 1) : 0;
                    
                return [
                    'id' => $llamador->id,
                    'nombre' => $llamador->name,
                    'total_asignados' => $llamador->total_asignados,
                    'clientes_llamados' => $clientesLlamados,
                    'clientes_pendientes' => $llamador->total_asignados - $clientesLlamados,
                    'total_llamadas' => $llamador->total_llamadas,
                    'porcentaje_progreso' => $porcentaje
                ];
            });
        
        return response()->json([
            'periodo' => $periodo,
            'resumen_general' => [
                'total_asignados' => $totalAsignados,
                'clientes_llamados' => $clientesLlamados,
                'clientes_pendientes' => $clientesPendientes,
                'total_llamadas' => $totalLlamadas,
                'porcentaje_progreso' => $porcentajeProgreso
            ],
            'estadisticas_por_llamador' => $estadisticasPorLlamador
        ]);
    }
    
    /**
     * Endpoint temporal para debugear datos
     */
    public function debugDatos(Request $request): JsonResponse
    {
        $user = Auth::user();
        $periodo = $request->get('periodo', '2025-08');
        
        // Información del usuario
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'isLlamador' => $user->isLlamador()
        ];
        
        // Contar asignaciones del usuario
        $totalAsignaciones = \App\Models\AsignacionLlamada::where('user_id', $user->id)
            ->where('periodo', $periodo)
            ->count();
            
        $asignacionesActivas = \App\Models\AsignacionLlamada::where('user_id', $user->id)
            ->where('periodo', $periodo)
            ->whereIn('estado', ['asignado', 'en_progreso'])
            ->count();
            
        // Contar llamadas del usuario en el período
        $totalLlamadas = \App\Models\Llamada::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha_llamada, '%Y-%m') = ?", [$periodo])
            ->count();
            
        // Obtener algunas asignaciones de ejemplo
        $asignacionesEjemplo = \App\Models\AsignacionLlamada::where('user_id', $user->id)
            ->where('periodo', $periodo)
            ->with('cliente:id,nombre,certi')
            ->limit(5)
            ->get();
            
        // Obtener algunas llamadas de ejemplo
        $llamadasEjemplo = \App\Models\Llamada::where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(fecha_llamada, '%Y-%m') = ?", [$periodo])
            ->with('cliente:id,nombre,certi')
            ->limit(5)
            ->get();
        
        return response()->json([
            'user' => $userData,
            'periodo' => $periodo,
            'counts' => [
                'total_asignaciones' => $totalAsignaciones,
                'asignaciones_activas' => $asignacionesActivas,
                'total_llamadas' => $totalLlamadas
            ],
            'samples' => [
                'asignaciones' => $asignacionesEjemplo,
                'llamadas' => $llamadasEjemplo
            ]
        ]);
    }
    
    /**
     * Crear algunas llamadas de prueba para el usuario actual
     */
    public function crearLlamadasPrueba(Request $request): JsonResponse
    {
        $user = Auth::user();
        $periodo = $request->get('periodo', '2025-08');
        $cantidad = $request->get('cantidad', 5);
        
        if (!$user->isLlamador()) {
            return response()->json([
                'message' => 'Solo los llamadores pueden crear llamadas de prueba'
            ], 403);
        }
        
        // Obtener algunos clientes asignados al usuario
        $clientes = Cliente::whereHas('asignacionLlamada', function($q) use ($user, $periodo) {
            $q->where('user_id', $user->id)
              ->where('periodo', $periodo)
              ->whereIn('estado', ['asignado', 'en_progreso']);
        })->limit($cantidad)->get();
        
        if ($clientes->isEmpty()) {
            return response()->json([
                'message' => 'No hay clientes asignados para crear llamadas de prueba'
            ], 404);
        }
        
        // Obtener un estado de llamada por defecto
        $estadoLlamada = \App\Models\EstadoLlamada::first();
        
        if (!$estadoLlamada) {
            return response()->json([
                'message' => 'No hay estados de llamada configurados'
            ], 404);
        }
        
        $llamadasCreadas = [];
        
        foreach ($clientes as $cliente) {
            // Crear una llamada en el período solicitado
            $fechaLlamada = \Carbon\Carbon::parse($periodo . '-01')->addDays(rand(0, 27));
            
            $llamada = \App\Models\Llamada::create([
                'cliente_id' => $cliente->id,
                'user_id' => $user->id,
                'estado_llamada_id' => $estadoLlamada->id,
                'telefono_utilizado' => $cliente->telefonos ?: '123456789',
                'observaciones' => 'Llamada de prueba generada automáticamente',
                'fecha_llamada' => $fechaLlamada
            ]);
            
            $llamadasCreadas[] = [
                'id' => $llamada->id,
                'cliente' => $cliente->nombre,
                'fecha' => $llamada->fecha_llamada->format('Y-m-d H:i:s')
            ];
        }
        
        return response()->json([
            'message' => "Se crearon {$cantidad} llamadas de prueba para el período {$periodo}",
            'llamadas' => $llamadasCreadas
        ]);
    }
}
