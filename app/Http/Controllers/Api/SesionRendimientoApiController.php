<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SesionRendimientoRequest;
use App\Http\Resources\SesionRendimientoResource;
use App\Models\Equipo;
use Illuminate\Http\JsonResponse;

/**
 * Registro de sesiones de rendimiento desde la aplicación móvil (RF-MOV-06).
 */
class SesionRendimientoApiController extends Controller
{
    public function store(SesionRendimientoRequest $request, Equipo $equipo): JsonResponse
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);

        $sesion = $equipo->sesionesRendimiento()->create($request->validated());

        return response()->json(new SesionRendimientoResource($sesion), 201);
    }
}
