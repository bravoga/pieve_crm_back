<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;


class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, AuthenticatesWithLdap;

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
        'username',
        'guid',
        'domain',
        'is_admin',
        'is_active',
        'is_blocked',
        'failed_login_attempts',
        'last_login_at',
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

    /**
     * Set the user's email attribute.
     * Prevent setting email to null for existing users with valid emails.
     */
    public function setEmailAttribute($value)
    {
        // Si el valor es null y el usuario ya existe con un email válido, no actualizar
        if (is_null($value) && $this->exists && !empty($this->attributes['email'])) {
            return;
        }
        
        // Si el valor es null y no existe email previo, asignar email genérico
        if (is_null($value) && (empty($this->attributes['email']) || !$this->exists)) {
            if (!empty($this->username)) {
                $value = $this->username . '@grupopieve.com';
            } else {
                $value = 'sincorreo@grupopieve.com';
            }
        }
        
        $this->attributes['email'] = $value;
    }

    /**
     * Set the user's domain attribute.
     * Prevent setting domain to 'default' string literal from LDAP sync.
     */
    public function setDomainAttribute($value)
    {
        // Si el valor es 'default' (literal), usar el dominio del email
        if ($value === 'default' && !empty($this->email)) {
            $emailParts = explode('@', $this->email);
            if (count($emailParts) === 2) {
                $value = $emailParts[1];
            } else {
                $value = 'grupopieve.com';
            }
        } elseif (empty($value) || $value === 'default') {
            $value = 'grupopieve.com';
        }
        
        $this->attributes['domain'] = $value;
    }
}
