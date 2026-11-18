package ar.edu.davinci.garagepc.data

import ar.edu.davinci.garagepc.data.local.SesionLocal
import ar.edu.davinci.garagepc.data.red.AlertaDto
import ar.edu.davinci.garagepc.data.red.ApiGaragePc
import ar.edu.davinci.garagepc.data.red.ComponenteDto
import ar.edu.davinci.garagepc.data.red.CotizacionDto
import ar.edu.davinci.garagepc.data.red.EquipoDto
import ar.edu.davinci.garagepc.data.red.FichaComponenteDto
import ar.edu.davinci.garagepc.data.red.PedidoIntervencion
import ar.edu.davinci.garagepc.data.red.PedidoSesion
import ar.edu.davinci.garagepc.data.red.PedidoSesionRendimiento
import retrofit2.HttpException
import java.io.IOException

/**
 * Única puerta de entrada a los datos.
 *
 * Las pantallas no hablan con Retrofit: hablan con el repositorio, que
 * devuelve Result y traduce los fallos a mensajes legibles. Así ninguna
 * pantalla tiene que saber qué significa un 401 ni qué es una IOException.
 */
class RepositorioGaragePc(
    private val api: ApiGaragePc,
    private val sesion: SesionLocal,
) {

    fun haySesion(): Boolean = sesion.haySesion()

    fun nombreGuardado(): String? = sesion.nombre()

    suspend fun iniciarSesion(email: String, clave: String): Result<String> = envolver {
        val respuesta = api.iniciarSesion(PedidoSesion(email, clave, DISPOSITIVO))
        sesion.guardar(respuesta.token, respuesta.usuario.nombre)
        respuesta.usuario.nombre
    }

    suspend fun cerrarSesion() {
        runCatching { api.cerrarSesion() }
        sesion.borrar()
    }

    suspend fun equipos(): Result<List<EquipoDto>> = envolver { api.equipos().data }

    suspend fun componentes(equipoId: Int): Result<List<ComponenteDto>> =
        envolver { api.componentes(equipoId).data }

    suspend fun ficha(componenteId: Int): Result<FichaComponenteDto> =
        envolver { api.ficha(componenteId) }

    suspend fun registrarIntervencion(componenteId: Int, pedido: PedidoIntervencion): Result<Unit> =
        envolver { api.registrarIntervencion(componenteId, pedido); Unit }

    suspend fun registrarSesion(equipoId: Int, pedido: PedidoSesionRendimiento): Result<Unit> =
        envolver { api.registrarSesion(equipoId, pedido) }

    suspend fun alertas(): Result<List<AlertaDto>> = envolver { api.alertas().data }

    suspend fun cotizacion(monto: Double? = null, moneda: String? = null): Result<CotizacionDto> =
        envolver { api.cotizacion(monto, moneda) }

    /**
     * Traduce las excepciones de red a mensajes que el usuario pueda leer.
     *
     * Un 401 significa que el token dejó de valer: se borra la sesión local
     * para que la aplicación vuelva al login en lugar de quedar mostrando
     * errores en cada pantalla.
     */
    private suspend fun <T> envolver(bloque: suspend () -> T): Result<T> = try {
        Result.success(bloque())
    } catch (error: HttpException) {
        if (error.code() == 401) {
            sesion.borrar()
        }

        Result.failure(Exception(mensajeDe(error)))
    } catch (error: IOException) {
        // Se incluye la causa técnica: sin ella, un bloqueo de tráfico sin
        // cifrar y un servidor apagado producen el mismo mensaje, y son
        // problemas distintos.
        Result.failure(
            Exception(
                "No se pudo contactar al servidor. " +
                    "[${error.javaClass.simpleName}: ${error.message}]"
            )
        )
    } catch (error: Throwable) {
        Result.failure(Exception("Error inesperado. [${error.javaClass.simpleName}: ${error.message}]"))
    }

    private fun mensajeDe(error: HttpException): String = when (error.code()) {
        401 -> "La sesión expiró. Ingresá de nuevo."
        403 -> "No tenés permiso para ver eso."
        404 -> "No se encontró lo que buscabas."
        422 -> leerValidacion(error) ?: "Revisá los datos cargados."
        in 500..599 -> "El servidor tuvo un problema. Probá de nuevo en un momento."
        else -> "Ocurrió un error inesperado (${error.code()})."
    }

    /** Laravel devuelve los errores de validación bajo la clave "message". */
    private fun leerValidacion(error: HttpException): String? = runCatching {
        val cuerpo = error.response()?.errorBody()?.string() ?: return null
        Regex("\"message\"\\s*:\\s*\"([^\"]+)\"").find(cuerpo)?.groupValues?.get(1)
    }.getOrNull()

    private companion object {
        const val DISPOSITIVO = "android"
    }
}
