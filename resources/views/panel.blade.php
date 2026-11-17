@extends('layouts.app')

@section('titulo', 'Dashboard | GaragePC')

@section('contenido')
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="etiqueta">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Hola, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        </div>

        @if ($cantidadEquipos > 0)
            <a href="{{ route('equipos.index') }}"
               class="rounded-lg border border-carbon-700 px-4 py-2 text-sm text-carbon-200 transition hover:border-carbon-600">
                Ir al inventario
            </a>
        @endif
    </div>

    @if ($cantidadEquipos === 0)
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">Tu garaje está vacío.</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-carbon-400">
                Empezá dando de alta tu PC. Después vas a poder cargarle los componentes,
                registrar el mantenimiento y seguir su rendimiento.
            </p>
            <a href="{{ route('equipos.create') }}"
               class="mt-6 inline-block rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white
                      transition hover:bg-marca-600">
                Agregar equipo
            </a>
        </div>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <p class="etiqueta">Costo total del garaje</p>
                <p class="mt-1 text-2xl font-semibold">US$ {{ number_format($costoTotalUsd, 2, ',', '.') }}</p>
                <p class="mt-1 text-xs text-carbon-400">A la cotización de cada compra</p>
            </div>

            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <p class="etiqueta">Componentes en uso</p>
                <p class="mt-1 text-2xl font-semibold">{{ $cantidadComponentes }}</p>
                <p class="mt-1 text-xs text-carbon-400">
                    En {{ $cantidadEquipos }} {{ \Illuminate\Support\Str::plural('equipo', $cantidadEquipos) }}
                </p>
            </div>

            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <p class="etiqueta">Intervenciones</p>
                <p class="mt-1 text-2xl font-semibold">{{ $totalIntervenciones }}</p>
                <p class="mt-1 text-xs text-carbon-400">Registradas en la bitácora</p>
            </div>

            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <p class="etiqueta">Avisos activos</p>
                <p @class([
                    'mt-1 text-2xl font-semibold',
                    'text-aviso-500' => $cantidadAlertas > 0,
                ])>{{ $cantidadAlertas }}</p>
                <p class="mt-1 text-xs text-carbon-400">Del Motor de Análisis</p>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">

            {{-- Avisos del motor --}}
            <div>
                <h2 class="text-sm font-medium">Lo que pide atención</h2>
                <p class="mt-1 text-sm text-carbon-400">Cada aviso trae el dato que lo originó.</p>

                @if ($alertas->isEmpty())
                    <div class="mt-4 rounded-2xl border border-carbon-700 bg-carbon-900 p-6 text-sm text-carbon-400">
                        No hay avisos activos. El motor evalúa una vez por día.
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-carbon-800 overflow-hidden rounded-2xl border border-carbon-700 bg-carbon-900">
                        @foreach ($alertas as $alerta)
                            <li>
                                <a href="{{ route('sugerencias') }}"
                                   class="block px-4 py-3 transition hover:bg-carbon-800">
                                    <span @class([
                                        'etiqueta',
                                        'text-aviso-500' => $alerta->estado === 'activa',
                                    ])>{{ $alerta->titulo() }}</span>
                                    <span class="mt-0.5 block text-sm text-carbon-100">{{ $alerta->componente->modelo }}</span>
                                    <span class="block text-xs text-carbon-400">{{ $alerta->componente->equipo->nombre }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if ($cantidadAlertas > $alertas->count())
                        <a href="{{ route('sugerencias') }}"
                           class="mt-4 inline-block text-sm text-carbon-300 underline underline-offset-4 hover:text-carbon-100">
                            Ver los {{ $cantidadAlertas }} avisos
                        </a>
                    @endif
                @endif
            </div>

            {{-- Actividad reciente --}}
            <div>
                <h2 class="text-sm font-medium">Última actividad</h2>
                <p class="mt-1 text-sm text-carbon-400">Lo último que anotaste en la bitácora.</p>

                @if ($ultimasIntervenciones->isEmpty())
                    <div class="mt-4 rounded-2xl border border-carbon-700 bg-carbon-900 p-6 text-sm text-carbon-400">
                        Todavía no registraste ninguna intervención.
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-carbon-800 overflow-hidden rounded-2xl border border-carbon-700 bg-carbon-900">
                        @foreach ($ultimasIntervenciones as $intervencion)
                            <li>
                                <a href="{{ route('equipos.componentes.show', [$intervencion->componente->equipo_id, $intervencion->componente]) }}"
                                   class="flex items-center justify-between gap-4 px-4 py-3 transition hover:bg-carbon-800">
                                    <span>
                                        <span class="text-sm text-carbon-100">{{ $intervencion->etiquetaTipo() }}</span>
                                        <span class="block text-xs text-carbon-400">{{ $intervencion->componente->modelo }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs text-carbon-400">{{ $intervencion->fecha->format('d/m/Y') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('bitacora') }}"
                       class="mt-4 inline-block text-sm text-carbon-300 underline underline-offset-4 hover:text-carbon-100">
                        Ver la bitácora completa
                    </a>
                @endif
            </div>
        </div>
    @endif
@endsection
