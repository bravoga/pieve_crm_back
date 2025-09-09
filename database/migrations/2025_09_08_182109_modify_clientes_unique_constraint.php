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
        Schema::table('clientes', function (Blueprint $table) {
            // Quitar el índice único de certi
            $table->dropUnique(['certi']);
            
            // Crear índice compuesto único para certi + periodo
            // Esto permite que un cliente esté en múltiples períodos pero no duplicado en el mismo período
            $table->unique(['certi', 'periodo'], 'clientes_certi_periodo_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Quitar el índice compuesto
            $table->dropUnique('clientes_certi_periodo_unique');
            
            // Restaurar el índice único original de certi
            $table->unique('certi');
        });
    }
};
