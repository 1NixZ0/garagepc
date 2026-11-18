package ar.edu.davinci.garagepc

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import ar.edu.davinci.garagepc.ui.Navegacion
import ar.edu.davinci.garagepc.ui.theme.GaragePCTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            GaragePCTheme {
                Navegacion()
            }
        }
    }
}
