<div class="p-6 space-y-6">

    {{-- Título --}}
    <div>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            Nivel Superior
        </h1>
        <p class="text-sm text-zinc-500">
            Matrícula por plan de estudio y tipo de formación
        </p>
    </div>

    {{-- Panel --}}
    <x-ui.card class="space-y-5">

        {{-- Años --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                Años de relevamiento
                <span class="text-zinc-400 font-normal">(uno o varios)</span>
            </label>

            <div class="flex flex-wrap gap-2">
                @foreach($this->aniosDisponibles as $anio)
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="aniosSeleccionados"
                            value="{{ $anio }}"
                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:bg-zinc-800 dark:border-zinc-600"
                        >
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">
                            {{ $anio }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Filtros --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">
                    Delegación zonal
                </label>
                <x-ui.select wire:model="delZonal">
                    <option value="">Todas</option>
                    @foreach($this->delegacionesZonales as $dz)
                        <option value="{{ $dz }}">{{ $dz }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">
                    Nombre o CUE
                </label>
                <x-ui.input wire:model="busqueda" placeholder="Buscar..." />
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">
                    Tipo de formación
                </label>
                <x-ui.input wire:model="tipoFormacion" placeholder="Ej: Docente, Técnica..." />
            </div>

        </div>

        {{-- Botones --}}
        <div class="flex items-center gap-3 pt-1">

            <x-ui.button wire:click="consultar" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="consultar">
                    Consultar
                </span>

                <span wire:loading wire:target="consultar" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Consultando
                </span>
            </x-ui.button>

            <x-ui.button variant="secondary" wire:click="limpiar">
                Limpiar
            </x-ui.button>

            @if($consultado && count($resultados) > 0)
                <x-ui.button
                    variant="success"
                    wire:click="exportarExcel"
                    class="ml-auto"
                >
                    Exportar Excel
                </x-ui.button>
            @endif

        </div>
    </x-ui.card>

    {{-- Error --}}
    @if($error)
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $error }}
        </div>
    @endif

    {{-- Resultados --}}
    @if($consultado)
        <div>

            <p class="text-sm text-zinc-500 mb-3">
                {{ number_format(count($resultados)) }} registros
            </p>

            @if(count($resultados) > 0)
                <x-ui.card class="overflow-x-auto p-0">

                    <table class="min-w-full text-xs">

                        {{-- HEADER --}}
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300">Delegación</th>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300">CUE</th>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300">Nombre</th>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300">Modalidad</th>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300 bg-zinc-200 dark:bg-zinc-700">Año</th>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300">Plan / Título</th>
                                <th class="px-3 py-2 text-left text-zinc-600 dark:text-zinc-300">Tipo Formación</th>
                                <th class="px-3 py-2 text-right text-zinc-600 dark:text-zinc-300">Total</th>
                            </tr>
                        </thead>

                        {{-- BODY --}}
                        <tbody class="divide-y dark:divide-zinc-700">

                            @foreach($resultados as $fila)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">

                                    <td class="px-3 py-2 text-zinc-800 dark:text-white">
                                        {{ $fila['del_zonal'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 font-mono text-zinc-800 dark:text-white">
                                        {{ $fila['cueanexo'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-800 dark:text-white truncate max-w-xs">
                                        {{ $fila['nombre'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-800 dark:text-white/80">
                                        {{ $fila['modalidad'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 font-medium text-zinc-800 dark:text-white bg-zinc-700/40">
                                        {{ $fila['anio'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-800 dark:text-white truncate max-w-xs">
                                        {{ $fila['plan_estudio_titulo'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-800 dark:text-white/80">
                                        {{ $fila['tipo_formacion'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 text-right tabular-nums text-zinc-800 dark:text-white">
                                        {{ $fila['total'] !== null ? number_format($fila['total']) : '—' }}
                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </x-ui.card>
            @else
                <x-ui.card class="text-center py-12">
                    <p class="text-sm text-zinc-400">
                        No se encontraron resultados
                    </p>
                </x-ui.card>
            @endif

        </div>
    @endif

</div>