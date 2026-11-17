<?php

namespace App\Http\Controllers;

use App\Models\Intervencion;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Historial completo de mantenimiento del usuario, de todos sus equipos
 * y componentes a la vez (CU-11).
 */
class BitacoraController extends Controller
{
    public function __invoke(Request $request): View
    {
        $intervenciones = Intervencion::query()
            ->whereHas('componente.equipo', fn ($consulta) => $consulta->where('user_id', $request->user()->id))
            ->with('componente.equipo')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->simplePaginate(20);

        return view('bitacora.index', compact('intervenciones'));
    }
}
