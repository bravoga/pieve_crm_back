<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarjeta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class TarjetaController extends Controller
{
    #[OA\Get(
        path: "/tarjetas",
        tags: ["Solicitudes"],
        summary: "Listar tarjetas activas",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de tarjetas activas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tipos de Tarjetas"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
    public function tarjetas(Request $request): JsonResponse
    {
        $tipos = Tarjeta::where('activo', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'Tipos de Tarjetas',
            'data' => $tipos
        ]);
    }
}
