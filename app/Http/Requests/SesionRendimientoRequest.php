<?php

namespace App\Http\Requests;

use App\Models\SesionRendimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Reglas de carga de una sesión de rendimiento.
 *
 * Las temperaturas tienen tope porque un valor absurdo contamina el
 * historial y, peor, puede disparar una alerta térmica falsa.
 */
class SesionRendimientoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('catalogo_juego_id') === '') {
            $this->merge(['catalogo_juego_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'juego' => ['required', 'string', 'max:120'],
            'catalogo_juego_id' => ['nullable', 'integer', Rule::exists('catalogo_juegos', 'id')],
            'resolucion' => ['required', Rule::in(array_keys(SesionRendimiento::RESOLUCIONES))],
            'nivel_calidad' => ['required', Rule::in(array_keys(SesionRendimiento::CALIDADES))],
            'fps_promedio' => ['required', 'numeric', 'min:1', 'max:9999.99'],
            'temp_cpu' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'temp_gpu' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'fecha_sesion' => ['required', 'date', 'before_or_equal:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'fps_promedio.min' => 'Los FPS tienen que ser mayores a cero.',
            'temp_cpu.max' => 'Esa temperatura no es posible; revisá el valor.',
            'temp_gpu.max' => 'Esa temperatura no es posible; revisá el valor.',
            'fecha_sesion.before_or_equal' => 'La sesión no puede ser en el futuro.',
        ];
    }
}
