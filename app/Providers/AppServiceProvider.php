<?php

namespace App\Providers;

use App\Repositories\EloquentRepositorioCatalogo;
use App\Repositories\EloquentRepositorioSesiones;
use App\Repositories\RepositorioCatalogo;
use App\Repositories\RepositorioSesiones;
use App\Services\Analisis\DetectorDesbalance;
use App\Services\Analisis\EvaluadorAlertas;
use App\Services\Analisis\ReglaAntiguedad;
use App\Services\Analisis\ReglaDegradacion;
use App\Services\Analisis\ReglaMantenimiento;
use App\Services\Cotizador\Conversor;
use App\Services\Cotizador\ServicioCotizaciones;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Armado del Motor de Análisis Proactivo y del Cotizador.
     *
     * Los repositorios se registran por interfaz para poder sustituirlos por
     * dobles en las pruebas. Los umbrales y la serie de cotización llegan
     * desde la configuración, conforme a RF-ANA-06 y RF-COT-05.
     */
    public function register(): void
    {
        $this->app->bind(RepositorioSesiones::class, EloquentRepositorioSesiones::class);
        $this->app->bind(RepositorioCatalogo::class, EloquentRepositorioCatalogo::class);

        $this->app->singleton(EvaluadorAlertas::class, function ($app) {
            $umbrales = config('analisis.umbrales');

            return new EvaluadorAlertas(
                new DetectorDesbalance((int) $umbrales['desbalance_niveles']),
                new ReglaAntiguedad($app->make(RepositorioCatalogo::class), (int) $umbrales['antiguedad_meses']),
                new ReglaDegradacion(
                    $app->make(RepositorioSesiones::class),
                    (int) $umbrales['degradacion_sesiones_minimas'],
                    (float) $umbrales['degradacion_tolerancia'],
                ),
                new ReglaMantenimiento((int) $umbrales['mantenimiento_meses']),
                config('analisis.reglas', []),
            );
        });

        $this->app->singleton(ServicioCotizaciones::class, fn () => new ServicioCotizaciones(
            rtrim((string) config('cotizador.base_url'), '/'),
            (string) config('cotizador.serie'),
            (int) config('cotizador.timeout'),
        ));

        $this->app->singleton(Conversor::class, fn () => new Conversor(
            (int) config('cotizador.advertencia_horas'),
            (int) config('cotizador.vencimiento_dias'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
