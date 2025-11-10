<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitud_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes');
            $table->foreignId('tipo_id')->constrained('tipos_pagos');

            //Domcilcio
            $table->string('calle')->nullable();
            $table->string('numero')->nullable();
            $table->string('barrio')->nullable();
            $table->bigInteger('provincia_id')->nullable();
            $table->bigInteger('localidad_id')->nullable();

            //Debito
            $table->string('cuentanumero')->nullable();  //CBU, CUENTA TARJETA
            $table->integer('vencimiento_ano')->nullable();
            $table->integer('vencimiento_mes')->nullable();
            $table->foreignId('banco_id')->nullable();
            $table->foreignId('tarjeta_id')->nullable();
            $table->foreignId('convenio_id')->nullable();

            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_pagos');
    }
};
