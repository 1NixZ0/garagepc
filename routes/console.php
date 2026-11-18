<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * RF-ANA-01: las condiciones de alerta se evalúan mediante una tarea
 * programada. Corre de madrugada, cuando no hay nadie usando el sistema.
 */
Schedule::command('analisis:evaluar')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();

/*
 * RF-COT-02: el servicio externo de tipos de cambio se consulta con una
 * frecuencia mínima de una vez por hora.
 */
Schedule::command('cotizaciones:actualizar')
    ->hourly()
    ->withoutOverlapping();
