@extends('layouts.app')

@section('titulo', 'Bitácora | GaragePC')

@section('contenido')
    <p class="etiqueta">Mantenimiento</p>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Bitácora</h1>
    <p class="mt-1 text-sm text-carbon-400">Todas las intervenciones de todos tus equipos, de la más nueva a la más vieja.</p>

    @if ($intervenciones->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">Todavía no registraste ninguna intervención.</p>
            <p class="mt-2 text-sm text-carbon-400">Entrá a un componente y usá «Registrar mantenimiento».</p>
        </div>
    @else
        <div class="mt-8 overflow-hidden rounded-2xl border border-carbon-700">
            <table class="w-full text-sm">
                <thead class="bg-carbon-800">
                    <tr class="text-left">
                        <th class="etiqueta px-4 py-3 font-normal">Fecha</th>
                        <th class="etiqueta px-4 py-3 font-normal">Tipo</th>
                        <th class="etiqueta px-4 py-3 font-normal">Componente</th>
                        <th class="etiqueta px-4 py-3 font-normal">Equipo</th>
                        <th class="etiqueta px-4 py-3 font-normal">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-800 bg-carbon-900">
                    @foreach ($intervenciones as $intervencion)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-carbon-400">{{ $intervencion->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-carbon-100">{{ $intervencion->etiquetaTipo() }}</td>
                            <td class="px-4 py-3">
                                <a class="text-carbon-200 underline underline-offset-4 hover:text-white"
                                   href="{{ route('equipos.componentes.show', [$intervencion->componente->equipo_id, $intervencion->componente]) }}">
                                    {{ $intervencion->componente->modelo }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-carbon-400">{{ $intervencion->componente->equipo->nombre }}</td>
                            <td class="px-4 py-3 text-carbon-400">{{ \Illuminate\Support\Str::limit($intervencion->descripcion, 70) ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($intervenciones->hasPages())
            <div class="mt-6 flex items-center justify-between">
                @if ($intervenciones->previousPageUrl())
                    <a href="{{ $intervenciones->previousPageUrl() }}"
                       class="rounded-lg border border-carbon-700 px-4 py-2 text-sm text-carbon-200 hover:border-carbon-600">Anteriores</a>
                @else
                    <span></span>
                @endif

                @if ($intervenciones->nextPageUrl())
                    <a href="{{ $intervenciones->nextPageUrl() }}"
                       class="rounded-lg border border-carbon-700 px-4 py-2 text-sm text-carbon-200 hover:border-carbon-600">Siguientes</a>
                @endif
            </div>
        @endif
    @endif
@endsection
