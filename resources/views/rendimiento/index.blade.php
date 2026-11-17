@extends('layouts.app')

@section('titulo', 'Rendimiento | GaragePC')

@section('contenido')
    <p class="etiqueta">Seguimiento</p>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Rendimiento</h1>
    <p class="mt-1 text-sm text-carbon-400">Elegí el equipo cuyo rendimiento querés seguir.</p>

    @if ($equipos->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">Todavía no registraste ningún equipo.</p>
        </div>
    @else
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($equipos as $equipo)
                <a href="{{ route('equipos.rendimiento', $equipo) }}"
                   class="group rounded-2xl border border-carbon-700 bg-carbon-800 p-6 transition hover:border-carbon-600">
                    <p class="etiqueta">Equipo</p>
                    <p class="mt-1 text-lg font-medium group-hover:text-white">{{ $equipo->nombre }}</p>
                    <p class="mt-4 text-sm text-carbon-400">
                        {{ $equipo->sesiones_rendimiento_count }}
                        {{ \Illuminate\Support\Str::plural('sesión', $equipo->sesiones_rendimiento_count) }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
