<div class="overflow-hidden rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700">
    <div class="flex items-center justify-between px-4 py-4">

        <div class="flex-1">
            <div class="flex items-center gap-3">
                <p class="font-medium text-zinc-800 dark:text-zinc-100">
                    {{ $mig->nombre }}
                </p>

                @include('livewire.partials.estado-badge', ['estado' => $mig->estado])
            </div>

            <p class="text-xs text-zinc-500 mt-0.5">
                {{ $mig->descripcion }}
            </p>

            @if(in_array($mig->estado, ['ejecutando', 'completado']) && $mig->total_registros > 0)
                <div class="flex items-center gap-2 mt-2 max-w-xs">
                    <div class="flex-1 bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                        <div
                            class="h-1.5 rounded-full transition-all
                            {{ $mig->estado === 'completado'
                                ? 'bg-green-500'
                                : 'bg-amber-400' }}"
                            style="width: {{ $mig->porcentaje() }}%"
                        ></div>
                    </div>

                    <span class="text-xs text-zinc-500 tabular-nums">
                        {{ $mig->porcentaje() }}%
                    </span>
                </div>
            @endif

            @if($mig->estado === 'error' && $mig->ultimo_error)
                <p class="mt-1 text-xs text-red-500 truncate max-w-md" title="{{ $mig->ultimo_error }}">
                    {{ Str::limit($mig->ultimo_error, 80) }}
                </p>
            @endif
        </div>

        <div class="ml-6 flex flex-col items-end gap-2">
            <div class="text-xs text-zinc-500">
                @include('livewire.partials.ultima-ejecucion', ['mig' => $mig])
            </div>

            @error("mig_{$mig->id}")
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror

            @include('livewire.partials.boton-ejecutar', [
                'mig' => $mig,
                'hayAlgunaEjecutando' => $hayAlgunaEjecutando,
            ])
        </div>

    </div>
</div>
