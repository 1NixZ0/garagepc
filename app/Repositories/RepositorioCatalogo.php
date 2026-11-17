<?php

namespace App\Repositories;

/**
 * Consultas contra el Catálogo de Hardware que necesita el motor.
 */
interface RepositorioCatalogo
{
    /**
     * ¿Existe en el catálogo una generación posterior del mismo tipo?
     *
     * Es la segunda mitad de RF-ANA-02: la antigüedad sola no justifica
     * sugerir un cambio si no hay nada más nuevo a lo que pasarse.
     */
    public function existeGeneracionPosterior(string $tipo, int $anioLanzamiento): bool;
}
