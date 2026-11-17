@extends('layouts.app')

@section('titulo', 'Nuevo equipo | GaragePC')

@section('contenido')
    <div class="mx-auto max-w-xl">
        <p class="etiqueta">Garaje Virtual</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">Nuevo equipo</h1>

        <form method="POST" action="{{ route('equipos.store') }}"
              class="mt-8 space-y-6 rounded-2xl border border-carbon-700 bg-carbon-800 p-8">
            @csrf
            @include('equipos._campos', ['equipo' => null])

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-marca-600">
                    Crear equipo
                </button>
                <a href="{{ route('equipos.index') }}" class="text-sm text-carbon-400 hover:text-carbon-200">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
