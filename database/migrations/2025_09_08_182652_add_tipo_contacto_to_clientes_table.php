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
        // Eliminar constraint específico que aparece en el error
        try {
            DB::statement("ALTER TABLE clientes DROP CONSTRAINT CK__clientes__tipo_c__01142BA1");
        } catch (\Exception $e) {
            // Si no existe, continuar
        }
        
        // Eliminar nuestro constraint personalizado
        try {
            DB::statement("ALTER TABLE clientes DROP CONSTRAINT chk_clientes_tipo_contacto");
        } catch (\Exception $e) {
            // Si no existe la constraint, continuar
        }
        
        // Método dinámico para encontrar y eliminar todos los constraints de check en la columna
        try {
            DB::statement("
                DECLARE @sql NVARCHAR(MAX) = ''
                SELECT @sql = @sql + 'ALTER TABLE clientes DROP CONSTRAINT ' + QUOTENAME(name) + '; '
                FROM sys.check_constraints 
                WHERE parent_object_id = OBJECT_ID('clientes') 
                AND definition LIKE '%tipo_contacto%'
                
                IF LEN(@sql) > 0
                    EXEC sp_executesql @sql
            ");
        } catch (\Exception $e) {
            // Si hay error, continuar
        }
        
        // Eliminar índice
        try {
            DB::statement("DROP INDEX clientes_tipo_contacto_index ON clientes");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        // Eliminar la columna usando SQL directo
        try {
            DB::statement("ALTER TABLE clientes DROP COLUMN tipo_contacto");
        } catch (\Exception $e) {
            // Si falla SQL directo, no usar Laravel porque también falla
            \Log::error("No se pudo eliminar la columna tipo_contacto: " . $e->getMessage());
        }
    }
};
