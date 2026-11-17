<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Componente;
use App\Models\Intervencion;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tablero principal: la vista de conjunto del garaje (M-09).
 *
 * LEE las alertas que dejó la tarea programada; no dispara la evaluación
 * (RF-DSH-09). Esa separación es lo que permite cumplir el tope de 500
 * milisegundos de RNF-REN-04.
 */
class PanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user();

        $equipos = $usuario->equipos()->orderBy('nombre')->get();

        $componentes = Componente::query()
            ->whereIn('equipo_id', $equipos->pluck('id'))
            ->with('equipo')
            ->withCount('intervenciones')
            ->get();

        $enUso = $componentes->where('estado', '!=', 'retirado');

        $alertas = Alerta::query()
            ->activas()
            ->whereIn('componente_id', $componentes->pluck('id'))
            ->with('componente.equipo')
            ->orderByRaw("FIELD(tipo, 'posible_desbalance', 'actualizacion', 'mantenimiento_preventivo')")
            ->orderByDesc('evaluada_en')
            ->get();

        $ultimasIntervenciones = Intervencion::query()
            ->whereIn('componente_id', $componentes->pluck('id'))
            ->with('componente')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('panel', [
            'equipos' => $equipos,
            'cantidadEquipos' => $equipos->count(),
            'cantidadComponentes' => $enUso->count(),
            'costoTotalUsd' => $enUso->sum(fn (Componente $componente) => $componente->costoEnUsd()),
            'totalIntervenciones' => $componentes->sum('intervenciones_count'),
            'alertas' => $alertas->take(5),
            'cantidadAlertas' => $alertas->count(),
            'ultimasIntervenciones' => $ultimasIntervenciones,
        ]);
    }
}
