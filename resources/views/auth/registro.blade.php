@extends('layouts.guest')

@section('titulo', 'Crear cuenta | GaragePC')

@section('contenido')
    <h1 class="text-lg font-semibold">Crear cuenta</h1>
    <p class="mt-1 text-sm text-carbon-400">Un minuto y ya pod&eacute;s cargar tu primera PC.</p>

    <form method="POST" action="{{ route('registro') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="name" class="etiqueta block">Nombre</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                   required autofocus autocomplete="name"
                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                          outline-none focus:border-carbon-400">
            @error('name')
                <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="etiqueta block">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   required autocomplete="username"
                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                          outline-none focus:border-carbon-400">
            @error('email')
                <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="etiqueta block">Contrase&ntilde;a</label>
            <input id="password" name="password" type="password"
                   required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                          outline-none focus:border-carbon-400">
            <p class="mt-1.5 text-xs text-carbon-400">Al menos 8 caracteres, con letras y n&uacute;meros.</p>
            @error('password')
                <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="etiqueta block">Repetir contrase&ntilde;a</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                          outline-none focus:border-carbon-400">
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-marca-500 px-4 py-2.5 text-sm font-medium text-white
                       transition hover:bg-marca-600">
            Crear cuenta
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-carbon-400">
        &iquest;Ya ten&eacute;s una?
        <a href="{{ route('login') }}" class="text-carbon-100 underline underline-offset-4">Ingres&aacute;</a>
    </p>
@endsection
