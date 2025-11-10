<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoBeneficio extends Model
{
    use HasFactory;

    protected $table = "tipo_beneficios";
    protected $fillable = ['nombre'];


}
