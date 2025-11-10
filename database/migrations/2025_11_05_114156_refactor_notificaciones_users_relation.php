<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla pivot notificacion_usuario
        Schema::create('notificacion_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notificacion_id')->constrained('notificaciones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_leida')->nullable();
            $table->timestamps();

            // Índices para optimizar consultas
            $table->unique(['notificacion_id', 'user_id']);
            $table->index(['user_id', 'leida']);
        });

        // Migrar datos existentes a la nueva tabla
        $notificaciones = DB::table('notificaciones')->get();
        foreach ($notificaciones as $notificacion) {
            DB::table('notificacion_usuario')->insert([
                'notificacion_id' => $notificacion->id,
                'user_id' => $notificacion->user_id,
                'leida' => $notificacion->leida,
                'fecha_leida' => $notificacion->fecha_leida,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Eliminar columnas de la tabla notificaciones
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'leida', 'fecha_leida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas en notificaciones
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_leida')->nullable();
        });

        // Migrar datos de vuelta (tomando el primer usuario de cada notificación)
        $pivots = DB::table('notificacion_usuario')
            ->groupBy('notificacion_id')
            ->select('notificacion_id', DB::raw('MAX(user_id) as user_id'), DB::raw('MAX(leida) as leida'), DB::raw('MAX(fecha_leida) as fecha_leida'))
            ->get();

        foreach ($pivots as $pivot) {
            DB::table('notificaciones')
                ->where('id', $pivot->notificacion_id)
                ->update([
                    'user_id' => $pivot->user_id,
                    'leida' => $pivot->leida,
                    'fecha_leida' => $pivot->fecha_leida,
                ]);
        }

        // Eliminar tabla pivot
        Schema::dropIfExists('notificacion_usuario');
    }
};
