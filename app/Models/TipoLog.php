<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoLog extends Model
{
    use HasFactory;

    protected $table = "tipo_logs";
    protected $fillable = ['nombre'];
    protected $dateFormat = 'Y-m-d H:i:s.v';


}
