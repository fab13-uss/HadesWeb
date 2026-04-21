<?php

use App\Livewire\Consultas\ConsultasDashboard;
use App\Livewire\GestionUsuarios;
use App\Livewire\MigracionDashboard;
use Illuminate\Support\Facades\Route;

// Redirigir / al login
Route::get('/', fn () => redirect()->route('login'));

// Bloquear registro público
Route::get('/register',  fn () => abort(404));
Route::post('/register', fn () => abort(404));

// Rutas autenticadas
Route::middleware(['auth', 'activo'])->group(function () {

    // Consultas — accesible para todos los roles
    Route::get('/consultas', ConsultasDashboard::class)->name('consultas');

    // Migraciones — solo técnicos
    Route::get('/migraciones', MigracionDashboard::class)
        ->middleware('tecnico')
        ->name('migraciones');

    // Gestión de usuarios — solo técnicos
    Route::get('/usuarios', GestionUsuarios::class)
        ->middleware('tecnico')
        ->name('usuarios');

});
