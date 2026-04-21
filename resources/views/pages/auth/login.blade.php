{{-- resources/views/pages/auth/login.blade.php --}}

<div class="min-h-screen flex items-center justify-center bg-zinc-100 dark:bg-zinc-950 p-6">

    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700 p-6 space-y-6 shadow-sm">

            {{-- Header --}}
            <div class="text-center">
                <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                    Iniciar sesión
                </h1>
                <p class="text-sm text-zinc-500 mt-1">
                    Acceso al sistema Hades
                </p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Username --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Usuario
                    </label>
                    <input
                        type="text"
                        name="username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        class="w-full text-sm rounded-lg border-zinc-300 focus:ring-zinc-500 focus:border-zinc-500
                               dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-200"
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
                        type="password"
                        name="password"
                        required
                        class="w-full text-sm rounded-lg border-zinc-300 focus:ring-zinc-500 focus:border-zinc-500
                               dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-200"
                    >
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <input type="checkbox"
                            name="remember"
                            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:bg-zinc-800 dark:border-zinc-600">
                        Recordarme
                    </label>

                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full inline-flex justify-center items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition
                           bg-zinc-900 text-white hover:bg-zinc-700
                           dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                >
                    Ingresar
                </button>
            </form>

        </div>

    </div>
</div>
