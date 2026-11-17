<?php

namespace App\Services\Analisis;

use App\Repositories\RepositorioSesiones;

/**
 * Degradación sostenida del rendimiento (RF-REN-07 y RF-REN-08).
 *
 * El umbral de tolerancia es máx(promedio − desvío, promedio × (1 − tolerancia)).
 * Se toma el mayor de los dos a propósito: con un desvío chico manda el primero
 * y con un desvío grande manda el segundo, de modo que una serie muy dispersa
 * no vuelva imposible disparar la alerta.
 *
 * La condición se cumple cuando las TRES últimas sesiones de la configuración
 * quedan por debajo de ese umbral. Una sesión exactamente en el umbral no
 * cuenta como «debajo».
 */
class ReglaDegradacion
{
    private const SESIONES_CONSECUTIVAS = 3;

    public function __construct(
        private readonly RepositorioSesiones $sesiones,
        private readonly int $sesionesMinimas,
        private readonly float $tolerancia,
    ) {}

    /**
     * Evalúa todas las configuraciones del equipo.
     *
     * @return array{hallazgos: list<array<string, mixed>>, omitidas: list<array<string, mixed>>}
     */
    public function evaluar(int $equipoId): array
    {
        $hallazgos = [];
        $omitidas = [];

        foreach ($this->sesiones->configuraciones($equipoId) as $configuracion) {
            $fps = $this->sesiones->fpsDeLaConfiguracion(
                $equipoId,
                $configuracion['juego'],
                $configuracion['resolucion'],
                $configuracion['nivel_calidad'],
            );

            // RF-REN-08: sin base suficiente no se evalúa, y queda el motivo.
            if (count($fps) < $this->sesionesMinimas) {
                $omitidas[] = $configuracion + [
                    'motivo' => 'sesiones_insuficientes',
                    'sesiones' => count($fps),
                    'minimo' => $this->sesionesMinimas,
                ];

                continue;
            }

            $resultado = $this->evaluarSerie($fps);

            if ($resultado !== null) {
                $hallazgos[] = $configuracion + $resultado;
            }
        }

        return ['hallazgos' => $hallazgos, 'omitidas' => $omitidas];
    }

    /**
     * @param  list<float>  $fps  De la sesión más vieja a la más nueva.
     * @return array{promedio: float, desvio: float, umbral: float, ultimas: list<float>, sesiones: int}|null
     */
    public function evaluarSerie(array $fps): ?array
    {
        $cantidad = count($fps);

        if ($cantidad < $this->sesionesMinimas) {
            return null;
        }

        $promedio = array_sum($fps) / $cantidad;
        $desvio = $this->desvioEstandar($fps, $promedio);
        $umbral = max($promedio - $desvio, $promedio * (1 - $this->tolerancia));

        $ultimas = array_slice($fps, -self::SESIONES_CONSECUTIVAS);

        foreach ($ultimas as $valor) {
            if ($valor >= $umbral) {
                return null;
            }
        }

        return [
            'promedio' => round($promedio, 2),
            'desvio' => round($desvio, 2),
            'umbral' => round($umbral, 2),
            'ultimas' => array_map(static fn (float $v) => round($v, 2), $ultimas),
            'sesiones' => $cantidad,
        ];
    }

    /** Desvío estándar poblacional de la serie. */
    private function desvioEstandar(array $valores, float $promedio): float
    {
        $suma = 0.0;

        foreach ($valores as $valor) {
            $suma += ($valor - $promedio) ** 2;
        }

        return sqrt($suma / count($valores));
    }
}
