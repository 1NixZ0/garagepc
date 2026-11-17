@extends('layouts.app')

@section('titulo', 'Rendimiento de ' . $equipo->nombre . ' | GaragePC')

@section('contenido')
    @php
        $ultima = $sesiones->first();
        $promedio = $grafico['promedio'] ?? ($sesiones->avg('fps_promedio') ? round($sesiones->avg('fps_promedio'), 2) : null);
        $variacion = ($ultima && $promedio) ? round((((float) $ultima->fps_promedio - $promedio) / $promedio) * 100, 1) : null;
    @endphp

    <p class="etiqueta">
        <a href="{{ route('equipos.show', $equipo) }}" class="hover:text-carbon-200">{{ $equipo->nombre }}</a>
    </p>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Rendimiento</h1>

    @if ($configuraciones->count() > 1)
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($configuraciones as $config)
                <a href="{{ route('equipos.rendimiento', ['equipo' => $equipo, 'config' => $config['clave']]) }}"
                   @class([
                       'rounded-lg px-3 py-1.5 text-sm transition',
                       'bg-carbon-800 text-carbon-100' => $config['clave'] === $elegida,
                       'border border-carbon-700 text-carbon-400 hover:text-carbon-200' => $config['clave'] !== $elegida,
                   ])>
                    {{ $config['etiqueta'] }}
                    <span class="ml-1 text-xs text-carbon-400">{{ $config['cantidad'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-5">

        {{-- Alta de sesión --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                <p class="etiqueta">Nueva sesión</p>
                <h2 class="mt-1 text-lg font-semibold tracking-tight">Registro de rendimiento</h2>

                <form method="POST" action="{{ route('sesiones.store', $equipo) }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="relative" data-auto data-auto-url="{{ route('catalogo.juegos.buscar') }}">
                        <label for="juego" class="etiqueta block">Juego</label>

                        <input id="juego" name="juego" type="text" required maxlength="120" autocomplete="off"
                               data-auto-campo
                               value="{{ old('juego', $sesiones->first()->juego ?? '') }}"
                               placeholder="Empezá a escribir: fort, elden, cs..."
                               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                      placeholder-carbon-600 outline-none focus:border-carbon-400">

                        <input type="hidden" name="catalogo_juego_id" data-auto-id
                               value="{{ old('catalogo_juego_id', $sesiones->first()->catalogo_juego_id ?? '') }}">

                        <ul data-auto-lista
                            class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-72 overflow-y-auto rounded-lg
                                   border border-carbon-700 bg-carbon-800 py-1 shadow-xl"></ul>

                        <p data-auto-ayuda class="mt-1.5 text-xs text-carbon-400">Si no está en la lista, escribilo igual.</p>
                        @error('juego')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                        @error('catalogo_juego_id')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="resolucion" class="etiqueta block">Resolución</label>
                            <select id="resolucion" name="resolucion" required
                                    class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                           outline-none focus:border-carbon-400">
                                @foreach (\App\Models\SesionRendimiento::RESOLUCIONES as $valor => $etiqueta)
                                    <option value="{{ $valor }}"
                                        @selected(old('resolucion', $sesiones->first()->resolucion ?? '1080p') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            @error('resolucion')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="nivel_calidad" class="etiqueta block">Calidad</label>
                            <select id="nivel_calidad" name="nivel_calidad" required
                                    class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                           outline-none focus:border-carbon-400">
                                @foreach (\App\Models\SesionRendimiento::CALIDADES as $valor => $etiqueta)
                                    <option value="{{ $valor }}"
                                        @selected(old('nivel_calidad', $sesiones->first()->nivel_calidad ?? 'alto') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            @error('nivel_calidad')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="fps_promedio" class="etiqueta block">FPS promedio</label>
                        <input id="fps_promedio" name="fps_promedio" type="number" step="0.01" min="1" required
                               value="{{ old('fps_promedio') }}" placeholder="200"
                               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                      placeholder-carbon-600 outline-none focus:border-carbon-400">
                        @error('fps_promedio')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="temp_cpu" class="etiqueta block">Temp. CPU <span class="normal-case tracking-normal">(°C)</span></label>
                            <input id="temp_cpu" name="temp_cpu" type="number" step="0.1" min="0" max="150"
                                   value="{{ old('temp_cpu') }}" placeholder="65"
                                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                          placeholder-carbon-600 outline-none focus:border-carbon-400">
                            @error('temp_cpu')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="temp_gpu" class="etiqueta block">Temp. GPU <span class="normal-case tracking-normal">(°C)</span></label>
                            <input id="temp_gpu" name="temp_gpu" type="number" step="0.1" min="0" max="150"
                                   value="{{ old('temp_gpu') }}" placeholder="72"
                                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                          placeholder-carbon-600 outline-none focus:border-carbon-400">
                            @error('temp_gpu')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="fecha_sesion" class="etiqueta block">Cuándo</label>
                        <input id="fecha_sesion" name="fecha_sesion" type="datetime-local" required
                               max="{{ now()->format('Y-m-d\\TH:i') }}"
                               value="{{ old('fecha_sesion', now()->format('Y-m-d\\TH:i')) }}"
                               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                                      outline-none focus:border-carbon-400 [color-scheme:dark]">
                        @error('fecha_sesion')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-marca-500 px-4 py-2.5 text-sm font-medium text-white
                                   transition hover:bg-marca-600">
                        Registrar sesión
                    </button>
                </form>
            </div>
        </div>

        {{-- Historial --}}
        <div class="lg:col-span-3">
            @if ($sesiones->isEmpty())
                <div class="rounded-2xl border border-dashed border-carbon-700 bg-carbon-900 p-10 text-center">
                    <p class="text-sm text-carbon-200">Todavía no hay sesiones registradas.</p>
                    <p class="mx-auto mt-2 max-w-sm text-sm text-carbon-400">
                        Cargá la primera con el formulario. Desde la segunda ya vas a ver el gráfico
                        y la comparación contra el promedio.
                    </p>
                </div>
            @else
                <div class="rounded-2xl border border-carbon-700 bg-carbon-800 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="etiqueta">Historial de FPS</p>
                            <h2 class="mt-1 text-lg font-semibold tracking-tight">
                                Últimas {{ min($sesiones->count(), 12) }}
                                {{ \Illuminate\Support\Str::plural('sesión', min($sesiones->count(), 12)) }}
                            </h2>
                        </div>

                        @if ($promedio)
                            <div class="rounded-xl border border-carbon-700 bg-carbon-900 px-4 py-3 text-right">
                                <p class="etiqueta">Promedio</p>
                                <p class="mt-0.5 text-xl font-semibold text-marca-500">
                                    {{ number_format($promedio, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-carbon-400">fps</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    @if ($grafico)
                        <svg viewBox="0 0 {{ $grafico['ancho'] }} {{ $grafico['alto'] }}" class="mt-6 w-full"
                             role="img" aria-label="Evolución de los FPS por sesión">
                            {{-- Rejilla --}}
                            <line x1="{{ $grafico['izquierda'] }}" y1="{{ $grafico['techoY'] }}"
                                  x2="{{ $grafico['derecha'] }}" y2="{{ $grafico['techoY'] }}" stroke="#2a2a2a"/>
                            <line x1="{{ $grafico['izquierda'] }}" y1="{{ $grafico['pisoY'] }}"
                                  x2="{{ $grafico['derecha'] }}" y2="{{ $grafico['pisoY'] }}" stroke="#2a2a2a"/>

                            <text x="{{ $grafico['izquierda'] - 8 }}" y="{{ $grafico['techoY'] + 4 }}"
                                  text-anchor="end" font-size="10" fill="#8a8a8a">{{ $grafico['techo'] }}</text>
                            <text x="{{ $grafico['izquierda'] - 8 }}" y="{{ $grafico['pisoY'] + 4 }}"
                                  text-anchor="end" font-size="10" fill="#8a8a8a">{{ $grafico['piso'] }}</text>

                            {{-- Promedio histórico --}}
                            <line x1="{{ $grafico['izquierda'] }}" y1="{{ $grafico['promedioY'] }}"
                                  x2="{{ $grafico['derecha'] }}" y2="{{ $grafico['promedioY'] }}"
                                  stroke="#8a8a8a" stroke-dasharray="4 4"/>
                            <text x="{{ $grafico['derecha'] }}" y="{{ $grafico['promedioY'] - 6 }}"
                                  text-anchor="end" font-size="10" fill="#8a8a8a">prom. {{ $grafico['promedio'] }}</text>

                            {{-- Serie --}}
                            <polyline points="{{ $grafico['linea'] }}" fill="none" stroke="#c8102e" stroke-width="2"
                                      stroke-linejoin="round" stroke-linecap="round"/>

                            @foreach ($grafico['puntos'] as $punto)
                                <circle cx="{{ $punto['x'] }}" cy="{{ $punto['y'] }}" r="4"
                                        fill="{{ $punto['bajoPromedio'] ? '#ff6f00' : '#c8102e' }}"/>
                                <text x="{{ $punto['x'] }}" y="{{ $grafico['alto'] - 10 }}"
                                      text-anchor="middle" font-size="9" fill="#8a8a8a">{{ $punto['fecha'] }}</text>
                            @endforeach
                        </svg>

                        <p class="mt-4 flex flex-wrap items-center gap-4 text-xs text-carbon-400">
                            <span class="flex items-center gap-2">
                                <span class="inline-block h-0.5 w-5" style="background:#c8102e"></span> FPS
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="inline-block h-0.5 w-5 border-t border-dashed border-carbon-400"></span>
                                Promedio histórico
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="inline-block size-2 rounded-full" style="background:#ff6f00"></span>
                                Por debajo del promedio
                            </span>
                        </p>
                    @else
                        <p class="mt-6 text-sm text-carbon-400">
                            Con una sola sesión no hay nada que comparar. Cargá la segunda y aparece el gráfico.
                        </p>
                    @endif
                </div>

                {{-- Tabla de sesiones --}}
                <div class="mt-6 overflow-hidden rounded-2xl border border-carbon-700">
                    <table class="w-full text-sm">
                        <thead class="bg-carbon-800">
                            <tr class="text-left">
                                <th class="etiqueta px-4 py-3 font-normal">Fecha</th>
                                <th class="etiqueta px-4 py-3 text-right font-normal">FPS</th>
                                <th class="etiqueta px-4 py-3 text-right font-normal">vs. prom.</th>
                                <th class="etiqueta px-4 py-3 text-right font-normal">CPU</th>
                                <th class="etiqueta px-4 py-3 text-right font-normal">GPU</th>
                                <th class="etiqueta px-4 py-3 text-right font-normal"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-carbon-800 bg-carbon-900">
                            @foreach ($sesiones as $sesion)
                                @php
                                    $delta = $promedio ? round(((float) $sesion->fps_promedio - $promedio) / $promedio * 100, 1) : null;
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-carbon-400">
                                        {{ $sesion->fecha_sesion->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-carbon-100">
                                        {{ number_format($sesion->fps_promedio, 0, ',', '.') }}
                                    </td>
                                    <td @class([
                                        'px-4 py-3 text-right',
                                        'text-aviso-500' => $delta !== null && $delta < 0,
                                        'text-carbon-400' => $delta === null || $delta >= 0,
                                    ])>{{ $delta === null ? '—' : ($delta > 0 ? '+' : '') . $delta . '%' }}</td>
                                    <td class="px-4 py-3 text-right text-carbon-400">
                                        {{ $sesion->temp_cpu ? number_format($sesion->temp_cpu, 0) . '°' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-carbon-400">
                                        {{ $sesion->temp_gpu ? number_format($sesion->temp_gpu, 0) . '°' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('sesiones.destroy', [$equipo, $sesion]) }}"
                                              onsubmit="return confirm('¿Eliminar esta sesión?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="etiqueta transition hover:text-marca-500">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-4 text-xs text-carbon-400">
                    El promedio se calcula solo con las sesiones de esta configuración. Comparar los FPS de un juego
                    en 1080p contra los del mismo juego en 4K no mide degradación, mide otra cosa.
                </p>
            @endif
        </div>
    </div>
@endsection
