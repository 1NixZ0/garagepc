package ar.edu.davinci.garagepc.ui.pantallas

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.FilterChip
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import ar.edu.davinci.garagepc.ui.Fabrica

@Composable
fun CotizadorPantalla(modelo: CotizadorViewModel = viewModel(factory = Fabrica.cotizador)) {
    val estado by modelo.estado.collectAsState()
    val cotizacion = estado.cotizacion

    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        if (estado.error != null) {
            Text(
                text = estado.error.orEmpty(),
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.error,
            )
        }

        if (cotizacion != null) {
            Card(
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.surfaceVariant,
                ),
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Text(
                        text = "Cotización aplicada",
                        style = MaterialTheme.typography.labelSmall,
                        color = if (cotizacion.requiereAdvertencia) {
                            MaterialTheme.colorScheme.secondary
                        } else {
                            MaterialTheme.colorScheme.onSurfaceVariant
                        },
                    )

                    Text(
                        text = "1 USD = ${cotizacion.arsPorUsd} ARS",
                        style = MaterialTheme.typography.bodyMedium,
                        modifier = Modifier.padding(top = 6.dp),
                    )
                    Text(
                        text = "1 USD = ${cotizacion.brlPorUsd} BRL",
                        style = MaterialTheme.typography.bodyMedium,
                    )

                    // RF-MOV-11: la fecha y hora de la cotización empleada
                    // viaja siempre y se muestra siempre.
                    Text(
                        text = "Obtenida el ${cotizacion.obtenidaEn.replace("T", " a las ").take(22)} " +
                            "desde ${cotizacion.origen}",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.padding(top = 8.dp),
                    )

                    if (cotizacion.vencida) {
                        Text(
                            text = "Supera los siete días. El sistema deja de convertir: " +
                                "una conversión vieja no es un dato aproximado, es un dato equivocado.",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.secondary,
                            modifier = Modifier.padding(top = 8.dp),
                        )
                    } else if (cotizacion.requiereAdvertencia) {
                        Text(
                            text = "No es del día. Los valores convertidos son orientativos.",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.secondary,
                            modifier = Modifier.padding(top = 8.dp),
                        )
                    }
                }
            }
        }

        OutlinedTextField(
            value = estado.monto,
            onValueChange = modelo::cambiarMonto,
            label = { Text("Importe") },
            singleLine = true,
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
            modifier = Modifier.fillMaxWidth(),
        )

        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            CotizadorViewModel.MONEDAS.forEach { codigo ->
                FilterChip(
                    selected = estado.moneda == codigo,
                    onClick = { modelo.cambiarMoneda(codigo) },
                    label = { Text(codigo) },
                )
            }
        }

        Button(
            onClick = modelo::consultar,
            enabled = !estado.cargando,
            modifier = Modifier.fillMaxWidth(),
        ) {
            Text(if (estado.cargando) "Consultando..." else "Convertir")
        }

        val conversion = cotizacion?.conversion

        if (conversion != null) {
            Card(
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.surfaceVariant,
                ),
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    listOf("ARS", "USD", "BRL").forEach { codigo ->
                        val valor = conversion[codigo]

                        if (valor != null) {
                            Row(
                                modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
                                horizontalArrangement = Arrangement.SpaceBetween,
                            ) {
                                Text(
                                    text = codigo,
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                                )
                                Text(
                                    text = "$codigo $valor",
                                    style = if (codigo == estado.moneda) {
                                        MaterialTheme.typography.titleMedium
                                    } else {
                                        MaterialTheme.typography.bodyMedium
                                    },
                                )
                            }
                        }
                    }
                }
            }
        }

        Text(
            text = "Esto es una conversión entre monedas. No es una estimación del valor " +
                "de reventa de un componente usado.",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
        )
    }
}
