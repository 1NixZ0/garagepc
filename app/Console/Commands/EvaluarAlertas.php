<?php

namespace App\Console\Commands;

use App\Models\Equipo;
use App\Services\Analisis\EvaluadorAlertas;
use Illuminate\Console\Command;

/**
 * Corrida del Motor de Análisis Proactivo (RF-ANA-01).
 *
 * La evaluación ocurre acá y no al abrir el Dashboard: el Dashboard LEE las
 * alertas ya calculadas (RF-DSH-09), que es lo que permite cumplir el tope de
 * 500 milisegundos de RNF-REN-04.
 */
class EvaluarAlertas extends Command
{
    protected $signature = 'analisis:evaluar {--equipo= : Evaluar un solo equipo por su identificador}';

    protected $description = 'Evalúa las condiciones de alerta de los equipos y actualiza el tablero';

    public function handle(EvaluadorAlertas $evaluador): int
    {
        $consulta = Equipo::query()->orderBy('id');

        if ($this->option('equipo')) {
            $consulta->whereKey($this->option('equipo'));
        }

        $equipos = $consulta->get();

        if ($equipos->isEmpty()) {
            $this->warn('No hay equipos para evaluar.');

            return self::SUCCESS;
        }

        $generadas = 0;
        $resueltas = 0;
        $comenzo = microtime(true);

        foreach ($equipos as $equipo) {
            $resumen = $evaluador->evaluarEquipo($equipo);

            $generadas += $resumen['generadas'];
            $resueltas += $resumen['resueltas'];

            $this->line(sprintf(
                '  %-28s %2d vigentes, %2d resueltas, %2d omisiones',
                \Illuminate\Support\Str::limit($equipo->nombre, 26),
                $resumen['generadas'],
                $resumen['resueltas'],
                count($resumen['omitidos']),
            ));

            foreach ($resumen['omitidos'] as $omitido) {
                $this->line('      <fg=gray>omitido: '.json_encode($omitido, JSON_UNESCAPED_UNICODE).'</>');
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%d %s evaluados en %d ms. %d alertas vigentes, %d resueltas.',
            $equipos->count(),
            $equipos->count() === 1 ? 'equipo' : 'equipos',
            (int) ((microtime(true) - $comenzo) * 1000),
            $generadas,
            $resueltas,
        ));

        return self::SUCCESS;
    }
}
