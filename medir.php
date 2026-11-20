<?php

/**
 * TP4 - Reconstruye el SQL exacto que arman los controladores, para poder
 * correr EXPLAIN ANALYZE sobre la consulta real y no sobre una version
 * escrita a mano que se le parezca.
 *
 * Uso:  php medir.php            -> imprime las tres consultas
 *       php medir.php --cargar   -> ademas carga 200 sesiones de rendimiento
 *                                   en una configuracion, para medir RNF-REN-05
 *                                   en la escala que el requerimiento declara.
 *
 * Para volver al estado de demostracion:  php artisan garagepc:demo
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CatalogoHardware;
use App\Models\CatalogoJuego;
use App\Models\Equipo;
use App\Models\SesionRendimiento;
use App\Support\Busqueda;
use Illuminate\Support\Carbon;

$equipo = Equipo::where('nombre', 'PC de demostración')->first() ?? Equipo::first();

if ($equipo === null) {
    exit("No hay equipos cargados. Corré antes: php artisan garagepc:demo\n");
}

// ---------------------------------------------------------------
// Carga opcional de 200 sesiones en una sola configuracion
// ---------------------------------------------------------------
if (in_array('--cargar', $argv, true)) {
    $base = $equipo->sesionesRendimiento()->orderByDesc('fecha_sesion')->first();

    if ($base === null) {
        exit("El equipo no tiene ninguna sesión de la que partir.\n");
    }

    $existentes = $equipo->sesionesRendimiento()
        ->where('juego', $base->juego)
        ->where('resolucion', $base->resolucion)
        ->where('nivel_calidad', $base->nivel_calidad)
        ->count();

    $faltan = max(0, 200 - $existentes);
    $fecha = Carbon::parse($base->fecha_sesion);

    for ($i = 1; $i <= $faltan; $i++) {
        $copia = $base->replicate();
        $copia->fecha_sesion = $fecha->copy()->subDays($i);
        $copia->fps_promedio = max(20, $base->fps_promedio - ($i % 17));
        $copia->save();
    }

    echo "Sesiones agregadas: {$faltan}\n";
    echo "Total en la configuración «{$base->juego} / {$base->resolucion} / {$base->nivel_calidad}»: "
        . ($existentes + $faltan) . "\n\n";
}

// ---------------------------------------------------------------
// Las tres consultas, armadas igual que en los controladores
// ---------------------------------------------------------------
echo "-- ============================================================\n";
echo "-- 1. Historial de rendimiento  (RendimientoController::show)\n";
echo "-- ============================================================\n";
echo $equipo->sesionesRendimiento()->orderByDesc('fecha_sesion')->toRawSql() . ";\n\n";

echo "-- ============================================================\n";
echo "-- 2. Autocompletado del Catálogo de Hardware  (CatalogoBusquedaController)\n";
echo "-- ============================================================\n";
$hw = CatalogoHardware::query()->where('tipo', 'gpu');
foreach (Busqueda::terminos('rtx') as $t) {
    $hw->where('modelo', 'like', '%' . $t . '%');
}
echo $hw->orderByDesc('anio_lanzamiento')->orderBy('modelo')->limit(10)->toRawSql() . ";\n\n";

echo "-- ============================================================\n";
echo "-- 3. Autocompletado del Catálogo de Juegos  (CatalogoJuegoBusquedaController)\n";
echo "-- ============================================================\n";
$jg = CatalogoJuego::query();
foreach (Busqueda::terminos('cyber') as $t) {
    $jg->where('nombre', 'like', '%' . $t . '%');
}
echo $jg->orderByDesc('anio_lanzamiento')->orderBy('nombre')->limit(10)->toRawSql() . ";\n";
