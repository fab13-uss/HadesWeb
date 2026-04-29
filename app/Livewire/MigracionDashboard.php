<?php

namespace App\Livewire;

use App\Jobs\EjecutarMigracionJob;
use App\Models\MigracionEtl;
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

    public function ejecutar(int $id): void
    {
        $migracion = MigracionEtl::findOrFail($id);

        if ($this->hayAlgunaEjecutando()) {
            $this->addError('general', 'Ya hay una migración en curso. Esperá que termine antes de iniciar otra.');
            return;
        }

        $migracion->marcarInicio();
        EjecutarMigracionJob::dispatch($id)->onQueue('migraciones');

        session()->flash('mensaje', "'{$migracion->nombre}' fue enviada a la cola. Corré migrar.bat para procesarla.");
    }

    public function render()
    {
        return view('livewire.migracion-dashboard');
    }
}
