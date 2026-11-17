<?php

namespace App\Repositories;

/**
 * Acceso a las sesiones de rendimiento.
 *
 * Es una interfaz y no una consulta suelta dentro de la regla para poder
 * simularla en las pruebas unitarias: así la regla de degradación se prueba
 * con series de números inventadas, sin tocar la base de datos.
 */
interface RepositorioSesiones
{
    /**
     * Configuraciones distintas registradas por un equipo.
     *
     * @return list<array{juego: string, resolucion: string, nivel_calidad: string}>
     */
    public function configuraciones(int $equipoId): array;

    /**
     * FPS de una configuración, de la sesión más vieja a la más nueva.
     *
     * La consulta agrupa por juego, resolución Y nivel de calidad. Omitir el
     * nivel de calidad es la falla que el apartado 8.1.1 del informe usa como
     * ejemplo de mantenimiento correctivo.
     *
     * @return list<float>
     */
    public function fpsDeLaConfiguracion(int $equipoId, string $juego, string $resolucion, string $nivelCalidad): array;
}
