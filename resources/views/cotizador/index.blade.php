@extends('layouts.app')

@section('titulo', 'Cotizador | GaragePC')

@section('contenido')
    <p class="etiqueta">Cotizador Multimoneda</p>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Conversión y valor estimado</h1>

    {{-- Estado de la cotización. RF-COT-06: la antigüedad se informa siempre. --}}
    @if ($cotizacion === null)
        <div class="mt-6 rounded-2xl border border-aviso-500/40 bg-aviso-500/5 p-6">
            <p class="etiqueta text-aviso-500">Sin cotización</p>
            <p class="mt-2 text-sm text-carbon-200">
                Todavía no hay ninguna cotización guardada, así que no se puede convertir nada.
            </p>
            <p class="mt-2 text-sm text-carbon-400">
                Corré <code class="rounded bg-carbon-800 px-1.5 py-0.5">php artisan cotizaciones:actualizar</code>
                para traer la primera.
            </p>
        </div>
    @else
        <div @class([
            'mt-6 rounded-2xl border p-6',
            'border-aviso-500/40 bg-aviso-500/5' => $advertencia,
            'border-carbon-700 bg-carbon-800' => ! $advertencia,
        ])>
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <p @class(['etiqueta', 'text-aviso-500' => $advertencia])>
                        Cotización aplicada &middot; serie {{ $serie }}
                    </p>
                    <p class="mt-2 text-sm text-carbon-100">
                        1 USD = {{ number_format((float) $cotizacion->ars_por_usd, 2, ',', '.') }} ARS
                        &nbsp;&middot;&nbsp;
                        1 USD = {{ number_format((float) $cotizacion->brl_por_usd, 4, ',', '.') }} BRL
                    </p>
                    <p class="mt-1 text-xs text-carbon-400">
                        Obtenida el {{ $cotizacion->obtenida_en->format('d/m/Y H:i') }}
                        ({{ $horas < 1 ? 'hace menos de una hora' : 'hace '.number_format($horas, 0).' horas' }})
                        desde {{ $cotizacion->origen }}.
                    </p>
                </div>
            </div>

            @if ($vencida)
                <p class="mt-4 text-sm text-aviso-500">
                    Esta cotización supera los siete días. El sistema deja de mostrar valores convertidos:
                    una conversión vieja no es un dato aproximado, es un dato equivocado.
                </p>
            @elseif ($advertencia)
                <p class="mt-4 text-sm text-aviso-500">
                    Esta cotización no es del día. Los valores convertidos son orientativos.
                </p>
            @endif
        </div>
    @endif

    {{-- Conversión simple --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <p class="etiqueta">Convertir</p>

                <form method="GET" action="{{ route('cotizador') }}" class="mt-4 space-y-5">
                    <div>
                        <label for="monto" class="etiqueta block">Importe</label>
                        <input id="monto" name="monto" type="number" step="0.01" min="0"
                               value="{{ $monto }}" placeholder="500"
                               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                      placeholder-carbon-600 outline-none focus:border-carbon-400">
                        @error('monto')<p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="moneda" class="etiqueta block">Moneda de origen</label>
                        <select id="moneda" name="moneda"
                                class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                       outline-none focus:border-carbon-400">
                            @foreach (\App\Models\Componente::MONEDAS as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected($moneda === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-marca-500 px-4 py-2.5 text-sm font-medium text-white
                                   transition hover:bg-marca-600">
                        Convertir
                    </button>
                </form>

                @if ($monto !== null)
                    @if ($convertido === null)
                        <p class="mt-6 border-t border-carbon-700 pt-6 text-sm text-aviso-500">
                            No se puede convertir con la cotización disponible. El importe queda en
                            {{ number_format($monto, 2, ',', '.') }} {{ $moneda }}.
                        </p>
                    @else
                        <dl class="mt-6 space-y-3 border-t border-carbon-700 pt-6 text-sm">
                            @foreach ($convertido as $codigo => $valor)
                                <div class="flex items-baseline justify-between gap-4">
                                    <dt class="etiqueta">{{ $codigo }}</dt>
                                    <dd @class([
                                        'text-right',
                                        'text-lg font-semibold' => $codigo === $moneda,
                                        'text-carbon-200' => $codigo !== $moneda,
                                    ])>{{ $codigo }} {{ number_format($valor, 2, ',', '.') }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                @endif

                <p class="mt-6 text-xs text-carbon-400">
                    Esto es una conversión entre monedas. No es una estimación del valor de reventa
                    de un componente usado.
                </p>
            </div>
        </div>

        {{-- Valor estimado de reventa --}}
        <div class="lg:col-span-3">
            <h2 class="text-sm font-medium">Valor estimado de reventa</h2>
            <p class="mt-1 max-w-2xl text-sm text-carbon-400">
                Es una <strong class="text-carbon-200">estimación</strong>. Parte del costo en dólares a la fecha
                de compra, le aplica la depreciación anual de la categoría y el ajuste por la condición declarada.
            </p>

            @if ($estimaciones->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-8 text-center">
                    <p class="text-sm text-carbon-400">Todavía no hay componentes en uso para estimar.</p>
                </div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($estimaciones as $fila)
                        @php($e = $fila['estimacion'])
                        <details class="group rounded-2xl border border-carbon-700 bg-carbon-900">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4">
                                <span>
                                    <span class="text-sm text-carbon-100">{{ $fila['componente']->modelo }}</span>
                                    <span class="block text-xs text-carbon-400">
                                        {{ $fila['componente']->etiquetaCategoria() }} &middot;
                                        {{ $fila['componente']->equipo->nombre }} &middot;
                                        {{ $e['antiguedad_meses'] }} meses
                                    </span>
                                </span>
                                <span class="shrink-0 text-right">
                                    @if ($e['en_monedas'])
                                        <span class="text-sm font-semibold">US$ {{ number_format($e['estimado_usd'], 2, ',', '.') }}</span>
                                        <span class="block text-xs text-carbon-400">
                                            ARS {{ number_format($e['en_monedas']['ARS'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-aviso-500">Sin cotización vigente</span>
                                    @endif
                                </span>
                            </summary>

                            {{-- RF-COT-10: el desglose completo, para que la estimación se pueda auditar. --}}
                            <dl class="space-y-2 border-t border-carbon-800 px-5 py-4 text-xs">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-carbon-400">Precio de compra</dt>
                                    <dd class="text-carbon-200">
                                        {{ $e['moneda_compra'] }} {{ number_format($e['precio_compra'], 2, ',', '.') }}
                                        @if ($e['moneda_compra'] !== 'USD')
                                            (a {{ number_format($e['cotizacion_compra'], 2, ',', '.') }} por dólar)
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-carbon-400">Costo original</dt>
                                    <dd class="text-carbon-200">US$ {{ number_format($e['costo_original_usd'], 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-carbon-400">Antigüedad considerada</dt>
                                    <dd class="text-carbon-200">{{ $e['antiguedad_meses'] }} meses</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-carbon-400">Depreciación aplicada</dt>
                                    <dd class="text-carbon-200">{{ number_format($e['depreciacion_anual'], 2, ',', '.') }} % anual</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-carbon-400">Valor depreciado</dt>
                                    <dd class="text-carbon-200">US$ {{ number_format($e['valor_depreciado_usd'], 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-carbon-400">Ajuste por condición</dt>
                                    <dd class="text-carbon-200">
                                        @if ($e['condicion_declarada'])
                                            ×{{ number_format($e['coeficiente_condicion'], 3, ',', '.') }}
                                            ({{ \App\Models\Componente::CONDICIONES[$e['condicion']] }})
                                        @else
                                            sin condición declarada, no se aplicó ajuste
                                        @endif
                                    </dd>
                                </div>
                                @if ($e['cotizacion'])
                                    <div class="flex justify-between gap-4 border-t border-carbon-800 pt-2">
                                        <dt class="text-carbon-400">Cotización utilizada</dt>
                                        <dd class="text-carbon-200">
                                            {{ number_format((float) $e['cotizacion']->ars_por_usd, 2, ',', '.') }} ARS/USD,
                                            del {{ $e['cotizacion']->obtenida_en->format('d/m/Y H:i') }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </details>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
