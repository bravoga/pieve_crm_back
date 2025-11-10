<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class TipoPagoController extends Controller
{
    #[OA\Get(
        path: "/tipos-pago",
        tags: ["Solicitudes"],
        summary: "Listar tipos de pago activos",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de tipos de pago activos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tipos de pagos"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
    public function tiposPagos(Request $request): JsonResponse
    {
        $tipos = TipoPago::where('activo', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'Tipos de pagos',
            'data' => $tipos
        ]);
    }
}
