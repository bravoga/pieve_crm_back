<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCondicionPago extends Model
{
    use HasFactory;

    protected $connection = 'sqlCOMPROBANTES';
    protected $table = "tipos_condiciones_pagos";

}
