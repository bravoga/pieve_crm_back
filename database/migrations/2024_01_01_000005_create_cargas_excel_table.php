<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargas_excel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('archivo_nombre')->nullable();
            $table->integer('total_registros')->default(0);
            $table->integer('registros_procesados')->default(0);
            $table->integer('exitosos')->default(0);
            $table->integer('errores')->default(0);
            $table->json('errores_detalle')->nullable();
            $table->enum('estado', ['procesando', 'completado', 'error'])->default('procesando');
            $table->timestamps();
            
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargas_excel');
    }
};