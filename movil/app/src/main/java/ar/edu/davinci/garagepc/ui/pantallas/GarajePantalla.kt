package ar.edu.davinci.garagepc.ui.pantallas

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import ar.edu.davinci.garagepc.data.red.ComponenteDto
import ar.edu.davinci.garagepc.data.red.EquipoDto
import ar.edu.davinci.garagepc.ui.Fabrica

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GarajePantalla(
    alCerrarSesion: () -> Unit,
    modelo: GarajeViewModel = viewModel(factory = Fabrica.garaje),
) {
    val estado by modelo.estado.collectAsState()

    LaunchedEffect(estado.salio) {
        if (estado.salio) {
            alCerrarSesion()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = estado.equipoElegido?.nombre ?: "Mi garaje",
                        fontWeight = FontWeight.SemiBold,
                    )
                },
                navigationIcon = {
                    // Se usa texto y no un icono a propósito: los iconos de
                    // Material viajan en un artefacto aparte, y sumar una
                    // dependencia entera por una flecha no se justifica.
                    if (estado.equipoElegido != null && estado.equipos.size > 1) {
                        TextButton(onClick = modelo::volver) {
                            Text("Volver")
                        }
                    }
                },
                actions = {
                    TextButton(onClick = modelo::cerrarSesion) {
                        Text("Salir")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                ),
            )
        },
    ) { relleno ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(relleno)
                .padding(horizontal = 16.dp),
        ) {
            when {
                estado.cargando -> Cargando()

                estado.error != null -> Aviso(estado.error.orEmpty(), modelo::cargar)

                estado.equipoElegido == null -> ListaDeEquipos(estado.equipos, modelo::elegir)

                else -> ListaDeComponentes(estado.componentes)
            }
        }
    }
}

@Composable
private fun Cargando() {
    Column(
        modifier = Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        CircularProgressIndicator(color = MaterialTheme.colorScheme.primary)
    }
}

@Composable
private fun Aviso(mensaje: String, alReintentar: () -> Unit) {
    Column(
        modifier = Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text(
            text = mensaje,
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )
        TextButton(onClick = alReintentar, modifier = Modifier.padding(top = 8.dp)) {
            Text("Reintentar")
        }
    }
}

@Composable
private fun ListaDeEquipos(equipos: List<EquipoDto>, alElegir: (EquipoDto) -> Unit) {
    if (equipos.isEmpty()) {
        Aviso("Todavía no registraste ningún equipo. Cargalo desde la web.") {}

        return
    }

    LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
        items(equipos, key = { it.id }) { equipo ->
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant),
                modifier = Modifier
                    .fillMaxWidth()
                    .clickable { alElegir(equipo) },
            ) {
                Column(modifier = Modifier.padding(20.dp)) {
                    Text(equipo.nombre, style = MaterialTheme.typography.titleMedium)
                    Text(
                        text = "${equipo.cantidadComponentes ?: 0} componentes",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.padding(top = 6.dp),
                    )
                }
            }
        }
    }
}

@Composable
private fun ListaDeComponentes(componentes: List<ComponenteDto>) {
    if (componentes.isEmpty()) {
        Aviso("Este equipo no tiene componentes cargados.") {}

        return
    }

    LazyColumn(verticalArrangement = Arrangement.spacedBy(10.dp)) {
        items(componentes, key = { it.id }) { componente ->
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant),
                modifier = Modifier.fillMaxWidth(),
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        Text(
                            text = componente.categoriaEtiqueta,
                            style = MaterialTheme.typography.labelSmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                        Text(
                            text = componente.estadoEtiqueta,
                            style = MaterialTheme.typography.labelSmall,
                            color = if (componente.estado == "en_reparacion") {
                                MaterialTheme.colorScheme.secondary
                            } else {
                                MaterialTheme.colorScheme.onSurfaceVariant
                            },
                        )
                    }

                    Text(
                        text = componente.modelo,
                        style = MaterialTheme.typography.titleMedium,
                        modifier = Modifier.padding(top = 4.dp),
                    )

                    Text(
                        text = "${componente.antiguedadMeses} meses · USD ${componente.costoUsd}",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.padding(top = 6.dp),
                    )
                }
            }
        }
    }
}
