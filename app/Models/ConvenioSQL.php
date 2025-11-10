<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvenioSQL extends Model
{
    use HasFactory;

    protected $connection = 'sqlGPIEVE';
    protected $table = "gpieve.saludope.dbo.v_ConvenioSalud";
    protected $primaryKey = 'IdContratanteCP';







}
