<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComponenteResource;
use App\Models\Componente;
use App\Models\SesionRendimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ficha de un componente en la aplicación móvil (RF-MOV-03).
 *
 * Devuelve los datos técnicos, la última intervención registrada y el
 * promedio de rendimiento del equipo en su configuración más usada. El
 * promedio no se calcula mezclando configuraciones, por el mismo motivo
 * que en la web: no mediría lo mismo.
 */
class ComponenteApiController extends Controller
{
    public function show(Request $request, Componente $componente): JsonResponse
    {
        $equipo = $componente->equipo;

        abort_unless($equipo->user_id === $request->user()->id, 403);

        $componente->load(['catalogo', 'intervenciones']);

        $configuracion = SesionRendimiento::query()
            ->where('equipo_id', $equipo->id)
            ->selectRaw('juego, resolucion, nivel_calidad, COUNT(*) as cantidad, AVG(fps_promedio) as promedio')
            ->groupBy('juego', 'resolucion', 'nivel_calidad')
            ->orderByDesc('cantidad')
            ->first();

        return response()->json([
            'componente' => new ComponenteResource($componente),
            'rendimiento' => $configuracion === null ? null : [
                'juego' => $configuracion->juego,
                'resolucion' => $configuracion->resolucion,
                'nivel_calidad' => $configuracion->nivel_calidad,
                'sesiones' => (int) $configuracion->cantidad,
                'fps_promedio' => round((float) $configuracion->promedio, 2),
            ],
        ]);
    }
}
