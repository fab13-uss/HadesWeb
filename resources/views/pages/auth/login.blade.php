<div class="min-h-screen flex items-center justify-center bg-zinc-100 dark:bg-zinc-950 p-6">
    <div class="w-full max-w-md">
        <div class="rounded-xl border bg-white dark:bg-zinc-900 dark:border-zinc-700 p-6 space-y-6 shadow-sm">

            <div class="text-center">
                <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                    Iniciar sesión
                </h1>
            </div>

            <form wire:submit="login" class="space-y-5">

                <div>
                    <input wire:model="username" type="text" placeholder="Usuario"
                        class="w-full rounded-lg border-zinc-300">
                    @error('username') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <input wire:model="password" type="password" placeholder="Contraseña"
                        class="w-full rounded-lg border-zinc-300">
                    @error('password') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="remember" type="checkbox">
                    Recordarme
                </label>

                <button type="submit"
                    class="w-full rounded-lg px-4 py-2 bg-zinc-900 text-white">
                    Ingresar
                </button>
            </form>
        </div>
    </div>
</div>
