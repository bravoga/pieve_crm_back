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
        Schema::table('clientes', function (Blueprint $table) {
            // En SQL Server usamos VARCHAR en lugar de ENUM
            $table->string('tipo_contacto', 10)
                  ->default('ambos')
                  ->after('periodo')
                  ->comment('Tipo de contacto preferido: llamada, visita o ambos');
                  
            $table->index('tipo_contacto');
        });
        
        // Agregar constraint check para simular ENUM
        try {
            DB::statement("
                ALTER TABLE clientes 
                ADD CONSTRAINT chk_clientes_tipo_contacto 
                CHECK (tipo_contacto IN ('llamada', 'visita', 'ambos'))
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
        // Eliminar todos los constraints asociados a la columna tipo_contacto
        try {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS 
                WHERE CONSTRAINT_NAME LIKE '%tipo_c%' OR CONSTRAINT_NAME LIKE '%clientes%'
            ");
            
            foreach ($constraints as $constraint) {
                try {
                    DB::statement("ALTER TABLE clientes DROP CONSTRAINT [{$constraint->CONSTRAINT_NAME}]");
                } catch (\Exception $e) {
                    // Continuar con el siguiente constraint
                }
            }
        } catch (\Exception $e) {
            // Si hay error, continuar
        }
        
        // Método alternativo: eliminar constraint por columna
        try {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = ''
                SELECT @sql = @sql + 'ALTER TABLE clientes DROP CONSTRAINT ' + QUOTENAME(name) + ';'
                FROM sys.check_constraints 
                WHERE parent_object_id = OBJECT_ID('clientes') 
                AND definition LIKE '%tipo_contacto%'
                EXEC sp_executesql @sql
            ");
        } catch (\Exception $e) {
            // Si hay error, continuar
        }
        
        // Eliminar índice antes de eliminar la columna
        try {
            DB::statement("DROP INDEX clientes_tipo_contacto_index ON clientes");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        // Finalmente eliminar la columna
        try {
            DB::statement("ALTER TABLE clientes DROP COLUMN tipo_contacto");
        } catch (\Exception $e) {
            // Si hay error, intentar con Laravel
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropColumn('tipo_contacto');
            });
        }
    }
};
