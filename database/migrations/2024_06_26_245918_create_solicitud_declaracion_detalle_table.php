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
        Schema::create('solicitud_declaracion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaracion_id')->constrained('solicitud_declaraciones');
            $table->longText('texto')->nullable();
            $table->string('tipo')->nullable();
            $table->string('valor')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_declaracion_detalles');
    }
};
