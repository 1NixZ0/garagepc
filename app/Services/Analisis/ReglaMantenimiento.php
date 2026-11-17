<?php

namespace App\Services\Analisis;

use App\Models\Componente;

/**
 * Aviso de mantenimiento preventivo (RF-ANA-07).
 *
 * Cuenta los meses desde la última intervención registrada. Si el componente
 * nunca fue intervenido, cuenta desde la fecha de compra: una pieza de tres
 * años sin un solo registro es precisamente el caso que el aviso busca cubrir.
 *
 * El aviso se desactiva solo al registrar una intervención, porque en la
 * corrida siguiente la condición deja de verificarse (RF-ANA-10).
 */
class ReglaMantenimiento
{
    public function __construct(private readonly int $umbralMeses) {}

    /**
     * @return array{meses_sin_intervencion: int, umbral: int, intervenciones: int}|null
     */
    public function evaluar(Componente $componente): ?array
    {
        $componente->loadMissing('intervenciones');

        $desdeUltima = $componente->mesesDesdeUltimaIntervencion();
        $meses = $desdeUltima ?? $componente->antiguedadEnMeses();

        if ($meses <= $this->umbralMeses) {
            return null;
        }

        return [
            'meses_sin_intervencion' => $meses,
            'umbral' => $this->umbralMeses,
            'intervenciones' => $componente->intervenciones->count(),
        ];
    }
}
