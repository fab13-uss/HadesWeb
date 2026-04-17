<div class="flex min-h-screen">

    {{-- ── Sidebar ── --}}
    <aside class="w-64 shrink-0 border-r border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-700">

        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                Consultas
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">
                Seleccioná un tipo de reporte
            </p>
        </div>

        <nav class="p-2 space-y-1">

            @foreach($tabs as $clave => $tab)
                <button
                    wire:click="cambiarTab('{{ $clave }}')"
                    class="w-full text-left rounded-lg px-3 py-2.5 transition

                        {{ $tabActiva === $clave
                            ? 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white'
                            : 'text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800' }}"
                >
                    <p class="text-sm font-medium leading-tight">
                        {{ $tab['label'] }}
                    </p>

                    <p class="text-xs mt-0.5
                        {{ $tabActiva === $clave
                            ? 'text-zinc-500'
                            : 'text-zinc-400' }}">
                        {{ $tab['descripcion'] }}
                    </p>
                </button>
            @endforeach

        </nav>
    </aside>

    {{-- ── Contenido ── --}}
    <main class="flex-1 overflow-auto bg-zinc-50 dark:bg-zinc-800">

        <div class="p-6">

            @foreach($tabs as $clave => $tab)
                @if($tabActiva === $clave)

                    {{-- Encabezado de sección --}}
                    <div class="mb-4">
                        <h1 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                            {{ $tab['label'] }}
                        </h1>
                        <p class="text-sm text-zinc-500">
                            {{ $tab['descripcion'] }}
                        </p>
                    </div>

                    {{-- Contenido Livewire --}}
                    <div class="rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700">
                        @livewire($tab['componente'], key($clave))
                    </div>

                @endif
            @endforeach

        </div>

    </main>

</div>
