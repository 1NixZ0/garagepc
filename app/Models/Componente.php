<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Componente del Garaje Virtual.
 *
 * cotizacion_compra guarda la paridad vigente a la fecha de compra, de modo
 * que el costo histórico no varíe ante fluctuaciones posteriores (RF-GAR-13).
 */
class Componente extends Model
{
    use HasFactory;

    protected $table = 'componentes';

    protected $fillable = [
        'equipo_id', 'catalogo_hardware_id', 'categoria', 'modelo',
        'fecha_compra', 'precio_compra', 'moneda_compra', 'cotizacion_compra',
        'estado', 'condicion_declarada', 'notas', 'imagen',
    ];

    protected function casts(): array
    {
        return [
            'fecha_compra' => 'date',
            'precio_compra' => 'decimal:2',
            'cotizacion_compra' => 'decimal:6',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    /** Nullable a propósito: CU-08 contempla componentes no catalogados. */
    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(CatalogoHardware::class, 'catalogo_hardware_id');
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(Intervencion::class)->orderByDesc('fecha');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function publicacion(): HasOne
    {
        return $this->hasOne(Publicacion::class)->where('estado', 'activa');
    }

    /** Antigüedad del componente en meses, desde la fecha de compra. */
    public function antiguedadEnMeses(): int
    {
        return (int) $this->fecha_compra->diffInMonths(Carbon::now());
    }

    /** Meses transcurridos desde la última intervención registrada. Null si no hay ninguna. */
    public function mesesDesdeUltimaIntervencion(): ?int
    {
        $ultima = $this->intervenciones()->first();

        return $ultima ? (int) Carbon::parse($ultima->fecha)->diffInMonths(Carbon::now()) : null;
    }

    public function estaCatalogado(): bool
    {
        return $this->catalogo_hardware_id !== null;
    }
}
