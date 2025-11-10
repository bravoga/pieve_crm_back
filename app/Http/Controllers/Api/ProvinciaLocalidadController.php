<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Provincia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Datos de Referencia", description: "Datos maestros y catálogos del sistema")]
class ProvinciaLocalidadController extends Controller
{
    #[OA\Get(
        path: "/provincias",
        tags: ["Datos de Referencia"],
        summary: "Listar todas las provincias",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de provincias",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Provincias"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "codigo", type: "string", example: "BA"),
                                    new OA\Property(property: "nombre", type: "string", example: "Buenos Aires")
                                ],
                                type: "object"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function provincias(Request $request): JsonResponse
    {
        $provincias = Provincia::orderBy('nombre', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Provincias',
            'data' => $provincias
        ]);
    }

    #[OA\Get(
        path: "/localidades",
        tags: ["Datos de Referencia"],
        summary: "Listar localidades con filtros",
        description: "Lista localidades filtradas por provincia y/o búsqueda (mínimo 3 caracteres)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "provincia_id",
                in: "query",
                required: false,
                description: "ID de la provincia para filtrar",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "busqueda",
                in: "query",
                required: false,
                description: "Texto de búsqueda (mínimo 3 caracteres)",
                schema: new OA\Schema(type: "string", example: "Mar")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de localidades",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Localidades"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "provincia_id", type: "integer", example: 1),
                                    new OA\Property(property: "nombre", type: "string", example: "Mar del Plata"),
                                    new OA\Property(
                                        property: "provincia",
                                        properties: [
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "codigo", type: "string", example: "BA"),
                                            new OA\Property(property: "nombre", type: "string", example: "Buenos Aires")
                                        ],
                                        type: "object"
                                    )
                                ],
                                type: "object"
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "La búsqueda debe tener al menos 3 caracteres")
                    ]
                )
            )
        ]
    )]
    public function localidades(Request $request): JsonResponse
    {
        $provinciaId = $request->input('provincia_id');
        $busqueda = $request->input('busqueda');

        // Validar que la búsqueda tenga al menos 3 caracteres si está presente
        if ($busqueda && strlen(trim($busqueda)) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'La búsqueda debe tener al menos 3 caracteres'
            ], 422);
        }

        $query = Localidad::with('provincia');

        // Filtrar por provincia si se proporciona
        if ($provinciaId) {
            $query->where('provincia_id', $provinciaId);
        }

        // Filtrar por búsqueda si se proporciona y tiene al menos 3 caracteres
        if ($busqueda && strlen(trim($busqueda)) >= 3) {
            $query->where('nombre', 'like', '%' . $busqueda . '%');
        }

        $localidades = $query->orderBy('nombre', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Localidades',
            'data' => $localidades
        ]);
    }

    #[OA\Get(
        path: "/paises",
        tags: ["Datos de Referencia"],
        summary: "Listar todos los países",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de países",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Países"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "nombre", type: "string", example: "Argentina")
                                ],
                                type: "object"
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function paises(Request $request): JsonResponse
    {
        $paises = Pais::orderBy('nombre', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Países',
            'data' => $paises
        ]);
    }
}
