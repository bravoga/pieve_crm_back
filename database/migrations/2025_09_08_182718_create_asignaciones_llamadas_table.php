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
        Schema::create('asignaciones_llamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Llamador asignado
            $table->foreignId('asignado_por')->constrained('users'); // Admin que asignó
            $table->string('periodo', 7); // Período para el que se asigna (YYYY-MM)
            $table->enum('estado', ['asignado', 'en_progreso', 'completado', 'cancelado'])->default('asignado');
            $table->dateTime('fecha_asignacion');
            $table->dateTime('fecha_vencimiento')->nullable(); // Fecha límite para completar
            $table->text('notas')->nullable(); // Notas del administrador
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'estado']);
            $table->index(['periodo', 'estado']);
            $table->index(['fecha_asignacion']);
            $table->index(['fecha_vencimiento']);
            
            // Un cliente solo puede estar asignado a un llamador por período
            $table->unique(['cliente_id', 'periodo'], 'asignacion_cliente_periodo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_llamadas');
    }
};
