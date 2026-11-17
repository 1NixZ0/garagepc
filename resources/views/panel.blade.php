@extends('layouts.app')

@section('titulo', 'Dashboard | GaragePC')

@section('contenido')
    <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="etiqueta">Dashboard</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Hola, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        </div>

        <a href="{{ route('equipos.create') }}"
           class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-marca-600">
            Agregar equipo
        </a>
    </div>

    @if ($equipos->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">Tu garaje está vacío.</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-carbon-400">
                Empezá dando de alta tu PC. Después vas a poder cargarle los componentes,
                registrar el mantenimiento y seguir su rendimiento.
            </p>
        </div>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($equipos as $equipo)
                <a href="{{ route('equipos.show', $equipo) }}"
                   class="group rounded-2xl border border-carbon-700 bg-carbon-800 p-6 transition hover:border-carbon-600">
                    <p class="etiqueta">Equipo</p>
                    <p class="mt-1 text-lg font-medium group-hover:text-white">{{ $equipo->nombre }}</p>
                    <p class="mt-4 text-sm text-carbon-400">
                        {{ $equipo->componentes_count }}
                        {{ \Illuminate\Support\Str::plural('componente', $equipo->componentes_count) }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
