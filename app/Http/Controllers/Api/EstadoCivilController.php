<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoCivil;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class EstadoCivilController extends Controller
{
    #[OA\Get(
        path: "/estados-civiles",
        tags: ["Datos de Referencia"],
        summary: "Listar todos los estados civiles",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de estados civiles",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Estados civiles"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
    public function estadosCiviles(Request $request): JsonResponse
    {
        $estados = EstadoCivil::all();

        return response()->json([
            'success' => true,
            'message' => 'Estados civiles',
            'data' => $estados
        ]);
    }
}
