<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migraciones_etl', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();          // 'localizaciones', 'cuadernillos', etc.
            $table->string('nombre');                    // Nombre legible para la UI
            $table->string('descripcion')->nullable();
            $table->string('comando');                   // 'migrar:localizaciones'
            $table->enum('estado', ['pendiente', 'ejecutando', 'completado', 'error'])
                  ->default('pendiente');
            $table->unsignedInteger('total_registros')->default(0);
            $table->unsignedInteger('registros_procesados')->default(0);
            $table->text('ultimo_error')->nullable();
            $table->timestamp('iniciado_at')->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
        });

        // Registros iniciales — agregar una fila por cada migración que exista
        \Illuminate\Support\Facades\DB::table('migraciones_etl')->insert([
    [
        'clave'       => 'ra_carga2025',
        'nombre'      => 'RA Carga 2025',
        'descripcion' => 'Localizaciones, cuadros, estados de carga desde ra_carga2025',
        'comando'     => 'migrar:ra-carga2025',
        'estado'      => 'pendiente',
        'created_at'  => now(),
        'updated_at'  => now(),
    ],
]);
    }
    public function down(): void
    {
        Schema::dropIfExists('migraciones_etl');
    }
};
