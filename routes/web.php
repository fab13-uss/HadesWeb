<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Livewire\MigracionDashboard;
use App\Livewire\Consultas\ConsultasDashboard;
use App\Livewire\Consultas\MatriculaHistorica;



Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('/consultas', ConsultasDashboard::class)
        ->name('consultas');

    Route::get('/consultas/matricula', MatriculaHistorica::class)
        ->name('consultas.matricula');

    Route::get('/migraciones', MigracionDashboard::class)
        ->name('migraciones');

    Route::get('/migrar-test', function () {

    // 🔴 Limpiar tabla antes de insertar
    DB::table('localizaciones_copia')->truncate();

    $datos = DB::connection('nacion')
        ->table('localizacion')
        ->selectRaw("
            substring(cueanexo from 1 for 7) as cue,
            substring(cueanexo from 8) as anexo,
            nombre
        ")
        ->limit(10)
        ->get();

    foreach ($datos as $fila) {
        DB::table('localizaciones_copia')->insert([
            'cue' => $fila->cue,
            'anexo' => $fila->anexo,
            'nombre' => $fila->nombre,
        ]);
    }

    return "Migración OK";
    });
    

});




require __DIR__.'/settings.php';
