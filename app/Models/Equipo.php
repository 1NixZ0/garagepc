<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Equipo: la PC del usuario, con identidad propia.
 */
class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';

    protected $fillable = ['user_id', 'nombre', 'descripcion'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(Componente::class);
    }

    public function sesionesRendimiento(): HasMany
    {
        return $this->hasMany(SesionRendimiento::class);
    }

    /** La CPU activa del equipo, necesaria para la regla de posible desbalance. */
    public function cpu(): ?Componente
    {
        return $this->componentes()->where('categoria', 'cpu')->where('estado', 'activo')->first();
    }

    /** La GPU activa del equipo, necesaria para la regla de posible desbalance. */
    public function gpu(): ?Componente
    {
        return $this->componentes()->where('categoria', 'gpu')->where('estado', 'activo')->first();
    }
}
