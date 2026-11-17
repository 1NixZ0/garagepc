/**
 * Autocompletado del Catálogo de Hardware.
 *
 * Sugiere modelos ya catalogados mientras el usuario escribe y, si elige uno,
 * guarda su identificador en un campo oculto. Eso es lo que después le permite
 * al Motor de Análisis conocer la gama y el año de lanzamiento de la pieza.
 *
 * Escribir un modelo que no está en el catálogo sigue siendo válido: es el
 * camino que contempla el CU-08 para hardware no catalogado. Por eso, si el
 * servidor no responde, el campo se comporta como un texto libre común.
 */

const ESPERA_MS = 200;
const MINIMO_CARACTERES = 2;
const AYUDA_LIBRE = 'Escribilo tal cual figura en la caja o en la factura.';

function montarAutocompletado(contenedor) {
    const campo = contenedor.querySelector('[data-catalogo-campo]');
    const oculto = contenedor.querySelector('[data-catalogo-id]');
    const lista = contenedor.querySelector('[data-catalogo-lista]');
    const ayuda = contenedor.querySelector('[data-catalogo-ayuda]');
    const categoria = document.querySelector('[data-catalogo-categoria]');
    const url = campo.dataset.catalogoUrl;

    let temporizador = null;
    let resultados = [];
    let resaltado = -1;

    const ocultarLista = () => {
        lista.classList.add('hidden');
        resaltado = -1;
    };

    const marcarAyuda = (texto, enCatalogo) => {
        ayuda.textContent = texto;
        ayuda.classList.toggle('text-carbon-200', enCatalogo);
        ayuda.classList.toggle('text-carbon-400', !enCatalogo);
    };

    const soltarSeleccion = () => {
        oculto.value = '';
        marcarAyuda(AYUDA_LIBRE, false);
    };

    const elegir = (item) => {
        campo.value = item.modelo;
        oculto.value = item.id;
        marcarAyuda(`En el catálogo · ${item.anio} · ${item.gama}`, true);
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

            const modelo = document.createElement('span');
            modelo.textContent = item.modelo;

            const detalle = document.createElement('span');
            detalle.className = 'shrink-0 text-xs text-carbon-400';
            detalle.textContent = `${item.anio} · ${item.gama}`;

            fila.append(modelo, detalle);

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

        if (termino.length < MINIMO_CARACTERES || !categoria) {
            resultados = [];
            ocultarLista();
            return;
        }

        const consulta = new URLSearchParams({ categoria: categoria.value, q: termino });

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

    if (categoria) {
        categoria.addEventListener('change', () => {
            soltarSeleccion();
            resultados = [];
            ocultarLista();
        });
    }

    if (oculto.value) {
        marcarAyuda('Vinculado al catálogo.', true);
    }
}

document.querySelectorAll('[data-catalogo]').forEach(montarAutocompletado);
