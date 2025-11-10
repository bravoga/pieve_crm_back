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
        if (!Schema::hasTable('tipos_solicitud_estados')) {
            Schema::create('tipos_solicitud_estados', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nombre');
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_solicitud_estados');
    }
};
