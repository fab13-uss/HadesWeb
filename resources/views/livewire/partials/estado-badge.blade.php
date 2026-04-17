@php
    $badge = match($estado) {
        'pendiente'  => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
        'ejecutando' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'completado' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'error'      => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        default      => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
    };

    $label = match($estado) {
        'pendiente'  => 'Pendiente',
        'ejecutando' => 'Ejecutando',
        'completado' => 'Completado',
        'error'      => 'Error',
        default      => $estado,
    };
@endphp

<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
    @if($estado === 'ejecutando')
        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
    @endif
    {{ $label }}
</span>
