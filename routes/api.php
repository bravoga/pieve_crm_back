<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\GeocodingController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\LlamadaController;
use App\Http\Controllers\AsignacionLlamadaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LdapController;
// use App\Http\Controllers\Api\RutaController;
// use App\Http\Controllers\Api\AsignacionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Ruta de prueba (temporal)
Route::get('test', function () {
    return response()->json(['message' => 'API funcionando correctamente', 'timestamp' => now()]);
});

// Rutas de autenticación (sin middleware)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('check', [AuthController::class, 'check'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Rutas protegidas con autenticación
Route::middleware('auth:sanctum')->group(function () {
    
    // Gestión de clientes
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'index']);
        Route::post('/', [ClienteController::class, 'store']);
        
        // Rutas específicas (deben ir antes de las rutas con parámetros dinámicos)
        Route::get('convenios', [ClienteController::class, 'convenios']);
        Route::get('config', [ClienteController::class, 'config']);
        Route::post('importar-excel', [ClienteController::class, 'importarExcel']);
        Route::get('carga-excel/{carga}', [ClienteController::class, 'estadoCarga']);
        Route::get('ultima-carga', [ClienteController::class, 'ultimaCarga']);
        Route::post('sincronizar-tarjetas', [ClienteController::class, 'sincronizarTarjetas']);
        Route::post('sincronizar-individuales', [ClienteController::class, 'sincronizarIndividuales']);

        // Rutas con parámetros dinámicos (deben ir al final)
        Route::get('{cliente}', [ClienteController::class, 'show']);
        Route::put('{cliente}', [ClienteController::class, 'update']);
        Route::delete('{cliente}', [ClienteController::class, 'destroy']);
    });
    
    // Geocodificación con Google Maps
    Route::prefix('geocoding')->group(function () {
        Route::post('/', [GeocodingController::class, 'geocodificar']);
        Route::get('estadisticas', [GeocodingController::class, 'estadisticas']);
        Route::post('cliente/{cliente}', [GeocodingController::class, 'geocodificarCliente']);
        Route::put('cliente/{cliente}/manual', [GeocodingController::class, 'marcarComoManual']);
        Route::put('cliente/{cliente}/resetear', [GeocodingController::class, 'resetearEstado']);
    });
    
    // TODO: Gestión de rutas - comentado temporalmente
    /*
    Route::prefix('rutas')->group(function () {
        Route::get('/', [RutaController::class, 'index']);
        Route::post('/', [RutaController::class, 'store']);
        Route::get('{ruta}', [RutaController::class, 'show']);
        Route::put('{ruta}', [RutaController::class, 'update']);
        Route::delete('{ruta}', [RutaController::class, 'destroy']);
        
        // Asignaciones de una ruta específica
        Route::get('{ruta}/asignaciones', [RutaController::class, 'asignaciones']);
        Route::post('{ruta}/asignaciones', [AsignacionController::class, 'store']);
        Route::put('{ruta}/reordenar', [AsignacionController::class, 'reordenar']);
    });
    
    // Gestión de asignaciones individuales
    Route::prefix('asignaciones')->group(function () {
        Route::get('{asignacion}', [AsignacionController::class, 'show']);
        Route::put('{asignacion}', [AsignacionController::class, 'update']);
        Route::delete('{asignacion}', [AsignacionController::class, 'destroy']);
        
        // Registrar visita (para móvil)
        Route::post('{asignacion}/visita', [AsignacionController::class, 'registrarVisita']);
    });
    */
    
    // Cobradores
    Route::get('cobradores', [AuthController::class, 'cobradores']);
    
    // Gestión de usuarios (solo admin)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('roles', [UserController::class, 'getRoles']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::post('{user}/toggle-active', [UserController::class, 'toggleActive']);
        Route::post('{user}/toggle-blocked', [UserController::class, 'toggleBlocked']);
    });
    
    // LDAP (solo admin)
    Route::prefix('ldap')->group(function () {
        Route::post('search', [LdapController::class, 'searchUsers']);
        Route::post('import', [LdapController::class, 'importUser']);
        Route::post('import-multiple', [LdapController::class, 'importMultipleUsers']);
        Route::post('sync/{user}', [LdapController::class, 'syncUser']);
    });
    
    // Gestión de llamadas telefónicas
    Route::prefix('llamadas')->group(function () {
        Route::get('/', [LlamadaController::class, 'index']);
        Route::post('/', [LlamadaController::class, 'store']);
        Route::get('estados', [LlamadaController::class, 'estadosLlamada']);
        Route::get('usuarios-llamadores', [LlamadaController::class, 'usuariosLlamadores']);
        Route::get('periodo-actual', [LlamadaController::class, 'periodoActual']);
        Route::get('clientes-para-llamar', [LlamadaController::class, 'clientesParaLlamar']);
        
        // Endpoints específicos para llamadores
        Route::get('mis-clientes-para-llamar', [LlamadaController::class, 'misClientesParaLlamar']);
        Route::get('mis-llamadas', [LlamadaController::class, 'misLlamadas']);
        Route::get('mis-estadisticas', [LlamadaController::class, 'misEstadisticas']);
        
        // Endpoints específicos para administradores
        Route::get('estadisticas-generales', [LlamadaController::class, 'estadisticasGenerales']);
        
        // Endpoint temporal para debugear datos
        Route::get('debug-datos', [LlamadaController::class, 'debugDatos']);
        
        // Endpoint temporal para crear datos de prueba
        Route::post('crear-llamadas-prueba', [LlamadaController::class, 'crearLlamadasPrueba']);
        
        Route::post('tomar-cliente', [LlamadaController::class, 'tomarCliente']);
        Route::get('{llamada}', [LlamadaController::class, 'show']);
        Route::put('{llamada}', [LlamadaController::class, 'update']);
        Route::delete('{llamada}', [LlamadaController::class, 'destroy']);
    });
    
    // Gestión de asignaciones de llamadas
    Route::prefix('asignaciones-llamadas')->group(function () {
        Route::get('/', [AsignacionLlamadaController::class, 'index']);
        Route::get('resumen', [AsignacionLlamadaController::class, 'resumenAsignaciones']);
        Route::post('asignar-automatico', [AsignacionLlamadaController::class, 'asignarAutomatico']);
        Route::get('llamadores-disponibles', [AsignacionLlamadaController::class, 'llamadoresDisponibles']);
        Route::get('estadisticas-llamador', [AsignacionLlamadaController::class, 'estadisticasLlamador']);
        Route::put('{asignacion}/reasignar', [AsignacionLlamadaController::class, 'reasignar']);
        Route::put('{asignacion}/cancelar', [AsignacionLlamadaController::class, 'cancelar']);
    });

    // Gestión de configuración (solo admin)
    Route::prefix('configuracion')->group(function () {
        Route::get('/', [ConfiguracionController::class, 'index']);
        Route::put('{id}', [ConfiguracionController::class, 'update']);
    });
});

// Ruta de fallback para manejo de errores 404
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada'
    ], 404);
});