# GaragePC — Aplicación Android

Cliente Android nativo de GaragePC. Cubre las tareas de campo: consultar el
garaje, abrir la ficha de un componente, registrar mantenimiento y sesiones de
rendimiento, ver los avisos del Motor de Análisis y convertir importes entre
monedas.

Es el módulo **M-10** del Seminario Final. No replica la aplicación web: la
administración del inventario y el panel de administración quedan en el
escritorio por decisión de diseño, no por limitación técnica.

---

## Requisitos

| Herramienta | Versión |
|---|---|
| Android Studio | Rabbit 2 (2026.2.2) o superior |
| JDK | 11 o superior (lo provee Android Studio) |
| Android SDK | API 37 |
| Versión mínima de Android | 8.0 (API 26) |

La aplicación necesita que el **servidor Laravel de GaragePC esté corriendo**.
Ver el `README.md` de la raíz del repositorio para levantarlo.

---

## Instalación

El proyecto vive en la carpeta `movil` del repositorio.

1. Abrir Android Studio y elegir **Open**.
2. Seleccionar la carpeta `movil`, no la raíz del repositorio.
3. Esperar a que termine la sincronización de Gradle. La primera vez descarga
   las dependencias y tarda varios minutos.

---

## Configurar la dirección del servidor

**Este es el único paso que hay que hacer sí o sí antes de compilar.**

La aplicación necesita saber en qué dirección de la red está el servidor. Por
defecto apunta a una IP que probablemente no sea la de tu máquina.

Averiguá la IP de la computadora donde corre Laravel:

```bash
ipconfig        # Windows
ifconfig        # Linux o macOS
```

Buscá la dirección IPv4 del adaptador activo; va a ser algo como
`192.168.1.15`.

Abrí `movil/app/build.gradle.kts` y cambiá esta línea:

```kotlin
buildConfigField("String", "URL_API", "\"http://192.168.1.15:8000/api/\"")
```

Poné tu IP en lugar de `192.168.1.15`. Después, **Sync Now**.

Levantá el servidor aceptando conexiones desde la red:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Sin el `--host=0.0.0.0`, Laravel solo escucha en la propia máquina y el
teléfono no llega.

> **Por qué no se usa `10.0.2.2`.** Esa es la dirección virtual con la que el
> emulador representa a su anfitrión. Funciona para el navegador del emulador,
> pero el tráfico de la aplicación sale por otra interfaz de red y allí no
> existe. Con la IP real funcionan las dos, y además la aplicación anda en un
> teléfono físico conectado al mismo Wi-Fi.

---

## Probar en el emulador

1. En Android Studio, **Device Manager**, crear un dispositivo virtual con
   API 37 si todavía no hay ninguno.
2. Seleccionarlo en la barra superior.
3. Tocar **Run** (el triángulo verde).

## Probar en un teléfono físico

1. En el teléfono, activar **Opciones de desarrollador** y la
   **Depuración por USB**.
2. Conectarlo por cable y aceptar la autorización que aparece en pantalla.
3. Conectar el teléfono **a la misma red Wi-Fi** que la computadora.
4. Seleccionarlo en la barra superior de Android Studio y tocar **Run**.

---

## Ingresar

Se usan **las mismas credenciales de la aplicación web**. No hay registro
desde el teléfono: la cuenta se crea en el sitio.

Si no hay ninguna cuenta todavía, crear una en `http://localhost:8000/registro`.

Para ver la aplicación con datos, conviene cargar el equipo de demostración
desde el servidor:

```bash
php artisan garagepc:demo
php artisan analisis:evaluar
```

Eso crea una PC de ejemplo que dispara las cuatro reglas del Motor de Análisis,
de modo que la sección **Avisos** no aparezca vacía.

---

## Qué hace cada sección

| Sección | Qué permite |
|---|---|
| **Garaje** | Lista los equipos y, al elegir uno, sus componentes con categoría, modelo y estado |
| **Sesión** | Registra una sesión de rendimiento: juego, resolución, calidad, FPS y temperaturas |
| **Avisos** | Lista las alertas activas con el fundamento que las originó |
| **Cotizar** | Convierte un importe entre ARS, USD y BRL, mostrando la fecha y hora de la cotización empleada |

