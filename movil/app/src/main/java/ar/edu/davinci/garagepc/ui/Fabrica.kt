package ar.edu.davinci.garagepc.ui

import androidx.lifecycle.ViewModelProvider.AndroidViewModelFactory.Companion.APPLICATION_KEY
import androidx.lifecycle.viewmodel.initializer
import androidx.lifecycle.viewmodel.viewModelFactory
import ar.edu.davinci.garagepc.GaragePcApp
import ar.edu.davinci.garagepc.ui.pantallas.ComponenteViewModel
import ar.edu.davinci.garagepc.ui.pantallas.GarajeViewModel
import ar.edu.davinci.garagepc.ui.pantallas.LoginViewModel

/**
 * Construcción de los modelos de vista con el repositorio ya inyectado.
 *
 * Evita que cada pantalla tenga que buscar la aplicación por su cuenta y
 * mantiene a los modelos de vista sin conocer a Android: reciben un
 * repositorio y nada más, lo que los vuelve probables sin un emulador.
 */
object Fabrica {

    val login = viewModelFactory {
        initializer {
            val app = this[APPLICATION_KEY] as GaragePcApp
            LoginViewModel(app.repositorio)
        }
    }

    val garaje = viewModelFactory {
        initializer {
            val app = this[APPLICATION_KEY] as GaragePcApp
            GarajeViewModel(app.repositorio)
        }
    }

    val componente = viewModelFactory {
        initializer {
            val app = this[APPLICATION_KEY] as GaragePcApp
            ComponenteViewModel(app.repositorio)
        }
    }
}
