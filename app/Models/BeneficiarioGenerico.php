<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiarioGenerico extends Model
{
    use HasFactory;

    protected $connection = 'sqlSalud';
    protected $table = "dbo.Beneficiarios";
    protected $primaryKey = 'idBenCP';
    protected $keyType = 'string';
}
