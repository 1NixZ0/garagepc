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
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.FilterChip
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import ar.edu.davinci.garagepc.ui.Fabrica

@Composable
fun RendimientoPantalla(modelo: RendimientoViewModel = viewModel(factory = Fabrica.rendimiento)) {
    val estado by modelo.estado.collectAsState()

    when {
        estado.cargando -> Column(
            modifier = Modifier.fillMaxSize(),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            CircularProgressIndicator(color = MaterialTheme.colorScheme.primary)
        }

        estado.equipoElegido == null -> ElegirEquipo(estado, modelo)

        else -> Formulario(estado, modelo)
    }
}

@Composable
private fun ElegirEquipo(estado: EstadoRendimiento, modelo: RendimientoViewModel) {
    if (estado.equipos.isEmpty()) {
        Column(
            modifier = Modifier.fillMaxSize().padding(32.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Text("No tenés equipos registrados.", style = MaterialTheme.typography.bodyMedium)
            TextButton(onClick = modelo::cargar) { Text("Reintentar") }
        }

        return
    }

    LazyColumn(
        modifier = Modifier.fillMaxSize().padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        item {
            Text("¿De qué equipo es la sesión?", style = MaterialTheme.typography.titleMedium)
        }

        items(estado.equipos, key = { it.id }) { equipo ->
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant),
                modifier = Modifier.fillMaxWidth().clickable { modelo.elegirEquipo(equipo) },
            ) {
                Text(
                    text = equipo.nombre,
                    style = MaterialTheme.typography.titleMedium,
                    modifier = Modifier.padding(20.dp),
                )
            }
        }
    }
}

@Composable
private fun Formulario(estado: EstadoRendimiento, modelo: RendimientoViewModel) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Text(
                text = estado.equipoElegido?.nombre.orEmpty(),
                style = MaterialTheme.typography.titleMedium,
            )

            if (estado.equipos.size > 1) {
                TextButton(onClick = modelo::volver) { Text("Cambiar") }
            }
        }

        OutlinedTextField(
            value = estado.juego,
            onValueChange = modelo::cambiarJuego,
            label = { Text("Juego") },
            singleLine = true,
            enabled = !estado.guardando,
            modifier = Modifier.fillMaxWidth(),
        )

        Text(
            text = "Resolución",
            style = MaterialTheme.typography.labelSmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )

        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            RendimientoViewModel.RESOLUCIONES.forEach { valor ->
                FilterChip(
                    selected = estado.resolucion == valor,
                    onClick = { modelo.cambiarResolucion(valor) },
                    label = { Text(valor) },
                )
            }
        }

        Text(
            text = "Calidad",
            style = MaterialTheme.typography.labelSmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )

        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            RendimientoViewModel.CALIDADES.forEach { (valor, etiqueta) ->
                FilterChip(
                    selected = estado.calidad == valor,
                    onClick = { modelo.cambiarCalidad(valor) },
                    label = { Text(etiqueta) },
                )
            }
        }

        OutlinedTextField(
            value = estado.fps,
            onValueChange = modelo::cambiarFps,
            label = { Text("FPS promedio") },
            singleLine = true,
            enabled = !estado.guardando,
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
            modifier = Modifier.fillMaxWidth(),
        )

        Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
            OutlinedTextField(
                value = estado.tempCpu,
                onValueChange = modelo::cambiarTempCpu,
                label = { Text("Temp. CPU") },
                singleLine = true,
                enabled = !estado.guardando,
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                modifier = Modifier.weight(1f),
            )

            OutlinedTextField(
                value = estado.tempGpu,
                onValueChange = modelo::cambiarTempGpu,
                label = { Text("Temp. GPU") },
                singleLine = true,
                enabled = !estado.guardando,
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                modifier = Modifier.weight(1f),
            )
        }

        if (estado.error != null) {
            Text(
                text = estado.error,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.error,
            )
        }

        if (estado.mensaje != null) {
            Text(
                text = estado.mensaje,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
            )
        }

        Button(
            onClick = modelo::guardar,
            enabled = !estado.guardando,
            modifier = Modifier.fillMaxWidth(),
        ) {
            Text(if (estado.guardando) "Guardando..." else "Registrar sesión")
        }

        Text(
            text = "La fecha y hora de la sesión se toman del teléfono en el momento de guardar.",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )
    }
}
