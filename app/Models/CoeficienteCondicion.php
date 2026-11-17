<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Coeficiente de ajuste según la condición declarada del componente,
 * aplicado sobre el precio ya depreciado.
 */
class CoeficienteCondicion extends Model
{
    use HasFactory;

    protected $table = 'coeficientes_condicion';

    protected $fillable = ['condicion', 'coeficiente'];

    protected function casts(): array
    {
        return ['coeficiente' => 'decimal:3'];
    }
}
