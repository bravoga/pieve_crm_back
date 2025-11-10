<?php

namespace App\Models;

use App\Models\Empresa;
use App\Models\EstadoCivil;
use App\Models\Localidad;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudPersona extends Model
{
    use HasFactory;

    protected $table = "solicitud_personas";
    protected $fillable = ['solicitud_id','persona_id','tipo_id','estado_civil_id','lugar_nacimiento','fiscal_id','calle','numero','barrio','provincia_id','localidad_id','email','celular','telefono','activo','vinculo_id'];

    public function datos()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function tipo()
    {
        return $this->belongsTo(TipoPersona::class, 'tipo_id');
    }

    public function estadoCivil()
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id');
    }


    public function lugarNacimiento()
    {
        return $this->belongsTo(Pais::class, 'lugar_nacimiento');
    }


    public function fiscal()
    {
        return $this->belongsTo(TipoCondicionFiscal::class, 'fiscal_id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'provincia_id');
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'localidad_id');
    }
}
