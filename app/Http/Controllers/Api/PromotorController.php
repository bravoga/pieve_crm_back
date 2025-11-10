<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Promotores", description: "Gestión de promotores")]
class PromotorController extends Controller
{
    #[OA\Get(
        path: "/promotores",
        tags: ["Promotores"],
        summary: "Listar promotores activos",
        description: "Lista todos los promotores que existen (existe = 1)",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de promotores activos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Promotores activos"),
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
            $promotores = Promotor::where('existe', 1)
                ->orderBy('IdProductorCp', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Promotores activos',
                'data' => $promotores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener promotores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/promotores/buscar-legajo/{legajo}",
        tags: ["Promotores"],
        summary: "Buscar promotor por legajo (IdProductorCp)",
        description: "Busca un promotor por su legajo (IdProductorCp) que existe (existe = 1)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "legajo",
                in: "path",
                required: true,
                description: "Legajo del promotor (IdProductorCp)",
                schema: new OA\Schema(type: "string", example: "12345")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Promotor encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Promotor encontrado"),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Promotor no encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Promotor no encontrado")
                    ]
                )
            )
        ]
    )]
    public function buscarPorLegajo(Request $request, $legajo): JsonResponse
    {
        try {
            $promotor = Promotor::where('IdProductorCp', $legajo)
                ->where('existe', 1)
                ->first();

            if (!$promotor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotor no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Promotor encontrado',
                'data' => $promotor
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar promotor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/promotores/buscar",
        tags: ["Promotores"],
        summary: "Buscar promotores por apellido y/o nombre",
        description: "Busca promotores por apellido y/o nombre que existen (existe = 1). Mínimo 3 caracteres para la búsqueda.",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "apellido",
                in: "query",
                required: false,
                description: "Apellido del promotor (mínimo 3 caracteres)",
                schema: new OA\Schema(type: "string", example: "García")
            ),
            new OA\Parameter(
                name: "nombre",
                in: "query",
                required: false,
                description: "Nombre del promotor (mínimo 3 caracteres)",
                schema: new OA\Schema(type: "string", example: "Juan")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Promotores encontrados",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Promotores encontrados"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(type: "object")
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validación fallida",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Debe proporcionar al menos apellido o nombre con mínimo 3 caracteres")
                    ]
                )
            )
        ]
    )]
    public function buscar(Request $request): JsonResponse
    {
        try {
            $apellido = $request->input('apellido');
            $nombre = $request->input('nombre');

            // Validar que al menos uno de los campos tenga mínimo 3 caracteres
            if ((!$apellido || strlen(trim($apellido)) < 3) && (!$nombre || strlen(trim($nombre)) < 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar al menos apellido o nombre con mínimo 3 caracteres'
                ], 422);
            }

            $query = Promotor::where('existe', 1);

            if ($apellido && strlen(trim($apellido)) >= 3) {
                $query->where('Apellido', 'like', '%' . $apellido . '%');
            }

            if ($nombre && strlen(trim($nombre)) >= 3) {
                $query->where('Nombre', 'like', '%' . $nombre . '%');
            }

            $promotores = $query->orderBy('Apellido', 'asc')
                ->orderBy('Nombre', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Promotores encontrados',
                'data' => $promotores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar promotores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
