@extends('layouts.app')

@section('titulo', $equipo->nombre . ' | GaragePC')

@section('contenido')
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="etiqueta">
                <a href="{{ route('equipos.index') }}" class="hover:text-carbon-200">Garaje Virtual</a>
            </p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ $equipo->nombre }}</h1>
            @if ($equipo->descripcion)
                <p class="mt-1 max-w-2xl text-sm text-carbon-400">{{ $equipo->descripcion }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('equipos.edit', $equipo) }}"
               class="rounded-lg border border-carbon-700 px-4 py-2 text-sm text-carbon-200 transition hover:border-carbon-600">
                Editar
            </a>
            <a href="{{ route('equipos.componentes.create', $equipo) }}"
               class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-marca-600">
                Agregar componente
            </a>
        </div>
    </div>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
            <p class="etiqueta">Componentes</p>
            <p class="mt-1 text-2xl font-semibold">{{ $componentes->count() }}</p>
        </div>
        <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
            <p class="etiqueta">Costo total del equipo</p>
            <p class="mt-1 text-2xl font-semibold">US$ {{ number_format($costoTotalUsd, 2, ',', '.') }}</p>
            <p class="mt-1 text-xs text-carbon-400">A la cotización de cada compra</p>
        </div>
        <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
            <p class="etiqueta">Activos</p>
            <p class="mt-1 text-2xl font-semibold">{{ $componentes->where('estado', 'activo')->count() }}</p>
        </div>
    </div>

    @if ($componentes->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">Este equipo todavía no tiene componentes.</p>
            <p class="mt-2 text-sm text-carbon-400">Empezá por la CPU o la placa de video.</p>
        </div>
    @else
        <div class="mt-8 overflow-hidden rounded-2xl border border-carbon-700">
            <table class="w-full text-sm">
                <thead class="bg-carbon-800">
                    <tr class="text-left">
                        <th class="etiqueta px-4 py-3 font-normal">Categoría</th>
                        <th class="etiqueta px-4 py-3 font-normal">Modelo</th>
                        <th class="etiqueta px-4 py-3 font-normal">Compra</th>
                        <th class="etiqueta px-4 py-3 text-right font-normal">Precio</th>
                        <th class="etiqueta px-4 py-3 font-normal">Estado</th>
                        <th class="etiqueta px-4 py-3 text-right font-normal">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-800 bg-carbon-900">
                    @foreach ($componentes as $componente)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="rounded-md bg-carbon-800 px-2 py-1 text-xs text-carbon-200">
                                    {{ $componente->etiquetaCategoria() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('equipos.componentes.show', [$equipo, $componente]) }}"
                                   class="text-carbon-100 underline underline-offset-4 hover:text-white">
                                    {{ $componente->modelo }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-carbon-400">{{ $componente->fecha_compra->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right text-carbon-200">
                                {{ $componente->moneda_compra }} {{ number_format($componente->precio_compra, 2, ',', '.') }}
                                <span class="block text-xs text-carbon-400">
                                    US$ {{ number_format($componente->costoEnUsd(), 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-md px-2 py-1 text-xs',
                                    'bg-carbon-800 text-carbon-200' => $componente->estado === 'activo',
                                    'bg-aviso-500/15 text-aviso-500' => $componente->estado === 'en_reparacion',
                                    'bg-carbon-800 text-carbon-400' => $componente->estado === 'retirado',
                                ])>{{ $componente->etiquetaEstado() }}</span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('equipos.componentes.show', [$equipo, $componente]) }}"
                                   class="text-carbon-300 underline underline-offset-4 hover:text-carbon-100">Bitácora</a>
                                <a href="{{ route('equipos.componentes.edit', [$equipo, $componente]) }}"
                                   class="ml-2 text-carbon-300 underline underline-offset-4 hover:text-carbon-100">Editar</a>
                                <form method="POST" class="ml-2 inline"
                                      action="{{ route('equipos.componentes.destroy', [$equipo, $componente]) }}"
                                      onsubmit="return confirm('¿Eliminar {{ $componente->modelo }}? Se borra también su bitácora.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-carbon-400 underline underline-offset-4 hover:text-marca-500">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <form method="POST" action="{{ route('equipos.destroy', $equipo) }}" class="mt-10"
          onsubmit="return confirm('Se elimina el equipo «{{ $equipo->nombre }}» y todos sus componentes. ¿Seguro?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="etiqueta transition hover:text-marca-500">Eliminar este equipo</button>
    </form>
@endsection
