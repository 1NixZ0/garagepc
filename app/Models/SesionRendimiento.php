<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sesión de rendimiento (M-04).
 *
 * La terna juego + resolucion + nivel_calidad define la CONFIGURACIÓN.
 * Los promedios se calculan por configuración y nunca mezclando: sin esa
 * normalización, la regla de degradación dispararía una falsa alerta cada
 * vez que el usuario probara un juego nuevo o bajara la calidad.
 */
class SesionRendimiento extends Model
{
    use HasFactory;

    public const RESOLUCIONES = [
        '1080p' => '1080p',
        '1440p' => '1440p',
        '2160p' => '2160p (4K)',
    ];

    public const CALIDADES = [
        'bajo' => 'Bajo',
        'medio' => 'Medio',
        'alto' => 'Alto',
        'ultra' => 'Ultra',
    ];

    protected $table = 'sesiones_rendimiento';

    protected $fillable = [
        'equipo_id', 'catalogo_juego_id', 'juego', 'resolucion', 'nivel_calidad',
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

    /** Nullable: el usuario puede registrar un juego que no esté catalogado. */
    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(CatalogoJuego::class, 'catalogo_juego_id');
    }

    /** Acota la consulta a una configuración concreta. */
    public function scopeDeLaConfiguracion(Builder $query, string $juego, string $resolucion, string $calidad): Builder
    {
        return $query->where('juego', $juego)
                     ->where('resolucion', $resolucion)
                     ->where('nivel_calidad', $calidad);
    }

    /** Identificador textual de la configuración, para agrupar y comparar. */
    public function claveConfiguracion(): string
    {
        return $this->juego.'|'.$this->resolucion.'|'.$this->nivel_calidad;
    }

    public function etiquetaConfiguracion(): string
    {
        return sprintf(
            '%s · %s · %s',
            $this->juego,
            self::RESOLUCIONES[$this->resolucion] ?? $this->resolucion,
            self::CALIDADES[$this->nivel_calidad] ?? $this->nivel_calidad,
        );
    }
}
