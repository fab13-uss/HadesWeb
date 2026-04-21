<div class="p-6 space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Gestión de Usuarios</h1>
            <p class="text-sm text-gray-500 mt-0.5">Administrá los usuarios del sistema</p>
        </div>
        <button
            wire:click="abrirModalCrear"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
        >
            + Nuevo usuario
        </button>
    </div>

    {{-- Flash --}}
    @if(session('mensaje'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('mensaje') }}
        </div>
    @endif
    @error('general')
        <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ $message }}
        </div>
    @enderror

    {{-- Búsqueda --}}
    <div class="max-w-sm">
        <input
            type="text"
            wire:model.live="busqueda"
            placeholder="Buscar por nombre, apellido o usuario..."
            class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
        >
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Usuario</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Username</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Rol</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($this->usuarios as $usuario)
                    <tr wire:key="{{ $usuario->id }}" class="hover:bg-gray-50 transition-colors {{ !$usuario->activo ? 'opacity-60' : '' }}">

                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $usuario->nombre_completo }}</p>
                        </td>

                        <td class="px-4 py-3 font-mono text-gray-600">{{ $usuario->username }}</td>

                        <td class="px-4 py-3 text-gray-500">{{ $usuario->email ?? '—' }}</td>

                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $usuario->rol === 'tecnico'
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'bg-amber-100 text-amber-700' }}">
                                {{ ucfirst($usuario->rol) }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $usuario->activo
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700' }}">
                                {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">

                                {{-- Editar --}}
                                <button
                                    wire:click="abrirModalEditar({{ $usuario->id }})"
                                    class="rounded px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors"
                                >
                                    Editar
                                </button>

                                {{-- Resetear contraseña --}}
                                <button
                                    wire:click="resetearPassword({{ $usuario->id }})"
                                    wire:confirm="¿Resetear la contraseña de {{ $usuario->nombre_completo }}? Se generará una contraseña temporal."
                                    class="rounded px-2.5 py-1 text-xs font-medium text-amber-600 hover:bg-amber-50 transition-colors"
                                >
                                    Resetear clave
                                </button>

                                {{-- Activar/Desactivar --}}
                                @if($usuario->id !== auth()->id())
                                    <button
                                        wire:click="toggleActivo({{ $usuario->id }})"
                                        wire:confirm="{{ $usuario->activo ? 'Desactivar' : 'Activar' }} al usuario {{ $usuario->nombre_completo }}?"
                                        class="rounded px-2.5 py-1 text-xs font-medium transition-colors
                                            {{ $usuario->activo
                                                ? 'text-red-600 hover:bg-red-50'
                                                : 'text-green-600 hover:bg-green-50' }}"
                                    >
                                        {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                @endif

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">
                            No se encontraron usuarios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal crear/editar --}}
    @if($modalAbierto)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6 space-y-5">

                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ $modoEdicion ? 'Editar usuario' : 'Nuevo usuario' }}
                    </h2>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                        <input type="text" wire:model="nombre"
                            class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Apellido</label>
                        <input type="text" wire:model="apellido"
                            class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('apellido') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de usuario</label>
                    <input type="text" wire:model="username"
                        class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Email
                        <span class="text-gray-400 font-normal">(opcional, para recuperación de contraseña)</span>
                    </label>
                    <input type="email" wire:model="email"
                        class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rol</label>
                    <select wire:model="rol"
                        class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="profesor">Profesor</option>
                        <option value="tecnico">Técnico</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Contraseña
                        @if($modoEdicion)
                            <span class="text-gray-400 font-normal">(dejá vacío para no cambiarla)</span>
                        @endif
                    </label>
                    <input type="password" wire:model="password"
                        class="w-full text-sm rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button wire:click="cerrarModal"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button
                        wire:click="{{ $modoEdicion ? 'actualizar' : 'guardar' }}"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        {{ $modoEdicion ? 'Guardar cambios' : 'Crear usuario' }}
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
