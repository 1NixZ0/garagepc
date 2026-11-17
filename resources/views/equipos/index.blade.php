@extends('layouts.app')

@section('titulo', 'Mis equipos | GaragePC')

@section('contenido')
    <div class="flex items-end justify-between gap-6">
        <div>
            <p class="etiqueta">Garaje Virtual</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Mis equipos</h1>
        </div>

        <a href="{{ route('equipos.create') }}"
           class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-marca-600">
            Agregar equipo
        </a>
    </div>

    @if ($equipos->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">Todavía no registraste ninguna PC.</p>
        </div>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($equipos as $equipo)
                <a href="{{ route('equipos.show', $equipo) }}"
                   class="group rounded-2xl border border-carbon-700 bg-carbon-800 p-6 transition hover:border-carbon-600">
                    <p class="etiqueta">Equipo</p>
                    <p class="mt-1 text-lg font-medium group-hover:text-white">{{ $equipo->nombre }}</p>

                    @if ($equipo->descripcion)
                        <p class="mt-2 line-clamp-2 text-sm text-carbon-400">{{ $equipo->descripcion }}</p>
                    @endif

                    <p class="mt-4 text-sm text-carbon-400">
                        {{ $equipo->componentes_count }}
                        {{ \Illuminate\Support\Str::plural('componente', $equipo->componentes_count) }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
