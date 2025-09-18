<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Cliente;
use App\Models\CargaExcel;
use App\Imports\ClientesImport;

class ClienteController extends Controller
{
    /**
     * Listar clientes
     */
    public function index(Request $request): JsonResponse
    {
        \Log::info('ClienteController@index called', [
            'params' => $request->all(),
            'geocodificado' => $request->get('geocodificado')
        ]);
        
        $query = Cliente::query();

        // Filtro por búsqueda
        if ($request->has('search') && $request->search) {
            $query->buscar($request->search);
        }

        // Filtro por convenio
        if ($request->has('convenio') && $request->convenio) {
            $query->where('nbre_convenio', $request->convenio);
        }

        // Filtro por tipo de contacto
        if ($request->has('tipo_contacto') && $request->tipo_contacto) {
            $query->where('tipo_contacto', $request->tipo_contacto);
        }

        // Filtro por geocodificación
        if ($request->has('geocodificado') && $request->geocodificado) {
            switch ($request->geocodificado) {
                case 'validados':
                    $query->where('geocoding_status', 'validated');
                    break;
                case 'pendientes':
                    $query->where('geocoding_status', 'pending');
                    break;
                case 'manuales':
                    $query->where('geocoding_status', 'manual');
                    break;
                case 'fallidos':
                    $query->where('geocoding_status', 'failed');
                    break;
                case 'todos_geocodificados':
                case 'true':
                    $query->whereIn('geocoding_status', ['validated', 'manual']);
                    break;
            }
        }

        // Filtro por período
        if ($request->has('periodo') && $request->periodo) {
            $query->where('periodo', $request->periodo);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Paginación
        $perPage = $request->get('per_page', 50);
        $clientes = $query->paginate($perPage);

        // Calcular estadísticas aplicando los mismos filtros que la consulta principal
        $estadisticasQuery = Cliente::query();
        
        // Aplicar los mismos filtros que para la consulta principal
        if ($request->has('search') && $request->search) {
            $estadisticasQuery->buscar($request->search);
        }
        
        if ($request->has('convenio') && $request->convenio) {
            $estadisticasQuery->where('nbre_convenio', $request->convenio);
        }
        
        if ($request->has('tipo_contacto') && $request->tipo_contacto) {
            $estadisticasQuery->where('tipo_contacto', $request->tipo_contacto);
        }
        
        if ($request->has('geocodificado') && $request->geocodificado) {
            switch ($request->geocodificado) {
                case 'validados':
                    $estadisticasQuery->where('geocoding_status', 'validated');
                    break;
                case 'pendientes':
                    $estadisticasQuery->where('geocoding_status', 'pending');
                    break;
                case 'manuales':
                    $estadisticasQuery->where('geocoding_status', 'manual');
                    break;
                case 'fallidos':
                    $estadisticasQuery->where('geocoding_status', 'failed');
                    break;
                case 'todos_geocodificados':
                case 'true':
                    $estadisticasQuery->whereIn('geocoding_status', ['validated', 'manual']);
                    break;
            }
        }
        
        $estadisticas = [
            'total_clientes' => $estadisticasQuery->count(),
            'clientes_activos' => $estadisticasQuery->count(), // Todos los clientes son considerados activos
            'clientes_geocodificados' => (clone $estadisticasQuery)->whereIn('geocoding_status', ['validated', 'manual'])->count(),
            'monto_total_adeudado' => $estadisticasQuery->sum('importe'),
        ];

        // Transformar datos para el frontend
        $clientesTransformados = $clientes->getCollection()->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'certi' => $cliente->certi,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefonos,
                'direccion' => $cliente->direccion,
                'localidad' => $cliente->localidad,
                'provincia' => $cliente->provincia,
                'pais' => $cliente->pais,
                'nbre_convenio' => $cliente->nbre_convenio,
                'tipo_contacto' => $cliente->tipo_contacto ?? 'visita',
                'latitud' => $cliente->lat,
                'longitud' => $cliente->lng,
                'monto_adeudado' => (float) $cliente->importe,
                'estado' => 'activo', // Todos los clientes son activos por defecto
                'geocodificado' => in_array($cliente->geocoding_status, ['validated', 'manual']),
                'fecha_creacion' => $cliente->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'clientes' => $clientesTransformados,
            'pagination' => [
                'current_page' => $clientes->currentPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
                'last_page' => $clientes->lastPage(),
            ],
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Crear nuevo cliente
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'certi' => 'required|string|max:50|unique:clientes,certi',
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'required|string',
            'monto_adeudado' => 'required|numeric|min:0',
        ]);

