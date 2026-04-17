<div class="p-6 space-y-6">

    {{-- Título --}}
    <div>
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            Matrícula Histórica
        </h1>
        <p class="text-sm text-zinc-500">
            Consultá matrícula por oferta y año de relevamiento
        </p>
    </div>

    {{-- Panel --}}
    <div class="rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700 p-5 space-y-5">

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
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $anio }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Ofertas --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                Ofertas educativas
            </label>

            <div class="flex flex-wrap gap-2">
                @foreach($this->todasLasOfertas as $oferta)
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="ofertasSeleccionadas"
                            value="{{ $oferta['codigo'] }}"
                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:bg-zinc-800 dark:border-zinc-600"
                        >
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">
                            {{ $oferta['nombre'] }}
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
                <select wire:model="delZonal"
                    class="w-full text-sm rounded-lg border-zinc-300 focus:ring-zinc-500 focus:border-zinc-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-200">
                    <option value="">Todas</option>
                    @foreach($this->delegacionesZonales as $dz)
                        <option value="{{ $dz }}">{{ $dz }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">
                    Nombre o CUE
                </label>
                <input
                    type="text"
                    wire:model="busqueda"
                    placeholder="Buscar..."
                    class="w-full text-sm rounded-lg border-zinc-300 focus:ring-zinc-500 focus:border-zinc-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-200"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">
                    Estado
                </label>
                <select wire:model="estado"
                    class="w-full text-sm rounded-lg border-zinc-300 focus:ring-zinc-500 focus:border-zinc-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-200">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                    <option value="TODOS">Todos</option>
                </select>
            </div>

        </div>

        {{-- Botones --}}
        <div class="flex items-center gap-3 pt-1">

            {{-- Consultar --}}
            <button
                wire:click="consultar"
                wire:loading.attr="disabled"
                wire:target="consultar"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition
                    bg-zinc-900 text-white hover:bg-zinc-700
                    dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
            >
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
            </button>

            {{-- Limpiar --}}
            <button
                wire:click="limpiar"
                class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50
                       dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800"
            >
                Limpiar
            </button>

            {{-- Exportar --}}
            @if($consultado && count($resultados) > 0)
                <a
                    wire:click="exportarExcel"
                    class="ml-auto inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium
                           bg-green-600 text-white hover:bg-green-700 cursor-pointer"
                >
                    Exportar Excel
                </a>
            @endif

        </div>
    </div>

    {{-- Error --}}
    @if($error)
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $error }}
        </div>
    @endif

    {{-- Resultados --}}
    @if($consultado)
        <div>

            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-zinc-500">
                    {{ number_format(count($resultados)) }} registros
                </p>
            </div>

            @if(count($resultados) > 0)
                <div class="overflow-x-auto rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700">

                    <table class="min-w-full text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-zinc-500">Delegación</th>
                                <th class="px-3 py-2 text-left text-zinc-500">CUE</th>
                                <th class="px-3 py-2 text-left text-zinc-500">Nombre</th>
                                <th class="px-3 py-2 text-left text-zinc-500">Oferta</th>
                                <th class="px-3 py-2 text-left text-zinc-500">Modalidad</th>
                                <th class="px-3 py-2 text-left text-zinc-500">Estado</th>

                                @foreach(array_map('intval', $aniosSeleccionados) as $anio)
                                    <th class="px-3 py-2 text-right text-zinc-500 bg-zinc-100 dark:bg-zinc-700">
                                        {{ $anio }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y dark:divide-zinc-700">

                            @foreach($resultados as $fila)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">

                                    <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">
                                        {{ $fila['del_zonal'] ?? '—' }}
                                    </td>

                                    <td class="px-3 py-2 font-mono text-zinc-700">
                                        {{ $fila['cueanexo'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-700 truncate max-w-xs">
                                        {{ $fila['nombre'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-500">
                                        {{ $fila['descripcion_oferta'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2 text-zinc-500">
                                        {{ $fila['modalidad'] ?? '' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ ($fila['estado'] ?? '') === 'ACTIVO'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                                : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                            {{ $fila['estado'] ?? '' }}
                                        </span>
                                    </td>

                                    @foreach(array_map('intval', $aniosSeleccionados) as $anio)
                                        <td class="px-3 py-2 text-right tabular-nums text-zinc-800 dark:text-zinc-200">
                                            {{ $fila["matricula_{$anio}"] !== null ? number_format($fila["matricula_{$anio}"]) : '—' }}
                                        </td>
                                    @endforeach

                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                </div>
            @else
                <div class="rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700 px-6 py-12 text-center">
                    <p class="text-sm text-zinc-400">
                        No se encontraron resultados
                    </p>
                </div>
            @endif

        </div>
    @endif

</div>
