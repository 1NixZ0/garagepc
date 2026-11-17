<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alerta generada por el Motor de Análisis Proactivo.
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
        return $query->where('estado', 'activa');
    }

    public function scopeDelTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
