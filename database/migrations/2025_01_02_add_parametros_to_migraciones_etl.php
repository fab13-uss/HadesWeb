<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columna parametros a migraciones_etl
        Schema::table('migraciones_etl', function (Blueprint $table) {
            $table->json('parametros')->nullable()->after('comando');
        });

        // 2. Limpiar registros existentes y poblar con todos los disponibles
        DB::table('migraciones_etl')->truncate();

        $filas = [];
        $now   = now();

        // Padrón
        $filas[] = [
            'clave'       => 'padron',
            'nombre'      => 'Padrón',
            'descripcion' => 'Localizaciones, ofertas, domicilios e histórico',
            'comando'     => 'migrar:padron',
            'parametros'  => null,
            'estado'      => 'pendiente',
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        // Relevamientos anuales 2011 → año actual
        for ($anio = 2011; $anio <= (int) date('Y'); $anio++) {
            $filas[] = [
                'clave'       => "ra_carga{$anio}",
                'nombre'      => "Relevamiento Anual {$anio}",
                'descripcion' => "Cuadernillos y cuadros del RA {$anio}",
                'comando'     => 'migrar:ra-carga',
                'parametros'  => json_encode(['--anio' => $anio]),
                'estado'      => 'pendiente',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        DB::table('migraciones_etl')->insert($filas);
    }

    public function down(): void
    {
        Schema::table('migraciones_etl', function (Blueprint $table) {
            $table->dropColumn('parametros');
        });
    }
};
