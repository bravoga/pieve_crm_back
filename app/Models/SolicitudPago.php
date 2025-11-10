<?php

namespace App\Models;

use App\Models\Empresa;
use App\Models\EstadoCivil;
use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\Provincia;
use App\Models\TipoPersona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudPago extends Model
{
    use HasFactory;

    protected $table = "solicitud_pagos";
    protected $fillable = [
        'solicitud_id',
        'tipo_id',
        'calle',
        'numero',
        'barrio',
        'provincia_id',
        'localidad_id',
        'cbu_cuenta_plastico',
        'vencimiento_ano',
        'vencimiento_mes',
        'banco_id',
        'convenio_id',
        'cuentanumero',
        'tarjeta_id'
    ];
    public function tipo()
    {
        return $this->belongsTo(TipoPersona::class, 'tipo_id');
    }

}
