{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Username --}}
        <div>
            <x-input-label for="username" value="Nombre de usuario" />
            <x-text-input
                id="username"
                name="username"
                type="text"
                class="mt-1 block w-full"
                :value="old('username')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input
                id="password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Remember me --}}
        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600">Recordarme</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-between">
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    class="text-sm text-gray-600 hover:text-gray-900 underline">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Ingresar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
