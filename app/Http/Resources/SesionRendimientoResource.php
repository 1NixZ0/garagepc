<?php

namespace App\Http\Resources;

use App\Models\SesionRendimiento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SesionRendimiento
 */
class SesionRendimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'equipo_id' => $this->equipo_id,
            'juego' => $this->juego,
            'resolucion' => $this->resolucion,
            'nivel_calidad' => $this->nivel_calidad,
            'configuracion' => $this->etiquetaConfiguracion(),
            'fps_promedio' => (float) $this->fps_promedio,
            'temp_cpu' => $this->temp_cpu !== null ? (float) $this->temp_cpu : null,
            'temp_gpu' => $this->temp_gpu !== null ? (float) $this->temp_gpu : null,
            'fecha_sesion' => $this->fecha_sesion->toIso8601String(),
        ];
    }
}
