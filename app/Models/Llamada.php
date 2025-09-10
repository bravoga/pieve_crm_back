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
        // Compatibilidad con SQL Server
        return $query->whereRaw("FORMAT(fecha_llamada, 'yyyy-MM') = ?", [$periodo]);
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
