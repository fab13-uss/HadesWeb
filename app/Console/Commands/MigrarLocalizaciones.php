<?php

namespace App\Console\Commands;

use App\Models\MigracionEtl;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\DB;

#[Signature('migrar:localizaciones {--etl-id= : ID en migraciones_etl para reportar progreso}')]
#[Description('Copia localizaciones y establecimientos desde RA_CARGA hacia planeamiento')]
class MigrarLocalizaciones extends Command
{
    private const CHUNK_SIZE = 1000;

    public function handle(): int
    {
        $etl = $this->resolverEtl();

        $this->info('Contando registros...');

        $total = DB::connection('nacion')
            ->table('ra2025.establecimiento as e')
            ->join('ra2025.localizacion as l', 'e.id_establecimiento', '=', 'l.id_establecimiento')
            ->count();

        $this->info("Total a migrar: {$total} registros.");

        DB::table('localizaciones_copia')->truncate();

        $procesados = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DB::connection('nacion')
            ->table('ra2025.establecimiento as e')
            ->join('ra2025.localizacion as l', 'e.id_establecimiento', '=', 'l.id_establecimiento')
            ->select('e.cue', 'l.anexo', 'e.nombre')
            ->orderBy('e.id_establecimiento')
            ->chunk(self::CHUNK_SIZE, function ($datos) use (&$procesados, $total, $etl, $bar) {

                $insertData = $datos->map(fn ($fila) => [
                    'cue'    => $fila->cue,
                    'anexo'  => $fila->anexo,
                    'nombre' => $fila->nombre,
                ])->all();

                DB::table('localizaciones_copia')->insert($insertData);

                $procesados += count($insertData);
                $bar->advance(count($insertData));

                // Reportar progreso a la UI si se ejecuta desde el Job
                $etl?->actualizarProgreso($procesados, $total);
            });

        $bar->finish();
        $this->newLine();

        $etl?->marcarCompletado($procesados);

        $this->info("✓ Migración completada: {$procesados} registros.");
        return self::SUCCESS;
    }

    private function resolverEtl(): ?MigracionEtl
    {
        $id = $this->option('etl-id');
        return $id ? MigracionEtl::find($id) : null;
    }
}
