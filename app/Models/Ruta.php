<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha',
        'total_clientes',
        'total_cobrado',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total_cobrado' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class)->orderBy('orden');
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'asignaciones')
            ->withPivot(['orden', 'estado', 'monto_cobrado', 'observaciones', 'fecha_visita', 'lat_visita', 'lng_visita'])
            ->orderBy('asignaciones.orden');
    }

    public function scopeByDate($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function getCompletionPercentageAttribute()
    {
        $total = $this->asignaciones()->count();
        if ($total === 0) return 0;
        
        $completadas = $this->asignaciones()->whereIn('estado', ['cobrado', 'visitado', 'no_estaba', 'direccion_incorrecta', 'rechazo_pago'])->count();
        return round(($completadas / $total) * 100, 2);
    }
}