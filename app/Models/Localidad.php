<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Localidad",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "provincia_id", type: "integer", example: 1),
        new OA\Property(property: "nombre", type: "string", example: "Mar del Plata"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
        new OA\Property(property: "provincia", ref: "#/components/schemas/Provincia")
    ]
)]
class Localidad extends Model
{
    use HasFactory;

    protected $fillable = ['provincia_id', 'nombre'];

    protected $table = 'localidades';

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
}
