package ar.edu.davinci.garagepc.ui.pantallas

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FilterChip
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
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
import ar.edu.davinci.garagepc.data.red.IntervencionDto
import ar.edu.davinci.garagepc.ui.Fabrica
import java.time.format.DateTimeFormatter

/**
 * Ficha del componente con su bitácora y el alta de intervenciones
 * (RF-MOV-03 y RF-MOV-04).
 */
@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ComponentePantalla(
    componenteId: Int,
    alVolver: () -> Unit,
    modelo: ComponenteViewModel = viewModel(factory = Fabrica.componente),
) {
    val estado by modelo.estado.collectAsState()

    LaunchedEffect(componenteId) {
        modelo.cargar(componenteId)
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = estado.ficha?.componente?.modelo ?: "Componente",
                        fontWeight = FontWeight.SemiBold,
                    )
                },
                navigationIcon = {
                    TextButton(onClick = alVolver) { Text("Volver") }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                ),
            )
        },
    ) { relleno ->
        when {
            estado.cargando && estado.ficha == null -> Column(
                modifier = Modifier.fillMaxSize().padding(relleno),
                verticalArrangement = Arrangement.Center,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                CircularProgressIndicator(color = MaterialTheme.colorScheme.primary)
            }

            estado.error != null -> Column(
                modifier = Modifier.fillMaxSize().padding(relleno).padding(24.dp),
                verticalArrangement = Arrangement.Center,
                horizontalAlignment = Alignment.CenterHorizontally,
            ) {
                Text(estado.error.orEmpty(), style = MaterialTheme.typography.bodyMedium)
                TextButton(onClick = { modelo.cargar(componenteId) }) { Text("Reintentar") }
            }

            else -> Contenido(
                componenteId = componenteId,
                estado = estado,
                modelo = modelo,
                modifier = Modifier.padding(relleno),
            )
        }
    }
}

@Composable
private fun Contenido(
    componenteId: Int,
    estado: EstadoComponente,
    modelo: ComponenteViewModel,
    modifier: Modifier = Modifier,
) {
    val ficha = estado.ficha ?: return
    val componente = ficha.componente
    val formato = DateTimeFormatter.ofPattern("dd/MM/yyyy")

    LazyColumn(
        modifier = modifier.fillMaxSize().padding(horizontal = 16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        item {
            Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Dato("Categoría", componente.categoriaEtiqueta)
                    Dato("Estado", componente.estadoEtiqueta)
                    Dato("Antigüedad", "${componente.antiguedadMeses} meses")
                    Dato("Costo", "USD ${componente.costoUsd}")
                    Dato("Catálogo", if (componente.catalogado) "Vinculado" else "Fuera del catálogo")

                    if (componente.notas != null) {
                        Text(
                            text = componente.notas,
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            modifier = Modifier.padding(top = 12.dp),
                        )
                    }
                }
            }
        }

        if (ficha.rendimiento != null) {
            item {
                Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = "Rendimiento del equipo",
                            style = MaterialTheme.typography.labelSmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                        Text(
                            text = "${ficha.rendimiento.fpsPromedio} fps",
                            style = MaterialTheme.typography.headlineSmall,
                            modifier = Modifier.padding(top = 4.dp),
                        )
                        Text(
                            text = "${ficha.rendimiento.juego} · ${ficha.rendimiento.resolucion} · " +
                                "calidad ${ficha.rendimiento.nivelCalidad} · ${ficha.rendimiento.sesiones} sesiones",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                        )
                    }
                }
            }
        }

        item {
            if (estado.formularioAbierto) {
                Formulario(componenteId, estado, modelo)
            } else {
                Button(
                    onClick = modelo::abrirFormulario,
                    modifier = Modifier.fillMaxWidth(),
                ) {
                    Text("Registrar mantenimiento")
                }
            }
        }

        if (estado.mensaje != null && !estado.formularioAbierto) {
            item {
                Text(
                    text = estado.mensaje,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }
        }

        item {
            Text(
                text = "Bitácora",
                style = MaterialTheme.typography.titleMedium,
                modifier = Modifier.padding(top = 8.dp),
            )
        }

        val intervenciones = componente.intervenciones.orEmpty()

        if (intervenciones.isEmpty()) {
            item {
                Text(
                    text = "Todavía no hay intervenciones registradas en esta pieza.",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                )
            }
        } else {
            items(intervenciones, key = { it.id }) { intervencion ->
                Registro(intervencion, formato)
            }
        }

        item { Text("") }
    }
}

