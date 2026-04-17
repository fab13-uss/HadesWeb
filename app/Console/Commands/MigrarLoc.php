<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('migrar:localizaciones')]
#[Description('Command description')]
class MigrarLocalizaciones extends Command
{
    public function handle()
{
        $this->info('Iniciando migración...');

        // 🔴 1. Limpiar tabla (una sola vez)
        DB::table('localizaciones_copia')->truncate();

        // 🟢 2. Procesar en bloques con batch insert
        DB::connection('nacion')
            ->table('ra2025.establecimiento as e')
            ->join('ra2025.localizacion as l', 'e.id_establecimiento', '=', 'l.id_establecimiento')
            ->select('e.cue', 'l.anexo', 'e.nombre')
            ->orderBy('e.id_establecimiento') // ⚠️ importante para chunk
            ->chunk(1000, function ($datos) {

                $insertData = [];

                foreach ($datos as $fila) {
                    $insertData[] = [
                        'cue' => $fila->cue,
                        'anexo' => $fila->anexo,
                        'nombre' => $fila->nombre,
                    ];
                }

                DB::table('localizaciones_copia')->insert($insertData);
            });

        $this->info('Migración completada');
}
    }
