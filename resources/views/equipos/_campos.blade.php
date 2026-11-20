<div>
    <label for="nombre" class="etiqueta block">Nombre</label>
    <input id="nombre" name="nombre" type="text" required maxlength="100"
           value="{{ old('nombre', $equipo->nombre ?? '') }}"
           placeholder="La Todoterreno"
           class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                  placeholder-carbon-600 outline-none focus:border-carbon-400">
    @error('nombre')
        <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="descripcion" class="etiqueta block">Descripción <span class="normal-case tracking-normal">(opcional)</span></label>
    <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000"
              placeholder="Para qué la usás, dónde está, lo que quieras recordar."
              class="mt-1.5 w-full rounded-lg border border-carbon-700 bg-carbon-900 px-3 py-2 text-sm
                     placeholder-carbon-600 outline-none focus:border-carbon-400">{{ old('descripcion', $equipo->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <p class="mt-1.5 text-xs text-marca-400">{{ $message }}</p>
    @enderror
</div>
