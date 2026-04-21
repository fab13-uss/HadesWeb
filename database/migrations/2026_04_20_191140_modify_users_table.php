<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar columnas que no usamos
            $table->dropColumn(['name', 'email_verified_at']);

            // Agregar nuevas columnas
            $table->string('nombre')->after('id');
            $table->string('apellido')->after('nombre');
            $table->string('username')->unique()->after('apellido');
            $table->string('email')->nullable()->unique()->after('username');
            $table->enum('rol', ['tecnico', 'profesor'])->default('profesor')->after('email');
            $table->boolean('activo')->default(true)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'apellido', 'username', 'rol', 'activo']);
            $table->string('name');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email')->unique()->change();
        });
    }
};
