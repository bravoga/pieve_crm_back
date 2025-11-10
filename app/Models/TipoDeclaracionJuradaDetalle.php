<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDeclaracionJuradaDetalle extends Model
{
    use HasFactory;

    protected $table = "tipos_declaracion_jurada_detalles";
    protected $fillable = ['tipo_id','texto','activo','tipo'];


}
