<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionLlamada extends Model
{
    protected $table = 'asignaciones_llamadas';
    
    protected $fillable = [
        'cliente_id',
        'user_id',
        'asignado_por',
        'periodo',
        'estado',
        'fecha_asignacion',
        'fecha_vencimiento',
        'notas'
    ];
    
    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_vencimiento' => 'datetime'
    ];
    
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function llamador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
    
    public function scopeByPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }
    
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
    
    public function scopeAsignadas($query)
    {
        return $query->where('estado', 'asignado');
    }
    
    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_progreso');
    }
    
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completado');
    }
    
    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
                    ->whereIn('estado', ['asignado', 'en_progreso']);
    }
    
    public function marcarComoEnProgreso()
    {
        $this->update(['estado' => 'en_progreso']);
    }
    
    public function marcarComoCompletado()
    {
        $this->update(['estado' => 'completado']);
    }
    
    public function cancelar()
    {
        $this->update(['estado' => 'cancelado']);
    }
}
