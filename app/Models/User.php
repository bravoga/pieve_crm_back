<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin',
        'role',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class);
    }

    public function cargasExcel()
    {
        return $this->hasMany(CargaExcel::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCobrador()
    {
        return $this->role === 'cobrador';
    }
    
    public function isLlamador()
    {
        return $this->role === 'llamador';
    }
    
    public function llamadas()
    {
        return $this->hasMany(Llamada::class);
    }
    
    public function asignacionesLlamadas()
    {
        return $this->hasMany(AsignacionLlamada::class);
    }
    
    public function asignacionesCreadas()
    {
        return $this->hasMany(AsignacionLlamada::class, 'asignado_por');
    }
    
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
    
    public function scopeLlamadores($query)
    {
        return $query->where('role', 'llamador')->where('activo', true);
    }
    
    public function scopeCobradores($query)
    {
        return $query->where('role', 'cobrador')->where('activo', true);
    }
}
