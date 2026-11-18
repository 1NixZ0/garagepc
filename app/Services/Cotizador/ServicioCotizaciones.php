<?php

namespace App\Services\Cotizador;

use App\Models\Cotizacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Obtención y persistencia de las paridades (RF-COT-02 y RF-COT-03).
 *
 * El dólar es la moneda pivote: se guarda cuántos pesos y cuántos reales
 * vale un dólar, y cualquier conversión entre las tres pasa por él. Con dos
 * números alcanza para las seis conversiones posibles.
 *
 * SOBRE DE DÓNDE SALE CADA NÚMERO
 * La paridad peso/dólar usa la serie configurada por el Administrador, que
 * por defecto es la del mercado paralelo: valuar hardware usado al tipo de
 * cambio oficial da un número que no se parece al mercado efectivo, y eso es
 * exactamente lo que RF-COT-05 busca evitar.
 *
 * La paridad real/dólar se deriva del listado de monedas de la misma API:
 * pesos por dólar dividido pesos por real. Para ese cruce se usan los dos
 * valores oficiales del mismo listado, y no el dólar de la serie elegida,
 * porque mezclar dos fuentes distintas produciría un cruce que no cierra.
 * Conviene saber que el resultado es la paridad implícita en el mercado
 * argentino, que no coincide exactamente con la paridad internacional.
 *
 * Ante una respuesta incompleta o inconsistente el servicio descarta la
 * respuesta entera y conserva la anterior, conforme al flujo alternativo A2
 * del CU-22: un dato viejo identificado como tal es mejor que uno dudoso.
 */
class ServicioCotizaciones
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $serie,
        private readonly int $timeout,
    ) {}

    /**
     * @return array{cotizacion: ?Cotizacion, motivo: ?string}
     */
    public function actualizar(): array
    {
        $anterior = Cotizacion::ultima();

        try {
            $ars = $this->paridadPesoDolar();
            $monedas = $this->listadoDeMonedas();
        } catch (Throwable $error) {
            Log::warning('Cotizador: el servicio externo no respondió.', [
                'serie' => $this->serie,
                'error' => $error->getMessage(),
            ]);

            return ['cotizacion' => null, 'motivo' => 'El servicio externo no respondió: '.$error->getMessage()];
        }

        if ($ars === null) {
            return ['cotizacion' => null, 'motivo' => 'La respuesta no traía una paridad peso/dólar utilizable.'];
        }

        $aviso = null;
        $brl = $this->paridadRealDolar($monedas);

        if ($brl === null) {
            // A2: antes que guardar medio dato, se conserva el real anterior.
            $brl = $anterior !== null ? (float) $anterior->brl_por_usd : null;
            $aviso = 'No se pudo derivar la paridad real/dólar del listado de monedas.';
        }

        if ($brl === null) {
            return [
                'cotizacion' => null,
                'motivo' => $aviso.' Y no hay ninguna cotización previa de la que tomarla, así que se descarta la respuesta entera.',
            ];
        }

        $cotizacion = Cotizacion::create([
            'ars_por_usd' => $ars,
            'brl_por_usd' => $brl,
            'origen' => 'DolarAPI ('.$this->serie.')',
            'obtenida_en' => Carbon::now(),
        ]);

        return ['cotizacion' => $cotizacion, 'motivo' => $aviso];
    }

    /** Pesos por dólar, según la serie configurada. */
    private function paridadPesoDolar(): ?float
    {
        $respuesta = Http::timeout($this->timeout)
            ->acceptJson()
            ->get($this->baseUrl.'/v1/dolares/'.$this->serie)
            ->throw()
            ->json();

        return is_array($respuesta) ? $this->valorDeVenta($respuesta) : null;
    }

    /** @return list<array<string, mixed>> */
    private function listadoDeMonedas(): array
    {
        $respuesta = Http::timeout($this->timeout)
            ->acceptJson()
            ->get($this->baseUrl.'/v1/cotizaciones')
            ->throw()
            ->json();

        return is_array($respuesta) ? $respuesta : [];
    }

    /**
     * Reales por dólar, derivados del listado: pesos por dólar sobre pesos
     * por real, tomando ambos del mismo origen para que el cruce cierre.
     *
     * @param  list<array<string, mixed>>  $monedas
     */
    private function paridadRealDolar(array $monedas): ?float
    {
        $pesosPorDolar = $this->valorDeVenta($this->buscarMoneda($monedas, 'USD'));
        $pesosPorReal = $this->valorDeVenta($this->buscarMoneda($monedas, 'BRL'));

        if ($pesosPorDolar === null || $pesosPorReal === null || $pesosPorReal <= 0) {
            return null;
        }

        return round($pesosPorDolar / $pesosPorReal, 6);
    }

    /**
     * @param  list<array<string, mixed>>  $monedas
     * @return array<string, mixed>|null
     */
    private function buscarMoneda(array $monedas, string $codigo): ?array
    {
        foreach ($monedas as $moneda) {
            if (is_array($moneda) && ($moneda['moneda'] ?? null) === $codigo) {
                return $moneda;
            }
        }

        return null;
    }

    /**
     * Se toma el valor de venta: es el que paga quien compra la divisa, y por
     * lo tanto el que refleja el costo real de reponer una pieza importada.
     *
     * @param  array<string, mixed>|null  $datos
     */
    private function valorDeVenta(?array $datos): ?float
    {
        if ($datos === null) {
            return null;
        }

        $valor = $datos['venta'] ?? $datos['compra'] ?? null;

        if (! is_numeric($valor) || (float) $valor <= 0) {
            return null;
        }

        return (float) $valor;
    }
}
