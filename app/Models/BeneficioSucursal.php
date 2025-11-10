<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficioSucursal extends Model
{
    use HasFactory;

    protected $table = 'beneficio_sucursales';
    protected $fillable = ['beneficio_id','localidad','direccion','direccion','mapa','activo'];

    protected $casts = [
        'activo'=>"integer",
        'beneficio_id'=>"integer",
    ];
    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'benficio_id');
    }
}
