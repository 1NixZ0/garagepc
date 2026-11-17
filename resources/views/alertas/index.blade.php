@extends('layouts.app')

@section('titulo', 'Sugerencias | GaragePC')

@section('contenido')
    <p class="etiqueta">Motor de Análisis</p>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Sugerencias y avisos</h1>
    <p class="mt-1 max-w-2xl text-sm text-carbon-400">
        Cada aviso muestra el dato que lo originó, para que puedas juzgar vos mismo si tiene sentido.
    </p>

    @if ($alertas->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
            <p class="text-sm text-carbon-200">No hay avisos activos.</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-carbon-400">
                El motor evalúa una vez por día. Si acabás de cargar datos, puede que todavía no haya corrido.
            </p>
        </div>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($alertas as $alerta)
                <div @class([
                    'rounded-2xl border p-6',
                    'border-aviso-500/40 bg-aviso-500/5' => $alerta->estado === 'activa',
                    'border-carbon-700 bg-carbon-900' => $alerta->estado !== 'activa',
                ])>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="flex items-center gap-3">
                                <span @class([
                                    'etiqueta',
                                    'text-aviso-500' => $alerta->estado === 'activa',
                                ])>{{ $alerta->titulo() }}</span>

                                @if ($alerta->estado === 'vista')
                                    <span class="rounded-md bg-carbon-800 px-2 py-0.5 text-xs text-carbon-400">Vista</span>
                                @endif
                            </p>

                            <a href="{{ route('equipos.componentes.show', [$alerta->componente->equipo_id, $alerta->componente]) }}"
                               class="mt-1 block text-lg font-medium underline-offset-4 hover:underline">
                                {{ $alerta->componente->modelo }}
                            </a>
                            <p class="text-xs text-carbon-400">{{ $alerta->componente->equipo->nombre }}</p>
                        </div>

                        @if ($alerta->estado === 'activa')
                            <form method="POST" action="{{ route('alertas.vista', $alerta) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-lg border border-carbon-700 px-3 py-1.5 text-xs text-carbon-200
                                               transition hover:border-carbon-600">
                                    Marcar como vista
                                </button>
                            </form>
                        @endif
                    </div>

                    <p class="mt-4 max-w-3xl text-sm text-carbon-200">{{ $alerta->explicacion() }}</p>

                    @if ($alerta->advertencia())
                        <p class="mt-3 max-w-3xl text-xs text-carbon-400">{{ $alerta->advertencia() }}</p>
                    @endif

                    <p class="etiqueta mt-4">Evaluada el {{ $alerta->evaluada_en->format('d/m/Y H:i') }}</p>
                </div>
            @endforeach
        </div>

        <p class="mt-6 text-xs text-carbon-400">
            Los avisos se retiran solos cuando su condición deja de verificarse. Si registrás una limpieza,
            el aviso de mantenimiento desaparece en la corrida siguiente.
        </p>
    @endif
@endsection
