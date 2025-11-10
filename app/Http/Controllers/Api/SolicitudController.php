<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Solicitudes", description: "Gestión de solicitudes")]
class SolicitudController extends Controller
{
    #[OA\Get(
        path: "/solicitudes/codigos",
        tags: ["Solicitudes"],
        summary: "Obtener códigos de solicitudes",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Códigos de solicitudes",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Codigos de Solicitudes"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "nombre", type: "string", example: "Solicitud 1"),
                                    new OA\Property(property: "codiogo", type: "integer", example: 1)
                                ],
                                type: "object"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function codigos(Request $request): JsonResponse
    {
        $codigos = [];

        for ($i = 1; $i <= 5; $i++) {
            $codigos[] = [
                'id' => $i,
                'nombre' => "Solicitud $i",
                'codiogo' => $i,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Codigos de Solicitudes',
            'data' => $codigos
        ]);
    }

    #[OA\Get(
        path: "/solicitudes/listado",
        tags: ["Solicitudes"],
        summary: "Listar todas las solicitudes",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de solicitudes",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Solicitud creada correctamente"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            )
        ]
    )]
    public function listado(Request $request): JsonResponse
    {
        $solicitudes = Solicitud::all();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada correctamente',
            'data' => $solicitudes
        ]);
    }
}
