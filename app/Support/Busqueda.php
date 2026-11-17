<?php

namespace App\Support;

/**
 * Divide un término de búsqueda en palabras sueltas, ya escapadas para
 * usarse dentro de un LIKE. Cada palabra acota el resultado, de modo que
 * "ryzen 7 7800" encuentra el modelo aunque se escriba desordenado.
 */
final class Busqueda
{
    /** @return list<string> */
    public static function terminos(string $consulta): array
    {
        $palabras = preg_split('/\s+/', trim($consulta)) ?: [];

        return array_map(
            static fn (string $palabra) => addcslashes($palabra, '%_\\'),
            array_filter($palabras, static fn (string $palabra) => $palabra !== ''),
        );
    }
}
