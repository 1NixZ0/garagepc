package ar.edu.davinci.garagepc.ui.pantallas

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import ar.edu.davinci.garagepc.ui.Fabrica

@Composable
fun LoginPantalla(
    alIngresar: () -> Unit,
    modelo: LoginViewModel = viewModel(factory = Fabrica.login),
) {
    val estado by modelo.estado.collectAsState()

    LaunchedEffect(estado.ingreso) {
        if (estado.ingreso) {
            alIngresar()
        }
    }

    Scaffold { relleno ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(relleno)
                .padding(horizontal = 28.dp),
            verticalArrangement = Arrangement.Center,
            horizontalAlignment = Alignment.CenterHorizontally,
        ) {
            Text(
                text = "GaragePC",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.SemiBold,
            )

            Text(
                text = "Entrá a tu garaje.",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.padding(top = 6.dp),
            )

            OutlinedTextField(
                value = estado.email,
                onValueChange = modelo::cambiarEmail,
                label = { Text("Correo") },
                singleLine = true,
                enabled = !estado.cargando,
                keyboardOptions = KeyboardOptions(
                    keyboardType = KeyboardType.Email,
                    imeAction = ImeAction.Next,
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 40.dp),
            )

            OutlinedTextField(
                value = estado.clave,
                onValueChange = modelo::cambiarClave,
                label = { Text("Contraseña") },
                singleLine = true,
                enabled = !estado.cargando,
                visualTransformation = PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(
                    keyboardType = KeyboardType.Password,
                    imeAction = ImeAction.Done,
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 16.dp),
            )

            if (estado.error != null) {
                Text(
                    text = estado.error.orEmpty(),
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodySmall,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(top = 12.dp),
                )
            }

            Button(
                onClick = modelo::ingresar,
                enabled = !estado.cargando,
                colors = ButtonDefaults.buttonColors(
                    containerColor = MaterialTheme.colorScheme.primary,
                ),
                modifier = Modifier
                    .fillMaxWidth()
                    .height(52.dp)
                    .padding(top = 0.dp),
            ) {
                if (estado.cargando) {
                    CircularProgressIndicator(
                        modifier = Modifier.size(20.dp),
                        strokeWidth = 2.dp,
                        color = MaterialTheme.colorScheme.onPrimary,
                    )
                } else {
                    Text("Ingresar")
                }
            }

            Text(
                text = "Usá las mismas credenciales de la aplicación web.",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.padding(top = 20.dp),
            )
        }
    }
}
