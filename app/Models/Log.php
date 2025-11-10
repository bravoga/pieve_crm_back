<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'user_id',
        'tipo_id',
        'observaciones',
        'ip',
        'navegador',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipo()
    {
        return $this->belongsTo(TipoLog::class, 'tipo_id');
    }
}
