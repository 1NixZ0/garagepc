<?php

namespace App\Http\Resources;

use App\Models\Componente;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Componente
 */
class ComponenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipo_id' => $this->equipo_id,
            'categoria' => $this->categoria,
            'categoria_etiqueta' => $this->etiquetaCategoria(),
            'modelo' => $this->modelo,
            'estado' => $this->estado,
            'estado_etiqueta' => $this->etiquetaEstado(),
            'fecha_compra' => $this->fecha_compra->toDateString(),
            'antiguedad_meses' => $this->antiguedadEnMeses(),
            'precio_compra' => (float) $this->precio_compra,
            'moneda_compra' => $this->moneda_compra,
            'costo_usd' => round($this->costoEnUsd(), 2),
            'condicion_declarada' => $this->condicion_declarada,
            'notas' => $this->notas,
            'catalogado' => $this->estaCatalogado(),
            'catalogo' => $this->whenLoaded('catalogo', fn () => $this->catalogo ? [
                'id' => $this->catalogo->id,
                'anio_lanzamiento' => $this->catalogo->anio_lanzamiento,
                'categoria_rendimiento' => $this->catalogo->categoria_rendimiento,
            ] : null),
            'intervenciones' => IntervencionResource::collection($this->whenLoaded('intervenciones')),
        ];
    }
}
