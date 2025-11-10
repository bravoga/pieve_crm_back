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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('tipo')->default('sistema'); // sistema, novedad, alerta, info
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_leida')->nullable();
            $table->string('icono')->default('notifications');
            $table->string('color')->default('primary');
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'leida']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
