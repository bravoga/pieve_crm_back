<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->integer('orden')->default(0);
            $table->enum('estado', ['pendiente', 'visitado', 'cobrado', 'no_estaba', 'direccion_incorrecta', 'rechazo_pago'])->default('pendiente');
            $table->decimal('monto_cobrado', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_visita')->nullable();
            $table->decimal('lat_visita', 10, 8)->nullable();
            $table->decimal('lng_visita', 11, 8)->nullable();
            $table->timestamps();
            
            $table->index(['ruta_id', 'orden']);
            $table->index('estado');
            $table->unique(['ruta_id', 'cliente_id'], 'unique_ruta_cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};