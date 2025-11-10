<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Novedad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NovedadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 5);

        $novedades = Novedad::with('user:id,name,username')
            ->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $novedades->items(),
            'pagination' => [
                'current_page' => $novedades->currentPage(),
                'last_page' => $novedades->lastPage(),
                'per_page' => $novedades->perPage(),
                'total' => $novedades->total(),
                'from' => $novedades->firstItem(),
                'to' => $novedades->lastItem(),
            ]
        ]);
    }

    /**
     * Display only active news
     */
    public function activas(): JsonResponse
    {
        $novedades = Novedad::with('user:id,name,username')
            ->where('activa', true)
            ->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $novedades
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'activa' => 'boolean',
            'fecha_publicacion' => 'nullable|date'
        ]);

        $validated['user_id'] = Auth::id();
        $validated['fecha_publicacion'] = $validated['fecha_publicacion'] ?? now();

        $novedad = Novedad::create($validated);
        $novedad->load('user:id,name,username');

        return response()->json([
            'success' => true,
            'message' => 'Novedad creada exitosamente',
            'data' => $novedad
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Novedad $novedad): JsonResponse
    {
        $novedad->load('user:id,name,username');

        return response()->json([
            'success' => true,
            'data' => $novedad
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Novedad $novedad): JsonResponse
    {
        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'contenido' => 'sometimes|required|string',
            'activa' => 'sometimes|boolean',
            'fecha_publicacion' => 'nullable|date'
        ]);

        $novedad->update($validated);
        $novedad->load('user:id,name,username');

        return response()->json([
            'success' => true,
            'message' => 'Novedad actualizada exitosamente',
            'data' => $novedad
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Novedad $novedad): JsonResponse
    {
        $novedad->delete();

        return response()->json([
            'success' => true,
            'message' => 'Novedad eliminada exitosamente'
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActiva(Novedad $novedad): JsonResponse
    {
        $novedad->activa = !$novedad->activa;
        $novedad->save();
        $novedad->load('user:id,name,username');

        return response()->json([
            'success' => true,
            'message' => $novedad->activa ? 'Novedad activada' : 'Novedad desactivada',
            'data' => $novedad
        ]);
    }
}
