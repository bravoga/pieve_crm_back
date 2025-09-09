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
        // Eliminar constraint check primero
        try {
            DB::statement("ALTER TABLE clientes DROP CONSTRAINT chk_clientes_tipo_contacto");
        } catch (\Exception $e) {
            // Si no existe la constraint, continuar
        }
        
        // Eliminar índice antes de eliminar la columna
        try {
            DB::statement("DROP INDEX clientes_tipo_contacto_index ON clientes");
        } catch (\Exception $e) {
            // Si no existe el índice, continuar
        }
        
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('tipo_contacto');
        });
    }
};
