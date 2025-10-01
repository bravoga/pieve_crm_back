<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConfiguracionController extends Controller
{
    /**
     * Listar todas las configuraciones
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }

        $configuraciones = Configuracion::orderBy('clave')->get();

        return response()->json($configuraciones);
    }

    /**
     * Actualizar una configuración
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }

        $configuracion = Configuracion::findOrFail($id);

        $validated = $request->validate([
            'valor' => 'required|string',
            'estado' => 'required|boolean',
            'fecha_limite_llamada' => 'nullable|date',
        ]);

        $configuracion->update($validated);

        return response()->json([
            'message' => 'Configuración actualizada exitosamente',
            'configuracion' => $configuracion
        ]);
    }
}
