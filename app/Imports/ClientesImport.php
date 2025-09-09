<?php

namespace App\Imports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ClientesImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;

    public $procesados = 0;
    public $exitosos = 0;
    public $errores = 0;
    public $errores_detalle = [];

    public function model(array $row)
    {
        $this->procesados++;

        // Si es la primera fila, guardar las columnas disponibles para debug
        if ($this->procesados == 1) {
            $this->errores_detalle[] = [
                'debug_info' => 'Columnas disponibles en Excel',
                'columnas' => array_keys($row),
                'primera_fila' => $row
            ];
        }

        // Intentar diferentes variantes de nombres de columnas
        $certi = $this->buscarCampo($row, ['certi', 'certificado', 'cert', 'id_cliente', 'codigo']);
        $nombre = $this->buscarCampo($row, ['nombre', 'client', 'cliente', 'name', 'razon_social']);
        $direccion = $this->buscarCampo($row, ['direccion', 'address', 'domicilio', 'dir', 'ubicacion']);
        $monto = $this->buscarCampo($row, ['monto_adeudado', 'monto', 'debt', 'deuda', 'saldo', 'importe']);

        if (!$certi || !$nombre || !$direccion || $monto === null) {
            $this->errores++;
            $this->errores_detalle[] = [
                'fila' => $this->procesados,
                'error' => 'Campos requeridos faltantes',
                'encontrados' => [
                    'certi' => $certi,
                    'nombre' => $nombre,
                    'direccion' => $direccion,
                    'monto' => $monto
                ],
                'columnas_disponibles' => array_keys($row)
            ];
            return null;
        }

        try {
            $telefono = $this->buscarCampo($row, ['telefono', 'phone', 'tel', 'celular', 'movil']);
            
            $cliente = Cliente::create([
                'certi' => $certi,
                'nombre' => $nombre,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'monto_adeudado' => (float) $monto,
                'estado' => 'activo',
                'geocodificado' => false,
                'geocoding_status' => 'pending',
            ]);

            $this->exitosos++;
            return $cliente;
        } catch (\Exception $e) {
            $this->errores++;
            $this->errores_detalle[] = [
                'fila' => $this->procesados,
                'error' => $e->getMessage(),
                'datos_enviados' => [
                    'certi' => $certi,
                    'nombre' => $nombre,
                    'telefono' => $telefono ?? null,
                    'direccion' => $direccion,
                    'monto_adeudado' => (float) $monto,
                ]
            ];
            return null;
        }
    }

    private function buscarCampo(array $row, array $posiblesNombres)
    {
        foreach ($posiblesNombres as $nombre) {
            if (isset($row[$nombre]) && !empty(trim($row[$nombre]))) {
                return trim($row[$nombre]);
            }
        }
        return null;
    }

    public function onError(\Throwable $error)
    {
        $this->errores++;
        $this->errores_detalle[] = [
            'error' => $error->getMessage()
        ];
    }

}