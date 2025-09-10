<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'certi',
        'nbre_convenio',
        'localidad',
        'provincia',
        'pais',
        'nombre',
        'dni',
        'direccion',
        'direccion_validada',
        'barrio_real',
        'telefonos',
        'telefono', // Alias para compatibilidad con frontend
        'motivo',
        'importe',
        'monto_adeudado', // Alias para compatibilidad con frontend
        'lat',
        'lng',
        'latitud', // Alias para compatibilidad con frontend
        'longitud', // Alias para compatibilidad con frontend
        'geocoding_status',
        'estado',
        'geocodificado',
        'periodo',
        'tipo_contacto',
    ];

    protected $appends = [
        'telefono',
        'monto_adeudado',
        'latitud',
        'longitud',
        'geocodificado',
        'estado'
    ];

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'geocodificado' => 'boolean',
        ];
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }
    
    public function llamadas()
    {
        return $this->hasMany(Llamada::class);
    }

    public function isGeocoded()
    {
        return $this->geocoding_status === 'validated';
    }

    public function hasValidCoordinates()
    {
        return !is_null($this->lat) && !is_null($this->lng);
    }

    public function scopeGeocoded($query)
    {
        return $query->where('geocoding_status', 'validated');
    }

    public function scopePending($query)
    {
        return $query->where('geocoding_status', 'pending');
    }

    public function scopeByLocalidad($query, $localidad)
    {
        return $query->where('localidad', $localidad);
    }

    public function scopeActivos($query)
    {
        // Por ahora todos los clientes están activos por defecto
        // hasta implementar la columna estado
        return $query;
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('certi', 'like', "%{$termino}%")
              ->orWhere('direccion', 'like', "%{$termino}%")
              ->orWhere('telefonos', 'like', "%{$termino}%");
        });
    }
    
    public function scopeConTelefono($query)
    {
        return $query->whereNotNull('telefonos')
                    ->where('telefonos', '!=', '')
                    ->where('telefonos', '!=', '0');
    }
    
    public function scopeByPeriodo($query, $periodo)
    {
        return $query->where('periodo', $periodo);
    }
    
    public function scopeParaLlamadas($query)
    {
        return $query->whereIn('tipo_contacto', ['llamada', 'ambos'])
                    ->conTelefono();
    }
    
    public function scopeParaVisitas($query)
    {
        return $query->whereIn('tipo_contacto', ['visita', 'ambos']);
    }
    
    public function asignacionLlamada()
    {
        return $this->hasOne(AsignacionLlamada::class);
    }

    // Accessors para compatibilidad con frontend
    public function getTelefonoAttribute()
    {
        return $this->telefonos;
    }

    public function getMontoAdeudadoAttribute()
    {
        return $this->importe;
    }

    public function getLatitudAttribute()
    {
        return $this->attributes['lat'];
    }

    public function getLongitudAttribute()
    {
        return $this->attributes['lng'];
    }

    public function getLatAttribute()
    {
        return $this->attributes['lat'];
    }

    public function getLngAttribute()
    {
        return $this->attributes['lng'];
    }

    public function getGeocodificadoAttribute()
    {
        return $this->geocoding_status === 'validated';
    }

    public function getEstadoAttribute()
    {
        return $this->attributes['estado'] ?? 'activo';
    }

    // Mutators para compatibilidad con frontend
    public function setTelefonoAttribute($value)
    {
        $this->attributes['telefonos'] = $value;
    }

    public function setMontoAdeudadoAttribute($value)
    {
        $this->attributes['importe'] = $value;
    }

    public function setLatitudAttribute($value)
    {
        $this->attributes['lat'] = $value;
    }

    public function setLongitudAttribute($value)
    {
        $this->attributes['lng'] = $value;
    }

    public function setLatAttribute($value)
    {
        $this->attributes['lat'] = $value;
    }

    public function setLngAttribute($value)
    {
        $this->attributes['lng'] = $value;
    }

    public function setGeocodificadoAttribute($value)
    {
        $this->attributes['geocoding_status'] = $value ? 'validated' : 'pending';
    }
}