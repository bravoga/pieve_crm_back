<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'ruta_id',
        'cliente_id',
        'orden',
        'estado',
        'monto_cobrado',
        'observaciones',
        'fecha_visita',
        'lat_visita',
        'lng_visita',
    ];

    protected function casts(): array
    {
        return [
            'monto_cobrado' => 'decimal:2',
            'fecha_visita' => 'datetime',
            'lat_visita' => 'decimal:8',
            'lng_visita' => 'decimal:8',
        ];
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeCobradas($query)
    {
        return $query->where('estado', 'cobrado');
    }

    public function scopeVisitadas($query)
    {
        return $query->whereIn('estado', ['cobrado', 'visitado', 'no_estaba', 'direccion_incorrecta', 'rechazo_pago']);
    }

    public function scopeByOrden($query)
    {
        return $query->orderBy('orden');
    }

    public function isVisitada()
    {
        return in_array($this->estado, ['cobrado', 'visitado', 'no_estaba', 'direccion_incorrecta', 'rechazo_pago']);
    }

    public function isCobrada()
    {
        return $this->estado === 'cobrado';
    }

    public function hasValidLocation()
    {
        return !is_null($this->lat_visita) && !is_null($this->lng_visita);
    }
}