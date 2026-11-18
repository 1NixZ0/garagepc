<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertaResource;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Alertas activas en la aplicación móvil (RF-MOV-10).
 */
class AlertaApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $alertas = Alerta::query()
            ->activas()
            ->whereHas('componente.equipo', fn ($consulta) => $consulta->where('user_id', $request->user()->id))
            ->with('componente.equipo')
            ->orderByRaw("FIELD(tipo, 'posible_desbalance', 'actualizacion', 'mantenimiento_preventivo')")
            ->orderByDesc('evaluada_en')
            ->get();

        return AlertaResource::collection($alertas);
    }
}
