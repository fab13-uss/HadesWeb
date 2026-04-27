<div {{ $attributes->merge([
    'class' => 'rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700 p-5'
]) }}>
    {{ $slot }}
</div>