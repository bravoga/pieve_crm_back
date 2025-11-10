<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banco;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class BancoController extends Controller
{
    #[OA\Get(
        path: "/bancos",
        tags: ["Datos de Referencia"],
        summary: "Listar bancos activos",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de bancos activos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Bancos"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
    public function bancos(Request $request): JsonResponse
    {
        $tipos = Banco::where('activo', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'Bancos',
            'data' => $tipos
        ]);
    }
}
