<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Promotor",
    properties: [
        new OA\Property(property: "IdProductorCp", type: "string", example: "12345"),
        new OA\Property(property: "existe", type: "integer", example: 1)
    ]
)]
class Promotor extends Model
{
    use HasFactory;

    protected $connection = 'sqlGPIEVE';
    protected $table = "dbo.Productores";
    protected $primaryKey = 'IdProductorCp';
    public $timestamps = false;
}
