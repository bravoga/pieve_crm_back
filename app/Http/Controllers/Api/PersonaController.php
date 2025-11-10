<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Personas", description: "Búsqueda de personas en el padrón")]
class PersonaController extends Controller
{
    #[OA\Get(
        path: "/personas/buscar/{dni_cuil}",
        tags: ["Personas"],
        summary: "Buscar persona por DNI o CUIL",
        description: "Busca una persona en el padrón externo por DNI o CUIL",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "dni_cuil",
                in: "path",
                required: true,
                description: "DNI o CUIL de la persona (solo números, sin guiones ni puntos)",
                schema: new OA\Schema(type: "string", example: "20123456789")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Búsqueda exitosa",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "estado", type: "integer", example: 200),
                        new OA\Property(property: "mensaje", type: "string", example: "Persona encontrada"),
                        new OA\Property(
                            property: "personas",
                            type: "array",
                            items: new OA\Items(type: "object")
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
                response: 500,
                description: "Error del servidor externo",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "error", type: "string", example: "No se pudo conectar con el servicio externo")
                    ]
                )
            )
        ]
    )]
    public function buscar(Request $request, $dni_cuil): JsonResponse
    {
        // Validación
        $messages = [
            'required' => 'El DNI/CUIL es requerido.',
            'string' => 'El DNI/CUIL debe ser texto.',
            'regex' => 'Solo números, sin guiones o puntos.',
        ];

        $validator = Validator::make(['dni_cuil' => $dni_cuil], [
            'dni_cuil' => 'required|string|regex:/^[0-9]+$/',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        // Obtener información del request
        $ip = $request->ip();
        $navegador = $request->header('User-Agent');
        $username = Auth::user()->username;

        // Registrar la búsqueda en el log
        $this->logSearch(Auth::user()->id, 8, $dni_cuil, $ip, $navegador);

        try {
            $client = new Client();
            $personas = $this->makeExternalRequest($client, $dni_cuil, $ip, $navegador, $username);

            // Manejar caso de personas ambiguas (múltiples resultados)
            if (isset($personas['estado']) && $personas['estado'] == 422) {
                $grupoPersonas = [];

                // Si hay múltiples personas, buscar cada una por su CUIL
                if (isset($personas['personas']) && is_array($personas['personas'])) {
                    foreach ($personas['personas'] as $persona) {
                        if (isset($persona['cuil'])) {
                            $personaDetalle = $this->makeExternalRequest($client, $persona['cuil'], $ip, $navegador, $username);
                            if (isset($personaDetalle['personas']) && !empty($personaDetalle['personas'])) {
                                $grupoPersonas[] = $personaDetalle['personas'][0];
                            }
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'estado' => 422,
                    'mensaje' => $personas['mensaje'] ?? 'Se encontraron múltiples personas',
                    'personas' => $grupoPersonas
                ]);
            }

            return response()->json([
                'success' => true,
                'estado' => $personas['estado'] ?? 200,
                'mensaje' => $personas['mensaje'] ?? 'Búsqueda completada',
                'personas' => $personas['personas'] ?? []
            ]);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return response()->json([
                'success' => false,
                'error' => 'No se pudo conectar con el servicio externo',
                'detalle' => 'El servicio de padrón no está disponible en este momento'
            ], 500);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en la solicitud al servicio externo',
                'detalle' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error inesperado',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar búsqueda en el log
     */
    private function logSearch($userId, $tipoId, $observaciones, $ip, $navegador): void
    {
        try {
            $log = new Log();
            $log->user_id = $userId;
            $log->tipo_id = $tipoId;
            $log->observaciones = $observaciones;
            $log->ip = $ip;
            $log->navegador = $navegador;
            $log->fecha = Carbon::now();
            $log->save();
        } catch (\Exception $e) {
            // Log silencioso - no interrumpir el flujo si falla el log
            \Log::error('Error al registrar log de búsqueda: ' . $e->getMessage());
        }
    }

    /**
     * Realizar solicitud al servicio externo
     */
    private function makeExternalRequest(Client $client, $buscador, $ip, $navegador, $username): array
    {
        $response = $client->post('http://172.16.20.202:5050/api/padron/buscar', [
            'form_params' => [
                'txtBuscar' => $buscador,
                'ip' => $ip,
                'navegador' => $navegador,
                'username' => $username
            ],
            'timeout' => 30,
            'connect_timeout' => 10
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Error en el servicio externo: ' . $response->getStatusCode());
        }

        return json_decode((string)$response->getBody(), true);
    }
}
