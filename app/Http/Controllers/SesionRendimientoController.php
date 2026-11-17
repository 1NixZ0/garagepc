<?php

namespace App\Http\Controllers;

use App\Http\Requests\SesionRendimientoRequest;
use App\Models\Equipo;
use App\Models\SesionRendimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Alta y baja de sesiones de rendimiento (CU-12 y CU-13).
 */
class SesionRendimientoController extends Controller
{
    public function store(SesionRendimientoRequest $request, Equipo $equipo): RedirectResponse
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);

        $sesion = $equipo->sesionesRendimiento()->create($request->validated());

        return redirect()
            ->route('equipos.rendimiento', ['equipo' => $equipo, 'config' => $sesion->claveConfiguracion()])
            ->with('estado', 'Sesión registrada.');
    }

    public function destroy(Request $request, Equipo $equipo, SesionRendimiento $sesion): RedirectResponse
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);

        // Mismo caso que en la bitacora: "sesion" no pluraliza al nombre real
        // de la relacion, asi que el vinculo se comprueba a mano.
        abort_unless($sesion->equipo_id === $equipo->id, 404);

        $clave = $sesion->claveConfiguracion();
        $sesion->delete();

        return redirect()
            ->route('equipos.rendimiento', ['equipo' => $equipo, 'config' => $clave])
            ->with('estado', 'Sesión eliminada.');
    }
}
