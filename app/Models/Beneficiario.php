<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Beneficiario",
    properties: [
        new OA\Property(property: "idBenCP", type: "string", example: "789012"),
        new OA\Property(property: "Apellido", type: "string", example: "Pérez"),
        new OA\Property(property: "Nombre", type: "string", example: "Juan")
    ]
)]
class Beneficiario extends Model
{
    use HasFactory;

    protected $connection = 'sqlGPIEVE';
    protected $table = "dbo.Beneficiarios";
    protected $primaryKey = 'idBenCP';
    protected $keyType = 'string';
}
