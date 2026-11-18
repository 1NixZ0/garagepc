package ar.edu.davinci.garagepc.ui.pantallas

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import ar.edu.davinci.garagepc.ui.Fabrica

@Composable
fun AlertasPantalla(modelo: AlertasViewModel = viewModel(factory = Fabrica.alertas)) {
    val estado by modelo.estado.collectAsState()

    when {
        estado.cargando -> Column(
            modifier = Modifier.fillMaxSize(),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            CircularProgressIndicator(color = MaterialTheme.colorScheme.primary)
        }

        estado.error != null -> Column(
            modifier = Modifier.fillMaxSize().padding(24.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Text(estado.error.orEmpty(), style = MaterialTheme.typography.bodyMedium)
            TextButton(onClick = modelo::cargar) { Text("Reintentar") }
        }

        estado.alertas.isEmpty() -> Column(
            modifier = Modifier.fillMaxSize().padding(32.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Text("No hay avisos activos.", style = MaterialTheme.typography.bodyMedium)
            Text(
                text = "El motor de análisis evalúa una vez por día.",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.padding(top = 6.dp),
            )
        }

        else -> LazyColumn(
            modifier = Modifier.fillMaxSize().padding(horizontal = 16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
            contentPadding = androidx.compose.foundation.layout.PaddingValues(vertical = 12.dp),
        ) {
            items(estado.alertas, key = { it.id }) { alerta ->
                Card(
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.surfaceVariant,
                    ),
                    modifier = Modifier.fillMaxWidth(),
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = alerta.titulo,
                            style = MaterialTheme.typography.labelMedium,
                            color = if (alerta.estado == "activa") {
                                MaterialTheme.colorScheme.secondary
                            } else {
                                MaterialTheme.colorScheme.onSurfaceVariant
                            },
                        )

                        Text(
                            text = alerta.componente?.modelo.orEmpty(),
                            style = MaterialTheme.typography.titleMedium,
                            modifier = Modifier.padding(top = 4.dp),
                        )

                        if (alerta.componente?.equipo != null) {
                            Text(
                                text = alerta.componente.equipo,
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant,
                            )
                        }

                        // El fundamento viene redactado por el servidor: es lo
                        // que vuelve verificable al aviso (RF-ANA-08).
                        Text(
                            text = alerta.explicacion,
                            style = MaterialTheme.typography.bodySmall,
                            modifier = Modifier.padding(top = 10.dp),
                        )

                        if (alerta.advertencia != null) {
                            Text(
                                text = alerta.advertencia,
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onSurfaceVariant,
                                modifier = Modifier.padding(top = 8.dp),
                            )
                        }
                    }
                }
            }
        }
    }
}
