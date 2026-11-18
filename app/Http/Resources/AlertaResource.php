<?php

namespace App\Http\Resources;

use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Alerta
 *
 * RF-MOV-10: la aplicación móvil lista las alertas con su fundamento, en los
 * mismos términos que la web. Por eso se envía la explicación ya redactada y
 * no sólo el JSON crudo: el cliente no tiene por qué saber interpretarlo.
 */
class AlertaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo(),
            'explicacion' => $this->explicacion(),
            'advertencia' => $this->advertencia(),
            'estado' => $this->estado,
            'evaluada_en' => $this->evaluada_en->toIso8601String(),
            'componente' => $this->whenLoaded('componente', fn () => [
                'id' => $this->componente->id,
                'modelo' => $this->componente->modelo,
                'categoria' => $this->componente->categoria,
                'equipo' => $this->componente->relationLoaded('equipo') ? $this->componente->equipo->nombre : null,
            ]),
        ];
    }
}
