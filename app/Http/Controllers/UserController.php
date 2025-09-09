<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios con filtros y paginación
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea administrador
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $query = User::query();
        
        // Filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('pin', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        if ($request->has('is_blocked')) {
            $query->where('is_blocked', $request->boolean('is_blocked'));
        }
        
        // Ordenamiento
        $sortBy = $request->get('sort_by', 'name');
        $sortDesc = $request->boolean('sort_desc', false);
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        
        // Paginación
        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage);
        
        // Agregar estadísticas de cada usuario
        $users->getCollection()->transform(function ($user) {
            // Contar asignaciones de llamadas activas
            $user->total_asignaciones = $user->asignacionesLlamadas()
                ->where('periodo', now()->format('Y-m'))
                ->whereIn('estado', ['asignado', 'en_progreso'])
                ->count();
            
            // Contar llamadas del mes
            $user->total_llamadas_mes = $user->llamadas()
                ->whereMonth('fecha_llamada', now()->month)
                ->whereYear('fecha_llamada', now()->year)
                ->count();
                
            return $user;
        });
        
        return response()->json($users);
    }
    
    /**
     * Obtener un usuario específico
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $userData = User::findOrFail($id);
        
        // Agregar estadísticas
        $userData->total_asignaciones = $userData->asignacionesLlamadas()
            ->where('periodo', now()->format('Y-m'))
            ->whereIn('estado', ['asignado', 'en_progreso'])
            ->count();
        
        $userData->total_llamadas_mes = $userData->llamadas()
            ->whereMonth('fecha_llamada', now()->month)
            ->whereYear('fecha_llamada', now()->year)
            ->count();
        
        $userData->historial_llamadas = $userData->llamadas()
            ->with('estadoLlamada', 'cliente')
            ->latest('fecha_llamada')
            ->limit(10)
            ->get();
        
        return response()->json($userData);
    }
    
    /**
     * Actualizar un usuario
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $userData = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'role' => 'sometimes|in:admin,cobrador,llamador',
            'is_active' => 'sometimes|boolean',
            'is_blocked' => 'sometimes|boolean',
            'pin' => ['sometimes', 'string', 'size:6', Rule::unique('users')->ignore($id)],
        ]);
        
        if ($request->has('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        
        $userData->update($validated);
        
        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $userData
        ]);
    }
    
    /**
     * Cambiar estado activo/inactivo de un usuario
     */
    public function toggleActive($id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $userData = User::findOrFail($id);
        
        // No permitir desactivar el propio usuario
        if ($userData->id === $user->id) {
            return response()->json([
                'message' => 'No puedes desactivar tu propio usuario'
            ], 400);
        }
        
        $userData->is_active = !$userData->is_active;
        $userData->save();
        
        return response()->json([
            'message' => $userData->is_active ? 'Usuario activado' : 'Usuario desactivado',
            'user' => $userData
        ]);
    }
    
    /**
     * Cambiar estado bloqueado/desbloqueado de un usuario
     */
    public function toggleBlocked($id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $userData = User::findOrFail($id);
        
        // No permitir bloquear el propio usuario
        if ($userData->id === $user->id) {
            return response()->json([
                'message' => 'No puedes bloquear tu propio usuario'
            ], 400);
        }
        
        $userData->is_blocked = !$userData->is_blocked;
        
        // Si se desbloquea, resetear intentos fallidos
        if (!$userData->is_blocked) {
            $userData->failed_login_attempts = 0;
        }
        
        $userData->save();
        
        return response()->json([
            'message' => $userData->is_blocked ? 'Usuario bloqueado' : 'Usuario desbloqueado',
            'user' => $userData
        ]);
    }
    
    /**
     * Obtener roles disponibles
     */
    public function getRoles(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $roles = [
            ['value' => 'admin', 'label' => 'Administrador'],
            ['value' => 'llamador', 'label' => 'Llamador'],
            ['value' => 'cobrador', 'label' => 'Cobrador'],
        ];
        
        return response()->json($roles);
    }
}