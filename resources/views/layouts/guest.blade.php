<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'GaragePC')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-carbon-950 text-carbon-100 antialiased">
    <div class="flex min-h-full flex-col items-center justify-center px-4 py-12">

        <a href="{{ url('/') }}" class="mb-10 text-xl font-semibold tracking-tight">
            Garage<span class="text-marca-400">PC</span>
        </a>

        <main class="w-full max-w-sm rounded-2xl border border-carbon-700 bg-carbon-800 p-8">
            @yield('contenido')
        </main>

        <p class="etiqueta mt-10">Seminario Final &middot; Escuela Da Vinci</p>
    </div>
</body>
</html>
