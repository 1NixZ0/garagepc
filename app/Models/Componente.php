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

    /**
     * Vocabulario del dominio. Vive acá y no repartido por las vistas:
     * los enum de la base y las etiquetas que ve el usuario tienen un
     * único lugar donde cambiar.
     */
    public const CATEGORIAS = [
        'cpu' => 'CPU',
        'gpu' => 'GPU',
        'ram' => 'RAM',
        'almacenamiento' => 'Almacenamiento',
        'motherboard' => 'Motherboard',
        'fuente' => 'Fuente',
        'refrigeracion' => 'Refrigeración',
        'gabinete' => 'Gabinete',
        'otro' => 'Otro',
    ];

    public const ESTADOS = [
        'activo' => 'Activo',
        'en_reparacion' => 'En reparación',
        'retirado' => 'Retirado',
    ];

    public const CONDICIONES = [
        'excelente' => 'Excelente',
        'muy_bueno' => 'Muy bueno',
        'bueno' => 'Bueno',
        'regular' => 'Regular',
    ];

    public const MONEDAS = [
        'ARS' => 'Peso argentino (ARS)',
        'USD' => 'Dólar (USD)',
        'BRL' => 'Real (BRL)',
    ];

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

    /**
     * Costo en dólares a la fecha de compra. Se divide por la paridad
     * congelada, nunca por la cotización de hoy: el costo histórico de un
     * equipo no puede cambiar porque el dólar se haya movido.
     */
    public function costoEnUsd(): float
    {
        $cotizacion = (float) $this->cotizacion_compra;

        return $cotizacion > 0 ? (float) $this->precio_compra / $cotizacion : 0.0;
    }

    public function etiquetaCategoria(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? $this->categoria;
    }

    public function etiquetaEstado(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
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
