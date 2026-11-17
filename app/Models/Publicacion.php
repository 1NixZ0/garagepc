<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Publicación del Marketplace Interno.
 *
 * precio_sugerido es el valor estimado de reventa que calcula el sistema;
 * precio_publicado es el que finalmente fija el usuario. El sistema no
 * interviene en el pago: solo pone en contacto a las partes.
 */
class Publicacion extends Model
{
    use HasFactory;

    protected $table = 'publicaciones';

    protected $fillable = [
        'componente_id', 'precio_sugerido', 'precio_publicado', 'moneda',
        'condicion', 'descripcion', 'contacto', 'mostrar_bitacora',
        'estado', 'motivo_moderacion', 'publicada_en', 'finalizada_en',
    ];

    protected function casts(): array
    {
        return [
            'precio_sugerido' => 'decimal:2',
            'precio_publicado' => 'decimal:2',
            'mostrar_bitacora' => 'boolean',
            'publicada_en' => 'datetime',
            'finalizada_en' => 'datetime',
        ];
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', 'activa');
    }
}
