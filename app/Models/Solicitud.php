<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;
    protected $table = "solicitudes";
    protected $fillable = [
        'certificado',
        'numero',
        'promotor',
        'sucursal_id',
        'importe',
        'convenio_id',
        'user_id',
        'estado_id',
        'grupo_id'

    ];
    public function titular()
    {
        return $this->hasOne(SolicitudPersona::class);
    }

    public function persona()
    {
        return $this->hasOne(SolicitudPersona::class);
    }

    public function beneficiario()
    {
        return $this->hasOne(SolicitudPersona::class);
    }

    public function integrantes()
    {
        return $this->hasMany(SolicitudPersona::class);
    }

    public function declaraciones()
    {
        return $this->hasMany(SolicitudDeclaracion::class, 'solicitud_id');
    }

    public function grupo()
    {
        return $this->belongsTo(TipoGrupoFamiliar::class,'grupo_id');
    }


    /*
     * public function scopePersonaActivo($query,$tipo_id)
    {

        return $query->whereHas('persona', function ($subQuery) use ($tipo_id) {
            $subQuery->where('tipo_id', $tipo_id)
                ->where('activo', 1);
        })->with(['titular.datos.sexo']);

    }
     */

    public function scopePersonaActivo($query,$persona_id,$tipo_id){

        switch ($tipo_id) {
            case 1://Titular
                $retorno = $query->whereHas('persona', function ($subQuery) use ($persona_id) {
                    $subQuery->where('id', $persona_id)
                        ->where('activo', 1);
                })->with(['persona.datos.sexo', 'persona.tipo', 'persona.estadoCivil']);
                break;
        }


        return $retorno;

    }

}
