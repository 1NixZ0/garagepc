package ar.edu.davinci.garagepc.data.local

import android.content.Context
import androidx.core.content.edit

/**
 * Guarda el token de la sesión entre usos de la aplicación (RF-MOV-01).
 *
 * Se usa el almacenamiento privado de la aplicación, que el sistema aísla
 * del resto: ninguna otra aplicación puede leerlo. No está cifrado en
 * reposo, de modo que en un dispositivo con acceso root sería legible; para
 * un token de sesión revocable desde el servidor es una exposición
 * aceptable, y queda anotado como mejora posible.
 */
class SesionLocal(contexto: Context) {

    private val preferencias = contexto.getSharedPreferences(ARCHIVO, Context.MODE_PRIVATE)

    fun token(): String? = preferencias.getString(CLAVE_TOKEN, null)

    fun nombre(): String? = preferencias.getString(CLAVE_NOMBRE, null)

    fun haySesion(): Boolean = token() != null

    fun guardar(token: String, nombre: String) {
        preferencias.edit {
            putString(CLAVE_TOKEN, token)
            putString(CLAVE_NOMBRE, nombre)
        }
    }

    fun borrar() {
        preferencias.edit { clear() }
    }

    private companion object {
        const val ARCHIVO = "sesion_garagepc"
        const val CLAVE_TOKEN = "token"
        const val CLAVE_NOMBRE = "nombre"
    }
}
