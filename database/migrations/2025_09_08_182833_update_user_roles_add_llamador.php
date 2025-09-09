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
        // Primero eliminar el índice si existe
        try {
            DB::statement("DROP INDEX users_role_index ON users");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        Schema::table('users', function (Blueprint $table) {
            // En SQL Server, cambiar VARCHAR para permitir 'llamador'
            // Primero verificar si la columna role existe y su tipo actual
            $columnExists = Schema::hasColumn('users', 'role');
            
            if ($columnExists) {
                // Alterar la columna role para permitir más caracteres si es necesario
                $table->string('role', 20)->default('cobrador')->change();
            }
            
            // Agregar campo activo si no existe
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('role');
                $table->index('activo');
            }
        });
        
        // Recrear el índice en la columna role
        try {
            DB::statement("CREATE INDEX users_role_index ON users (role)");
        } catch (\Exception $e) {
            // Si hay error, continuar
        }
        
        // Agregar constraint check para validar los valores permitidos
        try {
            DB::statement("
                ALTER TABLE users 
                ADD CONSTRAINT chk_users_role 
                CHECK (role IN ('admin', 'cobrador', 'llamador'))
            ");
        } catch (\Exception $e) {
            // Si ya existe la constraint, continuar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar constraint check
        try {
            DB::statement("ALTER TABLE users DROP CONSTRAINT chk_users_role");
        } catch (\Exception $e) {
            // Si no existe la constraint, continuar
        }
        
        // Eliminar el índice antes de modificar la columna
        try {
            DB::statement("DROP INDEX users_role_index ON users");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        // Eliminar el índice de activo antes de eliminar la columna
        try {
            DB::statement("DROP INDEX users_activo_index ON users");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        Schema::table('users', function (Blueprint $table) {
            // Eliminar campo activo si existe
            if (Schema::hasColumn('users', 'activo')) {
                $table->dropColumn('activo');
            }
        });
        
        // Recrear el índice
        try {
            DB::statement("CREATE INDEX users_role_index ON users (role)");
        } catch (\Exception $e) {
            // Si hay error, continuar
        }
        
        // Agregar nuevo constraint solo para admin y cobrador
        try {
            DB::statement("
                ALTER TABLE users 
                ADD CONSTRAINT chk_users_role 
                CHECK (role IN ('admin', 'cobrador'))
            ");
        } catch (\Exception $e) {
            // Si ya existe, continuar
        }
    }
};
