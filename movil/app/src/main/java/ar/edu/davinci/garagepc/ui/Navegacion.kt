package ar.edu.davinci.garagepc.ui

import androidx.compose.runtime.Composable
import androidx.compose.runtime.remember
import androidx.compose.ui.platform.LocalContext
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import ar.edu.davinci.garagepc.GaragePcApp
import ar.edu.davinci.garagepc.ui.pantallas.ComponentePantalla
import ar.edu.davinci.garagepc.ui.pantallas.GarajePantalla
import ar.edu.davinci.garagepc.ui.pantallas.LoginPantalla

/**
 * Rutas de la aplicación.
 *
 * El destino inicial depende de si hay un token guardado: si lo hay, se
 * entra directo al garaje sin volver a pedir la contraseña, que es lo que
 * pide RF-MOV-01.
 */
object Rutas {
    const val LOGIN = "login"
    const val GARAJE = "garaje"
    const val COMPONENTE = "componente"
}

@Composable
fun Navegacion() {
    val contexto = LocalContext.current
    val app = remember { contexto.applicationContext as GaragePcApp }
    val navegador = rememberNavController()

    val inicio = if (app.repositorio.haySesion()) Rutas.GARAJE else Rutas.LOGIN

    NavHost(navController = navegador, startDestination = inicio) {

        composable(Rutas.LOGIN) {
            LoginPantalla(
                alIngresar = {
                    navegador.navigate(Rutas.GARAJE) {
                        popUpTo(Rutas.LOGIN) { inclusive = true }
                    }
                },
            )
        }

        composable(Rutas.GARAJE) {
            GarajePantalla(
                alCerrarSesion = {
                    navegador.navigate(Rutas.LOGIN) {
                        popUpTo(Rutas.GARAJE) { inclusive = true }
                    }
                },
                alAbrirComponente = { id -> navegador.navigate("${Rutas.COMPONENTE}/$id") },
            )
        }

        composable(
            route = "${Rutas.COMPONENTE}/{id}",
            arguments = listOf(navArgument("id") { type = NavType.IntType }),
        ) { entrada ->
            ComponentePantalla(
                componenteId = entrada.arguments?.getInt("id") ?: 0,
                alVolver = { navegador.popBackStack() },
            )
        }
    }
}
