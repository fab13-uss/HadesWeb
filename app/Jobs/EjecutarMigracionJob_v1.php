<?php

namespace App\Jobs;

use App\Models\MigracionEtl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class EjecutarMigracionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Sin reintentos automáticos: si falla, el usuario lo ve y decide
    public int $tries = 1;

    // Timeout generoso para migraciones grandes (30 minutos)
    public int $timeout = 1800;

    public function __construct(public readonly int $migracionId) {}

    public function handle(): void
    {
        $migracion = MigracionEtl::findOrFail($this->migracionId);
        $migracion->marcarInicio();

        try {
            // Llama al comando pasándole el ID para que pueda reportar progreso
            Artisan::call($migracion->comando, [
                '--etl-id' => $this->migracionId,
            ]);
        } catch (Throwable $e) {
            $migracion->marcarError($e->getMessage());
            throw $e; // Re-lanzar para que Laravel lo registre en failed_jobs
        }
    }

    public function failed(Throwable $e): void
    {
        // Por si el Job falla por timeout u otra causa externa al try/catch
        MigracionEtl::find($this->migracionId)?->marcarError(
            'Job fallido: ' . $e->getMessage()
        );
    }
}
