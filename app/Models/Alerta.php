<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alerta generada por el Motor de Análisis Proactivo (M-06).
 *
 * componente_secundario_id solo se usa en el aviso de posible desbalance,
 * que involucra dos piezas. fundamento guarda el dato que originó la alerta:
 * ninguna se publica sin su fundamento verificable (RF-ANA-08).
 */
class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = [
        'componente_id', 'componente_secundario_id', 'tipo',
        'fundamento', 'estado', 'evaluada_en', 'vista_en',
    ];

    /**
     * La forma exacta del arreglo es lo que permite al análisis estático
     * reconocer los casts declarados por método, que es la forma que adopta
     * Laravel desde la versión 11. Sin ella, la herramienta se queda con el
     * tipo de la columna y trata fundamento como texto.
     *
     * @return array{fundamento: 'array', evaluada_en: 'datetime', vista_en: 'datetime'}
     */
    protected function casts(): array
    {
        return [
            'fundamento' => 'array',
            'evaluada_en' => 'datetime',
            'vista_en' => 'datetime',
        ];
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
    }

    /** Solo presente en los avisos de posible desbalance. */
    public function componenteSecundario(): BelongsTo
    {
        return $this->belongsTo(Componente::class, 'componente_secundario_id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', '!=', 'resuelta');
    }

    public function scopeDelTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function regla(): string
    {
        return $this->fundamento['regla'] ?? '';
    }

    public function titulo(): string
    {
        return match (true) {
            $this->tipo === 'posible_desbalance' => 'Posible desbalance',
            $this->tipo === 'mantenimiento_preventivo' => 'Mantenimiento preventivo',
            $this->regla() === 'degradacion' => 'Rendimiento degradado',
            default => 'Sugerencia de actualización',
        };
    }

    /**
     * El fundamento traducido a una frase. Es lo que hace verificable la
     * alerta: el usuario puede contrastar el número con sus propios datos.
     */
    public function explicacion(): string
    {
        $f = $this->fundamento ?? [];

        return match ($this->regla()) {
            'antiguedad' => sprintf(
                'Pasaron %d meses desde la compra, por encima del umbral de %d. El catálogo ya registra generaciones posteriores de este tipo de componente.',
                $f['meses_desde_la_compra'] ?? 0,
                $f['umbral_meses'] ?? 0,
            ),
            'degradacion' => sprintf(
                'Las últimas tres sesiones de %s en %s y calidad %s (%s fps) quedaron por debajo del umbral de tolerancia de %s fps, con un promedio histórico de %s fps sobre %d sesiones.',
                $f['juego'] ?? '—',
                $f['resolucion'] ?? '—',
                $f['nivel_calidad'] ?? '—',
                implode(', ', $f['ultimas_tres_sesiones'] ?? []),
                $f['umbral_de_tolerancia'] ?? '—',
                $f['promedio_historico'] ?? '—',
                $f['sesiones_evaluadas'] ?? 0,
            ),
            'desbalance' => sprintf(
                'La %s pertenece a la categoría inferior: %s está en nivel %d y %s en nivel %d, una diferencia de %d sobre la escala de 5 del catálogo.',
                strtoupper($f['pieza_inferior'] ?? ''),
                $f['modelo_cpu'] ?? 'la CPU',
                $f['nivel_cpu'] ?? 0,
                $f['modelo_gpu'] ?? 'la GPU',
                $f['nivel_gpu'] ?? 0,
                $f['diferencia'] ?? 0,
            ),
            'mantenimiento' => sprintf(
                'Pasaron %d meses sin intervenciones registradas, por encima del umbral de %d. Hay %d %s en la bitácora.',
                $f['meses_sin_intervencion'] ?? 0,
                $f['umbral_meses'] ?? 0,
                $f['intervenciones_registradas'] ?? 0,
                ($f['intervenciones_registradas'] ?? 0) === 1 ? 'registro' : 'registros',
            ),
            default => '',
        };
    }

    /**
     * RF-ANA-12: el aviso de desbalance no afirma que exista una limitación
     * efectiva, porque eso depende de qué se juegue y cómo.
     */
    public function advertencia(): ?string
    {
        return $this->tipo === 'posible_desbalance'
            ? 'Que esta diferencia se traduzca en una limitación real depende de la resolución, del título y del nivel de calidad que uses. Contrastalo con tu propio historial de rendimiento.'
            : null;
    }
}
