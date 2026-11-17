<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Cotización obtenida del servicio externo.
 *
 * Se persiste con su fecha y hora de obtención para poder advertir cuando
 * supera las 24 horas y abstenerse de convertir cuando supera los 7 días
 * (RF-COT-11). Una conversión vieja no es un dato aproximado: es un dato
 * equivocado.
 */
class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = ['ars_por_usd', 'brl_por_usd', 'origen', 'obtenida_en'];

    protected function casts(): array
    {
        return [
            'ars_por_usd' => 'decimal:6',
            'brl_por_usd' => 'decimal:6',
            'obtenida_en' => 'datetime',
        ];
    }

    public static function ultima(): ?self
    {
        return static::orderByDesc('obtenida_en')->first();
    }

    public function horasDeAntiguedad(): float
    {
        return $this->obtenida_en->diffInHours(Carbon::now(), absolute: true);
    }

    /** Pasadas las 24 horas, el sistema advierte que no es del día. */
    public function requiereAdvertencia(): bool
    {
        return $this->horasDeAntiguedad() > 24;
    }

    /** Pasados los 7 días, el sistema deja de mostrar valores convertidos. */
    public function estaVencida(): bool
    {
        return $this->horasDeAntiguedad() > 24 * 7;
    }
}
