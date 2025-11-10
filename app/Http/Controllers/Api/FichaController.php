<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Beneficiario;
use App\Models\Ficha;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Fichas", description: "Gestión de fichas y certificados")]
class FichaController extends Controller
{
    #[OA\Get(
        path: "/fichas/certificado/{numero}",
        tags: ["Fichas"],
        summary: "Buscar ficha por número de certificado",
        description: "Busca una ficha y los datos del titular por número de certificado",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "numero",
                in: "path",
                required: true,
                description: "Número de certificado (solo números, sin guiones ni puntos)",
                schema: new OA\Schema(type: "string", example: "123456")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Ficha encontrada",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Ficha encontrada"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "certificado", type: "string", example: "123456"),
                                new OA\Property(property: "titular", type: "string", example: "Pérez, Juan"),
                                new OA\Property(property: "ficha", type: "object"),
                                new OA\Property(property: "beneficiario", type: "object")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Error de validación",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "error", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Certificado no encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Certificado inválido o no existe")
                    ]
                )
            )
        ]
    )]
    public function certificado(Request $request, $numero): JsonResponse
    {
        // Validación
        $messages = [
            'required' => 'El número de certificado es requerido.',
            'regex' => 'Solo números, sin guiones o puntos.',
        ];

        $validator = Validator::make(['numero' => $numero], [
            'numero' => 'required|regex:/^[0-9]+$/',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        try {
            // Buscar ficha por certificado con relación a beneficiario
            $ficha = Ficha::with(['beneficiario'])
                ->where('idtitularcp', $numero)
                ->where('existe', 1)
                ->first();

            // Validar si existe la ficha
            if (empty($ficha)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificado inválido o no existe'
                ], 404);
            }

            // Obtener beneficiario titular
            $beneficiario = Beneficiario::where('idbencp', $ficha->IdBenCF)->first();

            if (empty($beneficiario)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos del titular'
                ], 404);
            }

            // Preparar datos de respuesta
            $datos = [
                'certificado' => $ficha->idTitularCp,
                'titular' => $beneficiario->Apellido . ', ' . $beneficiario->Nombre,
                'ficha' => $ficha,
                'beneficiario' => $beneficiario
            ];

            return response()->json([
                'success' => true,
                'message' => 'Ficha encontrada',
                'data' => $datos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la ficha',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/fichas/persona/{dni}",
        tags: ["Fichas"],
        summary: "Buscar fichas por DNI de la persona",
        description: "Busca un beneficiario por DNI y obtiene sus fichas activas con números de certificado",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "dni",
                in: "path",
                required: true,
                description: "DNI de la persona (solo números, sin guiones ni puntos)",
                schema: new OA\Schema(type: "string", example: "12345678")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Persona y fichas encontradas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Persona encontrada"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "persona", type: "string", example: "Pérez, Juan"),
                                new OA\Property(property: "dni", type: "string", example: "12345678"),
                                new OA\Property(property: "beneficiario", ref: "#/components/schemas/Beneficiario"),
                                new OA\Property(
                                    property: "fichas",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "certificado", type: "string", example: "123456"),
                                            new OA\Property(property: "ficha", ref: "#/components/schemas/Ficha")
                                        ],
                                        type: "object"
                                    )
                                )
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Error de validación",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "error", type: "object")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Persona no encontrada",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "No se encontró una persona con ese DNI")
                    ]
                )
            )
        ]
    )]
    public function buscarPorDni(Request $request, $dni): JsonResponse
    {
        // Validación
        $messages = [
            'required' => 'El DNI es requerido.',
            'regex' => 'Solo números, sin guiones o puntos.',
        ];

        $validator = Validator::make(['dni' => $dni], [
            'dni' => 'required|regex:/^[0-9]+$/',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        try {
            // Buscar beneficiario por DNI
            $beneficiario = Beneficiario::where('dni', $dni)->first();

            if (empty($beneficiario)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una persona con ese DNI'
                ], 404);
            }

            // Buscar fichas activas del beneficiario
            $fichas = Ficha::where('IdBenCF', $beneficiario->idBenCP)
                ->where('existe', 1)
                ->get();

            // Preparar array de fichas con certificados
            $fichasData = [];
            foreach ($fichas as $ficha) {
                $fichasData[] = [
                    'certificado' => $ficha->idTitularCp,
                    'ficha' => $ficha
                ];
            }

            // Preparar datos de respuesta
            $datos = [
                'persona' => $beneficiario->Apellido . ', ' . $beneficiario->Nombre,
                'dni' => $beneficiario->dni,
                'beneficiario' => $beneficiario,
                'fichas' => $fichasData,
                'cantidad_fichas' => count($fichasData)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Persona encontrada',
                'data' => $datos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la persona',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
