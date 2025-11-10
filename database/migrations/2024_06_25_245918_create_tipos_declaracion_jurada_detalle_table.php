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
        Schema::create('tipos_declaracion_jurada_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_id')->constrained('tipos_declaracion_jurada');
            $table->longText('texto')->nullable();
            $table->string('tipo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_declaracion_jurada_detalles');
    }
};
