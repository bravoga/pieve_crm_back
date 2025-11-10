<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TipoLogs extends Migration
{
    public function up()
    {
        Schema::create('tipo_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipo_logs');
    }
}

