package ar.edu.davinci.garagepc.ui.pantallas

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import ar.edu.davinci.garagepc.data.red.EquipoDto
import ar.edu.davinci.garagepc.data.red.PedidoSesionRendimiento
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDateTime
import java.time.format.DateTimeFormatter

data class EstadoRendimiento(
    val cargando: Boolean = true,
    val equipos: List<EquipoDto> = emptyList(),
    val equipoElegido: EquipoDto? = null,
    val juego: String = "",
    val resolucion: String = "1080p",
    val calidad: String = "alto",
    val fps: String = "",
    val tempCpu: String = "",
    val tempGpu: String = "",
    val guardando: Boolean = false,
    val error: String? = null,
    val mensaje: String? = null,
)

/**
 * Registro de sesiones de rendimiento desde el teléfono (RF-MOV-06).
 *
 * La configuración (juego, resolución y nivel de calidad) se pide completa
 * porque es lo que define la serie contra la que después se compara: sin los
 * tres datos, la sesión no se puede promediar con ninguna otra.
 */
class RendimientoViewModel(private val repositorio: RepositorioGaragePc) : ViewModel() {

    companion object {
        val RESOLUCIONES = listOf("1080p", "1440p", "2160p")
        val CALIDADES = listOf("bajo" to "Bajo", "medio" to "Medio", "alto" to "Alto", "ultra" to "Ultra")

        private val FORMATO = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")
    }

    private val _estado = MutableStateFlow(EstadoRendimiento())
    val estado: StateFlow<EstadoRendimiento> = _estado.asStateFlow()

    init {
        cargar()
    }

    fun cargar() {
        _estado.update { it.copy(cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.equipos()
                .onSuccess { lista ->
                    _estado.update {
                        it.copy(
                            cargando = false,
                            equipos = lista,
                            equipoElegido = if (lista.size == 1) lista.first() else it.equipoElegido,
                        )
                    }
                }
                .onFailure { fallo -> _estado.update { it.copy(cargando = false, error = fallo.message) } }
        }
    }

    fun elegirEquipo(equipo: EquipoDto) = _estado.update { it.copy(equipoElegido = equipo, mensaje = null) }

    fun volver() = _estado.update { it.copy(equipoElegido = null) }

    fun cambiarJuego(valor: String) = _estado.update { it.copy(juego = valor) }

    fun cambiarResolucion(valor: String) = _estado.update { it.copy(resolucion = valor) }

    fun cambiarCalidad(valor: String) = _estado.update { it.copy(calidad = valor) }

    fun cambiarFps(valor: String) = _estado.update { it.copy(fps = soloNumeros(valor)) }

    fun cambiarTempCpu(valor: String) = _estado.update { it.copy(tempCpu = soloNumeros(valor)) }

    fun cambiarTempGpu(valor: String) = _estado.update { it.copy(tempGpu = soloNumeros(valor)) }

    private fun soloNumeros(valor: String) = valor.filter { it.isDigit() || it == '.' }

    fun guardar() {
        val actual = _estado.value
        val equipo = actual.equipoElegido ?: return

        // Validación mínima en el cliente para no gastar un viaje de red.
        // La validación que manda es la del servidor, que es la misma que
        // usa la aplicación web.
        if (actual.juego.isBlank()) {
            _estado.update { it.copy(error = "Escribí el nombre del juego.") }

            return
        }

        val fps = actual.fps.toDoubleOrNull()

        if (fps == null || fps <= 0) {
            _estado.update { it.copy(error = "Los FPS tienen que ser un número mayor a cero.") }

            return
        }

        _estado.update { it.copy(guardando = true, error = null, mensaje = null) }

        viewModelScope.launch {
            val pedido = PedidoSesionRendimiento(
                juego = actual.juego.trim(),
                resolucion = actual.resolucion,
                nivelCalidad = actual.calidad,
                fpsPromedio = fps,
                tempCpu = actual.tempCpu.toDoubleOrNull(),
                tempGpu = actual.tempGpu.toDoubleOrNull(),
                fechaSesion = LocalDateTime.now().format(FORMATO),
            )

            repositorio.registrarSesion(equipo.id, pedido)
                .onSuccess {
                    _estado.update {
                        it.copy(
                            guardando = false,
                            fps = "",
                            tempCpu = "",
                            tempGpu = "",
                            mensaje = "Sesión registrada.",
                        )
                    }
                }
                .onFailure { fallo -> _estado.update { it.copy(guardando = false, error = fallo.message) } }
        }
    }
}
