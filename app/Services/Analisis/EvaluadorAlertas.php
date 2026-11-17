<?php

namespace App\Services\Analisis;

use App\Models\Alerta;
use App\Models\Componente;
use App\Models\Equipo;
use Illuminate\Support\Carbon;

/**
 * Orquestador del Motor de Análisis Proactivo (M-06).
 *
 * Corre las cuatro reglas sobre un equipo, persiste las alertas con su
 * fundamento (RF-ANA-08) y retira las que dejaron de verificarse (RF-ANA-10).
 * Una alerta ya vista conserva ese estado mientras su condición siga vigente,
 * de modo que no vuelva a destacarse sola (RF-ANA-09).
 *
 * NOTA SOBRE EL ESTADO DE ESTA CLASE
 * Esta clase conoce el formato de salida de cada regla, porque cada una
 * devuelve un arreglo con claves propias. Es el acoplamiento que el apartado
 * 8.1.4 del informe señala como deuda: agregar una quinta regla obliga a
 * modificar este archivo. La acción preventiva planificada es que todas las
 * reglas devuelvan un ResultadoRegla común y que este evaluador recorra una
 * lista sin distinguir cuál es cuál.
 */
class EvaluadorAlertas
{
    public function __construct(
        private readonly DetectorDesbalance $desbalance,
        private readonly ReglaAntiguedad $antiguedad,
        private readonly ReglaDegradacion $degradacion,
        private readonly ReglaMantenimiento $mantenimiento,
        private readonly array $reglasActivas,
    ) {}

    /**
     * @return array{generadas: int, resueltas: int, omitidos: list<array<string, mixed>>}
     */
    public function evaluarEquipo(Equipo $equipo): array
    {
        $momento = Carbon::now();
        $vigentes = [];
        $omitidos = [];

        $componentes = $equipo->componentes()
            ->where('estado', '!=', 'retirado')
            ->with(['catalogo', 'intervenciones'])
            ->get();

        // ------------------------------------------------ RF-ANA-02 antigüedad
        if ($this->reglasActivas['antiguedad'] ?? true) {
            foreach ($componentes as $componente) {
                if ($componente->catalogo === null) {
                    $omitidos[] = [
                        'componente' => $componente->modelo,
                        'regla' => 'antiguedad',
                        'motivo' => 'sin_vinculo_al_catalogo',
                    ];

                    continue;
                }

                $resultado = $this->antiguedad->evaluar($componente);

                if ($resultado !== null) {
                    $vigentes[] = $this->persistir($componente, null, 'actualizacion', [
                        'regla' => 'antiguedad',
                        'meses_desde_la_compra' => $resultado['meses'],
                        'umbral_meses' => $resultado['umbral'],
                        'generacion_del_componente' => $resultado['anio_generacion'],
                    ], $momento);
                }
            }
        }

        // ---------------------------------------------- RF-ANA-03 degradación
        if ($this->reglasActivas['degradacion'] ?? true) {
            $pieza = $equipo->gpu() ?? $equipo->cpu();

            if ($pieza === null) {
                $omitidos[] = ['regla' => 'degradacion', 'motivo' => 'sin_cpu_ni_gpu_activas'];
            } else {
                $salida = $this->degradacion->evaluar($equipo->id);

                foreach ($salida['omitidas'] as $omitida) {
                    $omitidos[] = ['regla' => 'degradacion'] + $omitida;
                }

                foreach ($salida['hallazgos'] as $hallazgo) {
                    $vigentes[] = $this->persistir($pieza, null, 'actualizacion', [
                        'regla' => 'degradacion',
                        'juego' => $hallazgo['juego'],
                        'resolucion' => $hallazgo['resolucion'],
                        'nivel_calidad' => $hallazgo['nivel_calidad'],
                        'promedio_historico' => $hallazgo['promedio'],
                        'desvio' => $hallazgo['desvio'],
                        'umbral_de_tolerancia' => $hallazgo['umbral'],
                        'ultimas_tres_sesiones' => $hallazgo['ultimas'],
                        'sesiones_evaluadas' => $hallazgo['sesiones'],
                    ], $momento);
                }
            }
        }

        // ---------------------------------------------- RF-ANA-04 desbalance
        if ($this->reglasActivas['desbalance'] ?? true) {
            $cpu = $equipo->cpu();
            $gpu = $equipo->gpu();

            if ($cpu === null || $gpu === null) {
                $omitidos[] = ['regla' => 'desbalance', 'motivo' => 'sin_cpu_y_gpu_registradas'];
            } elseif ($cpu->catalogo === null || $gpu->catalogo === null) {
                $omitidos[] = ['regla' => 'desbalance', 'motivo' => 'cpu_o_gpu_sin_vinculo_al_catalogo'];
            } else {
                $resultado = $this->desbalance->evaluar($equipo);

                if ($resultado !== null) {
                    $inferior = $resultado['inferior'] === 'cpu' ? $cpu : $gpu;
                    $otro = $resultado['inferior'] === 'cpu' ? $gpu : $cpu;

                    $vigentes[] = $this->persistir($inferior, $otro, 'posible_desbalance', [
                        'regla' => 'desbalance',
                        'pieza_inferior' => $resultado['inferior'],
                        'nivel_cpu' => $resultado['nivel_cpu'],
                        'nivel_gpu' => $resultado['nivel_gpu'],
                        'diferencia' => $resultado['diferencia'],
                        'modelo_cpu' => $cpu->modelo,
                        'modelo_gpu' => $gpu->modelo,
                    ], $momento);
                }
            }
        }

        // -------------------------------------------- RF-ANA-07 mantenimiento
        if ($this->reglasActivas['mantenimiento'] ?? true) {
            foreach ($componentes as $componente) {
                $resultado = $this->mantenimiento->evaluar($componente);

                if ($resultado !== null) {
                    $vigentes[] = $this->persistir($componente, null, 'mantenimiento_preventivo', [
                        'regla' => 'mantenimiento',
                        'meses_sin_intervencion' => $resultado['meses_sin_intervencion'],
                        'umbral_meses' => $resultado['umbral'],
                        'intervenciones_registradas' => $resultado['intervenciones'],
                    ], $momento);
                }
            }
        }

        $resueltas = $this->retirarLasQueYaNoAplican($componentes->pluck('id')->all(), $vigentes);

        return [
            'generadas' => count($vigentes),
            'resueltas' => $resueltas,
            'omitidos' => $omitidos,
        ];
    }

