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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->integer('certificado')->nullable();
            $table->integer('codigo')->nullable();
            $table->integer('numero')->nullable();
            $table->integer('sucursal_id');
            $table->decimal('importe',19,2)->default(0);
            $table->foreignId('estado_id')->constrained('tipos_solicitud_estados');
            $table->foreignId('convenio_id')->constrained('convenios');
            $table->foreignId('grupo_id')->constrained('tipos_grupos_familiares');
            $table->boolean('activo')->default(true);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
