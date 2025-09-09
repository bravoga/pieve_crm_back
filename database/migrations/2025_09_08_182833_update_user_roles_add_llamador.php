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
        // Alterar el ENUM para agregar 'llamador'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cobrador', 'llamador') NOT NULL DEFAULT 'cobrador'");
        
        // Agregar campo activo si no existe (renombrando is_active)
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active') && !Schema::hasColumn('users', 'activo')) {
                $table->renameColumn('is_active', 'activo');
            } elseif (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('role');
                $table->index('activo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el ENUM
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'cobrador') NOT NULL DEFAULT 'cobrador'");
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'activo')) {
                $table->renameColumn('activo', 'is_active');
            }
        });
    }
};
