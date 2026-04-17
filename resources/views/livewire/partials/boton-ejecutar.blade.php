@php
    $bloqueado = $mig->estaEjecutando() || $hayAlgunaEjecutando;
@endphp

<button
    wire:click="ejecutar({{ $mig->id }})"
    wire:confirm="¿Confirmar ejecución de '{{ $mig->nombre }}'?"
    wire:loading.attr="disabled"
    @disabled($bloqueado)
    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition
        {{ $bloqueado
            ? 'bg-zinc-100 text-zinc-400 cursor-not-allowed dark:bg-zinc-800 dark:text-zinc-500'
            : 'bg-zinc-900 text-white hover:bg-zinc-700 active:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300' }}"
>
    @if($mig->estaEjecutando())
        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        Ejecutando
    @elseif($hayAlgunaEjecutando)
        En espera
    @else
        Ejecutar
    @endif
</button>
