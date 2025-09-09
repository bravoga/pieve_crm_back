<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 6)->unique()->nullable()->after('email');
            $table->enum('role', ['admin', 'cobrador', 'llamador'])->default('cobrador')->after('pin');
            $table->boolean('is_active')->default(true)->after('role');
            
            $table->index('pin');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['pin']);
            $table->dropIndex(['role']);
            $table->dropColumn(['pin', 'role', 'is_active']);
        });
    }
};