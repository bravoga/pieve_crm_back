<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Ficha",
    properties: [
        new OA\Property(property: "idTitularCp", type: "string", example: "123456"),
        new OA\Property(property: "IdBenCF", type: "string", example: "789012"),
        new OA\Property(property: "existe", type: "integer", example: 1)
    ]
)]
class Ficha extends Model
{
    use HasFactory;

    protected $connection = 'sqlGPIEVE';
    protected $table = "dbo.Fichas";
    protected $primaryKey = 'idTitularCp';
    protected $keyType = 'string';

    //vincular con modelo Beneficiario uno a uno
    public function beneficiario()
    {
        return $this->hasOne(Beneficiario::class, 'IdBenCp', 'IdBenCF');
    }
}
