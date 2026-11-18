package ar.edu.davinci.garagepc.ui.pantallas

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import ar.edu.davinci.garagepc.data.RepositorioGaragePc
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class EstadoLogin(
    val email: String = "",
    val clave: String = "",
    val cargando: Boolean = false,
    val error: String? = null,
    val ingreso: Boolean = false,
)

class LoginViewModel(private val repositorio: RepositorioGaragePc) : ViewModel() {

    private val _estado = MutableStateFlow(EstadoLogin())
    val estado: StateFlow<EstadoLogin> = _estado.asStateFlow()

    fun cambiarEmail(valor: String) = _estado.update { it.copy(email = valor, error = null) }

    fun cambiarClave(valor: String) = _estado.update { it.copy(clave = valor, error = null) }

    fun ingresar() {
        val actual = _estado.value

        if (actual.email.isBlank() || actual.clave.isBlank()) {
            _estado.update { it.copy(error = "Completá el correo y la contraseña.") }

            return
        }

        _estado.update { it.copy(cargando = true, error = null) }

        viewModelScope.launch {
            repositorio.iniciarSesion(actual.email.trim(), actual.clave)
                .onSuccess { _estado.update { estado -> estado.copy(cargando = false, ingreso = true) } }
                .onFailure { fallo ->
                    _estado.update { it.copy(cargando = false, error = fallo.message) }
                }
        }
    }
}
