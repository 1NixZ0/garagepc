<?php

namespace Tests\Unit\Analisis;

use App\Models\CatalogoHardware;
use App\Models\Componente;
use App\Models\Equipo;
use App\Services\Analisis\DetectorDesbalance;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pruebas unitarias de la regla de posible desbalance (RF-ANA-04 y RF-ANA-05).
 *
 * No tocan la base de datos: el equipo es un doble que devuelve componentes
 * armados en memoria, con su ficha del catálogo ya vinculada. Eso permite
 * probar la regla con cualquier combinación de niveles sin cargar datos.
 */
class DetectorDesbalanceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    #[Test]
    public function avisa_cuando_la_diferencia_alcanza_el_umbral(): void
    {
        $equipo = $this->equipoCon(nivelCpu: 5, nivelGpu: 3);

        $resultado = (new DetectorDesbalance(2))->evaluar($equipo);

        $this->assertNotNull($resultado);
        $this->assertSame(2, $resultado['diferencia']);
        $this->assertSame(5, $resultado['nivel_cpu']);
        $this->assertSame(3, $resultado['nivel_gpu']);
    }

    #[Test]
    public function no_avisa_cuando_la_diferencia_es_de_un_solo_nivel(): void
    {
        $equipo = $this->equipoCon(nivelCpu: 4, nivelGpu: 3);

        $this->assertNull((new DetectorDesbalance(2))->evaluar($equipo));
    }

    #[Test]
    public function senala_a_la_cpu_cuando_es_la_pieza_inferior(): void
    {
        $equipo = $this->equipoCon(nivelCpu: 2, nivelGpu: 5);

        $resultado = (new DetectorDesbalance(2))->evaluar($equipo);

        $this->assertSame('cpu', $resultado['inferior']);
        $this->assertSame(3, $resultado['diferencia']);
    }

    #[Test]
    public function senala_a_la_gpu_cuando_es_la_pieza_inferior(): void
    {
        $equipo = $this->equipoCon(nivelCpu: 5, nivelGpu: 2);

        $resultado = (new DetectorDesbalance(2))->evaluar($equipo);

        $this->assertSame('gpu', $resultado['inferior']);
        $this->assertSame(3, $resultado['diferencia']);
    }

    /**
     * RF-ANA-06: el mismo equipo tiene que dar resultados distintos según el
     * umbral configurado. CPU 3 y GPU 5 avisa con umbral 2 y no con umbral 3.
     */
    #[Test]
    public function el_umbral_configurado_cambia_el_resultado(): void
    {
        $this->assertNotNull((new DetectorDesbalance(2))->evaluar($this->equipoCon(3, 5)));
        $this->assertNull((new DetectorDesbalance(3))->evaluar($this->equipoCon(3, 5)));
    }

    /** Equipo simulado con una CPU y una GPU de los niveles indicados. */
    private function equipoCon(int $nivelCpu, int $nivelGpu): Equipo
    {
        $equipo = Mockery::mock(Equipo::class)->makePartial();
        $equipo->shouldReceive('cpu')->andReturn($this->componente('cpu', $nivelCpu));
        $equipo->shouldReceive('gpu')->andReturn($this->componente('gpu', $nivelGpu));

        return $equipo;
    }

    private function componente(string $categoria, int $nivel): Componente
    {
        $ficha = new CatalogoHardware([
            'tipo' => $categoria,
            'modelo' => strtoupper($categoria).' de prueba',
            'categoria_rendimiento' => $nivel,
            'anio_lanzamiento' => 2022,
        ]);

        $componente = new Componente([
            'categoria' => $categoria,
            'modelo' => strtoupper($categoria).' de prueba',
            'estado' => 'activo',
        ]);

        $componente->setRelation('catalogo', $ficha);

        return $componente;
    }
}
