<?php

namespace App\Http\Requests;

use App\Models\Intervencion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Reglas de carga de una intervención.
 *
 * Una intervención no puede ser anterior a la compra del componente ni
 * posterior a hoy. La bitácora es un historial: si admite fechas imposibles
 * deja de servir como evidencia frente a un comprador, que es justamente
 * para lo que está pensada en el Marketplace.
 */
class IntervencionRequest extends FormRequest
{
    public function rules(): array
    {
        $componente = $this->route('componente');

        return [
            'tipo' => ['required', Rule::in(array_keys(Intervencion::TIPOS))],
            'fecha' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:'.$componente->fecha_compra->toDateString(),
            ],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        $componente = $this->route('componente');

        return [
            'fecha.before_or_equal' => 'La intervención no puede estar en el futuro.',
            'fecha.after_or_equal' => 'El componente se compró el '
                .$componente->fecha_compra->format('d/m/Y').'; no pudo intervenirse antes.',
        ];
    }
}
