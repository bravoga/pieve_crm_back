<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('certi', 50)->unique();
            $table->string('nbre_convenio')->nullable();
            $table->string('localidad')->nullable();
            $table->string('nombre');
            $table->string('dni', 20)->nullable();
            $table->text('direccion');
            $table->text('direccion_validada')->nullable();
            $table->string('barrio_real')->nullable();
            $table->text('telefonos')->nullable();
            $table->text('motivo')->nullable();
            $table->decimal('importe', 10, 2)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->enum('geocoding_status', ['pending', 'validated', 'failed', 'manual'])->default('pending');
            $table->timestamps();
            
            $table->index('certi');
            $table->index('dni');
            $table->index('geocoding_status');
            $table->index('localidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};