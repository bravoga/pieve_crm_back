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

class SolicitudDeclaracionDetalle extends Model
{
    use HasFactory;

    protected $table = "solicitud_declaracion_detalles";


}
