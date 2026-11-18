<?php

namespace App\Http\Resources;

use App\Models\Intervencion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Intervencion
 */
class IntervencionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'componente_id' => $this->componente_id,
            'tipo' => $this->tipo,
            'tipo_etiqueta' => $this->etiquetaTipo(),
            'fecha' => $this->fecha->toDateString(),
            'descripcion' => $this->descripcion,
            'reciente' => $this->esReciente(),
        ];
    }
}
