<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\SesionRendimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Seguimiento de Rendimiento (CU-12 a CU-14).
 *
 * Todo lo que se muestra está acotado a una configuración: comparar los FPS
 * de un juego en 1080p con los del mismo juego en 4K no mide degradación,
 * mide otra cosa.
 */
class RendimientoController extends Controller
{
    /** Cuántas sesiones entran en el gráfico. */
    private const SESIONES_EN_GRAFICO = 12;

    public function index(Request $request): View|RedirectResponse
    {
        $equipos = $request->user()->equipos()->withCount('sesionesRendimiento')->orderBy('nombre')->get();

        if ($equipos->count() === 1) {
            return redirect()->route('equipos.rendimiento', $equipos->first());
        }

        return view('rendimiento.index', compact('equipos'));
    }

    public function show(Request $request, Equipo $equipo): View
    {
        abort_unless($equipo->user_id === $request->user()->id, 403);

        $sesiones = $equipo->sesionesRendimiento()->orderByDesc('fecha_sesion')->get();

        // Las configuraciones existentes, con la más usada arriba.
        $configuraciones = $sesiones
            ->groupBy(fn (SesionRendimiento $sesion) => $sesion->claveConfiguracion())
            ->map(fn (Collection $grupo) => [
                'clave' => $grupo->first()->claveConfiguracion(),
                'etiqueta' => $grupo->first()->etiquetaConfiguracion(),
                'juego' => $grupo->first()->juego,
                'resolucion' => $grupo->first()->resolucion,
                'calidad' => $grupo->first()->nivel_calidad,
                'cantidad' => $grupo->count(),
            ])
            ->sortByDesc('cantidad')
            ->values();

        $elegida = $request->query('config') ?: $configuraciones->first()['clave'] ?? null;

        $delGrupo = $elegida
            ? $sesiones->filter(fn (SesionRendimiento $s) => $s->claveConfiguracion() === $elegida)->values()
            : collect();

        return view('rendimiento.show', [
            'equipo' => $equipo,
            'configuraciones' => $configuraciones,
            'elegida' => $elegida,
            'sesiones' => $delGrupo,
            'grafico' => $this->construirGrafico($delGrupo->take(self::SESIONES_EN_GRAFICO)->reverse()->values()),
        ]);
    }

    /**
     * Arma las coordenadas del gráfico en el servidor.
     *
     * No se usa ninguna librería de gráficos: el resultado es un SVG, que el
     * navegador dibuja sin descargar nada. La escala vertical no arranca en
     * cero a propósito, porque lo que interesa ver es la variación entre
     * sesiones y no la distancia al origen.
     */
    private function construirGrafico(Collection $sesiones): ?array
    {
        if ($sesiones->count() < 2) {
            return null;
        }

        $valores = $sesiones->map(fn (SesionRendimiento $s) => (float) $s->fps_promedio);
        $promedio = $valores->avg();

        $piso = min($valores->min(), $promedio);
        $techo = max($valores->max(), $promedio);
        $margen = max(($techo - $piso) * 0.15, 1);
        $piso -= $margen;
        $techo += $margen;
        $rango = $techo - $piso;

        $ancho = 640;
        $alto = 220;
        $izq = 46;
        $der = 14;
        $arr = 14;
        $aba = 30;

        $escalaY = fn (float $valor) => $arr + ($alto - $arr - $aba) * (1 - ($valor - $piso) / $rango);
        $paso = ($ancho - $izq - $der) / max($sesiones->count() - 1, 1);

        $puntos = $sesiones->map(function (SesionRendimiento $sesion, int $indice) use ($izq, $paso, $escalaY, $promedio) {
            $fps = (float) $sesion->fps_promedio;

            return [
                'x' => round($izq + $paso * $indice, 2),
                'y' => round($escalaY($fps), 2),
                'fps' => $fps,
                'fecha' => $sesion->fecha_sesion->format('d/m'),
                'bajoPromedio' => $fps < $promedio,
            ];
        });

        return [
            'ancho' => $ancho,
            'alto' => $alto,
            'izquierda' => $izq,
            'derecha' => $ancho - $der,
            'puntos' => $puntos,
            'linea' => $puntos->map(fn ($p) => $p['x'].','.$p['y'])->implode(' '),
            'promedio' => round($promedio, 2),
            'promedioY' => round($escalaY($promedio), 2),
            'techo' => round($techo),
            'piso' => round($piso),
            'techoY' => $arr,
            'pisoY' => $alto - $aba,
        ];
    }
}
