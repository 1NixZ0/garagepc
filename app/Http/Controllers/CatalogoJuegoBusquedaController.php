<?php

namespace App\Http\Controllers;

use App\Models\CatalogoJuego;
use App\Support\Busqueda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Búsqueda en el Catálogo de Juegos, para el autocompletado del formulario
 * de sesiones de rendimiento.
 */
class CatalogoJuegoBusquedaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $consulta = CatalogoJuego::query();

        foreach (Busqueda::terminos($datos['q']) as $termino) {
            $consulta->where('nombre', 'like', '%'.$termino.'%');
        }

        $resultados = $consulta
            ->orderByDesc('anio_lanzamiento')
            ->orderBy('nombre')
            ->limit(10)
            ->get()
            ->map(fn (CatalogoJuego $juego) => [
                'id' => $juego->id,
                'texto' => $juego->nombre,
                'detalle' => $juego->anio_lanzamiento.' · '.$juego->etiquetaExigencia(),
            ]);

        return response()->json($resultados);
    }
}
