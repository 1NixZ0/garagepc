<?php

namespace App\Repositories;

use App\Models\CatalogoHardware;

class EloquentRepositorioCatalogo implements RepositorioCatalogo
{
    public function existeGeneracionPosterior(string $tipo, int $anioLanzamiento): bool
    {
        return CatalogoHardware::query()
            ->where('tipo', $tipo)
            ->where('anio_lanzamiento', '>', $anioLanzamiento)
            ->exists();
    }
}
