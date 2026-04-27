<!DOCTYPE html>
<html lang="es" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

            <flux:sidebar.header>
                <div class="px-2 py-3">
                    <span class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Hades</span>
                    <span class="block text-xs text-zinc-500">Sistema de gestión educativa</span>
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>

                {{-- General --}}
                <flux:sidebar.group heading="General" class="grid">
                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- Módulos --}}
                <flux:sidebar.group heading="Módulos" class="grid">
                    <flux:sidebar.item
                        icon="magnifying-glass"
                        :href="route('consultas')"
                        :current="request()->routeIs('consultas')"
                        wire:navigate>
                        Consultas
                    </flux:sidebar.item>

                    @if(auth()->user()->esTecnico())
                        <flux:sidebar.item
                            icon="arrow-path"
                            :href="route('migraciones')"
                            :current="request()->routeIs('migraciones')"
                            wire:navigate>
                            Migraciones
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="users"
                            :href="route('usuarios')"
                            :current="request()->routeIs('usuarios')"
                            wire:navigate>
                            Usuarios
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Usuario en desktop --}}
            <div class="hidden lg:block border-t border-zinc-200 dark:border-zinc-700 p-3">
                <flux:dropdown position="top" align="start">
                    <button class="flex items-center gap-2 w-full rounded-lg px-2 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                        <div class="h-7 w-7 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-medium text-white shrink-0">
                            {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}{{ strtoupper(substr(auth()->user()->apellido, 0, 1)) }}
                        </div>
                        <div class="flex-1 text-left min-w-0">
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">
                                {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
                            </p>
                            <p class="text-xs text-zinc-500 truncate">
                                {{ ucfirst(auth()->user()->rol) }}
                            </p>
                        </div>
                    </button>

                    <flux:menu>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item
                                as="button"
                                type="submit"
                                icon="arrow-right-start-on-rectangle"
                                class="w-full cursor-pointer"
                            >
                                Cerrar sesión
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>

        </flux:sidebar>

        {{-- Mobile header --}}
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <span class="font-semibold text-zinc-800 dark:text-zinc-100">Hades</span>
            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <button class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-medium text-white">
                    {{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}{{ strtoupper(substr(auth()->user()->apellido, 0, 1)) }}
                </button>

                <flux:menu>
                    <div class="px-3 py-2 text-sm">
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">
                            {{ auth()->user()->nombre }} {{ auth()->user()->apellido }}
                        </p>
                        <p class="text-xs text-zinc-500">{{ ucfirst(auth()->user()->rol) }}</p>
                    </div>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                            Cerrar sesión
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
