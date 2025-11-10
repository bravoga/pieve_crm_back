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
        Schema::create('solicitud_personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes');
            $table->integer('persona_id');
            $table->foreignId('tipo_id')->constrained('tipos_personas')->nullable();
            $table->integer('estado_civil_id')->nullable();
            $table->foreignId('lugar_nacimiento')->constrained('paises')->nullable();
            $table->foreignId('fiscal_id')->constrained('tipos_condicion_fiscal')->default(1);
            $table->foreignId('vinculo_id')->constrained('tipos_vinculos')->nullable();
            $table->string('calle')->nullable();
            $table->string('numero')->nullable();
            $table->string('barrio')->nullable()->nullable();
            $table->integer('provincia_id')->nullable();
            $table->integer('localidad_id')->nullable();
            $table->string('email')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_personas');
    }
};
