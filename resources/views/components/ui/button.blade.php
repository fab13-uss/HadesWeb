@props([
    'variant' => 'primary', // primary | secondary | success | danger
])

@php
$base = "inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition disabled:opacity-60";

$variants = [
    'primary' => "bg-zinc-900 text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300",
    'secondary' => "border border-zinc-300 text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800",
    'success' => "bg-green-600 text-white hover:bg-green-700",
    'danger' => "bg-red-600 text-white hover:bg-red-700",
];

$classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>