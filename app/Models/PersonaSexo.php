<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaSexo extends Model
{
    use HasFactory;
    protected $connection = 'sqlPadron';
    protected $table = 'tipos_sexos';



}
