@props([
    'variant' => 'default', // default | success | warning | danger
])

@php
$variants = [
    'default' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
    'success' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'danger' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($variants[$variant] ?? $variants['default'])
]) }}>
    {{ $slot }}
</span>