Al tocar un componente se abre su ficha, con sus datos técnicos, el promedio de
rendimiento del equipo y su bitácora completa. Desde ahí se registra una
intervención de mantenimiento.

---

## Arquitectura

```
app/src/main/java/ar/edu/davinci/garagepc/
├── GaragePcApp.kt          Armado de dependencias de la aplicación
├── MainActivity.kt         Única actividad; todo lo demás es Compose
├── data/
│   ├── red/                Contrato de la API, cliente HTTP y objetos de transporte
│   ├── local/              Persistencia del token de sesión
│   └── RepositorioGaragePc Única puerta de entrada a los datos
└── ui/
    ├── Navegacion.kt       Rutas y barra de secciones
    ├── Fabrica.kt          Construcción de los modelos de vista
    ├── pantallas/          Una pantalla y un modelo de vista por caso de uso
    └── theme/              Paleta tomada de los prototipos del proyecto
```

**Tres decisiones que explican la estructura:**

Las pantallas no hablan con la red. Hablan con `RepositorioGaragePc`, que
devuelve `Result` y traduce los fallos a mensajes legibles. Ninguna pantalla
tiene que saber qué significa un 401 ni qué es una `IOException`.

Los modelos de vista no conocen Android: reciben un repositorio y exponen un
`StateFlow` con el estado de la pantalla. Eso los vuelve probables sin levantar
un emulador.

El token se agrega a cada petición mediante un interceptor del cliente HTTP, y
no pantalla por pantalla. Se lee en el momento de cada llamada, de modo que al
iniciar sesión las peticiones siguientes ya salen autenticadas sin reconstruir
nada.

---

## Librerías

| Librería | Para qué |
|---|---|
| Jetpack Compose | Interfaz declarativa |
| Material 3 | Componentes visuales |
| Navigation Compose | Rutas entre pantallas |
| Lifecycle ViewModel | Estado que sobrevive a la rotación |
| Retrofit 2 | Cliente de la API REST |
| Gson | Serialización del JSON |
| OkHttp Logging Interceptor | Registro de peticiones, solo en depuración |

No se usa ninguna librería de inyección de dependencias: para una aplicación de
este tamaño sumaría una herramienta más sin resolver un problema que exista.

Tampoco se usa el artefacto de iconos de Material. Las etiquetas de la barra de
secciones son texto, lo que evita una dependencia completa por cuatro dibujos.

---

## Seguridad

El token se guarda en el almacenamiento privado de la aplicación, que el
sistema aísla del resto. No está cifrado en reposo: en un dispositivo con
acceso root sería legible. Para un token de sesión revocable desde el servidor
es una exposición aceptable, y queda anotada como mejora posible.

El tráfico sin cifrar está habilitado **solo** para las direcciones del entorno
de desarrollo, declaradas una por una en
`app/src/main/res/xml/network_security_config.xml`. El resto del tráfico sigue
exigiendo `https`. Habilitar `cleartext` para todos los destinos sería más
simple y bastante peor.

El registro de peticiones muestra el cuerpo completo únicamente en compilaciones
de depuración, porque el cuerpo del login incluye la contraseña.

---

## Estado de los requisitos

| Requisito | Estado |
|---|---|
| RF-MOV-01 Inicio de sesión y sesión persistente | Implementado |
| RF-MOV-02 Listado de componentes del garaje | Implementado |
| RF-MOV-03 Ficha del componente con bitácora y rendimiento | Implementado |
| RF-MOV-04 Alta de intervención de mantenimiento | Implementado |
| RF-MOV-05 Fotografía tomada con la cámara | Pendiente |
| RF-MOV-06 Alta de sesión de rendimiento | Implementado |
| RF-MOV-07 Almacenamiento local sin conexión y sincronización | Pendiente |
| RF-MOV-08 Indicación de registros pendientes de sincronización | Pendiente |
| RF-MOV-09 Resolución de conflictos de sincronización | Pendiente |
| RF-MOV-10 Listado de alertas con su fundamento | Implementado |
| RF-MOV-11 Conversión multimoneda con fecha de la cotización | Implementado |
| RF-MOV-12 Notificación push ante una alerta nueva | Pendiente |
| RF-MOV-13 Modo solo lectura sin conexión | Pendiente |

Los pendientes y sus motivos están analizados en el informe técnico.
