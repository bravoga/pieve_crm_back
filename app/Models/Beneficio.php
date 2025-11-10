<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficio extends Model
{
    use HasFactory;

    protected $table = 'beneficios';
    protected $fillable = ['fecha','titulo','descripcion','direccion','diashorarios','email','telefono','descuentos','imagen','tipo_id','top','web','instagram','instagramLink','facebook','facebookLink','condiciones'];

    protected $casts = [
        'top' => "integer",
        'tipo_id'=>"integer",
        'activo'=>"integer",
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoBeneficio::class, 'tipo_id');
    }

    public function sucursales()
    {
        return $this->hasMany(BeneficioSucursal::class, 'beneficio_id');

    }
}
