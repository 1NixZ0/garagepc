<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipoRequest;
use App\Models\Componente;
use App\Models\Equipo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Garaje Virtual: alta, consulta, edición y baja de equipos (CU-03 a CU-05).
 */
class EquipoController extends Controller
{
    public function index(Request $request): View
    {
        $equipos = $request->user()
            ->equipos()
            ->withCount('componentes')
            ->orderBy('nombre')
            ->get();

        return view('equipos.index', compact('equipos'));
    }

    public function create(): View
    {
        return view('equipos.create');
    }

    public function store(EquipoRequest $request): RedirectResponse
    {
        $equipo = $request->user()->equipos()->create($request->validated());

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('estado', 'Equipo creado. Ahora cargale los componentes.');
    }

    public function show(Request $request, Equipo $equipo): View
    {
        $this->verificarPropiedad($request, $equipo);

        $componentes = $equipo->componentes()
            ->orderByRaw("FIELD(categoria, 'cpu','gpu','motherboard','ram','almacenamiento','fuente','refrigeracion','gabinete','otro')")
            ->get();

        $costoTotalUsd = $componentes
            ->where('estado', '!=', 'retirado')
            ->sum(fn (Componente $componente) => $componente->costoEnUsd());

        return view('equipos.show', compact('equipo', 'componentes', 'costoTotalUsd'));
    }

    public function edit(Request $request, Equipo $equipo): View
    {
        $this->verificarPropiedad($request, $equipo);

        return view('equipos.edit', compact('equipo'));
    }

    public function update(EquipoRequest $request, Equipo $equipo): RedirectResponse
    {
        $this->verificarPropiedad($request, $equipo);

        $equipo->update($request->validated());

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('estado', 'Equipo actualizado.');
    }

    public function destroy(Request $request, Equipo $equipo): RedirectResponse
    {
        $this->verificarPropiedad($request, $equipo);

        $equipo->delete();

        return redirect()
            ->route('equipos.index')
            ->with('estado', 'Equipo eliminado junto con sus componentes.');
    }

    /**
     * Un usuario solo puede tocar sus propios equipos. Se verifica en cada
     * acción y no una sola vez al entrar: la URL se puede escribir a mano.
     */
    private function verificarPropiedad(Request $request, Equipo $equipo): void
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);
    }
}
