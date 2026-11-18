<?php

namespace App\Services\Cotizador;

use App\Models\Cotizacion;

/**
 * Conversión entre las tres monedas soportadas (RF-COT-01).
 *
 * El cotizador se limita a convertir. Sus resultados no son una estimación
 * del valor de reventa de un componente usado: eso lo calcula el
 * EstimadorReventa y se presenta siempre identificado como estimación
 * (RF-COT-04).
 */
class Conversor
{
    public function __construct(
        private readonly int $advertenciaHoras,
        private readonly int $vencimientoDias,
    ) {}

    public function vigente(): ?Cotizacion
    {
        return Cotizacion::ultima();
    }

    public function horasDeAntiguedad(?Cotizacion $cotizacion): ?float
    {
        return $cotizacion?->obtenida_en->diffInHours(now(), absolute: true);
    }

    /** RF-COT-11, primer límite. */
    public function requiereAdvertencia(?Cotizacion $cotizacion): bool
    {
        $horas = $this->horasDeAntiguedad($cotizacion);

        return $horas !== null && $horas > $this->advertenciaHoras;
    }

    /** RF-COT-11, segundo límite: pasado esto no se convierte. */
    public function estaVencida(?Cotizacion $cotizacion): bool
    {
        $horas = $this->horasDeAntiguedad($cotizacion);

        return $horas === null || $horas > $this->vencimientoDias * 24;
    }

    /**
     * Devuelve el importe en las tres monedas, o null si no se puede convertir.
     *
     * @return array{ARS: float, USD: float, BRL: float}|null
     */
    public function convertir(float $monto, string $monedaOrigen, ?Cotizacion $cotizacion = null): ?array
    {
        $cotizacion ??= $this->vigente();

        if ($cotizacion === null || $this->estaVencida($cotizacion)) {
            return null;
        }

        $ars = (float) $cotizacion->ars_por_usd;
        $brl = (float) $cotizacion->brl_por_usd;

        if ($ars <= 0 || $brl <= 0) {
            return null;
        }

        $enUsd = match ($monedaOrigen) {
            'USD' => $monto,
            'ARS' => $monto / $ars,
            'BRL' => $monto / $brl,
            default => null,
        };

        if ($enUsd === null) {
            return null;
        }

        return [
            'ARS' => round($enUsd * $ars, 2),
            'USD' => round($enUsd, 2),
            'BRL' => round($enUsd * $brl, 2),
        ];
    }
}
