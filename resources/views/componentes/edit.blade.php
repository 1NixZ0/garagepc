@extends('layouts.app')

@section('titulo', 'Editar componente | GaragePC')

@section('contenido')
    <div class="mx-auto max-w-3xl">
        <p class="etiqueta">
            <a href="{{ route('equipos.show', $equipo) }}" class="hover:text-carbon-200">{{ $equipo->nombre }}</a>
        </p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ $componente->modelo }}</h1>

        <form method="POST" action="{{ route('equipos.componentes.update', [$equipo, $componente]) }}"
              class="mt-8 space-y-6 rounded-2xl border border-carbon-700 bg-carbon-800 p-8">
            @csrf
            @method('PUT')
            @include('componentes._campos', ['componente' => $componente])

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-marca-600">
                    Guardar cambios
                </button>
                <a href="{{ route('equipos.show', $equipo) }}" class="text-sm text-carbon-400 hover:text-carbon-200">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
