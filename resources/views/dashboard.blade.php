<x-layouts::app :title="'Dashboard'">
    <div class="flex flex-col gap-6">

        <!-- Cards -->
        <div class="grid gap-4 md:grid-cols-3">

            <div class="p-4 rounded-xl border bg-white dark:bg-zinc-900">
                <h2 class="text-sm text-gray-500">Consultas realizadas</h2>
                <p class="text-2xl font-bold">--</p>
            </div>

            <div class="p-4 rounded-xl border bg-white dark:bg-zinc-900">
                <h2 class="text-sm text-gray-500">Migraciones</h2>
                <p class="text-2xl font-bold">--</p>
            </div>

            <div class="p-4 rounded-xl border bg-white dark:bg-zinc-900">
                <h2 class="text-sm text-gray-500">Estado sistema</h2>
                <p class="text-green-500 font-semibold">Activo</p>
            </div>

        </div>

        <!-- Accesos rápidos -->
        <div class="p-4 rounded-xl border bg-white dark:bg-zinc-900">
            <h2 class="mb-4 font-semibold">Accesos rápidos</h2>

            <div class="flex gap-4">
                <a href="{{ route('consultas') }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Consultar Matrícula
                </a>

                <a href="{{ route('migraciones') }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg">
                    Ver Migraciones
                </a>
            </div>
        </div>

    </div>
</x-layouts::app>
