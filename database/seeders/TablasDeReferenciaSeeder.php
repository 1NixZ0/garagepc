<?php

namespace Database\Seeders;

use App\Models\CoeficienteCondicion;
use App\Models\FactorDepreciacion;
use Illuminate\Database\Seeder;

/**
 * Valores iniciales de las dos tablas que alimentan al Cotizador
 * (RF-CAT-10 y RF-CAT-11). Los mantiene el Administrador; estos son
 * los puntos de partida y no verdades absolutas.
 *
 * Las placas de video y los procesadores se deprecian más rápido porque
 * cada generación nueva los corre de lugar. Un gabinete o una fuente
 * conservan su valor mucho mejor.
 */
class TablasDeReferenciaSeeder extends Seeder
{
    public function run(): void
    {
        $depreciacion = [
            'gpu' => 25.00,
            'cpu' => 20.00,
            'ram' => 15.00,
            'almacenamiento' => 18.00,
            'motherboard' => 18.00,
            'fuente' => 10.00,
            'refrigeracion' => 12.00,
            'gabinete' => 8.00,
            'otro' => 15.00,
        ];

        foreach ($depreciacion as $categoria => $porcentaje) {
            FactorDepreciacion::updateOrCreate(
                ['categoria' => $categoria],
                ['porcentaje_anual' => $porcentaje],
            );
        }

        $condiciones = [
            'excelente' => 1.000,
            'muy_bueno' => 0.900,
            'bueno' => 0.780,
            'regular' => 0.620,
        ];

        foreach ($condiciones as $condicion => $coeficiente) {
            CoeficienteCondicion::updateOrCreate(
                ['condicion' => $condicion],
                ['coeficiente' => $coeficiente],
            );
        }
    }
}
