package ar.edu.davinci.garagepc.ui.pantallas

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import ar.edu.davinci.garagepc.data.red.FichaComponenteDto
import ar.edu.davinci.garagepc.data.red.PedidoIntervencion
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.time.LocalDate

data class EstadoComponente(
    val cargando: Boolean = true,
    val ficha: FichaComponenteDto? = null,
    val error: String? = null,
    val formularioAbierto: Boolean = false,
    val tipo: String = "limpieza",
    val fecha: LocalDate = LocalDate.now(),
    val descripcion: String = "",
    val guardando: Boolean = false,
    val errorFormulario: String? = null,
    val mensaje: String? = null,
)

class ComponenteViewModel(private val repositorio: RepositorioGaragePc) : ViewModel() {

    companion object {
        /**
         * Espejo de Intervencion::TIPOS del servidor. Está duplicado a
         * propósito y no se descarga: son seis valores que cambian una vez
         * cada nunca, y pedirlos por red agregaría una llamada y un estado
         * de carga más a una pantalla que se usa con el teléfono en una mano.
         */
        val TIPOS = listOf(
            "limpieza" to "Limpieza",
            "pasta_termica" to "Pasta térmica",
            "inspeccion" to "Inspección",
            "upgrade" to "Upgrade",
            "reparacion" to "Reparación",
            "otro" to "Otro",
        )
    }

    private val _estado = MutableStateFlow(EstadoComponente())
    val estado: StateFlow<EstadoComponente> = _estado.asStateFlow()

    fun cargar(componenteId: Int) {
        _estado.update { it.copy(cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.ficha(componenteId)
                .onSuccess { ficha -> _estado.update { it.copy(cargando = false, ficha = ficha) } }
                .onFailure { fallo -> _estado.update { it.copy(cargando = false, error = fallo.message) } }
        }
    }

    fun abrirFormulario() = _estado.update {
        it.copy(formularioAbierto = true, errorFormulario = null, mensaje = null)
    }

    fun cerrarFormulario() = _estado.update {
        it.copy(formularioAbierto = false, descripcion = "", errorFormulario = null)
    }

    fun cambiarTipo(valor: String) = _estado.update { it.copy(tipo = valor) }

    fun cambiarDescripcion(valor: String) = _estado.update { it.copy(descripcion = valor) }

    /** La fecha nunca puede pasar de hoy: el servidor lo rechazaría igual. */
    fun diaAnterior() = _estado.update { it.copy(fecha = it.fecha.minusDays(1)) }

    fun diaSiguiente() = _estado.update {
        val proximo = it.fecha.plusDays(1)

        if (proximo.isAfter(LocalDate.now())) it else it.copy(fecha = proximo)
    }

    fun guardar(componenteId: Int) {
        val actual = _estado.value

        _estado.update { it.copy(guardando = true, errorFormulario = null) }

        viewModelScope.launch {
            val pedido = PedidoIntervencion(
                tipo = actual.tipo,
                fecha = actual.fecha.toString(),
                descripcion = actual.descripcion.trim().ifBlank { null },
            )

            repositorio.registrarIntervencion(componenteId, pedido)
                .onSuccess {
                    _estado.update {
                        it.copy(
                            guardando = false,
                            formularioAbierto = false,
                            descripcion = "",
                            fecha = LocalDate.now(),
                            mensaje = "Intervención registrada.",
                        )
                    }

                    // Se recarga para que la bitácora muestre el registro nuevo
                    // con los datos tal como los guardó el servidor.
                    cargar(componenteId)
                }
                .onFailure { fallo ->
                    _estado.update { it.copy(guardando = false, errorFormulario = fallo.message) }
                }
        }
    }
}
