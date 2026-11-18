package ar.edu.davinci.garagepc.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable

/**
 * El tema es oscuro siempre, incluso si el teléfono está en modo claro.
 *
 * No es capricho: la identidad visual de GaragePC es oscura y los prototipos
 * del proyecto están diseñados así. Un tema claro generado automáticamente
 * rompería la correspondencia entre la aplicación y su diseño.
 */
private val EsquemaOscuro = darkColorScheme(
    primary = Marca,
    onPrimary = TextoPrincipal,
    primaryContainer = MarcaOscura,
    onPrimaryContainer = TextoPrincipal,
    secondary = Aviso,
    onSecondary = Fondo,
    background = Fondo,
    onBackground = TextoPrincipal,
    surface = Superficie,
    onSurface = TextoPrincipal,
    surfaceVariant = SuperficieAlta,
    onSurfaceVariant = TextoApagado,
    outline = Borde,
    error = Marca,
    onError = TextoPrincipal,
)

@Composable
fun GaragePCTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    dynamicColor: Boolean = false,
    content: @Composable () -> Unit,
) {
    MaterialTheme(
        colorScheme = EsquemaOscuro,
        typography = Typography,
        content = content,
    )
}
