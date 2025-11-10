<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = "empresas";
    protected $appends = ['nombreFull'];


    public function getNombreFullAttribute()
    {
        return strtoupper($this->nombre . ' - ' . $this->cuit);
    }
}
