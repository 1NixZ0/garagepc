<?php

/**
 * TP4 - Lleva el equipo de demostracion a 50 componentes, que es la escala
 * que declara RNF-REN-01 ("hasta cincuenta componentes"). Medir el Dashboard
 * con cinco no ejerce la condicion que el requerimiento enuncia.
 *
 * Uso:  php cargar50.php
 * Para volver al estado de demostracion:  php artisan garagepc:demo
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Equipo;

$equipo = Equipo::where('nombre', 'PC de demostración')->first() ?? Equipo::first();

if ($equipo === null) {
    exit("No hay equipos cargados. Corré antes: php artisan garagepc:demo\n");
}

$base = $equipo->componentes()->first();

if ($base === null) {
    exit("El equipo no tiene componentes de los que partir.\n");
}

$actuales = $equipo->componentes()->count();
$faltan = max(0, 50 - $actuales);

for ($i = 1; $i <= $faltan; $i++) {
    $copia = $base->replicate();
    $copia->modelo = $base->modelo.' (unidad '.$i.')';
    $copia->save();
}

echo "Equipo: {$equipo->nombre}\n";
echo "Componentes antes: {$actuales}\n";
echo "Agregados: {$faltan}\n";
echo "Total ahora: ".$equipo->componentes()->count()."\n";
