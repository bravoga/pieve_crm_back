<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Llamada extends Model
{
    protected $fillable = [
        'cliente_id',
        'user_id',
        'estado_llamada_id',
        'telefono_utilizado',
        'observaciones',
        'fecha_llamada'
    ];
    
    protected $casts = [
        'fecha_llamada' => 'datetime'
    ];
    
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function estadoLlamada(): BelongsTo
    {
        return $this->belongsTo(EstadoLlamada::class);
    }
    
    public function scopeByPeriodo($query, $periodo)
    {
        return $query->whereHas('cliente', function($q) use ($periodo) {
            $q->where('periodo', $periodo);
        });
    }
    
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    public function scopeByFecha($query, $fecha)
    {
        return $query->whereDate('fecha_llamada', $fecha);
    }
}
