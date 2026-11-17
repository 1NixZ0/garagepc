<?php

namespace Tests\Unit\Analisis;

use App\Repositories\RepositorioSesiones;
use App\Services\Analisis\ReglaDegradacion;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas de la condición de degradación sostenida (RF-REN-07 y RF-REN-08).
 *
 * El repositorio de sesiones está simulado, así que la regla se prueba con
 * series de números elegidas a mano y sin base de datos.
 */
class ReglaDegradacionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    #[Test]
    public function no_evalua_con_menos_de_ocho_sesiones(): void
    {
        $regla = $this->regla();

        $this->assertNull($regla->evaluarSerie([200, 198, 199, 150, 148, 147, 146]));
    }

    #[Test]
    public function avisa_cuando_las_tres_ultimas_caen_bajo_el_umbral(): void
    {
        $serie = [200, 201, 199, 200, 198, 150, 148, 147];

        $resultado = $this->regla()->evaluarSerie($serie);

        $this->assertNotNull($resultado);
        $this->assertSame([150.0, 148.0, 147.0], $resultado['ultimas']);
        $this->assertSame(8, $resultado['sesiones']);
    }

    #[Test]
    public function no_avisa_si_solo_dos_de_las_tres_ultimas_caen(): void
    {
        $serie = [200, 201, 199, 200, 198, 150, 148, 199];

        $this->assertNull($this->regla()->evaluarSerie($serie));
    }

    /**
     * El umbral es máx(promedio − desvío, promedio × 0,9). Con una serie plana
     * el desvío es casi cero, así que manda el 0,9 del promedio.
     */
    #[Test]
    public function con_desvio_chico_manda_el_diez_por_ciento_del_promedio(): void
    {
        $serie = [200, 200, 200, 200, 200, 200, 200, 200];

        $resultado = $this->regla()->evaluarSerie($serie);

        // Ninguna sesión cae: todas valen exactamente el promedio.
        $this->assertNull($resultado);

        $conCaida = [200, 200, 200, 200, 200, 179, 178, 177];
        $hallazgo = $this->regla()->evaluarSerie($conCaida);

        $this->assertNotNull($hallazgo);
        $this->assertGreaterThan(0, $hallazgo['umbral']);
    }

    private function regla(): ReglaDegradacion
    {
        return new ReglaDegradacion(
            Mockery::mock(RepositorioSesiones::class),
            sesionesMinimas: 8,
            tolerancia: 0.10,
        );
    }
}
