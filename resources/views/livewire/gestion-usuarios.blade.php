<div class="min-h-screen bg-zinc-950 text-zinc-100 p-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">

        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">
                Gestión de Usuarios
            </h1>

            <p class="mt-1 text-sm text-zinc-400">
                Administrá usuarios, accesos y permisos del sistema
            </p>
        </div>

        <button
            wire:click="abrirModalCrear"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition-all hover:bg-indigo-500"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                />

            </svg>

            Nuevo usuario
        </button>

    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">

        {{-- TOTAL --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-5 backdrop-blur">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-400">
                        Usuarios
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-white">
                        {{ $this->usuarios->count() }}
                    </h3>
                </div>

                <div class="rounded-xl bg-zinc-800 p-3 text-zinc-300">
                    👥
                </div>

            </div>

        </div>

        {{-- ACTIVOS --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-5 backdrop-blur">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-400">
                        Activos
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-green-400">
                        {{ $this->usuarios->where('activo', true)->count() }}
                    </h3>
                </div>

                <div class="rounded-xl bg-green-500/10 p-3 text-green-400">
                    ✓
                </div>

            </div>

        </div>

        {{-- TECNICOS --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-5 backdrop-blur">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-400">
                        Técnicos
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-indigo-400">
                        {{ $this->usuarios->where('rol', 'tecnico')->count() }}
                    </h3>
                </div>

                <div class="rounded-xl bg-indigo-500/10 p-3 text-indigo-400">
                    🛠
                </div>

            </div>

        </div>

        {{-- PROFESORES --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-5 backdrop-blur">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-sm text-zinc-400">
                        Profesores
                    </p>

                    <h3 class="mt-2 text-3xl font-bold text-amber-400">
                        {{ $this->usuarios->where('rol', 'profesor')->count() }}
                    </h3>
                </div>

                <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                    🎓
                </div>

            </div>

        </div>

    </div>

    {{-- ALERTAS --}}
    @if(session('mensaje'))
        <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 px-5 py-4 text-sm text-green-300">
            {{ session('mensaje') }}
        </div>
    @endif

    @error('general')
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-sm text-red-300">
            {{ $message }}
        </div>
    @enderror

    {{-- TOOLBAR --}}
    <div class="mb-6 rounded-2xl border border-zinc-800 bg-zinc-900/70 p-4 backdrop-blur">

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            {{-- SEARCH --}}
            <div class="relative w-full max-w-md">

                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">

                    <svg class="h-4 w-4 text-zinc-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"
                        />

                    </svg>

                </div>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="busqueda"
                    placeholder="Buscar usuario..."
                    class="w-full rounded-xl border border-zinc-700 bg-zinc-800 pl-11 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                >

            </div>

        </div>

    </div>

    {{-- TABLA --}}
    <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900/70 shadow-2xl shadow-black/20 backdrop-blur">

        <div class="overflow-x-auto">

            <table class="min-w-full">

                {{-- HEAD --}}
                <thead class="border-b border-zinc-800 bg-zinc-900">

                    <tr>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Usuario
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Contacto
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Rol
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Estado
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            Acciones
                        </th>

                    </tr>

                </thead>

                {{-- BODY --}}
                <tbody class="divide-y divide-zinc-800">

                    @forelse($this->usuarios as $usuario)

                        <tr
                            wire:key="{{ $usuario->id }}"
                            class="transition hover:bg-zinc-800/50"
                        >

                            {{-- USER --}}
                            <td class="px-6 py-5">

                                <div class="flex items-center gap-4">

                                    {{-- AVATAR --}}
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-sm font-bold text-white shadow-lg shadow-indigo-950/30">

                                        {{ strtoupper(substr($usuario->nombre, 0, 1)) }}

                                    </div>

                                    {{-- INFO --}}
                                    <div>

                                        <p class="font-semibold text-white">
                                            {{ $usuario->nombre_completo }}
                                        </p>

                                        <p class="mt-0.5 text-sm font-mono text-zinc-500">
                                            {{ '@'.$usuario->username }}
                                        </p>

                                    </div>

                                </div>

                            </td>

                            {{-- EMAIL --}}
                            <td class="px-6 py-5">

                                <p class="text-sm text-zinc-300">
                                    {{ $usuario->email ?: 'Sin email' }}
                                </p>

                            </td>

                            {{-- ROL --}}
                            <td class="px-6 py-5">

                                <span class="
                                    inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold

                                    {{ $usuario->rol === 'tecnico'
                                        ? 'bg-indigo-500/10 text-indigo-300 border border-indigo-500/20'
                                        : 'bg-amber-500/10 text-amber-300 border border-amber-500/20'
                                    }}
                                ">

                                    {{ ucfirst($usuario->rol) }}

                                </span>

                            </td>

                            {{-- ESTADO --}}
                            <td class="px-6 py-5">

                                <span class="
                                    inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold

                                    {{ $usuario->activo
                                        ? 'bg-green-500/10 text-green-300 border border-green-500/20'
                                        : 'bg-red-500/10 text-red-300 border border-red-500/20'
                                    }}
                                ">

                                    <span class="
                                        h-2 w-2 rounded-full

                                        {{ $usuario->activo
                                            ? 'bg-green-400'
                                            : 'bg-red-400'
                                        }}
                                    "></span>

                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}

                                </span>

                            </td>

                            {{-- ACCIONES --}}
                            <td class="px-6 py-5">

                                <div class="flex items-center justify-end gap-2">

                                    {{-- EDITAR --}}
                                    <button
                                        wire:click="abrirModalEditar({{ $usuario->id }})"
                                        class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-600 hover:bg-zinc-700 hover:text-white"
                                    >
                                        Editar
                                    </button>

                                    {{-- RESET --}}
                                    <button
                                        wire:click="resetearPassword({{ $usuario->id }})"
                                        wire:confirm="¿Resetear la contraseña?"
                                        class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-xs font-medium text-amber-300 transition hover:bg-amber-500/20"
                                    >
                                        Resetear
                                    </button>

                                    {{-- TOGGLE --}}
                                    @if($usuario->id !== auth()->id())

                                        <button
                                            wire:click="toggleActivo({{ $usuario->id }})"
                                            wire:confirm="¿Confirmar acción?"
                                            class="
                                                rounded-xl px-3 py-2 text-xs font-medium transition

                                                {{ $usuario->activo
                                                    ? 'bg-red-500/10 text-red-300 hover:bg-red-500/20 border border-red-500/20'
                                                    : 'bg-green-500/10 text-green-300 hover:bg-green-500/20 border border-green-500/20'
                                                }}
                                            "
                                        >

                                            {{ $usuario->activo ? 'Desactivar' : 'Activar' }}

                                        </button>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="px-6 py-20 text-center">

                                <div class="flex flex-col items-center">

                                    <div class="rounded-2xl bg-zinc-800 p-5 text-4xl">
                                        👤
                                    </div>

                                    <p class="mt-5 text-sm font-medium text-zinc-400">
                                        No se encontraron usuarios
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- MODAL --}}
    @if($modalAbierto)

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-900 shadow-2xl shadow-black/40">

                {{-- HEADER --}}
                <div class="flex items-start justify-between border-b border-zinc-800 px-6 py-5">

                    <div>

                        <h2 class="text-xl font-bold text-white">
                            {{ $modoEdicion ? 'Editar usuario' : 'Nuevo usuario' }}
                        </h2>

                        <p class="mt-1 text-sm text-zinc-400">
                            Configuración general del usuario
                        </p>

                    </div>

                    <button
                        wire:click="cerrarModal"
                        class="rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-800 hover:text-white"
                    >
                        ✕
                    </button>

                </div>

                {{-- BODY --}}
                <div class="space-y-5 p-6">

                    {{-- GRID --}}
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- NOMBRE --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-300">
                                Nombre
                            </label>

                            <input
                                type="text"
                                wire:model="nombre"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white placeholder-zinc-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            >

                            @error('nombre')
                                <p class="mt-1 text-xs text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- APELLIDO --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-300">
                                Apellido
                            </label>

                            <input
                                type="text"
                                wire:model="apellido"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white placeholder-zinc-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            >

                            @error('apellido')
                                <p class="mt-1 text-xs text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                    {{-- USERNAME --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-zinc-300">
                            Usuario
                        </label>

                        <input
                            type="text"
                            wire:model="username"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white placeholder-zinc-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        >

                        @error('username')
                            <p class="mt-1 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- EMAIL --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-zinc-300">
                            Email
                        </label>

                        <input
                            type="email"
                            wire:model="email"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white placeholder-zinc-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        >

                        @error('email')
                            <p class="mt-1 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- GRID --}}
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- ROL --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-300">
                                Rol
                            </label>

                            <select
                                wire:model="rol"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            >
                                <option value="profesor">
                                    Profesor
                                </option>

                                <option value="tecnico">
                                    Técnico
                                </option>

                            </select>

                        </div>

                        {{-- PASSWORD --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-zinc-300">
                                Contraseña
                            </label>

                            <input
                                type="password"
                                wire:model="password"
                                class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white placeholder-zinc-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            >

                            @error('password')
                                <p class="mt-1 text-xs text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-3 border-t border-zinc-800 px-6 py-5">

                    <button
                        wire:click="cerrarModal"
                        class="rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-2.5 text-sm font-medium text-zinc-300 transition hover:bg-zinc-700 hover:text-white"
                    >
                        Cancelar
                    </button>

                    <button
                        wire:click="{{ $modoEdicion ? 'actualizar' : 'guardar' }}"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500"
                    >
                        {{ $modoEdicion ? 'Guardar cambios' : 'Crear usuario' }}
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>
