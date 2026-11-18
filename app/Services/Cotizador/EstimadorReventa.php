<?php

namespace App\Services\Cotizador;

use App\Models\CoeficienteCondicion;
use App\Models\Componente;
use App\Models\FactorDepreciacion;

/**
 * Valor estimado de reventa de un componente (RF-COT-09 y RF-COT-10).
 *
 * El cálculo parte del costo en dólares a la fecha de compra, no del importe
 * en pesos: el hardware se mueve con el dólar, y depreciar un número en pesos
 * mezclaría el desgaste de la pieza con la inflación, que son dos cosas
 * distintas.
 *
 * Sobre ese valor se aplica la depreciación compuesta por año según la
 * categoría, y después el coeficiente de la condición declarada. El resultado
 * se devuelve con todo el desglose, porque RF-COT-10 exige que la estimación
 * se pueda auditar: precio de compra, antigüedad, factor y cotización.
 */
class EstimadorReventa
{
    public function __construct(private readonly Conversor $conversor) {}

    /**
     * @return array<string, mixed>|null
     */
    public function estimar(Componente $componente): ?array
    {
        $factor = FactorDepreciacion::where('categoria', $componente->categoria)->first();

        if ($factor === null) {
            return null;
        }

        $costoUsd = $componente->costoEnUsd();
        $anios = $componente->antiguedadEnMeses() / 12;
        $tasa = (float) $factor->porcentaje_anual / 100;

        $depreciado = $costoUsd * (1 - $tasa) ** $anios;

        $condicion = $componente->condicion_declarada
            ? CoeficienteCondicion::where('condicion', $componente->condicion_declarada)->first()
            : null;

        $coeficiente = $condicion ? (float) $condicion->coeficiente : 1.0;
        $estimadoUsd = $depreciado * $coeficiente;

        $cotizacion = $this->conversor->vigente();

        return [
            'costo_original_usd' => round($costoUsd, 2),
            'precio_compra' => (float) $componente->precio_compra,
            'moneda_compra' => $componente->moneda_compra,
            'cotizacion_compra' => (float) $componente->cotizacion_compra,
            'antiguedad_meses' => $componente->antiguedadEnMeses(),
            'depreciacion_anual' => (float) $factor->porcentaje_anual,
            'valor_depreciado_usd' => round($depreciado, 2),
            'condicion' => $componente->condicion_declarada,
            'coeficiente_condicion' => $coeficiente,
            'condicion_declarada' => $condicion !== null,
            'estimado_usd' => round($estimadoUsd, 2),
            'en_monedas' => $this->conversor->convertir($estimadoUsd, 'USD', $cotizacion),
            'cotizacion' => $cotizacion,
        ];
    }
}
