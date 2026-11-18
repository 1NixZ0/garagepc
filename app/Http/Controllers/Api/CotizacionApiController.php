<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Componente;
use App\Services\Cotizador\Conversor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Conversión multimoneda en la aplicación móvil (RF-MOV-11).
 *
 * Siempre viaja la fecha y hora de la cotización empleada, y las dos señales
 * de antigüedad de RF-COT-11, para que la aplicación pueda advertirlo sin
 * tener que calcularlo por su cuenta.
 */
class CotizacionApiController extends Controller
{
    public function __construct(private readonly Conversor $conversor) {}

    public function show(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'monto' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['nullable', Rule::in(array_keys(Componente::MONEDAS))],
        ]);

        $cotizacion = $this->conversor->vigente();

        if ($cotizacion === null) {
            return response()->json(['mensaje' => 'Todavía no hay ninguna cotización disponible.'], 503);
        }

        $monto = isset($datos['monto']) ? (float) $datos['monto'] : null;
        $moneda = $datos['moneda'] ?? 'USD';

        return response()->json([
            'ars_por_usd' => (float) $cotizacion->ars_por_usd,
            'brl_por_usd' => (float) $cotizacion->brl_por_usd,
            'origen' => $cotizacion->origen,
            'obtenida_en' => $cotizacion->obtenida_en->toIso8601String(),
            'horas_de_antiguedad' => round($this->conversor->horasDeAntiguedad($cotizacion), 1),
            'requiere_advertencia' => $this->conversor->requiereAdvertencia($cotizacion),
            'vencida' => $this->conversor->estaVencida($cotizacion),
            'conversion' => $monto !== null
                ? $this->conversor->convertir($monto, $moneda, $cotizacion)
                : null,
        ]);
    }
}
