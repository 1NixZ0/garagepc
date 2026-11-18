<?php

namespace App\Console\Commands;

use App\Services\Cotizador\ServicioCotizaciones;
use Illuminate\Console\Command;

/**
 * Consulta al servicio externo de tipos de cambio (RF-COT-02).
 *
 * Corre cada hora. Si el servicio no responde, el sistema sigue funcionando
 * con la última cotización persistida, señalando su fecha (RF-COT-03).
 */
class ActualizarCotizaciones extends Command
{
    protected $signature = 'cotizaciones:actualizar';

    protected $description = 'Consulta el servicio externo de tipos de cambio y persiste la paridad obtenida';

    public function handle(ServicioCotizaciones $servicio): int
    {
        $resultado = $servicio->actualizar();

        if ($resultado['motivo'] !== null) {
            $this->warn($resultado['motivo']);
        }

        if ($resultado['cotizacion'] === null) {
            $this->error('No se persistió ninguna cotización. Se conserva la anterior.');

            return self::FAILURE;
        }

        $cotizacion = $resultado['cotizacion'];

        $this->info(sprintf(
            'Cotización guardada desde %s: 1 USD = %s ARS = %s BRL.',
            $cotizacion->origen,
            number_format((float) $cotizacion->ars_por_usd, 2, ',', '.'),
            number_format((float) $cotizacion->brl_por_usd, 4, ',', '.'),
        ));

        return self::SUCCESS;
    }
}
