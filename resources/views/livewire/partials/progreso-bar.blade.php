@if(in_array($mig->estado, ['ejecutando', 'completado']) && $mig->total_registros > 0)
    <div class="flex items-center gap-2">
        <div class="flex-1 bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
            <div
                class="h-1.5 rounded-full transition-all
                {{ $mig->estado === 'completado'
                    ? 'bg-green-500'
                    : 'bg-amber-400' }}"
                style="width: {{ $mig->porcentaje() }}%"
            ></div>
        </div>
        <span class="text-xs text-zinc-500 tabular-nums w-8 text-right">
            {{ $mig->porcentaje() }}%
        </span>
    </div>

    <p class="text-xs text-zinc-400 mt-0.5 tabular-nums">
        {{ number_format($mig->registros_procesados) }} / {{ number_format($mig->total_registros) }}
    </p>
@else
    <span class="text-xs text-zinc-400">—</span>
@endif
