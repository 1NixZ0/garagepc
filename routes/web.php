<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\CatalogoBusquedaController;
use App\Http\Controllers\ComponenteController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegistroController::class, 'create'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/panel', PanelController::class)->name('panel');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::resource('equipos', EquipoController::class);

    // scoped() hace que Laravel verifique que el componente pertenece al equipo de la URL.
    Route::resource('equipos.componentes', ComponenteController::class)
        ->except(['index', 'show'])
        ->scoped();

    Route::get('/catalogo/buscar', CatalogoBusquedaController::class)
        ->middleware('throttle:60,1')
        ->name('catalogo.buscar');
});
