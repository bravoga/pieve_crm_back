<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    use HasFactory;

    protected $table = "tipos_pagos";
    protected $fillable = ['nombre'];


}
