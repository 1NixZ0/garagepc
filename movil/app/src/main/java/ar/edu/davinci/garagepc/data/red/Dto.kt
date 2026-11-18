package ar.edu.davinci.garagepc.data.red

import com.google.gson.annotations.SerializedName

/**
 * Objetos que viajan hacia y desde la API.
 *
 * Los nombres de los campos siguen los de la API, que está en español, para
 * que no haga falta traducir en el medio. Donde el nombre de Kotlin difiere
 * del JSON se usa @SerializedName.
 */

data class PedidoSesion(
    val email: String,
    val password: String,
    val dispositivo: String,
)

data class RespuestaSesion(
    val token: String,
    val usuario: UsuarioDto,
)

data class UsuarioDto(
    val id: Int,
    val nombre: String,
    val email: String,
)

data class EquipoDto(
    val id: Int,
    val nombre: String,
    val descripcion: String?,
    @SerializedName("componentes_count") val cantidadComponentes: Int?,
)

data class ComponenteDto(
    val id: Int,
    @SerializedName("equipo_id") val equipoId: Int,
    val categoria: String,
    @SerializedName("categoria_etiqueta") val categoriaEtiqueta: String,
    val modelo: String,
    val estado: String,
    @SerializedName("estado_etiqueta") val estadoEtiqueta: String,
    @SerializedName("fecha_compra") val fechaCompra: String,
    @SerializedName("antiguedad_meses") val antiguedadMeses: Int,
    @SerializedName("precio_compra") val precioCompra: Double,
    @SerializedName("moneda_compra") val monedaCompra: String,
    @SerializedName("costo_usd") val costoUsd: Double,
    @SerializedName("condicion_declarada") val condicionDeclarada: String?,
    val notas: String?,
    val catalogado: Boolean,
    val intervenciones: List<IntervencionDto>?,
)

data class IntervencionDto(
    val id: Int,
    @SerializedName("componente_id") val componenteId: Int,
    val tipo: String,
    @SerializedName("tipo_etiqueta") val tipoEtiqueta: String,
    val fecha: String,
    val descripcion: String?,
    val reciente: Boolean,
)

data class FichaComponenteDto(
    val componente: ComponenteDto,
    val rendimiento: RendimientoDto?,
)

data class RendimientoDto(
    val juego: String,
    val resolucion: String,
    @SerializedName("nivel_calidad") val nivelCalidad: String,
    val sesiones: Int,
    @SerializedName("fps_promedio") val fpsPromedio: Double,
)

data class AlertaDto(
    val id: Int,
    val tipo: String,
    val titulo: String,
    val explicacion: String,
    val advertencia: String?,
    val estado: String,
    @SerializedName("evaluada_en") val evaluadaEn: String,
    val componente: ComponenteDeAlertaDto?,
)

data class ComponenteDeAlertaDto(
    val id: Int,
    val modelo: String,
    val categoria: String,
    val equipo: String?,
)

data class CotizacionDto(
    @SerializedName("ars_por_usd") val arsPorUsd: Double,
    @SerializedName("brl_por_usd") val brlPorUsd: Double,
    val origen: String,
    @SerializedName("obtenida_en") val obtenidaEn: String,
    @SerializedName("horas_de_antiguedad") val horasDeAntiguedad: Double,
    @SerializedName("requiere_advertencia") val requiereAdvertencia: Boolean,
    val vencida: Boolean,
    val conversion: Map<String, Double>?,
)

data class PedidoIntervencion(
    val tipo: String,
    val fecha: String,
    val descripcion: String?,
)

data class PedidoSesionRendimiento(
    val juego: String,
    val resolucion: String,
    @SerializedName("nivel_calidad") val nivelCalidad: String,
    @SerializedName("fps_promedio") val fpsPromedio: Double,
    @SerializedName("temp_cpu") val tempCpu: Double?,
    @SerializedName("temp_gpu") val tempGpu: Double?,
    @SerializedName("fecha_sesion") val fechaSesion: String,
)

/** La API envuelve las colecciones en una clave "data". */
data class Envoltorio<T>(val data: T)
