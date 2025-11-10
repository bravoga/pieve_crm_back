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
        Schema::create('solicitud_declaraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes');
            $table->foreignId('tipo_id')->constrained('tipos_declaracion_jurada');
            $table->date('fecha');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_declaraciones');
    }
};
