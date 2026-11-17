@php
    $actual = $componente ?? null;
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="categoria" class="etiqueta block">Categoría</label>
        <select id="categoria" name="categoria" required data-catalogo-categoria
                class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                       outline-none focus:border-carbon-400">
            @foreach (\App\Models\Componente::CATEGORIAS as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('categoria', $actual->categoria ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('categoria')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="estado" class="etiqueta block">Estado</label>
        <select id="estado" name="estado" required
                class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                       outline-none focus:border-carbon-400">
            @foreach (\App\Models\Componente::ESTADOS as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('estado', $actual->estado ?? 'activo') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('estado')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>
</div>

<div class="relative" data-catalogo>
    <label for="modelo" class="etiqueta block">Modelo</label>

    <input id="modelo" name="modelo" type="text" required maxlength="150" autocomplete="off"
           value="{{ old('modelo', $actual->modelo ?? '') }}"
           placeholder="Empezá a escribir: ryzen 7, rtx 3070, b550..."
           data-catalogo-campo data-catalogo-url="{{ route('catalogo.buscar') }}"
           class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                  placeholder-carbon-600 outline-none focus:border-carbon-400">

    <input type="hidden" name="catalogo_hardware_id" data-catalogo-id
           value="{{ old('catalogo_hardware_id', $actual->catalogo_hardware_id ?? '') }}">

    <ul data-catalogo-lista
        class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-72 overflow-y-auto rounded-lg
               border border-carbon-700 bg-carbon-800 py-1 shadow-xl"></ul>

    <p data-catalogo-ayuda class="mt-1.5 text-xs text-carbon-400">Escribilo tal cual figura en la caja o en la factura.</p>
    @error('modelo')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    @error('catalogo_hardware_id')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
</div>

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="fecha_compra" class="etiqueta block">Fecha de compra</label>
        <input id="fecha_compra" name="fecha_compra" type="date" required
               max="{{ now()->toDateString() }}"
               value="{{ old('fecha_compra', optional($actual?->fecha_compra)->toDateString() ?? '') }}"
               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                      outline-none focus:border-carbon-400 [color-scheme:dark]">
        @error('fecha_compra')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="condicion_declarada" class="etiqueta block">Condición <span class="normal-case tracking-normal">(opcional)</span></label>
        <select id="condicion_declarada" name="condicion_declarada"
                class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                       outline-none focus:border-carbon-400">
            <option value="">Sin declarar</option>
            @foreach (\App\Models\Componente::CONDICIONES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('condicion_declarada', $actual->condicion_declarada ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('condicion_declarada')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid gap-6 sm:grid-cols-3">
    <div>
        <label for="precio_compra" class="etiqueta block">Precio pagado</label>
        <input id="precio_compra" name="precio_compra" type="number" step="0.01" min="0.01" required
               value="{{ old('precio_compra', $actual->precio_compra ?? '') }}"
               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                      outline-none focus:border-carbon-400">
        @error('precio_compra')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="moneda_compra" class="etiqueta block">Moneda</label>
        <select id="moneda_compra" name="moneda_compra" required
                class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                       outline-none focus:border-carbon-400">
            @foreach (\App\Models\Componente::MONEDAS as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('moneda_compra', $actual->moneda_compra ?? 'ARS') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('moneda_compra')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="cotizacion_compra" class="etiqueta block">Dólar a esa fecha</label>
        <input id="cotizacion_compra" name="cotizacion_compra" type="number" step="0.000001" min="0.000001"
               value="{{ old('cotizacion_compra', $actual->cotizacion_compra ?? '') }}"
               placeholder="1450"
               class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                      placeholder-carbon-600 outline-none focus:border-carbon-400">
        <p class="mt-1.5 text-xs text-carbon-400">Cuántos pesos (o reales) valía un dólar ese día. Si pagaste en dólares, dejalo vacío.</p>
        @error('cotizacion_compra')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label for="notas" class="etiqueta block">Notas <span class="normal-case tracking-normal">(opcional)</span></label>
    <textarea id="notas" name="notas" rows="3" maxlength="2000"
              placeholder="Dónde lo compraste, si tiene garantía, lo que quieras."
              class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                     placeholder-carbon-600 outline-none focus:border-carbon-400">{{ old('notas', $actual->notas ?? '') }}</textarea>
    @error('notas')<p class="mt-1.5 text-xs text-marca-500">{{ $message }}</p>@enderror
</div>
