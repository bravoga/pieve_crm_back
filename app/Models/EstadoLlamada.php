<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoLlamada extends Model
{
    protected $table = 'estados_llamada';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'activo',
        'orden'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];
    
    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class);
    }
    
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }
}
