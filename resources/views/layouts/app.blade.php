<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'GaragePC')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-carbon-950 text-carbon-100 antialiased">

@php
    $secciones = [
        ['panel', 'Dashboard'],
        ['equipos.index', 'Componentes'],
        ['bitacora', 'Bitacora'],
        ['rendimiento', 'Rendimiento'],
        ['sugerencias', 'Sugerencias'],
        ['marketplace', 'Marketplace'],
        ['cotizador', 'Cotizador'],
    ];
    $usuario = auth()->user();
    $iniciales = collect(explode(' ', trim($usuario->name)))
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
@endphp

<header class="border-b border-carbon-800 bg-carbon-950">
    <div class="mx-auto flex h-14 max-w-7xl items-center gap-8 px-6">

        <a href="{{ route('panel') }}" class="shrink-0 text-sm font-semibold tracking-tight">
            Garage<span class="text-marca-500">PC</span>
        </a>

        <nav class="flex items-center gap-1 text-sm">
            @foreach ($secciones as [$ruta, $titulo])
                @php $existe = \Illuminate\Support\Facades\Route::has($ruta); @endphp
                <a href="{{ $existe ? route($ruta) : '#' }}"
                   @class([
                       'rounded-md px-3 py-1.5 transition',
                       'bg-carbon-800 text-carbon-100' => $existe && request()->routeIs($ruta . '*'),
                       'text-carbon-400 hover:text-carbon-200' => ! ($existe && request()->routeIs($ruta . '*')),
                       'cursor-default opacity-40 hover:text-carbon-400' => ! $existe,
                   ])>{{ $titulo }}</a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="etiqueta transition hover:text-carbon-200">Salir</button>
            </form>

            <span title="{{ $usuario->name }}"
                  class="flex size-8 items-center justify-center rounded-full bg-carbon-700 text-xs font-medium">
                {{ $iniciales }}
            </span>
        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl px-6 py-10">
    @if (session('estado'))
        <div class="mb-6 rounded-lg border border-carbon-700 bg-carbon-800 px-4 py-3 text-sm text-carbon-200">
            {{ session('estado') }}
        </div>
    @endif

    @yield('contenido')
</main>

</body>
</html>
