<?php

namespace App\Http\Controllers;

use App\Models\Componente;
use App\Services\Cotizador\Conversor;
use App\Services\Cotizador\EstimadorReventa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Cotizador Multimoneda (CU-22 y CU-23).
 */
class CotizadorController extends Controller
{
    public function __construct(
        private readonly Conversor $conversor,
        private readonly EstimadorReventa $estimador,
    ) {}

    public function __invoke(Request $request): View
    {
        // RF-COT-08: un importe no numérico o negativo no se convierte.
        $datos = $request->validate([
            'monto' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['nullable', Rule::in(array_keys(Componente::MONEDAS))],
        ], [
            'monto.numeric' => 'Escribí un importe numérico.',
            'monto.min' => 'El importe no puede ser negativo.',
        ]);

        $cotizacion = $this->conversor->vigente();
        $monto = isset($datos['monto']) ? (float) $datos['monto'] : null;
        $moneda = $datos['moneda'] ?? 'USD';

        $convertido = $monto !== null
            ? $this->conversor->convertir($monto, $moneda, $cotizacion)
            : null;

        // Valor estimado de reventa de cada componente en uso del usuario.
        $componentes = Componente::query()
            ->whereHas('equipo', fn ($consulta) => $consulta->where('user_id', $request->user()->id))
            ->where('estado', '!=', 'retirado')
            ->with('equipo')
            ->orderBy('categoria')
            ->get();

        $estimaciones = $componentes
            ->map(fn (Componente $componente) => [
                'componente' => $componente,
                'estimacion' => $this->estimador->estimar($componente),
            ])
            ->filter(fn (array $fila) => $fila['estimacion'] !== null)
            ->values();

        return view('cotizador.index', [
            'cotizacion' => $cotizacion,
            'horas' => $this->conversor->horasDeAntiguedad($cotizacion),
            'advertencia' => $this->conversor->requiereAdvertencia($cotizacion),
            'vencida' => $this->conversor->estaVencida($cotizacion),
            'monto' => $monto,
            'moneda' => $moneda,
            'convertido' => $convertido,
            'estimaciones' => $estimaciones,
            'serie' => config('cotizador.series_disponibles')[config('cotizador.serie')] ?? config('cotizador.serie'),
        ]);
    }
}
