<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sesión de rendimiento.
 *
 * La terna juego + resolucion + nivel_calidad define la CONFIGURACIÓN.
 * Los promedios se calculan por configuración y nunca mezclando: sin esa
 * normalización, la regla de degradación dispararía una falsa alerta cada
 * vez que el usuario probara un juego nuevo.
 */
class SesionRendimiento extends Model
{
    use HasFactory;

    protected $table = 'sesiones_rendimiento';

    protected $fillable = [
        'equipo_id', 'juego', 'resolucion', 'nivel_calidad',
        'fps_promedio', 'temp_cpu', 'temp_gpu', 'fecha_sesion',
    ];

    protected function casts(): array
    {
        return [
            'fps_promedio' => 'decimal:2',
            'temp_cpu' => 'decimal:2',
            'temp_gpu' => 'decimal:2',
            'fecha_sesion' => 'datetime',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    /** Acota la consulta a una configuración concreta. */
    public function scopeDeLaConfiguracion(Builder $query, string $juego, string $resolucion, string $calidad): Builder
    {
        return $query->where('juego', $juego)
                     ->where('resolucion', $resolucion)
                     ->where('nivel_calidad', $calidad);
    }
}
