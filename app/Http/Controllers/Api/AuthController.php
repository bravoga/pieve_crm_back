<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Inicio de sesión con email y password
     */


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

    /**
     * Cerrar sesión
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Verificar estado de autenticación
     */
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

    /**
     * Obtener información del usuario autenticado
     */
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
