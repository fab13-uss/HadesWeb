<div class="p-6 space-y-8" wire:poll.3000ms>

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                Migraciones ETL
            </h1>
            <p class="text-sm text-zinc-500">
                Sincronización desde Nación hacia Planeamiento
            </p>
        </div>

        @if($this->hayAlgunaEjecutando())
            <span class="flex items-center gap-2 text-sm text-amber-500">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                Migración en curso...
            </span>
        @endif
    </div>

    {{-- Aviso VPN --}}
    <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-4 flex gap-3">
        <svg class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                Activá la VPN antes de migrar
            </p>
            <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                Sin VPN la conexión a Nación fallará.
            </p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('mensaje'))
        <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-700 px-4 py-3 text-sm text-green-800 dark:text-green-300">
            {{ session('mensaje') }}
        </div>
    @endif

    @error('general')
        <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-700 px-4 py-3 text-sm text-red-800 dark:text-red-300">
            {{ $message }}
        </div>
    @enderror

    {{-- Worker --}}
@if($this->workerActivo())
    <div class="rounded-xl border border-green-200 bg-green-50 dark:bg-green-900/20 p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
            <div>
                <p class="text-sm font-medium text-green-800 dark:text-green-300">Worker activo</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">Procesando migraciones en cola</p>
            </div>
        </div>
        <button
            wire:click="detenerWorker"
            wire:confirm="¿Detener el worker?"
            class="rounded-lg px-4 py-2 text-sm font-medium bg-red-600 text-white hover:bg-red-700 transition"
        >
            Detener worker
        </button>
    </div>
@else
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Worker detenido</p>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Necesario para procesar migraciones en cola
                </p>
            </div>
            <button
                wire:click="iniciarWorker"
                class="rounded-lg px-4 py-2 text-sm font-medium bg-zinc-900 text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 transition"
            >
                Iniciar worker
            </button>
        </div>

        {{-- Instrucciones para ra_carga --}}
        <div class="rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800 p-3">
            <p class="text-xs font-medium text-blue-800 dark:text-blue-300 mb-1">
                Para migrar Relevamientos Anuales (ra_carga):
            </p>
            <ol class="text-xs text-blue-700 dark:text-blue-400 space-y-1 list-decimal list-inside">
                <li>Activá la VPN</li>
                <li>Hacé clic en <strong>Ejecutar</strong> en el relevamiento que querés migrar</li>
                <li>Corré <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded font-mono">migrar.bat</code> en tu computadora</li>
                <li>Esperá que termine y cerrá el CMD</li>
            </ol>
        </div>
    </div>
@endif

    {{-- PADRÓN --}}
    <section class="space-y-3">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">
            Padrón
        </h2>

        @if($this->padron)
            @include('livewire.partials.migracion-fila', [
                'mig' => $this->padron,
                'hayAlgunaEjecutando' => $this->hayAlgunaEjecutando(),
            ])
        @endif
    </section>

    {{-- RELEVAMIENTOS --}}
    <section class="space-y-3">
        <h2 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">
            Relevamientos anuales
        </h2>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">

            <table class="min-w-full text-xs">

                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-zinc-500">Año</th>
                        <th class="px-4 py-3 text-left text-zinc-500">Estado</th>
                        <th class="px-4 py-3 text-left text-zinc-500">Progreso</th>
                        <th class="px-4 py-3 text-left text-zinc-500">Última ejecución</th>
                        <th class="px-4 py-3 text-right text-zinc-500">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                    @foreach($this->relevamientos as $mig)
                        <tr wire:key="{{ $mig->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">

                            <td class="px-4 py-3 text-zinc-800 dark:text-zinc-100">
                                <p class="font-medium">{{ $mig->nombre }}</p>
                                <p class="text-xs text-zinc-400 mt-0.5">
                                    {{ $mig->descripcion }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                @include('livewire.partials.estado-badge', ['estado' => $mig->estado])
                            </td>

                            <td class="px-4 py-3 w-44">
                                @include('livewire.partials.progreso-bar', ['mig' => $mig])
                            </td>

                            <td class="px-4 py-3 text-xs text-zinc-400">
                                @include('livewire.partials.ultima-ejecucion', ['mig' => $mig])
                            </td>

                            <td class="px-4 py-3 text-right">
                                @include('livewire.partials.boton-ejecutar', [
                                    'mig' => $mig,
                                    'hayAlgunaEjecutando' => $this->hayAlgunaEjecutando(),
                                ])
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </section>

    {{-- Nota --}}
    <p class="text-xs text-zinc-400">
        Log del worker en
        <code class="font-mono bg-zinc-100 dark:bg-zinc-800 px-1 rounded">
            storage/logs/worker.log
        </code>
    </p>

</div>