        $cliente = Cliente::create([
            'certi' => $request->certi,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'monto_adeudado' => $request->monto_adeudado,
            'estado' => 'activo',
            'geocodificado' => false,
            'geocoding_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'cliente' => [
                'id' => $cliente->id,
                'certi' => $cliente->certi,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'latitud' => $cliente->latitud,
                'longitud' => $cliente->longitud,
                'monto_adeudado' => (float) $cliente->monto_adeudado,
                'estado' => $cliente->estado,
                'geocodificado' => $cliente->geocodificado,
                'fecha_creacion' => $cliente->created_at->toISOString(),
            ]
        ], 201);
    }

    /**
     * Mostrar cliente específico
     */
    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json([
            'cliente' => [
                'id' => $cliente->id,
                'certi' => $cliente->certi,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'latitud' => $cliente->latitud,
                'longitud' => $cliente->longitud,
                'monto_adeudado' => (float) $cliente->monto_adeudado,
                'estado' => $cliente->estado,
                'geocodificado' => $cliente->geocodificado,
                'fecha_creacion' => $cliente->created_at->toISOString(),
            ]
        ]);
    }

    /**
     * Actualizar cliente
     */
    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $request->validate([
            'certi' => 'required|string|max:50|unique:clientes,certi,' . $cliente->id,
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'required|string',
            'monto_adeudado' => 'required|numeric|min:0',
            'estado' => 'nullable|in:activo,inactivo',
        ]);

        $cliente->update([
            'certi' => $request->certi,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'monto_adeudado' => $request->monto_adeudado,
            'estado' => $request->estado ?? $cliente->estado,
        ]);

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'cliente' => [
                'id' => $cliente->id,
                'certi' => $cliente->certi,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'latitud' => $cliente->latitud,
                'longitud' => $cliente->longitud,
                'monto_adeudado' => (float) $cliente->monto_adeudado,
                'estado' => $cliente->estado,
                'geocodificado' => $cliente->geocodificado,
                'fecha_creacion' => $cliente->created_at->toISOString(),
            ]
        ]);
    }

    /**
     * Eliminar cliente
     */
    public function destroy(Cliente $cliente): JsonResponse
    {
        // Verificar si tiene asignaciones activas
        $asignacionesActivas = $cliente->asignaciones()
            ->whereHas('ruta', function ($query) {
                $query->whereIn('estado', ['planificada', 'asignada', 'en_progreso']);
            })
            ->exists();

        if ($asignacionesActivas) {
            return response()->json([
                'message' => 'No se puede eliminar el cliente porque tiene asignaciones activas en rutas.'
            ], 422);
        }

        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado exitosamente'
        ]);
    }

    /**
     * Importar clientes desde Excel
     */
    public function importarExcel(Request $request): JsonResponse
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        $file = $request->file('excel_file');
        
        // Crear registro de carga
        $carga = CargaExcel::create([
            'user_id' => $request->user()->id,
            'archivo_nombre' => $file->getClientOriginalName(),
            'total_registros' => 0,
            'registros_procesados' => 0,
            'exitosos' => 0,
            'errores' => 0,
            'estado' => 'procesando',
            'errores_detalle' => null,
        ]);

        try {
            // Procesar Excel inmediatamente
            $import = new ClientesImport();
            $import->import($file);

            // Actualizar registro de carga con resultados
            $carga->update([
                'total_registros' => $import->procesados,
                'registros_procesados' => $import->procesados,
                'exitosos' => $import->exitosos,
                'errores' => $import->errores,
                'errores_detalle' => $import->errores_detalle,
                'estado' => $import->errores > 0 ? 'completado' : 'completado',
            ]);

            $message = $import->errores > 0 
                ? "Archivo procesado con {$import->exitosos} registros exitosos y {$import->errores} errores."
                : "Archivo procesado exitosamente. {$import->exitosos} registros importados.";

            return response()->json([
                'message' => $message,
                'carga_id' => $carga->id,
                'total_registros' => $import->procesados,
                'exitosos' => $import->exitosos,
                'errores' => $import->errores,
                'errores_detalle' => $import->errores_detalle,
            ]);

        } catch (\Exception $e) {
            // Error en el procesamiento
            $carga->update([
                'estado' => 'error',
                'errores_detalle' => [['error' => $e->getMessage()]],
            ]);

            return response()->json([
                'message' => 'Error procesando el archivo: ' . $e->getMessage(),
                'carga_id' => $carga->id,
            ], 500);
        }
    }

    /**
     * Obtener estado de carga de Excel
     */
    public function estadoCarga(CargaExcel $carga): JsonResponse
    {
        return response()->json([
            'carga' => [
                'id' => $carga->id,
                'nombre_archivo' => $carga->nombre_archivo,
                'total_registros' => $carga->total_registros,
                'procesados' => $carga->procesados,
                'exitosos' => $carga->exitosos,
                'errores' => $carga->errores,
                'estado' => $carga->estado,
                'fecha_carga' => $carga->created_at->toISOString(),
                'errores_detalle' => $carga->errores_detalle,
                'primeros_errores' => array_slice($carga->errores_detalle ?? [], 0, 3), // Solo primeros 3 errores
            ]
        ]);
    }

    public function ultimaCarga(): JsonResponse
    {
        $carga = CargaExcel::latest()->first();
        
        if (!$carga) {
            return response()->json(['message' => 'No hay cargas registradas'], 404);
        }

        return response()->json([
            'carga' => [
                'id' => $carga->id,
                'nombre_archivo' => $carga->archivo_nombre,
                'total_registros' => $carga->total_registros,
                'procesados' => $carga->registros_procesados,
                'exitosos' => $carga->exitosos,
                'errores' => $carga->errores,
                'estado' => $carga->estado,
                'fecha_carga' => $carga->created_at->toISOString(),
                'primeros_errores' => array_slice($carga->errores_detalle ?? [], 0, 5),
            ]
        ]);
    }

    /**
     * Sincronizar clientes desde el procedimiento almacenado
     */
    public function sincronizarTarjetas(Request $request): JsonResponse
    {
        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|between:2020,2030',
            'sucursal' => 'required|integer|min:1',
            'cartilla' => 'required|integer|min:1',
        ]);

        try {
            $mes = str_pad($request->mes, 2, '0', STR_PAD_LEFT);
            $anio = $request->anio;
            $sucursal = $request->sucursal;
            $cartilla = $request->cartilla;
            
            // Crear el período en formato YYYY-MM
            $periodo = "{$anio}-{$mes}";

            // Crear registro de sincronización
            $carga = CargaExcel::create([
                'user_id' => $request->user()->id,
                'archivo_nombre' => "Sincronización SP - {$mes}/{$anio} - S:{$sucursal} - C:{$cartilla}",
                'total_registros' => 0,
                'registros_procesados' => 0,
                'exitosos' => 0,
                'errores' => 0,
                'estado' => 'procesando',
                'errores_detalle' => [],
            ]);

            // Ejecutar procedimiento almacenado
            $results = \DB::connection('sqlGPIEVE')
                ->select("EXEC usp_tarjetas ?, ?, ?, ?", [
                    $mes, $anio, $sucursal, $cartilla
                ]);

            $procesados = 0;
            $exitosos = 0;
            $errores = 0;
            $errores_detalle = [];

            foreach ($results as $record) {
                $procesados++;
                
                try {
                    // Analizar el campo telefonos para determinar tipo_contacto
                    $telefonos = $record->Telefonos ?? null;
                    $tipoContacto = 'visita'; // Por defecto
                    
                    // Si hay teléfonos y contiene al menos un dígito, asignar 'llamada'
                    if (!empty($telefonos) && preg_match('/\d/', $telefonos)) {
                        $tipoContacto = 'llamada';
                    }
                    
                    // Verificar si el cliente ya existe en el mismo período
                    $clienteExistente = Cliente::where('certi', $record->certi)
                        ->where('periodo', $periodo)
                        ->first();
                    
                    if ($clienteExistente) {
                        // Actualizar cliente existente en el mismo período
                        $clienteExistente->update([
                            'nombre' => $record->nombre ?? $clienteExistente->nombre,
                            'telefonos' => $telefonos ?? $clienteExistente->telefonos,
                            'direccion' => $record->Direccion ?? $clienteExistente->direccion,
                            'importe' => (float) ($record->importe ?? 0),
                            'nbre_convenio' => $record->NbreConvenio ?? null,
                            'localidad' => $record->localidad ?? null,
                            'dni' => $record->dni ?? null,
                            'periodo' => $periodo, // Asignar el período actual
                            'tipo_contacto' => $tipoContacto, // Asignar tipo de contacto basado en teléfonos
                        ]);
                        $exitosos++;
                    } else {
                        // Crear nuevo cliente (puede existir en otro período, pero es válido tener múltiples registros)
                        Cliente::create([
                            'certi' => $record->certi,
                            'nombre' => $record->nombre,
                            'telefonos' => $telefonos,
                            'direccion' => $record->Direccion,
                            'importe' => (float) ($record->importe ?? 0),
                            'nbre_convenio' => $record->NbreConvenio ?? null,
                            'localidad' => $record->localidad ?? null,
                            'provincia' => 'Salta',
                            'pais' => 'Argentina',
                            'dni' => $record->dni ?? null,
                            'periodo' => $periodo, // Asignar el período actual
                            'tipo_contacto' => $tipoContacto, // Asignar tipo de contacto basado en teléfonos
                            'geocoding_status' => 'pending',
                        ]);
                        $exitosos++;
                    }
                } catch (\Exception $e) {
                    $errores++;
                    $errores_detalle[] = [
                        'registro' => $procesados,
                        'certi' => $record->certi ?? 'N/A',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Actualizar registro de sincronización
            $carga->update([
                'total_registros' => $procesados,
                'registros_procesados' => $procesados,
                'exitosos' => $exitosos,
                'errores' => $errores,
                'errores_detalle' => $errores_detalle,
                'estado' => $errores > 0 ? 'completado' : 'completado',
            ]);

            $message = $errores > 0 
                ? "Sincronización completada para período {$periodo}: {$exitosos} registros exitosos, {$errores} errores."
                : "Sincronización exitosa para período {$periodo}: {$exitosos} registros procesados.";

            return response()->json([
                'message' => $message,
                'sincronizacion' => [
                    'id' => $carga->id,
                    'total_registros' => $procesados,
                    'exitosos' => $exitosos,
                    'errores' => $errores,
                    'parametros' => [
                        'mes' => $mes,
                        'anio' => $anio,
                        'periodo' => $periodo,
                        'sucursal' => $sucursal,
                        'cartilla' => $cartilla,
                    ],
                    'errores_detalle' => array_slice($errores_detalle, 0, 10),
                ]
            ]);

        } catch (\Exception $e) {
            // Error en la sincronización
            if (isset($carga)) {
                $carga->update([
                    'estado' => 'error',
                    'errores_detalle' => [['error' => $e->getMessage()]],
                ]);
            }

            return response()->json([
                'message' => 'Error en la sincronización: ' . $e->getMessage(),
                'carga_id' => $carga->id ?? null,
            ], 500);
        }
    }

    /**
     * Obtener lista de convenios únicos
     */
    public function convenios(): JsonResponse
    {
        $convenios = Cliente::select('nbre_convenio')
            ->whereNotNull('nbre_convenio')
            ->distinct()
            ->orderBy('nbre_convenio')
            ->pluck('nbre_convenio');

        return response()->json([
            'convenios' => $convenios
        ]);
    }

    /**
     * Sincronizar clientes individuales desde el procedimiento almacenado
     */
    public function sincronizarIndividuales(Request $request): JsonResponse
    {
        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|between:2020,2030',
            'sucursal' => 'required|integer|min:1',
            'cartilla' => 'required|integer|min:1',
            'periodo' => 'nullable|string', // Período opcional del frontend
        ]);

        try {
            \Log::info('=== INICIO sincronizarIndividuales ===', [
                'mes' => $request->mes,
                'anio' => $request->anio,
                'sucursal' => $request->sucursal,
                'cartilla' => $request->cartilla,
                'usuario' => $request->user()->id
            ]);

            // Aumentar el tiempo límite de ejecución para manejar grandes volúmenes
            set_time_limit(600); // 10 minutos
            \Log::info('Tiempo límite establecido a 600 segundos');

            $mes = str_pad($request->mes, 2, '0', STR_PAD_LEFT);
            $anio = $request->anio;
            $sucursal = $request->sucursal;
            $cartilla = $request->cartilla;

            // Usar el período del frontend si está disponible, sino crear uno con mes/año
            $periodo = $request->periodo ?? "{$anio}-{$mes}";
            \Log::info('Período a usar:', [
                'periodo_frontend' => $request->periodo,
                'periodo_calculado' => "{$anio}-{$mes}",
                'periodo_final' => $periodo
            ]);

            // Crear registro de sincronización
            $carga = CargaExcel::create([
                'user_id' => $request->user()->id,
                'archivo_nombre' => "Sincronización Individuales - {$mes}/{$anio} - S:{$sucursal} - C:{$cartilla}",
                'total_registros' => 0,
                'registros_procesados' => 0,
                'exitosos' => 0,
                'errores' => 0,
                'estado' => 'procesando',
                'errores_detalle' => [],
            ]);
            \Log::info('Registro de carga creado:', ['carga_id' => $carga->id]);

            // Ejecutar consulta SQL personalizada
            \Log::info('Ejecutando consulta SQL en base de datos sqlGPIEVE...');
            $results = \DB::connection('sqlGPIEVE')
                ->select("SELECT
                    f.IdTitularCp AS Certificado,
                    CASE WHEN f.RAplicar = 0
                         THEN b.Apellido + ', ' + b.Nombre
                         ELSE f.RApellidoNombre + '- Resp'
                    END AS ApellidoNombre,
                    DireccionCob AS DomicilioCobro,
                    BarrioCobro,
                    ISNULL(l.NomLocalidad, '-') AS localidad,
                    f.telefonos,
                    CAST(ROUND(epp.TotalPre, 0) AS INT) AS importe,
                    dbo.fn_SRL_DevolverPLanesCertificado(f.IdTitularCp) AS Planes,
                    RIGHT('0' + CAST(vupfa.Mes AS VARCHAR(2)), 2) + '/' + CAST(vupfa.Anio AS VARCHAR(5)) AS UltimoPago,
                    f.IdCobradorCf AS NroCobrador,
                    CASE WHEN f.CobOficina = 1 THEN 'Pagado en Oficina' ELSE '-' END AS Oficina
                FROM fichas f
                INNER JOIN dbo.Beneficiarios AS b
                    ON f.IdBenCF = b.idbencp
                INNER JOIN dbo.v_UltimoPagoFichasActivas2 AS vupfa
                    ON f.IdTitularCp = vupfa.IdTitularCp
                LEFT JOIN dbo.Localidades AS l
                    ON l.IdLocalidadCp = f.IdlocalidadCf
                INNER JOIN dbo.Estadisticas_paraProyeccion AS epp
                    ON epp.IdTitularCp = f.IdTitularCp
                WHERE f.IdTitularCp > 0");

            \Log::info('Consulta SQL ejecutada exitosamente', [
                'total_resultados' => count($results)
            ]);

            $procesados = 0;
            $exitosos = 0;
            $actualizados = 0;
            $errores = 0;
            $errores_detalle = [];

            // Procesar en lotes para evitar problemas de memoria
            $chunks = array_chunk($results, 500);
            \Log::info('Procesando en lotes', [
                'total_lotes' => count($chunks),
                'registros_por_lote' => 500
            ]);

            foreach ($chunks as $chunkIndex => $chunk) {
                \Log::info("Procesando lote {$chunkIndex}/{count($chunks)}", [
                    'registros_en_lote' => count($chunk)
                ]);

                foreach ($chunk as $record) {
                    $procesados++;

                    // Log cada 100 registros procesados
                    if ($procesados % 100 === 0) {
                        \Log::info("Progreso: {$procesados} registros procesados");
                    }

                    try {
                    // Analizar el campo telefonos para determinar tipo_contacto
                    $telefonos = $record->telefonos ?? null;
                    $tipoContacto = 'visita'; // Por defecto

                    // Si hay teléfonos y contiene al menos un dígito, asignar 'llamada'
                    if (!empty($telefonos) && $telefonos != '0' && preg_match('/\d/', $telefonos)) {
                        $tipoContacto = 'llamada';
                    }

                    // Procesar el campo de dirección y barrio
                    $direccion = trim($record->DomicilioCobro ?? '');
                    if (!empty($record->BarrioCobro) && $record->BarrioCobro != '-') {
                        $direccion .= ', ' . $record->BarrioCobro;
                    }

                    // Establecer convenio como OFICINA por defecto para todos
                    $nbreConvenio = 'OFICINA';

                    // Verificar si el cliente ya existe (sin importar el período)
                    // para evitar duplicados en sincronizaciones múltiples
                    $clienteExistente = Cliente::where('certi', $record->Certificado)
                        ->where('periodo', $periodo)
                        ->first();

                    if ($clienteExistente) {
                        // Actualizar cliente existente en el mismo período
                        $clienteExistente->update([
                            'nombre' => $record->ApellidoNombre ?? $clienteExistente->nombre,
                            'telefonos' => $telefonos ?? $clienteExistente->telefonos,
                            'direccion' => $direccion ?? $clienteExistente->direccion,
                            'importe' => (float) ($record->importe ?? 0),
                            'nbre_convenio' => $nbreConvenio,
                            'localidad' => $record->localidad ?? $clienteExistente->localidad,
                            'periodo' => $periodo,
                            'tipo_contacto' => $tipoContacto,
                            'ultimo_pago' => $record->UltimoPago ?? null,
                            'nro_cobrador' => $record->NroCobrador ?? null,
                        ]);
                        $actualizados++;
                    } else {
                        // Crear nuevo cliente
                        Cliente::create([
                            'certi' => $record->Certificado,
                            'nombre' => $record->ApellidoNombre,
                            'telefonos' => $telefonos,
                            'direccion' => $direccion,
                            'importe' => (float) ($record->importe ?? 0),
                            'nbre_convenio' => $nbreConvenio,
                            'localidad' => $record->localidad ?? '-',
                            'provincia' => 'Salta',
                            'pais' => 'Argentina',
                            'periodo' => $periodo,
                            'tipo_contacto' => $tipoContacto,
                            'ultimo_pago' => $record->UltimoPago ?? null,
                            'nro_cobrador' => $record->NroCobrador ?? null,
                            'geocoding_status' => 'pending',
                        ]);
                        $exitosos++;
                    }
                } catch (\Exception $e) {
                    $errores++;
                    $errores_detalle[] = [
                        'registro' => $procesados,
                        'certificado' => $record->Certificado ?? 'N/A',
                        'nombre' => $record->ApellidoNombre ?? 'N/A',
                        'error' => $e->getMessage(),
                    ];
                    }
                }
            }

            // Actualizar registro de sincronización
            $carga->update([
                'total_registros' => $procesados,
                'registros_procesados' => $procesados,
                'exitosos' => $exitosos,
                'errores' => $errores,
                'errores_detalle' => $errores_detalle,
                'estado' => 'completado',
            ]);

            \Log::info('Procesamiento completado', [
                'total_procesados' => $procesados,
                'nuevos' => $exitosos,
                'actualizados' => $actualizados,
                'errores' => $errores
            ]);

            $totalProcesados = $exitosos + $actualizados;
            $message = $errores > 0
                ? "Sincronización Individual completada para período {$periodo}: {$exitosos} nuevos, {$actualizados} actualizados, {$errores} errores."
                : "Sincronización Individual exitosa para período {$periodo}: {$exitosos} nuevos, {$actualizados} actualizados.";

            \Log::info('=== FIN sincronizarIndividuales EXITOSO ===', [
                'mensaje' => $message,
                'carga_id' => $carga->id
            ]);

            return response()->json([
                'message' => $message,
                'sincronizacion' => [
                    'id' => $carga->id,
                    'total_registros' => $procesados,
                    'nuevos' => $exitosos,
                    'actualizados' => $actualizados,
                    'errores' => $errores,
                    'parametros' => [
                        'mes' => $mes,
                        'anio' => $anio,
                        'periodo' => $periodo,
                        'sucursal' => $sucursal,
                        'cartilla' => $cartilla,
                    ],
                    'errores_detalle' => array_slice($errores_detalle, 0, 10),
                ]
            ]);

        } catch (\Exception $e) {
            // Error en la sincronización
            if (isset($carga)) {
                $carga->update([
                    'estado' => 'error',
                    'errores_detalle' => [['error' => $e->getMessage()]],
                ]);
            }

            \Log::error('=== ERROR EN sincronizarIndividuales ===', [
                'error' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error en la sincronización: ' . $e->getMessage(),
                'carga_id' => $carga->id ?? null,
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Obtener configuración para el frontend
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'config' => [
                'google_maps_api_key' => config('services.google_maps.api_key')
            ]
        ]);
    }
}