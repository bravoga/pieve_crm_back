<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    
    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];
    
    public static function obtenerValor($clave, $valorPorDefecto = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $valorPorDefecto;
    }
    
    public static function establecerValor($clave, $valor, $descripcion = null)
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'descripcion' => $descripcion
            ]
        );
    }
}
