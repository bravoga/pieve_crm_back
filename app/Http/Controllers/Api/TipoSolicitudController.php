<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoSolicitud;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class TipoSolicitudController extends Controller
{
    #[OA\Get(
        path: "/tipos-solicitud/activos",
        tags: ["Solicitudes"],
        summary: "Listar tipos de solicitud activos",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de tipos de solicitud activos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/TipoSolicitud"))
                    ]
                )
            )
        ]
    )]
    public function activos(): JsonResponse
    {
        $tipos = TipoSolicitud::where('activo', 1)
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tipos
        ]);
    }
}
