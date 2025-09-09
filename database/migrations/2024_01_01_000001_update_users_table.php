<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 6)->unique()->nullable()->after('email');
            // En SQL Server usamos VARCHAR en lugar de ENUM
            $table->string('role', 20)->default('cobrador')->after('pin');

            $table->index('pin');
            $table->index('role');
        });
        
        // Agregar constraint check para simular ENUM
        try {
            DB::statement("
                ALTER TABLE users 
                ADD CONSTRAINT chk_users_role_v1 
                CHECK (role IN ('admin', 'cobrador', 'llamador'))
            ");
        } catch (\Exception $e) {
            // Si ya existe la constraint, continuar
        }
    }

    public function down(): void
    {
        // Eliminar constraint check
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT chk_users_role_v1");
        } catch (\Exception $e) {
            // Si no existe la constraint, continuar
        }
        
        // Eliminar índice único de pin
        try {
            DB::statement("DROP INDEX users_pin_unique ON users");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        // Eliminar índices normales
        try {
            DB::statement("DROP INDEX users_pin_index ON users");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        try {
            DB::statement("DROP INDEX users_role_index ON users");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        // Eliminar columnas usando SQL directo
        try {
            DB::statement("ALTER TABLE users DROP COLUMN pin");
        } catch (\Exception $e) {
            \Log::error("No se pudo eliminar la columna pin: " . $e->getMessage());
        }
        
        try {
            DB::statement("ALTER TABLE users DROP COLUMN role");
        } catch (\Exception $e) {
            \Log::error("No se pudo eliminar la columna role: " . $e->getMessage());
        }
    }
};
