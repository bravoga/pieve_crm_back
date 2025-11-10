<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /**
     * Display a listing of all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $userId = Auth::id();

        $notificaciones = Notificacion::with(['usuarios' => function ($query) use ($userId) {
                $query->where('users.id', $userId);
            }])
            ->paraUsuario($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Formatear respuesta con datos del pivot
        $data = $notificaciones->items();
        $data = collect($data)->map(function ($notificacion) use ($userId) {
            $pivot = $notificacion->usuarios->first()?->pivot;
            return [
                'id' => $notificacion->id,
                'titulo' => $notificacion->titulo,
                'mensaje' => $notificacion->mensaje,
                'tipo' => $notificacion->tipo,
                'icono' => $notificacion->icono,
                'color' => $notificacion->color,
                'url' => $notificacion->url,
                'leida' => $pivot ? $pivot->leida : false,
                'fecha_leida' => $pivot ? $pivot->fecha_leida : null,
                'created_at' => $notificacion->created_at,
                'updated_at' => $notificacion->updated_at,
            ];
        })->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $notificaciones->currentPage(),
                'last_page' => $notificaciones->lastPage(),
                'per_page' => $notificaciones->perPage(),
                'total' => $notificaciones->total(),
                'from' => $notificaciones->firstItem(),
                'to' => $notificaciones->lastItem(),
            ]
        ]);
    }

    /**
     * Get unread notifications for the authenticated user.
     */
    public function noLeidas(): JsonResponse
    {
        $userId = Auth::id();

        $notificaciones = Notificacion::with(['usuarios' => function ($query) use ($userId) {
                $query->where('users.id', $userId);
            }])
            ->noLeidasPorUsuario($userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $total = Notificacion::noLeidasPorUsuario($userId)->count();

        // Formatear respuesta con datos del pivot
        $data = $notificaciones->map(function ($notificacion) use ($userId) {
            $pivot = $notificacion->usuarios->first()?->pivot;
            return [
                'id' => $notificacion->id,
                'titulo' => $notificacion->titulo,
                'mensaje' => $notificacion->mensaje,
                'tipo' => $notificacion->tipo,
                'icono' => $notificacion->icono,
                'color' => $notificacion->color,
                'url' => $notificacion->url,
                'leida' => $pivot ? $pivot->leida : false,
                'fecha_leida' => $pivot ? $pivot->fecha_leida : null,
                'created_at' => $notificacion->created_at,
                'updated_at' => $notificacion->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total_no_leidas' => $total
        ]);
    }

    /**
     * Get count of unread notifications.
     */
    public function contarNoLeidas(): JsonResponse
    {
        $count = Notificacion::noLeidasPorUsuario(Auth::id())->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|string|in:sistema,novedad,alerta,info',
            'icono' => 'nullable|string',
            'color' => 'nullable|string',
            'url' => 'nullable|string',
        ]);

        $userIds = $validated['user_ids'];
        unset($validated['user_ids']);

        $notificacion = Notificacion::create($validated);

        // Asociar la notificación con los usuarios
        $notificacion->usuarios()->attach($userIds);

        return response()->json([
            'success' => true,
            'message' => 'Notificación creada exitosamente',
            'data' => $notificacion
        ], 201);
    }

    /**
     * Display the specified notification.
     */
    public function show(Notificacion $notificacion): JsonResponse
    {
        $userId = Auth::id();

        // Verificar que el usuario tiene esta notificación
        if (!$notificacion->usuarios()->where('users.id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $notificacion->load(['usuarios' => function ($query) use ($userId) {
            $query->where('users.id', $userId);
        }]);

        $pivot = $notificacion->usuarios->first()?->pivot;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notificacion->id,
                'titulo' => $notificacion->titulo,
                'mensaje' => $notificacion->mensaje,
                'tipo' => $notificacion->tipo,
                'icono' => $notificacion->icono,
                'color' => $notificacion->color,
                'url' => $notificacion->url,
                'leida' => $pivot ? $pivot->leida : false,
                'fecha_leida' => $pivot ? $pivot->fecha_leida : null,
                'created_at' => $notificacion->created_at,
                'updated_at' => $notificacion->updated_at,
            ]
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function marcarLeida(Notificacion $notificacion): JsonResponse
    {
        $userId = Auth::id();

        // Verificar que el usuario tiene esta notificación
        if (!$notificacion->usuarios()->where('users.id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $notificacion->marcarComoLeidaPara($userId);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function marcarTodasLeidas(): JsonResponse
    {
        $userId = Auth::id();

        $notificaciones = Notificacion::noLeidasPorUsuario($userId)->get();

        foreach ($notificaciones as $notificacion) {
            $notificacion->marcarComoLeidaPara($userId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notificacion $notificacion): JsonResponse
    {
        $userId = Auth::id();

        // Verificar que el usuario tiene esta notificación
        if (!$notificacion->usuarios()->where('users.id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        // Solo desvinculamos la notificación del usuario, no la eliminamos
        $notificacion->usuarios()->detach($userId);

        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada exitosamente'
        ]);
    }
}
