<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDeclaracionJurada extends Model
{
    use HasFactory;

    protected $table = "tipos_declaracion_jurada";
    protected $fillable = ['nombre','fecha','activo'];


}
