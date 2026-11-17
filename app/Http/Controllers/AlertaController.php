<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consulta de alertas y marcado como vistas (CU-17 a CU-21).
 *
 * Esta pantalla LEE las alertas que dejó la tarea programada; no dispara la
 * evaluación. Es lo que permite cumplir el tope de RNF-REN-04.
 */
class AlertaController extends Controller
{
    public function index(Request $request): View
    {
        $alertas = Alerta::query()
            ->activas()
            ->whereHas('componente.equipo', fn ($consulta) => $consulta->where('user_id', $request->user()->id))
            ->with(['componente.equipo', 'componenteSecundario'])
            ->orderByRaw("FIELD(tipo, 'posible_desbalance', 'actualizacion', 'mantenimiento_preventivo')")
            ->orderByDesc('evaluada_en')
            ->get();

        return view('alertas.index', [
            'alertas' => $alertas,
            'sinVer' => $alertas->where('estado', 'activa')->count(),
        ]);
    }

    /** RF-ANA-09: queda vista, pero el registro histórico no se borra. */
    public function marcarVista(Request $request, Alerta $alerta): RedirectResponse
    {
        abort_unless(
            $alerta->componente->equipo->user_id === $request->user()->id,
            403,
        );

        $alerta->update(['estado' => 'vista', 'vista_en' => now()]);

        return back()->with('estado', 'Alerta marcada como vista.');
    }
}
