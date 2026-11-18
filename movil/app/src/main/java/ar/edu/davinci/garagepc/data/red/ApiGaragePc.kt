package ar.edu.davinci.garagepc.data.red

import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

/**
 * Contrato de la API REST de GaragePC.
 *
 * Cada método se corresponde con una ruta de routes/api.php del servidor.
 * Retrofit genera la implementación a partir de esta interfaz.
 */
interface ApiGaragePc {

    @POST("sesion")
    suspend fun iniciarSesion(@Body pedido: PedidoSesion): RespuestaSesion

    @DELETE("sesion")
    suspend fun cerrarSesion()

    @GET("perfil")
    suspend fun perfil(): UsuarioDto

    @GET("equipos")
    suspend fun equipos(): Envoltorio<List<EquipoDto>>

    @GET("equipos/{id}/componentes")
    suspend fun componentes(@Path("id") equipoId: Int): Envoltorio<List<ComponenteDto>>

    @GET("componentes/{id}")
    suspend fun ficha(@Path("id") componenteId: Int): FichaComponenteDto

    @POST("componentes/{id}/intervenciones")
    suspend fun registrarIntervencion(
        @Path("id") componenteId: Int,
        @Body pedido: PedidoIntervencion,
    ): IntervencionDto

    @POST("equipos/{id}/sesiones")
    suspend fun registrarSesion(
        @Path("id") equipoId: Int,
        @Body pedido: PedidoSesionRendimiento,
    )

    @GET("alertas")
    suspend fun alertas(): Envoltorio<List<AlertaDto>>

    @GET("cotizacion")
    suspend fun cotizacion(
        @Query("monto") monto: Double? = null,
        @Query("moneda") moneda: String? = null,
    ): CotizacionDto
}
