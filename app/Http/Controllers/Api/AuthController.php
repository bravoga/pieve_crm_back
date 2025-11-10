<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Autenticación", description: "Endpoints de autenticación y gestión de sesiones")]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/auth/login",
        tags: ["Autenticación"],
        summary: "Iniciar sesión",
        description: "Autentica un usuario usando username (samaccountname) y password. Retorna un token JWT válido por 30 días.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "password", "device"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "usuario123"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "device", type: "string", example: "web-browser")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Inicio de sesión exitoso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Inicio de sesión exitoso"),
                        new OA\Property(property: "token", type: "string", example: "1|abcdef123456..."),
                        new OA\Property(
                            property: "user",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                new OA\Property(property: "email", type: "string", example: "juan@example.com"),
                                new OA\Property(property: "role", type: "string", example: "cobrador"),
                                new OA\Property(property: "active", type: "boolean", example: true)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Credenciales incorrectas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Credenciales incorrectas")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Usuario bloqueado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Usuario bloqueado, comunicarse con sistemas")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Usuario no encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Usuario no encontrado")
                    ]
                )
            )
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'device' => 'required',
        ]);

        $credentials = ['samaccountname'=>$request->username, 'password'=>$request->password];

        $user = User::where('username', $credentials['samaccountname'])->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($user->is_blocked) {
            return response()->json(['message' => 'Usuario bloqueado, comunicarse con sistemas'], 403);
        }

        if (Auth::attempt($credentials)) {
            // Autenticación exitosa
            $user->update(['failed_login_attempts' => 0]);
            $token = $user->createToken('sanLuis545')->plainTextToken;
            $user->token = $token;

            /*
            $log = new \App\Models\Log();
            $log->user_id = $user->id;
            $log->tipo_id = 1;
            $log->observaciones = '';
            $log->fecha = Carbon::now();
            $log->ip = $request->ip();
            $log->navegador = $request->device;
            $log->save();
            */

            $nameParts = explode(' ', $user->name);
            $user->nombre = $nameParts[0];


            // Agregar avatar genérico si no tiene foto de perfil
            if (empty($user->profile_photo_path)) {
                $user->profile_photo_path = 'avatars/avatar.png';
                $user->profile_photo_url = url('storage/avatars/avatar.png');
            } else {
                $user->profile_photo_url = url('storage/' . $user->profile_photo_path);
            }

            // Revocar tokens anteriores del usuario
            $user->tokens()->delete();

            // Crear nuevo token
            $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'cobrador',
                    'active' => $user->activo,
                ]
            ]);


        } else {
            // Autenticación fallida
            $user->increment('failed_login_attempts');

            if ($user->failed_login_attempts >= 5) {
                $user->update(['is_blocked' => 1]);
            }

            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

    }


    public function login2(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Buscar usuario por email
        $user = User::where('email', $request->email)
                   ->where('activo', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas o usuario inactivo.'],
            ]);
        }

        // Revocar tokens anteriores del usuario
        $user->tokens()->delete();

        // Crear nuevo token
        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'cobrador',
                'active' => $user->activo,
            ]
        ]);
    }

    #[OA\Post(
        path: "/auth/logout",
        tags: ["Autenticación"],
        summary: "Cerrar sesión",
        description: "Revoca el token actual del usuario autenticado",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Sesión cerrada exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Sesión cerrada exitosamente")
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    #[OA\Get(
        path: "/auth/check",
        tags: ["Autenticación"],
        summary: "Verificar estado de autenticación",
        description: "Verifica si el token es válido y retorna información básica del usuario",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Usuario autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "authenticated", type: "boolean", example: true),
                        new OA\Property(
                            property: "user",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                new OA\Property(property: "email", type: "string", example: "juan@example.com"),
                                new OA\Property(property: "role", type: "string", example: "collector"),
                                new OA\Property(property: "active", type: "boolean", example: true)
                            ],
                            type: "object"
                        )
                    ]
                )
            )
        ]
    )]
    public function check(Request $request): JsonResponse
    {
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role ?? 'collector',
                'active' => $request->user()->is_active,
            ]
        ]);
    }

    #[OA\Get(
        path: "/auth/user",
        tags: ["Autenticación"],
        summary: "Obtener información del usuario autenticado",
        description: "Retorna la información completa del usuario actualmente autenticado",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Información del usuario",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "user",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Juan Pérez"),
                                new OA\Property(property: "email", type: "string", example: "juan@example.com"),
                                new OA\Property(property: "role", type: "string", example: "collector"),
                                new OA\Property(property: "active", type: "boolean", example: true)
                            ],
                            type: "object"
                        )
                    ]
                )
            )
        ]
    )]
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role ?? 'collector',
                'active' => $request->user()->is_active,
            ]
        ]);
    }

    /**
     * Obtener lista de cobradores activos
     */
    public function cobradores(Request $request): JsonResponse
    {
        $cobradores = User::where('activo', true)
            ->where(function ($query) {
                $query->where('role', 'cobrador')
                      ->orWhere('role', 'admin');
            })
            ->select('id', 'name', 'pin', 'activo', 'role')
            ->withCount(['rutas as rutas_asignadas' => function ($query) {
                $query->where('fecha', '>=', now()->startOfDay());
            }])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'pin' => $user->pin,
                    'active' => $user->activo,
                    'rutas_asignadas' => $user->rutas_asignadas ?? 0,
                ];
            });

        return response()->json([
            'cobradores' => $cobradores
        ]);
    }
}
