<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    @include('partials.head')
</head>
<body class="antialiased bg-white dark:bg-zinc-950">
    {{ $slot }}
    @livewireScripts
</body>
</html>