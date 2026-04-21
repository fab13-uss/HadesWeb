<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!\App\Models\User::where('username', 'admin')->exists()) {
            \App\Models\User::create([
                'nombre'   => 'Administrador',
                'apellido' => 'Sistema',
                'username' => 'admin',
                'email'    => null,
                'password' => Hash::make('cambia_esta_clave'),
                'rol'      => 'tecnico',
                'activo'   => true,
            ]);
        }
    }
}
