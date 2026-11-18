package ar.edu.davinci.garagepc.data.red

import ar.edu.davinci.garagepc.BuildConfig
import ar.edu.davinci.garagepc.data.local.SesionLocal
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

/**
 * Construcción del cliente HTTP.
 *
 * Un interceptor agrega el token a cada petición, de modo que ninguna
 * pantalla tenga que acordarse de hacerlo. El token se lee en el momento de
 * cada llamada y no al crear el cliente: así, cuando el usuario inicia
 * sesión, las llamadas siguientes ya salen autenticadas sin reconstruir nada.
 */
object ClienteApi {

    fun crear(sesion: SesionLocal): ApiGaragePc {
        val autenticacion = Interceptor { cadena ->
            val pedido = cadena.request().newBuilder()
                .addHeader("Accept", "application/json")
                .apply {
                    sesion.token()?.let { addHeader("Authorization", "Bearer $it") }
                }
                .build()

            cadena.proceed(pedido)
        }

        val registro = HttpLoggingInterceptor().apply {
            // Solo en depuración: el cuerpo de las peticiones incluye la
            // contraseña en el login, y eso no puede terminar en el log de
            // una aplicación publicada.
            level = if (BuildConfig.DEBUG) {
                HttpLoggingInterceptor.Level.BODY
            } else {
                HttpLoggingInterceptor.Level.NONE
            }
        }

        val cliente = OkHttpClient.Builder()
            .addInterceptor(autenticacion)
            .addInterceptor(registro)
            .connectTimeout(15, TimeUnit.SECONDS)
            .readTimeout(15, TimeUnit.SECONDS)
            .build()

        return Retrofit.Builder()
            .baseUrl(BuildConfig.URL_API)
            .client(cliente)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiGaragePc::class.java)
    }
}
