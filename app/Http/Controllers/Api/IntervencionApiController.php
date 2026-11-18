<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntervencionRequest;
use App\Http\Resources\IntervencionResource;
use App\Models\Componente;
use Illuminate\Http\JsonResponse;

/**
 * Registro de intervenciones desde la aplicación móvil (RF-MOV-04).
 *
 * Reutiliza el mismo IntervencionRequest de la aplicación web: las reglas de
 * validación viven en un solo lugar y no se pueden desincronizar entre los
 * dos clientes.
 */
class IntervencionApiController extends Controller
{
    public function store(IntervencionRequest $request, Componente $componente): JsonResponse
    {
        abort_unless(
            $componente->equipo()->first()?->user_id === $request->user()->id,
            403,
        );

        $intervencion = $componente->intervenciones()->create($request->validated());

        return response()->json(new IntervencionResource($intervencion), 201);
    }
}
