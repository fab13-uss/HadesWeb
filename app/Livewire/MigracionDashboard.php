<?php

namespace App\Livewire;

use App\Jobs\EjecutarMigracionJob;
use App\Models\MigracionEtl;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('ETL — Migraciones')]
class MigracionDashboard extends Component
{
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

    public function workerActivo(): bool
    {
        $pid = Cache::get('worker_pid');
        if (!$pid) return false;

        if (PHP_OS_FAMILY === 'Linux') {
            return file_exists("/proc/{$pid}");
        }

        return false;
    }

    public function ejecutar(int $id): void
    {
        $migracion = MigracionEtl::findOrFail($id);

        if ($this->hayAlgunaEjecutando()) {
            $this->addError('general', 'Ya hay una migración en curso. Esperá que termine antes de iniciar otra.');
            return;
        }

        $migracion->marcarInicio();
        EjecutarMigracionJob::dispatch($id)->onQueue('migraciones');

        session()->flash('mensaje', "'{$migracion->nombre}' fue enviada a la cola. Iniciá el worker para procesarla.");
    }

    public function iniciarWorker(): void
    {
        if ($this->workerActivo()) {
            $this->addError('worker', 'El worker ya está corriendo.');
            return;
        }

        $artisan = base_path('artisan');
        $php     = PHP_BINARY;
        $log     = storage_path('logs/worker.log');

        $comando = "{$php} {$artisan} queue:work --queue=migraciones >> {$log} 2>&1 & echo $!";
        $pid     = trim(shell_exec($comando));

        if ($pid) {
            Cache::put('worker_pid', $pid, now()->addHours(8));
            session()->flash('mensaje', 'Worker iniciado correctamente.');
        }
    }

    public function detenerWorker(): void
    {
        $pid = Cache::get('worker_pid');

        if (!$pid) {
            $this->addError('worker', 'No hay worker corriendo.');
            return;
        }

        shell_exec("kill {$pid} 2>/dev/null");
        Cache::forget('worker_pid');
        session()->flash('mensaje', 'Worker detenido.');
    }

    public function render()
    {
        return view('livewire.migracion-dashboard');
    }
}
