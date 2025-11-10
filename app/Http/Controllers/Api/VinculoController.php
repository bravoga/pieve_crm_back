<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoVinculo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Vínculos", description: "Gestión de tipos de vínculos")]
class VinculoController extends Controller
{
    #[OA\Get(
        path: "/vinculos",
        tags: ["Datos de Referencia"],
        summary: "Listar tipos de vínculos activos",
        description: "Lista todos los tipos de vínculos activos ordenados por orden",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de tipos de vínculos activos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tipos de vínculos"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(type: "object")
                        )
                    ]
                )
            )
        ]
    )]
    public function listar(Request $request): JsonResponse
    {
        try {
            $vinculos = TipoVinculo::where('activo', 1)
                ->orderBy('orden', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Tipos de vínculos',
                'data' => $vinculos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tipos de vínculos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
