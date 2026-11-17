<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos de referencia del sistema. Los seeders son idempotentes: se
     * pueden volver a correr sin duplicar nada.
     */
    public function run(): void
    {
        $this->call([
            CatalogoHardwareSeeder::class,
            CatalogoJuegoSeeder::class,
            TablasDeReferenciaSeeder::class,
        ]);
    }
}
