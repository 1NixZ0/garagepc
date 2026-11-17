<?php

namespace App\Services\Analisis;

use App\Models\Equipo;

/**
 * Aviso de posible desbalance entre CPU y GPU (RF-ANA-04 y RF-ANA-05).
 *
 * El aviso señala cuál de las dos piezas pertenece a la categoría inferior y
 * muestra la categoría de ambas. No afirma que exista una limitación efectiva
 * del rendimiento, porque eso depende de la resolución, del título y del nivel
 * de calidad empleados (RF-ANA-12).
 *
 * NOTA SOBRE EL ESTADO DE ESTA CLASE
 * Es la primera regla implementada del motor y el apartado 8.1.4 del informe
 * la analiza como deuda técnica. Tres cosas que hoy no fallan pero escalan mal:
 *
 *   1. evaluar() devuelve un arreglo con claves propias, o null. Cada regla
 *      inventa su formato y el EvaluadorAlertas tiene que conocerlos todos.
 *   2. El umbral tiene un valor por defecto escrito en el constructor, cuando
 *      RNF-MNT-03 exige que viva en la configuración.
 *   3. No deja registro de lo que decide, lo que vuelve lento diagnosticar
 *      una alerta equivocada.
 *
 * La acción preventiva planificada es extraer una interfaz ReglaAnalisis con
 * un ResultadoRegla de sólo lectura, quitar el valor por defecto y agregar un
 * canal de log propio. Se hace después de escribir la prueba de
 * caracterización, no antes.
 */
class DetectorDesbalance
{
    private int $umbral;

    public function __construct(int $umbral = 2)
    {
        $this->umbral = $umbral;
    }

    /**
     * @return array{inferior: string, nivel_cpu: int, nivel_gpu: int, diferencia: int}|null
     */
    public function evaluar(Equipo $equipo): ?array
    {
        $cpu = $equipo->cpu();
        $gpu = $equipo->gpu();

        // RF-ANA-11: sin CPU y GPU registradas, o sin vínculo con el catálogo,
        // no hay con qué comparar. El motivo lo registra el evaluador.
        if ($cpu === null || $gpu === null) {
            return null;
        }

        $cpu->loadMissing('catalogo');
        $gpu->loadMissing('catalogo');

        if ($cpu->catalogo === null || $gpu->catalogo === null) {
            return null;
        }

        $nivelCpu = (int) $cpu->catalogo->categoria_rendimiento;
        $nivelGpu = (int) $gpu->catalogo->categoria_rendimiento;
        $diferencia = abs($nivelCpu - $nivelGpu);

        if ($diferencia < $this->umbral) {
            return null;
        }

        return [
            'inferior' => $nivelCpu < $nivelGpu ? 'cpu' : 'gpu',
            'nivel_cpu' => $nivelCpu,
            'nivel_gpu' => $nivelGpu,
            'diferencia' => $diferencia,
        ];
    }
}
