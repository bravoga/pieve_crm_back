<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BancoController;
use App\Http\Controllers\Api\EstadoCivilController;
use App\Http\Controllers\Api\FichaController;
use App\Http\Controllers\Api\NovedadController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PersonaController;
use App\Http\Controllers\Api\PromotorController;
use App\Http\Controllers\Api\ProvinciaLocalidadController;
use App\Http\Controllers\Api\SolicitudController;
use App\Http\Controllers\Api\TarjetaController;
use App\Http\Controllers\Api\TipoPagoController;
use App\Http\Controllers\Api\TipoSolicitudController;
use App\Http\Controllers\Api\VinculoController;
use App\Http\Controllers\LdapController;

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

    // LDAP (solo admin)
    Route::prefix('ldap')->group(function () {
        Route::post('search', [LdapController::class, 'searchUsers']);
        Route::post('import', [LdapController::class, 'importUser']);
        Route::post('import-multiple', [LdapController::class, 'importMultipleUsers']);
        Route::post('sync/{user}', [LdapController::class, 'syncUser']);
    });

    // Novedades
    Route::prefix('novedades')->group(function () {
        Route::get('/', [NovedadController::class, 'index']);
        Route::get('/activas', [NovedadController::class, 'activas']);
        Route::post('/', [NovedadController::class, 'store']);
        Route::get('/{novedad}', [NovedadController::class, 'show']);
        Route::put('/{novedad}', [NovedadController::class, 'update']);
        Route::delete('/{novedad}', [NovedadController::class, 'destroy']);
        Route::post('/{novedad}/toggle-activa', [NovedadController::class, 'toggleActiva']);
    });

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::get('/', [NotificacionController::class, 'index']);
        Route::get('/no-leidas', [NotificacionController::class, 'noLeidas']);
        Route::get('/contar-no-leidas', [NotificacionController::class, 'contarNoLeidas']);
        Route::post('/', [NotificacionController::class, 'store']);
        Route::post('/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas']);
        Route::get('/{notificacion}', [NotificacionController::class, 'show']);
        Route::post('/{notificacion}/marcar-leida', [NotificacionController::class, 'marcarLeida']);
        Route::delete('/{notificacion}', [NotificacionController::class, 'destroy']);
    });

    // Tipos de Solicitud
    Route::prefix('tipos-solicitud')->group(function () {
        Route::get('/activos', [TipoSolicitudController::class, 'activos']);
    });

    // Solicitudes
    Route::prefix('solicitudes')->group(function () {
        Route::get('/codigos', [SolicitudController::class, 'codigos']);
        Route::get('/listado', [SolicitudController::class, 'listado']);
    });

    // Tipos de Pago
    Route::prefix('tipos-pago')->group(function () {
        Route::get('/', [TipoPagoController::class, 'tiposPagos']);
    });

    // Tarjetas
    Route::prefix('tarjetas')->group(function () {
        Route::get('/', [TarjetaController::class, 'tarjetas']);
    });

    // Bancos
    Route::prefix('bancos')->group(function () {
        Route::get('/', [BancoController::class, 'bancos']);
    });

    // Estados Civiles
    Route::prefix('estados-civiles')->group(function () {
        Route::get('/', [EstadoCivilController::class, 'estadosCiviles']);
    });

    // Provincias, Localidades y Países
    Route::get('/provincias', [ProvinciaLocalidadController::class, 'provincias']);
    Route::get('/localidades', [ProvinciaLocalidadController::class, 'localidades']);
    Route::get('/paises', [ProvinciaLocalidadController::class, 'paises']);
    Route::get('/vinculos', [VinculoController::class, 'listar']);

    // Personas - Búsqueda en Padrón
    Route::get('/personas/buscar/{dni_cuil}', [PersonaController::class, 'buscar']);

    // Fichas - Búsqueda
    Route::get('/fichas/certificado/{numero}', [FichaController::class, 'certificado']);
    Route::get('/fichas/persona/{dni}', [FichaController::class, 'buscarPorDni']);

    // Promotores
    Route::get('/promotores', [PromotorController::class, 'listar']);
    Route::get('/promotores/buscar', [PromotorController::class, 'buscar']);
    Route::get('/promotores/buscar-legajo/{legajo}', [PromotorController::class, 'buscarPorLegajo']);
});

// Ruta de fallback para manejo de errores 404
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada'
    ], 404);
});