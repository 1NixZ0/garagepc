<?php

namespace App\Repositories;

use App\Models\SesionRendimiento;

class EloquentRepositorioSesiones implements RepositorioSesiones
{
    public function configuraciones(int $equipoId): array
    {
        return SesionRendimiento::query()
            ->where('equipo_id', $equipoId)
            ->select('juego', 'resolucion', 'nivel_calidad')
            ->distinct()
            ->get()
            ->map(fn (SesionRendimiento $sesion) => [
                'juego' => $sesion->juego,
                'resolucion' => $sesion->resolucion,
                'nivel_calidad' => $sesion->nivel_calidad,
            ])
            ->all();
    }

    public function fpsDeLaConfiguracion(int $equipoId, string $juego, string $resolucion, string $nivelCalidad): array
    {
        return SesionRendimiento::query()
            ->where('equipo_id', $equipoId)
            ->deLaConfiguracion($juego, $resolucion, $nivelCalidad)
            ->orderBy('fecha_sesion')
            ->orderBy('id')
            ->pluck('fps_promedio')
            ->map(fn ($fps) => (float) $fps)
            ->all();
    }
}
