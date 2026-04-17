<?php

namespace App\Jobs;

use App\Models\MigracionEtl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EjecutarMigracionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 7200;

    public function __construct(public readonly int $migracionId) {}

    public function handle(): void
    {
        $migracion = MigracionEtl::findOrFail($this->migracionId);
        $migracion->marcarInicio();

        $parametrosBase = array_merge(
            ['--etl-id' => $this->migracionId],
            $migracion->parametros ?? []
        );

        try {
            // ── 1. Intentar migrar a la base del trabajo ──────────────────────
            if ($this->trabajoAccesible()) {
                Log::info("ETL [{$migracion->nombre}]: Migrando a base del trabajo...");

                // Cambiamos temporalmente la conexión default al trabajo
                $this->ejecutarConDestino('planeamiento_trabajo', $migracion->comando, $parametrosBase);

                Log::info("ETL [{$migracion->nombre}]: Base del trabajo completada.");
            } else {
                Log::info("ETL [{$migracion->nombre}]: Base del trabajo no accesible, se omite.");
            }

            // ── 2. Siempre migrar a la base propia ───────────────────────────
            Log::info("ETL [{$migracion->nombre}]: Migrando a base propia...");

            $this->ejecutarConDestino('pgsql', $migracion->comando, $parametrosBase);

            Log::info("ETL [{$migracion->nombre}]: Base propia completada.");

            $migracion->marcarCompletado();

        } catch (Throwable $e) {
            $migracion->marcarError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si la base de datos del trabajo es accesible.
     */
    private function trabajoAccesible(): bool
    {
        try {
            DB::connection('planeamiento_trabajo')->getPdo();
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Cambia la conexión default, corre el comando Artisan y la restaura.
     *
     * Usamos config() para cambiar la default en runtime — esto afecta
     * a todos los DB::statement() y DB::table() dentro del comando.
     */
    private function ejecutarConDestino(string $conexion, string $comando, array $parametros): void
    {
        $defaultOriginal = config('database.default');

        try {
            // Apuntar la conexión default al destino deseado
            config(['database.default' => $conexion]);
            DB::purge($conexion); // Limpiar conexión cacheada por si acaso

            Artisan::call($comando, $parametros);
        } finally {
            // Siempre restaurar la conexión original, haya error o no
            config(['database.default' => $defaultOriginal]);
        }
    }

    public function failed(Throwable $e): void
    {
        MigracionEtl::find($this->migracionId)?->marcarError(
            'Job fallido: ' . $e->getMessage()
        );
    }
}
