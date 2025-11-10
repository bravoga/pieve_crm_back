<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSolicitudEstado extends Model
{
    use HasFactory;

    protected $table = "tipos_solicitud_estados";
    protected $fillable = ['nombre'];


}
