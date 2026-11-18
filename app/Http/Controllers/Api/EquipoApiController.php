<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComponenteResource;
use App\Http\Resources\EquipoResource;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Garaje del usuario en la aplicación móvil (RF-MOV-02).
 */
class EquipoApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $equipos = $request->user()
            ->equipos()
            ->withCount('componentes')
            ->orderBy('nombre')
            ->get();

        return EquipoResource::collection($equipos);
    }

    public function componentes(Request $request, Equipo $equipo): AnonymousResourceCollection
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);

        $componentes = $equipo->componentes()
            ->where('estado', '!=', 'retirado')
            ->with('catalogo')
            ->orderBy('categoria')
            ->get();

        return ComponenteResource::collection($componentes);
    }
}
