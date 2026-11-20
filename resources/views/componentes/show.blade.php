@extends('layouts.app')

@section('titulo', $componente->modelo . ' | GaragePC')

@section('contenido')
    @php
        $intervenciones = $componente->intervenciones;
        $meses = $componente->mesesDesdeUltimaIntervencion();
    @endphp

    <p class="etiqueta">
        <a href="{{ route('equipos.show', $equipo) }}" class="hover:text-carbon-200">{{ $equipo->nombre }}</a>
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-5">

        {{-- Ficha del componente --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <div class="flex items-center justify-between gap-3">
                    <span class="rounded-md bg-carbon-700 px-2 py-1 text-xs text-carbon-200">
                        {{ $componente->etiquetaCategoria() }}
                    </span>
                    <span @class([
                        'rounded-md px-2 py-1 text-xs',
                        'bg-carbon-700 text-carbon-200' => $componente->estado === 'activo',
                        'bg-aviso-500/15 text-aviso-500' => $componente->estado === 'en_reparacion',
                        'bg-carbon-700 text-carbon-400' => $componente->estado === 'retirado',
                    ])>{{ $componente->etiquetaEstado() }}</span>
                </div>

                <h1 class="mt-4 text-xl font-semibold tracking-tight">{{ $componente->modelo }}</h1>

                @if ($componente->catalogo)
                    <p class="mt-1 text-sm text-carbon-400">
                        Catalogado · {{ $componente->catalogo->anio_lanzamiento }} ·
                        gama {{ $componente->catalogo->categoria_rendimiento }} de 5
                    </p>
                @else
                    <p class="mt-1 text-sm text-carbon-400">Fuera del catálogo</p>
                @endif

                <dl class="mt-6 space-y-4 border-t border-carbon-700 pt-6 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="etiqueta">Comprado</dt>
                        <dd class="text-carbon-200">{{ $componente->fecha_compra->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="etiqueta">Antigüedad</dt>
                        <dd class="text-carbon-200">{{ $componente->antiguedadEnMeses() }} meses</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="etiqueta">Precio</dt>
                        <dd class="text-right text-carbon-200">
                            {{ $componente->moneda_compra }} {{ number_format($componente->precio_compra, 2, ',', '.') }}
                            <span class="block text-xs text-carbon-400">
                                US$ {{ number_format($componente->costoEnUsd(), 2, ',', '.') }}
                            </span>
                        </dd>
                    </div>
                    @if ($componente->condicion_declarada)
                        <div class="flex justify-between gap-4">
                            <dt class="etiqueta">Condición</dt>
                            <dd class="text-carbon-200">
                                {{ \App\Models\Componente::CONDICIONES[$componente->condicion_declarada] }}
                            </dd>
                        </div>
                    @endif
                </dl>

                @if ($componente->notas)
                    <p class="mt-6 border-t border-carbon-700 pt-6 text-sm text-carbon-400">{{ $componente->notas }}</p>
                @endif

                <a href="{{ route('equipos.componentes.edit', [$equipo, $componente]) }}"
                   class="mt-6 block rounded-lg border border-carbon-700 px-4 py-2 text-center text-sm text-carbon-200
                          transition hover:border-carbon-600">
                    Editar datos
                </a>
            </div>
        </div>

        {{-- Bitácora --}}
        <div class="lg:col-span-3">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="etiqueta">Historial</p>
                    <h2 class="mt-1 text-xl font-semibold tracking-tight">Bitácora de mantenimiento</h2>
                </div>
                <p class="etiqueta">
                    {{ $intervenciones->count() }} {{ \Illuminate\Support\Str::plural('registro', $intervenciones->count()) }}
                </p>
            </div>

            @if ($intervenciones->isEmpty())
                <div class="mt-6 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-8 text-center">
                    <p class="text-sm text-carbon-200">Todavía no registraste mantenimiento en esta pieza.</p>
                </div>
            @else
                <ol class="mt-6 space-y-3">
                    @foreach ($intervenciones as $intervencion)
                        <li class="flex gap-4">
                            <span @class([
                                'mt-5 size-2 shrink-0 rounded-full',
                                'bg-marca-500' => $intervencion->esReciente(),
                                'bg-carbon-600' => ! $intervencion->esReciente(),
                            ])></span>

                            <div class="flex-1 rounded-xl border border-carbon-700 bg-carbon-800 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="font-medium">{{ $intervencion->etiquetaTipo() }}</p>

                                    <form method="POST" class="shrink-0"
                                          action="{{ route('intervenciones.destroy', [$componente, $intervencion]) }}"
                                          onsubmit="return confirm('¿Eliminar este registro de la bitácora?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="etiqueta transition hover:text-marca-500">Eliminar</button>
                                    </form>
                                </div>

                                @if ($intervencion->descripcion)
                                    <p class="mt-1 text-sm text-carbon-400">{{ $intervencion->descripcion }}</p>
                                @endif

                                <p class="mt-3 flex items-center gap-3">
                                    <span class="etiqueta">{{ $intervencion->fecha->format('d/m/Y') }}</span>
                                    @if ($intervencion->esReciente())
                                        <span class="rounded-md bg-marca-500/15 px-2 py-0.5 text-xs text-marca-500">Reciente</span>
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                        <p class="etiqueta">Último mantenimiento</p>
                        <p class="mt-1 text-2xl font-semibold">
                            {{ $meses === 0 ? 'Este mes' : $meses . ' ' . \Illuminate\Support\Str::plural('mes', $meses) }}
                            @if ($meses > 0)<span class="text-sm font-normal text-carbon-400">atrás</span>@endif
                        </p>
                    </div>
                    <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                        <p class="etiqueta">Total de registros</p>
                        <p class="mt-1 text-2xl font-semibold">{{ $intervenciones->count() }}</p>
                    </div>
                </div>
            @endif

            {{-- Alta de intervención. El <details> se abre solo si hubo un error. --}}
            <details class="group mt-6" @if ($errors->any()) open @endif>
                <summary class="cursor-pointer list-none rounded-lg bg-marca-500 px-4 py-2.5 text-center text-sm
                                font-medium text-white transition hover:bg-marca-600">
                    <span class="group-open:hidden">Registrar mantenimiento</span>
                    <span class="hidden group-open:inline">Cerrar</span>
                </summary>

                <form method="POST" action="{{ route('intervenciones.store', $componente) }}"
                      class="mt-4 space-y-5 rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="tipo" class="etiqueta block">Tipo</label>
                            <select id="tipo" name="tipo" required
                                    class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                           outline-none focus:border-carbon-400">
                                @foreach (\App\Models\Intervencion::TIPOS as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            @error('tipo')<p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="fecha" class="etiqueta block">Fecha</label>
                            <input id="fecha" name="fecha" type="date" required
                                   min="{{ $componente->fecha_compra->toDateString() }}"
                                   max="{{ now()->toDateString() }}"
                                   value="{{ old('fecha', now()->toDateString()) }}"
                                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                          outline-none focus:border-carbon-400 [color-scheme:dark]">
                            @error('fecha')<p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="descripcion" class="etiqueta block">Qué hiciste <span class="normal-case tracking-normal">(opcional)</span></label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="2000"
                                  placeholder="Arctic MX-4 aplicada. La temperatura en reposo bajó 8 °C."
                                  class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                         placeholder-carbon-600 outline-none focus:border-carbon-400">{{ old('descripcion') }}</textarea>
                        @error('descripcion')<p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                            class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-marca-600">
                        Guardar en la bitácora
                    </button>
                </form>
            </details>

            <p class="mt-4 text-center text-xs text-carbon-400">
                Un historial de mantenimiento sostenido es lo que después respalda el precio en el Marketplace.
            </p>
        </div>
    </div>
@endsection
