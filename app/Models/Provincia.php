<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Provincia",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "codigo", type: "string", example: "BA"),
        new OA\Property(property: "nombre", type: "string", example: "Buenos Aires"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Provincia extends Model
{
    use HasFactory;

    protected $fillable = ['codigo', 'nombre'];

    protected $table = 'provincias';

    public function localidades()
    {
        return $this->hasMany(Localidad::class);
    }
}
