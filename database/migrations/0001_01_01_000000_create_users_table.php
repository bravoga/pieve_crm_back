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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable(); // Email no es requerido (viene de AD opcionalmente)
            $table->string('pin', 6)->unique()->nullable();
            $table->integer('legajo')->nullable();
            $table->string('role', 20)->default('cobrador')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('guid'); // GUID de Active Directory
            $table->string('domain'); // Dominio de Active Directory
            $table->string('username')->unique(); // Username es el identificador único
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Agregar constraint check para simular ENUM en role
        try {
            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT chk_users_role
                CHECK (role IN ('admin', 'cobrador', 'llamador'))
            ");
        } catch (\Exception $e) {
            // Si ya existe la constraint, continuar
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
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

        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
