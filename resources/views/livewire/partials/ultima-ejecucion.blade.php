@if($mig->completado_at)
    {{ $mig->completado_at->diffForHumans() }}
@elseif($mig->iniciado_at)
    Iniciado {{ $mig->iniciado_at->diffForHumans() }}
@else
    Nunca ejecutado
@endif

@if($mig->estado === 'error' && $mig->ultimo_error)
    <p
        class="mt-1 text-red-500 truncate max-w-xs cursor-help"
        title="{{ $mig->ultimo_error }}"
    >
        {{ Str::limit($mig->ultimo_error, 60) }}
    </p>
@endif