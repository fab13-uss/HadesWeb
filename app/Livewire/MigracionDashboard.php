<?php

namespace App\Livewire;

use App\Jobs\EjecutarMigracionJob;
use App\Models\MigracionEtl;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MigracionDashboard extends Component
{
    public bool $workerIniciado = false;

    #[Computed]
    public function padron(): ?MigracionEtl
    {
        return MigracionEtl::where('clave', 'padron')->first();
    }

    #[Computed]
    public function relevamientos(): \Illuminate\Support\Collection
    {
        return MigracionEtl::where('clave', 'like', 'ra_carga%')
            ->orderByDesc('clave')
            ->get();
    }

    public function hayAlgunaEjecutando(): bool
    {
        return MigracionEtl::where('estado', 'ejecutando')->exists();
    }

    public function hayJobsPendientes(): bool
    {
        return DB::table('jobs')->where('queue', 'migraciones')->exists();
    }

    // =========================================================================
    // Ejecutar migración
    // =========================================================================

    public function ejecutar(int $id): void
    {
        $migracion = MigracionEtl::findOrFail($id);

        if ($this->hayAlgunaEjecutando()) {
            $this->addError('general', 'Ya hay una migración en curso. Esperá que termine antes de iniciar otra.');
            return;
        }

        if (!$this->verificarConexion($migracion)) {
            $this->addError("mig_{$id}", "La base de datos para '{$migracion->nombre}' aún no está disponible. ¿Tenés la VPN activa?");
            return;
        }

        $migracion->marcarInicio();
        EjecutarMigracionJob::dispatch($id)->onQueue('migraciones');

        $this->workerIniciado = false;
        session()->flash('mensaje', "'{$migracion->nombre}' fue enviada a la cola. Iniciá el worker para procesarla.");
    }

    // =========================================================================
    // Iniciar worker
    // =========================================================================

    public function iniciarWorker(): void
    {
        if (!$this->hayJobsPendientes()) {
            $this->addError('worker', 'No hay migraciones en la cola.');
            return;
        }

        $artisan = base_path('artisan');
        $php     = PHP_BINARY;
        $log     = storage_path('logs/worker.log');

        // Ejecutar en background — el & hace que PHP no espere que termine
        $comando = "{$php} {$artisan} queue:work --queue=migraciones --stop-when-empty >> {$log} 2>&1 &";
        proc_close(proc_open($comando, [], $pipes));

        $this->workerIniciado = true;
        session()->flash('worker', 'Worker iniciado. La migración está procesándose en segundo plano.');
    }

    // =========================================================================

    private function verificarConexion(MigracionEtl $migracion): bool
    {
        try {
            if ($migracion->comando === 'migrar:padron') {
                DB::connection('nacion_padron')->getPdo();
                return true;
            }

            if ($migracion->comando === 'migrar:ra-carga') {
                $anio = $migracion->parametros['--anio'] ?? null;
                if (!$anio) return false;

                $base             = config('database.connections.nacion');
                $base['database'] = "ra_carga{$anio}";
                config(["database.connections.nacion_check_{$anio}" => $base]);
                DB::connection("nacion_check_{$anio}")->getPdo();
                DB::purge("nacion_check_{$anio}");
                return true;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function hydrate(): void
    {
        // Si el worker terminó y no hay más jobs ni ejecuciones, resetear
        if ($this->workerIniciado && !$this->hayJobsPendientes() && !$this->hayAlgunaEjecutando()) {
            $this->workerIniciado = false;
        }
    }

    public function render()
    {
        return view('livewire.migracion-dashboard')->title('ETL — Migraciones');
    }
}
