<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComponenteRequest;
use App\Models\Componente;
use App\Models\Equipo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Componentes dentro de un equipo (CU-06 a CU-08).
 *
 * Las rutas están anidadas bajo el equipo y declaradas como scoped, así que
 * Laravel ya garantiza que el componente pertenece al equipo de la URL.
 * Lo que falta verificar es que el equipo sea del usuario.
 */
class ComponenteController extends Controller
{
    public function create(Request $request, Equipo $equipo): View
    {
        $this->verificarPropiedad($request, $equipo);

        return view('componentes.create', compact('equipo'));
    }

    public function store(ComponenteRequest $request, Equipo $equipo): RedirectResponse
    {
        $this->verificarPropiedad($request, $equipo);

        $equipo->componentes()->create($request->validated());

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('estado', 'Componente agregado.');
    }

    public function edit(Request $request, Equipo $equipo, Componente $componente): View
    {
        $this->verificarPropiedad($request, $equipo);

        return view('componentes.edit', compact('equipo', 'componente'));
    }

    public function update(ComponenteRequest $request, Equipo $equipo, Componente $componente): RedirectResponse
    {
        $this->verificarPropiedad($request, $equipo);

        $componente->update($request->validated());

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('estado', 'Componente actualizado.');
    }

    public function destroy(Request $request, Equipo $equipo, Componente $componente): RedirectResponse
    {
        $this->verificarPropiedad($request, $equipo);

        $componente->delete();

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('estado', 'Componente eliminado.');
    }

    private function verificarPropiedad(Request $request, Equipo $equipo): void
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);
    }
}
