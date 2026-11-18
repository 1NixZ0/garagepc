package ar.edu.davinci.garagepc.ui.pantallas

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import ar.edu.davinci.garagepc.data.red.ComponenteDto
import ar.edu.davinci.garagepc.data.red.EquipoDto
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class EstadoGaraje(
    val cargando: Boolean = true,
    val equipos: List<EquipoDto> = emptyList(),
    val equipoElegido: EquipoDto? = null,
    val componentes: List<ComponenteDto> = emptyList(),
    val error: String? = null,
    val salio: Boolean = false,
)

class GarajeViewModel(private val repositorio: RepositorioGaragePc) : ViewModel() {

    private val _estado = MutableStateFlow(EstadoGaraje())
    val estado: StateFlow<EstadoGaraje> = _estado.asStateFlow()

    val nombre: String? get() = repositorio.nombreGuardado()

    init {
        cargar()
    }

    fun cargar() {
        _estado.update { it.copy(cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.equipos()
                .onSuccess { equipos ->
                    _estado.update { it.copy(cargando = false, equipos = equipos) }

                    // Con un solo equipo se abre directo: pedirle al usuario
                    // que elija entre una única opción no aporta nada.
                    if (equipos.size == 1) {
                        elegir(equipos.first())
                    }
                }
                .onFailure { fallo ->
                    _estado.update { it.copy(cargando = false, error = fallo.message) }
                }
        }
    }

    fun elegir(equipo: EquipoDto) {
        _estado.update { it.copy(equipoElegido = equipo, cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.componentes(equipo.id)
                .onSuccess { lista ->
                    _estado.update { it.copy(cargando = false, componentes = lista) }
                }
                .onFailure { fallo ->
                    _estado.update { it.copy(cargando = false, error = fallo.message) }
                }
        }
    }

    fun volver() {
        _estado.update { it.copy(equipoElegido = null, componentes = emptyList()) }
    }

    fun cerrarSesion() {
        viewModelScope.launch {
            repositorio.cerrarSesion()
            _estado.update { it.copy(salio = true) }
        }
    }
}
