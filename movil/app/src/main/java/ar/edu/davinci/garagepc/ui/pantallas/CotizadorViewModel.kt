package ar.edu.davinci.garagepc.ui.pantallas

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import ar.edu.davinci.garagepc.data.red.CotizacionDto
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class EstadoCotizador(
    val cargando: Boolean = true,
    val cotizacion: CotizacionDto? = null,
    val monto: String = "",
    val moneda: String = "USD",
    val error: String? = null,
)

/**
 * Conversión multimoneda (RF-MOV-11).
 *
 * La conversión la hace el servidor y no el teléfono: así el resultado es
 * idéntico al de la web y la aplicación no necesita conocer las reglas de
 * antigüedad de la cotización.
 */
class CotizadorViewModel(private val repositorio: RepositorioGaragePc) : ViewModel() {

    companion object {
        val MONEDAS = listOf("ARS", "USD", "BRL")
    }

    private val _estado = MutableStateFlow(EstadoCotizador())
    val estado: StateFlow<EstadoCotizador> = _estado.asStateFlow()

    init {
        consultar()
    }

    fun cambiarMonto(valor: String) {
        _estado.update { it.copy(monto = valor.filter { caracter -> caracter.isDigit() || caracter == '.' }) }
    }

    fun cambiarMoneda(valor: String) = _estado.update { it.copy(moneda = valor) }

    fun consultar() {
        val actual = _estado.value

        _estado.update { it.copy(cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.cotizacion(actual.monto.toDoubleOrNull(), actual.moneda)
                .onSuccess { dato -> _estado.update { it.copy(cargando = false, cotizacion = dato) } }
                .onFailure { fallo -> _estado.update { it.copy(cargando = false, error = fallo.message) } }
        }
    }
}
