package ar.edu.davinci.garagepc.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import ar.edu.davinci.garagepc.GaragePcApp
import ar.edu.davinci.garagepc.ui.pantallas.AlertasPantalla
import ar.edu.davinci.garagepc.ui.pantallas.ComponentePantalla
import ar.edu.davinci.garagepc.ui.pantallas.CotizadorPantalla
import ar.edu.davinci.garagepc.ui.pantallas.GarajePantalla
import ar.edu.davinci.garagepc.ui.pantallas.LoginPantalla
import ar.edu.davinci.garagepc.ui.pantallas.RendimientoPantalla

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
    const val RENDIMIENTO = "rendimiento"
    const val ALERTAS = "alertas"
    const val COTIZADOR = "cotizador"
}

private data class Seccion(val ruta: String, val etiqueta: String)

private val SECCIONES = listOf(
    Seccion(Rutas.GARAJE, "Garaje"),
    Seccion(Rutas.RENDIMIENTO, "Sesión"),
    Seccion(Rutas.ALERTAS, "Avisos"),
    Seccion(Rutas.COTIZADOR, "Cotizar"),
)

@Composable
fun Navegacion() {
    val contexto = LocalContext.current
    val app = remember { contexto.applicationContext as GaragePcApp }
    val navegador = rememberNavController()

    val inicio = if (app.repositorio.haySesion()) Rutas.GARAJE else Rutas.LOGIN

    val entrada by navegador.currentBackStackEntryAsState()
    val rutaActual = entrada?.destination?.route

    // La barra solo aparece en las cuatro secciones principales: ni en el
    // login ni en la ficha de un componente, que son pantallas de paso.
    val mostrarBarra = SECCIONES.any { it.ruta == rutaActual }

    Scaffold(
        bottomBar = {
            if (mostrarBarra) {
                BarraInferior(rutaActual, navegador)
            }
        },
    ) { relleno ->
        NavHost(
            navController = navegador,
            startDestination = inicio,
            modifier = Modifier.padding(relleno),
        ) {

            composable(Rutas.LOGIN) {
                LoginPantalla(
                    alIngresar = {
                        navegador.navigate(Rutas.GARAJE) {
                            popUpTo(0) { inclusive = true }
                        }
                    },
                )
            }

            composable(Rutas.GARAJE) {
                GarajePantalla(
                    alCerrarSesion = {
                        navegador.navigate(Rutas.LOGIN) {
                            popUpTo(0) { inclusive = true }
                        }
                    },
                    alAbrirComponente = { id -> navegador.navigate("${Rutas.COMPONENTE}/$id") },
                )
            }

            composable(
                route = "${Rutas.COMPONENTE}/{id}",
                arguments = listOf(navArgument("id") { type = NavType.IntType }),
            ) { destino ->
                ComponentePantalla(
                    componenteId = destino.arguments?.getInt("id") ?: 0,
                    alVolver = { navegador.popBackStack() },
                )
            }

            composable(Rutas.RENDIMIENTO) { RendimientoPantalla() }

            composable(Rutas.ALERTAS) { AlertasPantalla() }

            composable(Rutas.COTIZADOR) { CotizadorPantalla() }
        }
    }
}

/**
 * Barra de secciones hecha con texto y no con iconos.
 *
 * Los iconos de Material viajan en un artefacto aparte que la aplicación no
 * declara; cuatro etiquetas cortas resuelven lo mismo sin sumar una
 * dependencia, y además se leen sin tener que adivinar el dibujo.
 */
@Composable
private fun BarraInferior(rutaActual: String?, navegador: NavHostController) {
    Surface(color = MaterialTheme.colorScheme.surface) {
        Row(
            modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
            horizontalArrangement = Arrangement.SpaceEvenly,
        ) {
            SECCIONES.forEach { seccion ->
                val activa = seccion.ruta == rutaActual

                TextButton(
                    onClick = {
                        if (!activa) {
                            navegador.navigate(seccion.ruta) {
                                // Evita apilar una copia nueva de la sección
                                // cada vez que se toca la barra.
                                popUpTo(navegador.graph.findStartDestination().id) { saveState = true }
                                launchSingleTop = true
                                restoreState = true
                            }
                        }
                    },
                ) {
                    Text(
                        text = seccion.etiqueta,
                        style = MaterialTheme.typography.labelLarge,
                        fontWeight = if (activa) FontWeight.SemiBold else FontWeight.Normal,
                        color = if (activa) {
                            MaterialTheme.colorScheme.primary
                        } else {
                            MaterialTheme.colorScheme.onSurfaceVariant
                        },
                    )
                }
            }
        }
    }
}