@Composable
private fun Formulario(
    componenteId: Int,
    estado: EstadoComponente,
    modelo: ComponenteViewModel,
) {
    val formato = DateTimeFormatter.ofPattern("dd/MM/yyyy")

    Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)) {
        Column(modifier = Modifier.padding(16.dp)) {
            Text("Nueva intervención", style = MaterialTheme.typography.titleSmall)

            LazyRow(
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                modifier = Modifier.padding(top = 12.dp),
            ) {
                items(ComponenteViewModel.TIPOS) { (valor, etiqueta) ->
                    FilterChip(
                        selected = estado.tipo == valor,
                        onClick = { modelo.cambiarTipo(valor) },
                        label = { Text(etiqueta) },
                    )
                }
            }

            // Selector de fecha sin calendario: en el taller, lo que se
            // registra es casi siempre de hoy o de ayer, y dos botones
            // resuelven eso sin abrir un diálogo.
            Row(
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                modifier = Modifier.padding(top = 16.dp),
            ) {
                OutlinedButton(onClick = modelo::diaAnterior) { Text("−1 día") }

                Text(
                    text = estado.fecha.format(formato),
                    style = MaterialTheme.typography.bodyMedium,
                )

                OutlinedButton(onClick = modelo::diaSiguiente) { Text("+1 día") }
            }

            OutlinedTextField(
                value = estado.descripcion,
                onValueChange = modelo::cambiarDescripcion,
                label = { Text("Qué hiciste (opcional)") },
                enabled = !estado.guardando,
                modifier = Modifier.fillMaxWidth().padding(top = 16.dp),
            )

            if (estado.errorFormulario != null) {
                Text(
                    text = estado.errorFormulario,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error,
                    modifier = Modifier.padding(top = 8.dp),
                )
            }

            Row(
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                verticalAlignment = Alignment.CenterVertically,
                modifier = Modifier.padding(top = 16.dp),
            ) {
                Button(
                    onClick = { modelo.guardar(componenteId) },
                    enabled = !estado.guardando,
                ) {
                    if (estado.guardando) {
                        CircularProgressIndicator(
                            modifier = Modifier.size(18.dp),
                            strokeWidth = 2.dp,
                            color = MaterialTheme.colorScheme.onPrimary,
                        )
                    } else {
                        Text("Guardar")
                    }
                }

                TextButton(onClick = modelo::cerrarFormulario, enabled = !estado.guardando) {
                    Text("Cancelar")
                }
            }
        }
    }
}

@Composable
private fun Registro(intervencion: IntervencionDto, formato: DateTimeFormatter) {
    Card(colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)) {
        Column(modifier = Modifier.padding(14.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
            ) {
                Text(intervencion.tipoEtiqueta, style = MaterialTheme.typography.titleSmall)
                Text(
                    text = intervencion.fecha,
                    style = MaterialTheme.typography.labelSmall,
                    color = if (intervencion.reciente) {
                        MaterialTheme.colorScheme.primary
                    } else {
                        MaterialTheme.colorScheme.onSurfaceVariant
                    },
                )
            }

            if (intervencion.descripcion != null) {
                Text(
                    text = intervencion.descripcion,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.padding(top = 4.dp),
                )
            }
        }
    }
}

@Composable
private fun Dato(etiqueta: String, valor: String) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(vertical = 3.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
    ) {
        Text(
            text = etiqueta,
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )
        Text(text = valor, style = MaterialTheme.typography.bodyMedium)
    }
}
