/**
 * Autocompletado contra los catálogos del sistema.
 *
 * Sirve para el Catálogo de Hardware y para el Catálogo de Juegos: el
 * contenedor declara a qué URL consultar y, si corresponde, de qué otro campo
 * depende la búsqueda (la categoría del componente, por ejemplo).
 *
 * Cuando el usuario elige una opción se guarda su identificador en un campo
 * oculto; eso es lo que después le permite al sistema conocer la gama, el año
 * o la exigencia de lo que cargó. Escribir algo que no está catalogado sigue
 * siendo válido, así que si el servidor no responde el campo se comporta como
 * un texto libre común.
 */

const ESPERA_MS = 200;
const MINIMO_CARACTERES = 2;

function montarAutocompletado(contenedor) {
    const campo = contenedor.querySelector('[data-auto-campo]');
    const oculto = contenedor.querySelector('[data-auto-id]');
    const lista = contenedor.querySelector('[data-auto-lista]');
    const ayuda = contenedor.querySelector('[data-auto-ayuda]');

    if (!campo || !oculto || !lista) {
        return;
    }

    const url = contenedor.dataset.autoUrl;
    const ayudaLibre = ayuda ? ayuda.textContent.trim() : '';
    const dependencia = contenedor.dataset.autoDepende
        ? document.querySelector(contenedor.dataset.autoDepende)
        : null;
    const nombreParametro = contenedor.dataset.autoParametro || null;

    let temporizador = null;
    let resultados = [];
    let resaltado = -1;

    const ocultarLista = () => {
        lista.classList.add('hidden');
        resaltado = -1;
    };

    const marcarAyuda = (texto, catalogado) => {
        if (!ayuda) {
            return;
        }
        ayuda.textContent = texto;
        ayuda.classList.toggle('text-carbon-200', catalogado);
        ayuda.classList.toggle('text-carbon-400', !catalogado);
    };

    const soltarSeleccion = () => {
        oculto.value = '';
        marcarAyuda(ayudaLibre, false);
    };

    const elegir = (item) => {
        campo.value = item.texto;
        oculto.value = item.id;
        marcarAyuda(`En el catálogo · ${item.detalle}`, true);
        ocultarLista();
    };

    const pintarLista = () => {
        if (resultados.length === 0) {
            ocultarLista();
            return;
        }

        lista.replaceChildren();

        resultados.forEach((item, indice) => {
            const fila = document.createElement('li');
            fila.className = [
                'flex cursor-pointer items-baseline justify-between gap-3 px-3 py-2 text-sm',
                indice === resaltado ? 'bg-carbon-700 text-white' : 'text-carbon-200',
            ].join(' ');

            const texto = document.createElement('span');
            texto.textContent = item.texto;

            const detalle = document.createElement('span');
            detalle.className = 'shrink-0 text-xs text-carbon-400';
            detalle.textContent = item.detalle;

            fila.append(texto, detalle);

            // mousedown y no click: el blur del campo llega antes que el click
            // y cerraria la lista justo cuando el usuario intenta elegir.
            fila.addEventListener('mousedown', (evento) => {
                evento.preventDefault();
                elegir(item);
            });

            lista.appendChild(fila);
        });

        lista.classList.remove('hidden');
    };

    const buscar = async () => {
        const termino = campo.value.trim();

        if (termino.length < MINIMO_CARACTERES) {
            resultados = [];
            ocultarLista();
            return;
        }

        const consulta = new URLSearchParams({ q: termino });

        if (dependencia && nombreParametro) {
            consulta.set(nombreParametro, dependencia.value);
        }

        try {
            const respuesta = await fetch(`${url}?${consulta}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!respuesta.ok) {
                ocultarLista();
                return;
            }

            resultados = await respuesta.json();
            resaltado = -1;
            pintarLista();
        } catch (error) {
            ocultarLista();
        }
    };

    campo.addEventListener('input', () => {
        soltarSeleccion();
        clearTimeout(temporizador);
        temporizador = setTimeout(buscar, ESPERA_MS);
    });

    campo.addEventListener('keydown', (evento) => {
        if (lista.classList.contains('hidden')) {
            return;
        }

        if (evento.key === 'ArrowDown') {
            evento.preventDefault();
            resaltado = Math.min(resaltado + 1, resultados.length - 1);
            pintarLista();
        } else if (evento.key === 'ArrowUp') {
            evento.preventDefault();
            resaltado = Math.max(resaltado - 1, 0);
            pintarLista();
        } else if (evento.key === 'Enter' && resaltado >= 0) {
            evento.preventDefault();
            elegir(resultados[resaltado]);
        } else if (evento.key === 'Escape') {
            ocultarLista();
        }
    });

    campo.addEventListener('blur', () => setTimeout(ocultarLista, 120));

    if (dependencia) {
        dependencia.addEventListener('change', () => {
            soltarSeleccion();
            resultados = [];
            ocultarLista();
        });
    }

    if (oculto.value) {
        marcarAyuda('Vinculado al catálogo.', true);
    }
}

document.querySelectorAll('[data-auto]').forEach(montarAutocompletado);
