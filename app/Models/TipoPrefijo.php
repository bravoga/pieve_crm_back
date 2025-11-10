<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPrefijo extends Model
{
    use HasFactory;

    protected $table = "tipos_prefijos";
    protected $fillable = ['nombre','activo'];
}
