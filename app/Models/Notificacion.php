<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo',
        'icono',
        'color',
        'url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the users that have this notification.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notificacion_usuario')
            ->withPivot(['leida', 'fecha_leida'])
            ->withTimestamps();
    }

    /**
     * Scope para obtener notificaciones de un usuario específico.
     */
    public function scopeParaUsuario($query, $userId)
    {
        return $query->whereHas('usuarios', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope para obtener solo notificaciones no leídas de un usuario.
     */
    public function scopeNoLeidasPorUsuario($query, $userId)
    {
        return $query->whereHas('usuarios', function ($q) use ($userId) {
            $q->where('users.id', $userId)
              ->where('notificacion_usuario.leida', false);
        });
    }

    /**
     * Marcar como leída para un usuario específico.
     */
    public function marcarComoLeidaPara($userId)
    {
        $this->usuarios()->updateExistingPivot($userId, [
            'leida' => true,
            'fecha_leida' => now(),
        ]);
    }

    /**
     * Verificar si está leída para un usuario.
     */
    public function estaLeidaPor($userId): bool
    {
        $pivot = $this->usuarios()->where('users.id', $userId)->first()?->pivot;
        return $pivot ? $pivot->leida : false;
    }

    /**
     * Obtener fecha de lectura para un usuario.
     */
    public function fechaLeidaPor($userId)
    {
        $pivot = $this->usuarios()->where('users.id', $userId)->first()?->pivot;
        return $pivot ? $pivot->fecha_leida : null;
    }
}
