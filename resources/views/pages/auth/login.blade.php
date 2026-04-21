<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950 px-4">

        <div class="w-full max-w-md">

            {{-- Card --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-6 shadow-sm">

                {{-- Header --}}
                <div class="text-center space-y-1">
                    <h1 class="text-lg font-semibold text-zinc-800 dark:text-zinc-100">
                        Iniciar sesión
                    </h1>
                    <p class="text-sm text-zinc-500">
                        Accedé al sistema Hades
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Usuario --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Usuario
                        </label>

                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700
                                   bg-white dark:bg-zinc-800
                                   text-zinc-900 dark:text-zinc-100
                                   text-sm px-3 py-2
                                   focus:ring-2 focus:ring-zinc-500 focus:border-zinc-500"
                        >

                        @error('username')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Contraseña
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700
                                   bg-white dark:bg-zinc-800
                                   text-zinc-900 dark:text-zinc-100
                                   text-sm px-3 py-2
                                   focus:ring-2 focus:ring-zinc-500 focus:border-zinc-500"
                        >

                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:bg-zinc-800 dark:border-zinc-600"
                            >
                            <span class="text-zinc-600 dark:text-zinc-400">
                                Recordarme
                            </span>
                        </label>

                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition
                               bg-zinc-900 text-white hover:bg-zinc-700
                               dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                    >
                        Ingresar
                    </button>

                </form>

            </div>

        </div>

    </div>
</x-guest-layout>
