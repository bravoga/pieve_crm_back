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
        'fecha_llamada',
        'fecha_promesa_pago'
    ];
    
    protected $casts = [
        'fecha_llamada' => 'datetime',
        'fecha_promesa_pago' => 'date'
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
        // Filtrar por el período del cliente, no por la fecha de llamada
        return $query->whereHas('cliente', function($q) use ($periodo) {
            $q->where('periodo', $periodo);
        });
    }
    
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
