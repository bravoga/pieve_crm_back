<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Novedad extends Model
{
    protected $table = 'novedades';

    protected $fillable = [
        'titulo',
        'contenido',
        'user_id',
        'activa',
        'fecha_publicacion'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'fecha_publicacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el usuario que creó la novedad
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
