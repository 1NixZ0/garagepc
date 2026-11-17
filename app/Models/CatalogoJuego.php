<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Entrada del Catálogo de Juegos.
 */
class CatalogoJuego extends Model
{
    use HasFactory;

    public const EXIGENCIAS = [
        1 => 'muy liviano',
        2 => 'liviano',
        3 => 'medio',
        4 => 'exigente',
        5 => 'muy exigente',
    ];

    protected $table = 'catalogo_juegos';

    protected $fillable = ['nombre', 'exigencia', 'anio_lanzamiento', 'notas'];

    protected function casts(): array
    {
        return [
            'exigencia' => 'integer',
            'anio_lanzamiento' => 'integer',
        ];
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionRendimiento::class, 'catalogo_juego_id');
    }

    public function etiquetaExigencia(): string
    {
        return self::EXIGENCIAS[$this->exigencia] ?? '';
    }
}
