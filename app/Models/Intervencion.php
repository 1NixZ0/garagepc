<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intervención de la Bitácora de Mantenimiento.
 */
class Intervencion extends Model
{
    use HasFactory;

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
}
