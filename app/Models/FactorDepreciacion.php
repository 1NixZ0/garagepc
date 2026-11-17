<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Depreciación anual por categoría de componente, mantenida por el
 * Administrador. Es lo que permite estimar el valor de reventa, que es
 * un problema distinto de convertir monedas.
 */
class FactorDepreciacion extends Model
{
    use HasFactory;

    protected $table = 'factores_depreciacion';

    protected $fillable = ['categoria', 'porcentaje_anual'];

    protected function casts(): array
    {
        return ['porcentaje_anual' => 'decimal:2'];
    }
}
