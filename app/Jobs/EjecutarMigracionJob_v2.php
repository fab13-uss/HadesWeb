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

    public int $tries   = 1;
    public int $timeout = 7200;

    public function __construct(public readonly int $migracionId) {}

    public function handle(): void
    {
        $migracion = MigracionEtl::findOrFail($this->migracionId);
        $migracion->marcarInicio();

        try {
            $parametros = array_merge(
                ['--etl-id' => $this->migracionId],
                $migracion->parametros ?? []
            );

            Artisan::call($migracion->comando, $parametros);
        } catch (Throwable $e) {
            $migracion->marcarError($e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        MigracionEtl::find($this->migracionId)?->marcarError(
            'Job fallido: ' . $e->getMessage()
        );
    }
}
