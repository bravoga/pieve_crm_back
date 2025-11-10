<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCondicionFiscal extends Model
{
    use HasFactory;

    protected $table = "tipos_condicion_fiscal";

    protected $fillable = [ 'nombre'];


}
