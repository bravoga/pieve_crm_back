<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TipoVinculo",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Cónyuge"),
        new OA\Property(property: "sql_id", type: "integer", example: 1),
        new OA\Property(property: "orden", type: "integer", example: 1),
        new OA\Property(property: "activo", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class TipoVinculo extends Model
{
    use HasFactory;

    protected $table = "tipos_vinculos";

    protected $fillable = [
        'nombre',
        'sql_id',
        'orden',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
