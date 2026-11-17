<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intervención de la Bitácora de Mantenimiento (M-03).
 */
class Intervencion extends Model
{
    use HasFactory;

    public const TIPOS = [
        'limpieza' => 'Limpieza',
        'pasta_termica' => 'Cambio de pasta térmica',
        'inspeccion' => 'Inspección',
        'upgrade' => 'Upgrade',
        'reparacion' => 'Reparación',
        'otro' => 'Otro',
    ];

    protected $table = 'intervenciones';

    protected $fillable = ['componente_id', 'tipo', 'fecha', 'descripcion', 'imagen'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
    }

    public function etiquetaTipo(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /** Se considera reciente una intervención de los últimos tres meses. */
    public function esReciente(): bool
    {
        return $this->fecha->greaterThanOrEqualTo(now()->subMonths(3));
    }
}
