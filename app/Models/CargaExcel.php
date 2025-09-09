<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaExcel extends Model
{
    use HasFactory;

    protected $table = 'cargas_excel';

    protected $fillable = [
        'user_id',
        'archivo_nombre',
        'total_registros',
        'registros_procesados',
        'exitosos',
        'errores',
        'errores_detalle',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'errores_detalle' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopeProcesando($query)
    {
        return $query->where('estado', 'procesando');
    }

    public function scopeConErrores($query)
    {
        return $query->where('estado', 'error');
    }

    public function getSuccessPercentageAttribute()
    {
        if ($this->total_registros === 0) return 0;
        
        return round(($this->registros_procesados / $this->total_registros) * 100, 2);
    }

    public function getFailurePercentageAttribute()
    {
        if ($this->total_registros === 0) return 0;
        
        return round(($this->registros_fallidos / $this->total_registros) * 100, 2);
    }

    public function isCompleted()
    {
        return $this->estado === 'completado';
    }

    public function hasErrors()
    {
        return $this->estado === 'error' || $this->registros_fallidos > 0;
    }

    // Accessors para compatibilidad con frontend
    public function getNombreArchivoAttribute()
    {
        return $this->archivo_nombre;
    }

    public function getProcesadosAttribute()
    {
        return $this->registros_procesados;
    }

    public function getExitososAttribute()
    {
        return $this->registros_procesados - $this->registros_fallidos;
    }

    public function getErroresDetalleAttribute()
    {
        return $this->errores ?? [];
    }

    // Mutators para compatibilidad con frontend (removidos para evitar conflictos)
}