<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Datos de referencia del sistema. Los dos seeders son idempotentes:
     * se pueden volver a correr sin duplicar nada ni pisar ajustes hechos
     * desde el panel de administración, salvo los de los modelos que ya
     * figuran en la carga inicial.
     */
    public function run(): void
    {
        $this->call([
            CatalogoHardwareSeeder::class,
            TablasDeReferenciaSeeder::class,
        ]);
    }
}
