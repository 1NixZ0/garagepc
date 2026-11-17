<?php

namespace App\Console\Commands;

use App\Models\CatalogoHardware;
use App\Models\CatalogoJuego;
use App\Models\Componente;
use App\Models\Equipo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Carga un equipo de demostración que dispara las cuatro reglas del motor.
 *
 * Existe para poder mostrar el sistema funcionando sin esperar tres años a
 * que un componente envejezca. No forma parte del funcionamiento normal:
 * crea un único equipo, claramente identificado, que se borra con --borrar.
 */
class CargarDemostracion extends Command
{
    protected $signature = 'garagepc:demo
                            {--usuario= : Correo del usuario dueño del equipo}
                            {--borrar : Elimina el equipo de demostración y sale}';

    protected $description = 'Carga un equipo de ejemplo con datos que disparan las cuatro reglas de análisis';

    private const NOMBRE = 'PC de demostración';

    public function handle(): int
    {
        $usuario = $this->option('usuario')
            ? User::where('email', $this->option('usuario'))->first()
            : User::orderBy('id')->first();

        if ($usuario === null) {
            $this->error('No encontré ese usuario. Registrate primero en el sitio.');

            return self::FAILURE;
        }

        // Idempotente: si ya existe, se rehace desde cero.
        $usuario->equipos()->where('nombre', self::NOMBRE)->each(fn (Equipo $viejo) => $viejo->delete());

        if ($this->option('borrar')) {
            $this->info('Equipo de demostración eliminado.');

            return self::SUCCESS;
        }

        $equipo = $usuario->equipos()->create([
            'nombre' => self::NOMBRE,
            'descripcion' => 'Datos de ejemplo para mostrar el Motor de Análisis Proactivo. Se puede borrar con php artisan garagepc:demo --borrar.',
        ]);

        $cpu = $this->componente($equipo, 'cpu', 'Intel Core i9-10900K', meses: 40, precio: 520000, cotizacion: 350);
        $gpu = $this->componente($equipo, 'gpu', 'NVIDIA GeForce RTX 3060', meses: 30, precio: 480000, cotizacion: 620);
        $ram = $this->componente($equipo, 'ram', 'Corsair Vengeance LPX 16GB DDR4-3200', meses: 44, precio: 95000, cotizacion: 300);
        $this->componente($equipo, 'almacenamiento', 'Samsung 970 EVO Plus 1TB NVMe', meses: 40, precio: 130000, cotizacion: 350);

        // Una intervención reciente en la RAM: así se ve el contraste con las
        // piezas que nunca se tocaron.
        $ram->intervenciones()->create([
            'tipo' => 'limpieza',
            'fecha' => Carbon::now()->subMonths(2),
            'descripcion' => 'Limpieza de contactos y reasiento en los zócalos.',
        ]);

        $cpu->intervenciones()->create([
            'tipo' => 'pasta_termica',
            'fecha' => Carbon::now()->subMonths(14),
            'descripcion' => 'Arctic MX-4 aplicada. La temperatura en reposo bajó 8 °C.',
        ]);

        $this->sesiones($equipo);

        $this->info('Equipo de demostración creado para '.$usuario->email.'.');
        $this->newLine();
        $this->line('  CPU gama 5 de 2020, comprada hace 40 meses y sin mantenimiento hace 14.');
        $this->line('  GPU gama 3: dos niveles por debajo de la CPU.');
        $this->line('  11 sesiones de Counter-Strike 2, con las tres últimas en caída.');
        $this->newLine();
        $this->comment('Ahora corré: php artisan analisis:evaluar');

        return self::SUCCESS;
    }

    private function componente(Equipo $equipo, string $categoria, string $modelo, int $meses, float $precio, float $cotizacion): Componente
    {
        $ficha = CatalogoHardware::where('tipo', $categoria)->where('modelo', $modelo)->first();

        if ($ficha === null) {
            $this->warn("No encontré «{$modelo}» en el catálogo. Corré php artisan db:seed primero.");
        }

        return $equipo->componentes()->create([
            'catalogo_hardware_id' => $ficha?->id,
            'categoria' => $categoria,
            'modelo' => $modelo,
            'fecha_compra' => Carbon::now()->subMonths($meses),
            'precio_compra' => $precio,
            'moneda_compra' => 'ARS',
            'cotizacion_compra' => $cotizacion,
            'estado' => 'activo',
            'condicion_declarada' => 'bueno',
        ]);
    }

    /**
     * Once sesiones de la misma configuración: ocho estables y tres en caída.
     *
     * Con esa serie el promedio queda cerca de 228 fps y el umbral de
     * tolerancia alrededor de 207, de modo que las tres últimas quedan por
     * debajo y se cumple la condición de RF-REN-07.
     */
    private function sesiones(Equipo $equipo): void
    {
        $juego = CatalogoJuego::where('nombre', 'Counter-Strike 2')->first();

        $fps = [242, 238, 245, 240, 243, 239, 241, 244, 196, 194, 192];
        $dias = count($fps);

        foreach ($fps as $indice => $valor) {
            $equipo->sesionesRendimiento()->create([
                'catalogo_juego_id' => $juego?->id,
                'juego' => 'Counter-Strike 2',
                'resolucion' => '1080p',
                'nivel_calidad' => 'alto',
                'fps_promedio' => $valor,
                'temp_cpu' => 62 + $indice,
                'temp_gpu' => 68 + $indice,
                'fecha_sesion' => Carbon::now()->subDays($dias - $indice)->setTime(21, 0),
            ]);
        }
    }
}
