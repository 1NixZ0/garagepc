package ar.edu.davinci.garagepc

import android.app.Application
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import ar.edu.davinci.garagepc.data.local.SesionLocal
import ar.edu.davinci.garagepc.data.red.ClienteApi

/**
 * Punto de armado de la aplicación.
 *
 * El repositorio y sus dependencias se construyen una sola vez, acá, en
 * lugar de que cada pantalla arme su propio cliente HTTP. No se usa una
 * librería de inyección de dependencias: para una aplicación de este tamaño
 * sumaría una herramienta más sin resolver un problema que exista.
 */
class GaragePcApp : Application() {

    val sesion: SesionLocal by lazy { SesionLocal(this) }

    val repositorio: RepositorioGaragePc by lazy {
        RepositorioGaragePc(ClienteApi.crear(sesion), sesion)
    }
}
