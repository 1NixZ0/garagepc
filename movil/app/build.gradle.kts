plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.compose)
}

android {
    namespace = "ar.edu.davinci.garagepc"
    compileSdk {
        version = release(37)
    }

    defaultConfig {
        applicationId = "ar.edu.davinci.garagepc"
        minSdk = 26
        targetSdk = 37
        versionCode = 1
        versionName = "1.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"

        // Dirección del servidor de desarrollo.
        //
        // Se usa la IP de la máquina en la red local y no 10.0.2.2, que es la
        // dirección virtual que el emulador ofrece para su anfitrión: esa sólo
        // existe en una de las dos interfaces de red que emula el teléfono, y
        // las peticiones de la aplicación salen por la otra. Con la IP real
        // funcionan las dos, y además la aplicación anda en un teléfono físico
        // conectado al mismo Wi-Fi.
        //
        // Al cambiar de red hay que actualizar este valor y volver a compilar.
        buildConfigField("String", "URL_API", "\"http://192.168.1.15:8000/api/\"")
    }

    buildTypes {
        release {
            optimization {
                enable = true
                packageScope = setOf("androidx.**", "kotlin.**", "kotlinx.**")
            }
        }
    }
    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }
    buildFeatures {
        compose = true
        buildConfig = true
    }
}

dependencies {
    implementation(platform(libs.androidx.compose.bom))
    implementation(libs.androidx.activity.compose)
    implementation(libs.androidx.compose.material3)
    implementation(libs.androidx.compose.ui)
    implementation(libs.androidx.compose.ui.graphics)
    implementation(libs.androidx.compose.ui.tooling.preview)
    implementation(libs.androidx.core.ktx)
    implementation(libs.androidx.lifecycle.runtime.ktx)

    // Estado de pantalla y navegación
    implementation(libs.androidx.lifecycle.viewmodel.compose)
    implementation(libs.androidx.navigation.compose)

    // Cliente HTTP de la API REST
    implementation(libs.retrofit)
    implementation(libs.retrofit.converter.gson)
    implementation(libs.okhttp.logging.interceptor)

    testImplementation(libs.junit)
    androidTestImplementation(platform(libs.androidx.compose.bom))
    androidTestImplementation(libs.androidx.compose.ui.test.junit4)
    androidTestImplementation(libs.androidx.espresso.core)
    androidTestImplementation(libs.androidx.junit)
    debugImplementation(libs.androidx.compose.ui.test.manifest)
    debugImplementation(libs.androidx.compose.ui.tooling)
}
