package ar.edu.davinci.garagepc.ui.pantallas

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import ar.edu.davinci.garagepc.data.red.AlertaDto
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class EstadoAlertas(
    val cargando: Boolean = true,
    val alertas: List<AlertaDto> = emptyList(),
    val error: String? = null,
)

/**
 * Alertas activas del usuario (RF-MOV-10).
 *
 * La aplicación no evalúa nada: lee lo que dejó la tarea programada del
 * servidor, con el fundamento ya redactado. Así el teléfono y la web dicen
 * exactamente lo mismo, que es lo que pide el requisito.
 */
class AlertasViewModel(private val repositorio: RepositorioGaragePc) : ViewModel() {

    private val _estado = MutableStateFlow(EstadoAlertas())
    val estado: StateFlow<EstadoAlertas> = _estado.asStateFlow()

    init {
        cargar()
    }

    fun cargar() {
        _estado.update { it.copy(cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.alertas()
                .onSuccess { lista -> _estado.update { it.copy(cargando = false, alertas = lista) } }
                .onFailure { fallo -> _estado.update { it.copy(cargando = false, error = fallo.message) } }
        }
    }
}
