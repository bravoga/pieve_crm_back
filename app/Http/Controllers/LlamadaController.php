<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Llamada;
use App\Models\EstadoLlamada;
use App\Models\Configuracion;
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
}
