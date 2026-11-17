<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EquipoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Ponele un nombre al equipo para poder distinguirlo.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
        ];
    }
}