    /**
     * Crea la alerta o actualiza su fundamento si ya existía.
     *
     * No se reabre una alerta ya vista: RF-ANA-09 pide no volver a destacarla
     * mientras su condición no cambie.
     */
    private function persistir(Componente $componente, ?Componente $secundario, string $tipo, array $fundamento, Carbon $momento): int
    {
        $alerta = Alerta::query()
            ->where('componente_id', $componente->id)
            ->where('tipo', $tipo)
            ->where('estado', '!=', 'resuelta')
            ->first();

        if ($alerta === null) {
            $alerta = new Alerta([
                'componente_id' => $componente->id,
                'tipo' => $tipo,
                'estado' => 'activa',
            ]);
        }

        $alerta->componente_id = $componente->id;
        $alerta->componente_secundario_id = $secundario?->id;
        $alerta->tipo = $tipo;
        $alerta->fundamento = $fundamento;
        $alerta->evaluada_en = $momento;
        $alerta->save();

        return $alerta->id;
    }

    /**
     * RF-ANA-10: toda alerta no revalidada en esta corrida deja de aplicar.
     *
     * @param  list<int>  $componenteIds
     * @param  list<int>  $vigentes
     */
    private function retirarLasQueYaNoAplican(array $componenteIds, array $vigentes): int
    {
        if ($componenteIds === []) {
            return 0;
        }

        return Alerta::query()
            ->whereIn('componente_id', $componenteIds)
            ->where('estado', '!=', 'resuelta')
            ->whereNotIn('id', $vigentes ?: [0])
            ->update(['estado' => 'resuelta']);
    }
}
