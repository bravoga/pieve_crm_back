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

class SolicitudDeclaracion extends Model
{
    use HasFactory;

    protected $table = "solicitud_declaraciones";

    public function detalles()
    {
        return $this->hasMany(SolicitudDeclaracionDetalle::class, 'declaracion_id');
    }

}
