<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    protected $connection = 'sqlPadron';
    protected $table = 'personas';
    protected $fillable = ['solicitud_id','persona_id','tipo_id','estado_civil_id','lugar_nacimiento','fiscal_id','calle','numero','barrio','provincia_id','localidad_id','email','celular','telefono','activo'];

    public function getEdadAttribute()
    {
        return Carbon::parse($this->nacimiento)->age;
    }

    public function sexo()
    {
        return $this->belongsTo(PersonaSexo::class, 'sexo_id');
    }

}
