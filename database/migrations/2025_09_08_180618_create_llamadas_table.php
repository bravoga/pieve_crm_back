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
        Schema::create('llamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Usuario que realizó la llamada
            $table->foreignId('estado_llamada_id')->constrained('estados_llamada')->onDelete('restrict');
            $table->string('telefono_utilizado')->nullable(); // Teléfono específico que se utilizó
            $table->text('observaciones')->nullable(); // Comentarios adicionales del usuario
            $table->dateTime('fecha_llamada'); // Fecha y hora exacta de la llamada
            $table->timestamps();
            
            $table->index(['cliente_id', 'fecha_llamada']);
            $table->index(['user_id', 'fecha_llamada']);
            $table->index('estado_llamada_id');
            $table->index('fecha_llamada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llamadas');
    }
};
