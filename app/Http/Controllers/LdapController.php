<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LdapController extends Controller
{
    /**
     * Generar un PIN único de 6 dígitos
     */
    private function generateUniquePin(): string
    {
        do {
            $pin = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (User::where('pin', $pin)->exists());
        
        return $pin;
    }
    /**
     * Buscar usuarios en LDAP
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar que el usuario sea administrador
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $request->validate([
            'search' => 'required|string|min:3'
        ]);
        
        $searchTerm = $request->search;
        
        try {
            // Convertir el término de búsqueda a UTF-8 si es necesario
            $searchTerm = mb_convert_encoding($searchTerm, 'UTF-8', 'auto');
            
            // Buscar usuarios en LDAP
            $ldapUsers = LdapUser::where('cn', 'contains', $searchTerm)
                ->orWhere('mail', 'contains', $searchTerm)
                ->orWhere('sAMAccountName', 'contains', $searchTerm)
                ->orWhere('displayName', 'contains', $searchTerm)
                ->limit(20)
                ->get(['cn', 'mail', 'sAMAccountName', 'displayName', 'objectGuid', 'distinguishedName']);
            
            // Formatear resultados
            $results = [];
            foreach ($ldapUsers as $ldapUser) {
                try {
                    // Función helper para limpiar strings UTF-8
                    $cleanUtf8 = function($value) {
                        if (!$value) return '';
                        
                        // Si es un array, tomar el primer elemento
                        if (is_array($value)) {
                            $value = $value[0] ?? '';
                        }
                        
                        // Convertir a string
                        $value = (string) $value;
                        
                        // Si está vacío, retornar
                        if (empty($value)) return '';
                        
                        // Intentar detectar y convertir la codificación
                        $encoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                        if ($encoding && $encoding !== 'UTF-8') {
                            $value = mb_convert_encoding($value, 'UTF-8', $encoding);
                        }
                        
                        // Limpiar caracteres problemáticos
                        $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
                        
                        // Remover caracteres de control excepto espacios normales
                        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
                        
                        return trim($value);
                    };
                    
                    // Obtener atributos de forma segura
                    $email = $cleanUtf8($ldapUser->getFirstAttribute('mail'));
                    $username = $cleanUtf8($ldapUser->getFirstAttribute('sAMAccountName'));
                    $displayName = $cleanUtf8($ldapUser->getFirstAttribute('displayName'));
                    $cn = $cleanUtf8($ldapUser->getFirstAttribute('cn'));
                    $name = $displayName ?: $cn;
                    
                    // Si no tiene email, generar uno genérico
                    if (empty($email) && !empty($username)) {
                        $email = $username . '@grupopieve.com';
                    } elseif (empty($email) && !empty($name)) {
                        // Si no tiene username, usar el nombre (limpiar espacios y caracteres especiales)
                        $emailUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
                        $email = $emailUser . '@grupopieve.com';
                    } elseif (empty($email)) {
                        $email = 'sincorreo@grupopieve.com';
                    }
                    
                    // Para GUID, usar el método getConvertedGuid si está disponible
                    $guid = '';
                    try {
                        if (method_exists($ldapUser, 'getConvertedGuid')) {
                            $guid = $ldapUser->getConvertedGuid();
                        } elseif ($ldapUser->getFirstAttribute('objectGuid')) {
                            $guid = bin2hex($ldapUser->getFirstAttribute('objectGuid'));
                        }
                    } catch (\Exception $e) {
                        $guid = '';
                    }
                    
                    $dn = $cleanUtf8($ldapUser->getDn());
                    
                    // Verificar si el usuario ya existe en la base de datos
                    $existsInDb = false;
                    if ($email || $username) {
                        $query = User::query();
                        if ($email) {
                            $query->where('email', $email);
                        }
                        if ($username) {
                            $query->orWhere('username', $username);
                        }
                        $existsInDb = $query->exists();
                    }
                    
                    $results[] = [
                        'guid' => $guid,
                        'username' => $username,
                        'email' => $email,
                        'name' => $name,
                        'dn' => $dn,
                        'exists_in_db' => $existsInDb
                    ];
                } catch (\Exception $e) {
                    // Si hay error con un usuario específico, continuar con el siguiente
                    \Log::warning('Error procesando usuario LDAP: ' . $e->getMessage());
                    continue;
                }
            }
            
            // Asegurar que la respuesta sea UTF-8 válido
            return response()->json([
                'users' => $results,
                'count' => count($results)
            ], 200, ['Content-Type' => 'application/json; charset=UTF-8']);
            
        } catch (\Exception $e) {
            \Log::error('Error en búsqueda LDAP: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al buscar en LDAP. Por favor intente nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Importar un usuario específico desde LDAP
     */
    public function importUser(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $request->validate([
            'email' => 'required|email',
            'username' => 'nullable|string'
        ]);
        
        $email = $request->email;
        $username = $request->username;
        
        try {
            // Verificar si el usuario ya existe por email o username
            $existingQuery = User::where('email', $email);
            if ($username) {
                $existingQuery->orWhere('username', $username);
            }
            
            if ($existingQuery->exists()) {
                return response()->json([
                    'message' => 'El usuario ya existe en la base de datos'
                ], 422);
            }
            
            // Si el email es genérico (sin correo), intentar crear el usuario manualmente desde LDAP
            if ($email === 'sincorreo@grupopieve.com' || strpos($email, '@grupopieve.com') !== false) {
                // Buscar el usuario en LDAP por username si está disponible
                if ($username) {
                    $ldapUser = LdapUser::where('sAMAccountName', '=', $username)->first();
                    
                    if ($ldapUser) {
                        // Función helper para limpiar strings UTF-8
                        $cleanUtf8 = function($value) {
                            if (!$value) return '';
                            if (is_array($value)) $value = $value[0] ?? '';
                            $value = (string) $value;
                            if (empty($value)) return '';
                            $encoding = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                            if ($encoding && $encoding !== 'UTF-8') {
                                $value = mb_convert_encoding($value, 'UTF-8', $encoding);
                            }
                            $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);
                            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
                            return trim($value);
                        };
                        
                        // Crear usuario manualmente
                        $newUser = new User();
                        $newUser->name = $cleanUtf8($ldapUser->getFirstAttribute('displayName')) ?: $cleanUtf8($ldapUser->getFirstAttribute('cn'));
                        $newUser->email = $email; // Usar el email genérico
                        $newUser->username = $username;
                        $newUser->pin = $this->generateUniquePin(); // Generar PIN aleatorio único
                        $newUser->role = 'llamador';
                        $newUser->is_active = true;
                        $newUser->is_blocked = false;
                        $newUser->failed_login_attempts = 0;
                        $newUser->domain = 'grupopieve.com';
                        
                        // Intentar obtener GUID
                        try {
                            if (method_exists($ldapUser, 'getConvertedGuid')) {
                                $newUser->guid = $ldapUser->getConvertedGuid();
                            } elseif ($ldapUser->getFirstAttribute('objectGuid')) {
                                $newUser->guid = bin2hex($ldapUser->getFirstAttribute('objectGuid'));
                            }
                        } catch (\Exception $e) {
                            $newUser->guid = 'manual-' . uniqid();
                        }
                        
                        // Generar password temporal (será autenticado por LDAP)
                        $newUser->password = bcrypt('temp-' . uniqid());
                        
                        $newUser->save();
                        
                        return response()->json([
                            'message' => 'Usuario importado exitosamente (con email genérico)',
                            'user' => $newUser
                        ]);
                    }
                }
            }
            
            // Ejecutar comando de importación LDAP estándar
            $exitCode = Artisan::call('ldap:import', [
                'provider' => 'users',
                'user' => $email,
                '--no-interaction' => true
            ]);
            
            if ($exitCode === 0) {
                // Buscar el usuario recién importado
                $importedUser = User::where('email', $email)->first();
                
                if ($importedUser) {
                    // Asignar rol por defecto si no tiene
                    if (!$importedUser->role) {
                        $importedUser->role = 'llamador';
                    }
                    
                    // Asignar PIN aleatorio si no tiene
                    if (!$importedUser->pin) {
                        $importedUser->pin = $this->generateUniquePin();
                    }
                    
                    $importedUser->save();
                    
                    return response()->json([
                        'message' => 'Usuario importado exitosamente',
                        'user' => $importedUser
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Usuario importado pero no encontrado en la base de datos'
                    ], 500);
                }
            } else {
                return response()->json([
                    'message' => 'Error al importar el usuario desde LDAP'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Error al importar usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al importar usuario. Por favor intente nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Importar múltiples usuarios desde LDAP
     */
    public function importMultipleUsers(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $request->validate([
            'users' => 'required|array',
            'users.*.email' => 'required|email',
            'users.*.username' => 'nullable|string'
        ]);
        
        $users = $request->users;
        $imported = [];
        $failed = [];
        $existing = [];
        
        foreach ($users as $userData) {
            $email = $userData['email'];
            $username = $userData['username'] ?? null;
            
            try {
                // Verificar si ya existe
                $existingQuery = User::where('email', $email);
                if ($username) {
                    $existingQuery->orWhere('username', $username);
                }
                
                if ($existingQuery->exists()) {
                    $existing[] = $email;
                    continue;
                }
                
                // Usar el mismo método que importUser
                $importRequest = new Request([
                    'email' => $email,
                    'username' => $username
                ]);
                
                $result = $this->importUser($importRequest);
                
                if ($result->getStatusCode() === 200) {
                    $imported[] = $email;
                } else {
                    $failed[] = $email;
                }
                
            } catch (\Exception $e) {
                $failed[] = $email;
            }
        }
        
        return response()->json([
            'message' => 'Proceso de importación completado',
            'imported' => $imported,
            'failed' => $failed,
            'existing' => $existing,
            'summary' => [
                'total' => count($users),
                'imported' => count($imported),
                'failed' => count($failed),
                'existing' => count($existing)
            ]
        ]);
    }
    
    /**
     * Sincronizar un usuario existente con LDAP
     */
    public function syncUser(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a este recurso'
            ], 403);
        }
        
        $userData = User::findOrFail($id);
        
        if (!$userData->email) {
            return response()->json([
                'message' => 'El usuario no tiene email para sincronizar'
            ], 422);
        }
        
        try {
            // Buscar usuario en LDAP
            $ldapUser = LdapUser::where('mail', '=', $userData->email)->first();
            
            if ($ldapUser) {
                // Actualizar datos desde LDAP
                $userData->name = $ldapUser->displayName[0] ?? $ldapUser->cn[0] ?? $userData->name;
                $userData->username = $ldapUser->sAMAccountName[0] ?? $userData->username;
                $userData->guid = $ldapUser->objectGuid[0] ?? $userData->guid;
                $userData->domain = explode('@', $userData->email)[1] ?? 'grupopieve.com';
                $userData->save();
                
                return response()->json([
                    'message' => 'Usuario sincronizado con LDAP exitosamente',
                    'user' => $userData
                ]);
            } else {
                return response()->json([
                    'message' => 'Usuario no encontrado en LDAP'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al sincronizar con LDAP: ' . $e->getMessage()
            ], 500);
        }
    }
}