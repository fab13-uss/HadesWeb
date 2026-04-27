<?php

use App\Models\MigracionEtl;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {

    public function ultimasMigraciones(): \Illuminate\Support\Collection
    {
        return MigracionEtl::whereIn('estado', ['completado', 'error'])
            ->whereNotNull('completado_at')
            ->orderByDesc('completado_at')
            ->limit(10)
            ->get();
    }

    public function totalEsquemas(): int
    {
        try {
            return DB::select("
                SELECT COUNT(*) as total FROM information_schema.schemata
                WHERE schema_name LIKE 'ra_carga%'
            ")[0]->total ?? 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    public function padronMigrado(): bool
    {
        try {
            return DB::select("
                SELECT 1 FROM information_schema.schemata
                WHERE schema_name = 'padron' LIMIT 1
            ") !== [];
        } catch (\Throwable) {
            return false;
        }
    }

}; ?>

<x-layouts.app :title="'Dashboard'">
    <flux:main>
        <div class="p-6 space-y-6">

            {{-- Encabezado --}}
            <div>
                <h1 class="text-xl font-medium text-gray-900 dark:text-zinc-100">
                    Bienvenido, {{ auth()->user()->nombre }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ now()->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                </p>
            </div>

            {{-- Cards de estado --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Relevamientos migrados</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $this->totalEsquemas() }}</p>
                    <p class="text-xs text-gray-400 mt-1">esquemas ra_carga disponibles</p>
                </div>

                <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Padrón</p>
                    <p class="text-sm font-medium mt-2">
                        @if($this->padronMigrado())
                            <span class="inline-flex items-center gap-1.5 text-green-600">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Disponible
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-amber-600">
                                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                No migrado
                            </span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-1">datos de establecimientos</p>
                </div>

                <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tu rol</p>
                    <p class="text-sm font-medium mt-2">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ auth()->user()->esTecnico() ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst(auth()->user()->rol) }}
                        </span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ auth()->user()->esTecnico() ? 'Acceso completo al sistema' : 'Acceso a consultas' }}
                    </p>
                </div>

            </div>

            {{-- Historial de migraciones --}}
            @if(auth()->user()->esTecnico())
                <div>
                    <h2 class="text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">
                        Últimas migraciones
                    </h2>

                    @php $migraciones = $this->ultimasMigraciones(); @endphp

                    @if($migraciones->isEmpty())
                        <div class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-4 py-8 text-center">
                            <p class="text-sm text-gray-400">Todavía no se realizaron migraciones.</p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-zinc-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-zinc-400">Migración</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-zinc-400">Estado</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-zinc-400">Completada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                                    @foreach($migraciones as $mig)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-zinc-100">
                                                {{ $mig->nombre }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    {{ $mig->estado === 'completado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ $mig->estado === 'completado' ? 'Completado' : 'Error' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">
                                                {{ $mig->completado_at?->diffForHumans() ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </flux:main>
</x-layouts.app>
