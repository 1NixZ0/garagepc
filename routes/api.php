<?php

use App\Http\Controllers\Api\AlertaApiController;
use App\Http\Controllers\Api\CatalogoApiController;
use App\Http\Controllers\Api\ComponenteApiController;
use App\Http\Controllers\Api\CotizacionApiController;
use App\Http\Controllers\Api\EquipoApiController;
use App\Http\Controllers\Api\IntervencionApiController;
use App\Http\Controllers\Api\SesionController;
use App\Http\Controllers\Api\SesionRendimientoApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de la aplicación móvil (M-10)
|--------------------------------------------------------------------------
|
| La API se construyó antes que las pantallas móviles porque la consumen dos
| clientes. Reutiliza los mismos FormRequest de la aplicación web, de modo
| que las reglas de validación no puedan desincronizarse entre ambos.
|
*/

// Único endpoint público: entregar un token a cambio de credenciales válidas.
Route::post('/sesion', [SesionController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('api.sesion.store');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/perfil', [SesionController::class, 'show'])->name('api.perfil');
    Route::delete('/sesion', [SesionController::class, 'destroy'])->name('api.sesion.destroy');

    // ------------------------------------------------------------- Garaje
    Route::get('/equipos', [EquipoApiController::class, 'index'])->name('api.equipos');
    Route::get('/equipos/{equipo}/componentes', [EquipoApiController::class, 'componentes'])
        ->name('api.equipos.componentes');

    Route::get('/componentes/{componente}', [ComponenteApiController::class, 'show'])
        ->name('api.componentes.show');

    // ------------------------------------------------------------ Bitácora
    Route::post('/componentes/{componente}/intervenciones', [IntervencionApiController::class, 'store'])
        ->name('api.intervenciones.store');

    // --------------------------------------------------------- Rendimiento
    Route::post('/equipos/{equipo}/sesiones', [SesionRendimientoApiController::class, 'store'])
        ->name('api.sesiones.store');

    // ------------------------------------------------------------- Alertas
    Route::get('/alertas', [AlertaApiController::class, 'index'])->name('api.alertas');

    // ----------------------------------------------------------- Cotizador
    Route::get('/cotizacion', [CotizacionApiController::class, 'show'])->name('api.cotizacion');

    // ----------------------------------------------------------- Catálogos
    Route::get('/catalogo/hardware', [CatalogoApiController::class, 'hardware'])->name('api.catalogo.hardware');
    Route::get('/catalogo/juegos', [CatalogoApiController::class, 'juegos'])->name('api.catalogo.juegos');
});
