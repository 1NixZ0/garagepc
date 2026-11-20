@extends('layouts.guest')

@section('titulo', 'Ingresar | GaragePC')

@section('contenido')
    <h1 class="text-lg font-semibold">Ingresar</h1>
    <p class="mt-1 text-sm text-carbon-400">Entra a tu garaje.</p>

    @if (session('estado'))
        <p class="mt-4 rounded-md border border-carbon-700 px-3 py-2 text-sm text-carbon-200">
            {{ session('estado') }}
        </p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="etiqueta block">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                          placeholder-carbon-600 outline-none focus:border-carbon-400">
            @error('email')
                <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="etiqueta block">Contrase&ntilde;a</label>
            <input id="password" name="password" type="password"
                   required autocomplete="current-password"
                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                          outline-none focus:border-carbon-400">
            @error('password')
                <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-carbon-400">
            <input type="checkbox" name="recordarme" value="1"
                   class="size-4 rounded border-carbon-600 bg-carbon-900 text-marca-500">
            Mantener la sesi&oacute;n abierta
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-marca-500 px-4 py-2.5 text-sm font-medium text-white
                       transition hover:bg-marca-600">
            Ingresar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-carbon-400">
        &iquest;Todav&iacute;a no tenes cuenta?
        <a href="{{ route('registro') }}" class="text-carbon-100 underline underline-offset-4">Cre&aacute; una</a>
    </p>
@endsection
