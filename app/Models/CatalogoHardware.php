<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de Hardware. Base de conocimiento del Motor de Análisis Proactivo.
 * La escala de categoria_rendimiento va de 1 (entrada) a 5 (gama alta).
 */
class CatalogoHardware extends Model
{
    use HasFactory;

    protected $table = 'catalogo_hardware';

    protected $fillable = ['tipo', 'modelo', 'categoria_rendimiento', 'anio_lanzamiento', 'notas'];

    protected function casts(): array
    {
        return [
            'categoria_rendimiento' => 'integer',
            'anio_lanzamiento' => 'integer',
        ];
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(Componente::class);
    }

    /** Antigüedad de la generación, en años cumplidos. */
    public function antiguedadEnAnios(): int
    {
        return (int) date('Y') - $this->anio_lanzamiento;
    }
}
