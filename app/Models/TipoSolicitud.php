<?php

namespace App\Models;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "TipoSolicitud",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Solicitud de Alta"),
        new OA\Property(property: "certificado", type: "string", nullable: true, example: "certificado.pdf"),
        new OA\Property(property: "activo", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class TipoSolicitud extends Model
{
    use HasFactory;

    protected $table = "tipos_solicitudes";
    protected $fillable = ['nombre', 'certificado', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'activo' => true,
    ];
}
