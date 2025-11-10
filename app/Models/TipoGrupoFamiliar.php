<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoGrupoFamiliar extends Model
{
    use HasFactory;

    protected $table = "tipos_grupos_familiares";
    protected $fillable = ['nombre','descripcion','sql_id'];


}
