<?php

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CatalogoBusquedaController;
use App\Http\Controllers\CatalogoJuegoBusquedaController;
use App\Http\Controllers\ComponenteController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\IntervencionController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RendimientoController;
use App\Http\Controllers\SesionRendimientoController;
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

    // ---------------------------------------------------------- Garaje Virtual
    Route::resource('equipos', EquipoController::class);

    // scoped() hace que Laravel verifique que el componente pertenece al equipo
    // de la URL. Funciona porque "componente" pluraliza a "componentes", que es
    // el nombre real de la relacion; con "intervencion" o "sesion" no pasa lo
    // mismo, y por eso esas se verifican a mano en su controlador.
    Route::resource('equipos.componentes', ComponenteController::class)
        ->except(['index'])
        ->scoped();

    // ---------------------------------------------------------- Bitacora
    Route::post('/componentes/{componente}/intervenciones', [IntervencionController::class, 'store'])
        ->name('intervenciones.store');
    Route::delete('/componentes/{componente}/intervenciones/{intervencion}', [IntervencionController::class, 'destroy'])
        ->name('intervenciones.destroy');

    Route::get('/bitacora', BitacoraController::class)->name('bitacora');

    // ---------------------------------------------------------- Rendimiento
    Route::get('/rendimiento', [RendimientoController::class, 'index'])->name('rendimiento');
    Route::get('/equipos/{equipo}/rendimiento', [RendimientoController::class, 'show'])->name('equipos.rendimiento');

    Route::post('/equipos/{equipo}/sesiones', [SesionRendimientoController::class, 'store'])
        ->name('sesiones.store');
    Route::delete('/equipos/{equipo}/sesiones/{sesion}', [SesionRendimientoController::class, 'destroy'])
        ->name('sesiones.destroy');

    // ---------------------------------------------------- Motor de Analisis
    Route::get('/sugerencias', [AlertaController::class, 'index'])->name('sugerencias');
    Route::patch('/alertas/{alerta}/vista', [AlertaController::class, 'marcarVista'])->name('alertas.vista');

    // ---------------------------------------------------------- Cotizador
    Route::get('/cotizador', CotizadorController::class)->name('cotizador');

    // ---------------------------------------------------------- Catalogos
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/catalogo/buscar', CatalogoBusquedaController::class)->name('catalogo.buscar');
        Route::get('/catalogo/juegos/buscar', CatalogoJuegoBusquedaController::class)->name('catalogo.juegos.buscar');
    });
});
