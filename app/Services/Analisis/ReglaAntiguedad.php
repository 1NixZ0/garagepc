<?php

namespace App\Services\Analisis;

use App\Models\Componente;
use App\Repositories\RepositorioCatalogo;

/**
 * Sugerencia de actualización por antigüedad (RF-ANA-02).
 *
 * No alcanza con que el componente sea viejo: tiene que existir en el catálogo
 * una generación posterior del mismo tipo. Sugerir un cambio sin tener a qué
 * cambiarse sería ruido.
 *
 * El requerimiento dice «supere el umbral», así que la comparación es estricta:
 * con el umbral en 36, a los 36 meses no avisa y a los 37 sí.
 */
class ReglaAntiguedad
{
    public function __construct(
        private readonly RepositorioCatalogo $catalogo,
        private readonly int $umbralMeses,
    ) {}

    /**
     * @return array{meses: int, umbral: int, anio_generacion: int}|null
     */
    public function evaluar(Componente $componente): ?array
    {
        $componente->loadMissing('catalogo');

        // RF-ANA-11: los componentes sin vínculo al catálogo quedan fuera.
        if ($componente->catalogo === null) {
            return null;
        }

        $meses = $componente->antiguedadEnMeses();

        if ($meses <= $this->umbralMeses) {
            return null;
        }

        $hayPosterior = $this->catalogo->existeGeneracionPosterior(
            $componente->catalogo->tipo,
            (int) $componente->catalogo->anio_lanzamiento,
        );

        if (! $hayPosterior) {
            return null;
        }

        return [
            'meses' => $meses,
            'umbral' => $this->umbralMeses,
            'anio_generacion' => (int) $componente->catalogo->anio_lanzamiento,
        ];
    }
}
