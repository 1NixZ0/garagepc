<?php

namespace App\Http\Requests;

use App\Models\Componente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Reglas de carga de un componente.
 *
 * Las validaciones de fecha y de precio no son adorno: un componente con
 * fecha futura rompe el cálculo de antigüedad del Motor de Análisis, y un
 * precio en cero invalida la estimación de reventa.
 */
class ComponenteRequest extends FormRequest
{
    /**
     * Una compra en dólares no necesita paridad: es 1 por definición.
     * Se completa acá para que la regla de abajo no tenga que contemplarlo.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('moneda_compra') === 'USD') {
            $this->merge(['cotizacion_compra' => 1]);
        }

        if ($this->input('catalogo_hardware_id') === '') {
            $this->merge(['catalogo_hardware_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'categoria' => ['required', Rule::in(array_keys(Componente::CATEGORIAS))],
            'modelo' => ['required', 'string', 'max:150'],

            // Nullable por el CU-08. Cuando viene, tiene que ser una entrada
            // del catálogo del mismo tipo: una GPU no puede quedar vinculada
            // a la ficha de un procesador.
            'catalogo_hardware_id' => [
                'nullable',
                'integer',
                Rule::exists('catalogo_hardware', 'id')
                    ->where(fn ($consulta) => $consulta->where('tipo', $this->input('categoria'))),
            ],

            'fecha_compra' => ['required', 'date', 'before_or_equal:today'],
            'precio_compra' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'moneda_compra' => ['required', Rule::in(array_keys(Componente::MONEDAS))],
            'cotizacion_compra' => ['required', 'numeric', 'min:0.000001'],
            'estado' => ['required', Rule::in(array_keys(Componente::ESTADOS))],
            'condicion_declarada' => ['nullable', Rule::in(array_keys(Componente::CONDICIONES))],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'modelo.required' => 'Escribí el modelo exacto, así después se puede cruzar con el catálogo.',
            'catalogo_hardware_id.exists' => 'Ese modelo del catálogo no corresponde a la categoría elegida.',
            'fecha_compra.before_or_equal' => 'La fecha de compra no puede estar en el futuro.',
            'precio_compra.min' => 'El precio tiene que ser mayor a cero.',
            'cotizacion_compra.required' => 'Necesitamos la cotización del dólar a esa fecha para congelar el costo.',
            'cotizacion_compra.min' => 'La cotización tiene que ser mayor a cero.',
        ];
    }
}
