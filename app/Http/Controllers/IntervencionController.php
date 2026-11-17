<?php

namespace App\Http\Controllers;

use App\Http\Requests\IntervencionRequest;
use App\Models\Componente;
use App\Models\Intervencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Registro y baja de intervenciones (CU-09 y CU-10).
 */
class IntervencionController extends Controller
{
    public function store(IntervencionRequest $request, Componente $componente): RedirectResponse
    {
        $this->verificarPropiedad($request, $componente);

        $componente->intervenciones()->create($request->validated());

        return redirect()
            ->route('equipos.componentes.show', [$componente->equipo_id, $componente])
            ->with('estado', 'Intervención registrada.');
    }

    public function destroy(Request $request, Componente $componente, Intervencion $intervencion): RedirectResponse
    {
        $this->verificarPropiedad($request, $componente);

        // El vinculo entre la intervencion y el componente de la URL se
        // comprueba explicitamente: el scoped binding automatico de Laravel
        // buscaria una relacion llamada "intervencions", que no existe.
        abort_unless($intervencion->componente_id === $componente->id, 404);

        $intervencion->delete();

        return redirect()
            ->route('equipos.componentes.show', [$componente->equipo_id, $componente])
            ->with('estado', 'Intervención eliminada.');
    }

    /**
     * El componente no guarda el usuario: se llega a él por el equipo.
     */
    private function verificarPropiedad(Request $request, Componente $componente): void
    {
        abort_unless($componente->equipo()->first()?->user_id === $request->user()->id, 403);
    }
}
