<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tablero principal. Por ahora resume el Garaje Virtual del usuario;
 * los indicadores de alertas y costos se suman en fases posteriores.
 */
class PanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $equipos = $request->user()
            ->equipos()
            ->withCount('componentes')
            ->orderBy('nombre')
            ->get();

        return view('panel', compact('equipos'));
    }
}
