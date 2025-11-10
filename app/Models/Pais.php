<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Pais",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Argentina"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Pais extends Model
{
    use HasFactory;

    protected $table = 'paises';
    protected $fillable = ['nombre'];
}
