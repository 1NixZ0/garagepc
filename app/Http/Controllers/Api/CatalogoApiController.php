<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogoHardware;
use App\Models\CatalogoJuego;
use App\Models\Componente;
use App\Support\Busqueda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Búsquedas en los catálogos para los formularios de la aplicación móvil.
 */
class CatalogoApiController extends Controller
{
    public function hardware(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'categoria' => ['required', Rule::in(array_keys(Componente::CATEGORIAS))],
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $consulta = CatalogoHardware::query()->where('tipo', $datos['categoria']);

        foreach (Busqueda::terminos($datos['q']) as $termino) {
            $consulta->where('modelo', 'like', '%'.$termino.'%');
        }

        return response()->json(
            $consulta->orderByDesc('anio_lanzamiento')->limit(10)->get(['id', 'modelo', 'anio_lanzamiento', 'categoria_rendimiento'])
        );
    }

    public function juegos(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $consulta = CatalogoJuego::query();

        foreach (Busqueda::terminos($datos['q']) as $termino) {
            $consulta->where('nombre', 'like', '%'.$termino.'%');
        }

        return response()->json(
            $consulta->orderByDesc('anio_lanzamiento')->limit(10)->get(['id', 'nombre', 'anio_lanzamiento', 'exigencia'])
        );
    }
}
