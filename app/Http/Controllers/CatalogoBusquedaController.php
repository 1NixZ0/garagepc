<?php

namespace App\Http\Controllers;

use App\Models\CatalogoHardware;
use App\Models\Componente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Búsqueda de modelos en el Catálogo de Hardware, para el autocompletado
 * del formulario de componentes.
 *
 * Devuelve a lo sumo diez resultados: una lista larga no ayuda a elegir.
 * Si no hay coincidencias el usuario escribe libremente, que es el
 * camino previsto por el CU-08 para hardware no catalogado.
 */
class CatalogoBusquedaController extends Controller
{
    private const GAMAS = [
        1 => 'entrada',
        2 => 'gama baja',
        3 => 'gama media',
        4 => 'gama media-alta',
        5 => 'gama alta',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'categoria' => ['required', Rule::in(array_keys(Componente::CATEGORIAS))],
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $consulta = CatalogoHardware::query()->where('tipo', $datos['categoria']);

        // Cada palabra acota la búsqueda, así "ryzen 7 7800" encuentra el
        // modelo aunque el usuario escriba los terminos en otro orden.
        foreach (preg_split('/\s+/', trim($datos['q'])) as $termino) {
            $escapado = addcslashes($termino, '%_\\');
            $consulta->where('modelo', 'like', '%'.$escapado.'%');
        }

        $resultados = $consulta
            ->orderByDesc('anio_lanzamiento')
            ->orderBy('modelo')
            ->limit(10)
            ->get()
            ->map(fn (CatalogoHardware $hardware) => [
                'id' => $hardware->id,
                'modelo' => $hardware->modelo,
                'anio' => $hardware->anio_lanzamiento,
                'gama' => self::GAMAS[$hardware->categoria_rendimiento] ?? '',
            ]);

        return response()->json($resultados);
    }
}
