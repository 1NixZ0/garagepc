# GaragePC — Hardware Tracker & Marketplace

Sistema de seguimiento del hardware de una PC: inventario, historial de
mantenimiento, evolución del rendimiento y sugerencias automáticas de
actualización, con valuación en pesos argentinos, dólares y reales.

Proyecto del Seminario Final de la carrera de Analista de Sistemas,
Escuela Da Vinci. Autor: Lautaro Daniel Britez Giménez.

---

## El problema que resuelve

Quien arma y mantiene su propia PC no tiene dónde registrar qué compró,
cuándo, a qué precio y qué mantenimiento le hizo. Esa información queda
dispersa entre facturas, chats y memoria. Cuando llega el momento de
decidir qué conviene cambiar, o de vender una pieza, no hay datos sobre
los que apoyarse.

GaragePC guarda ese historial y lo usa para dos cosas: avisar cuándo una
pieza pide atención, y estimar cuánto vale hoy.

---

## Qué hace

**Garaje Virtual.** Alta de equipos y de sus componentes, con fecha de
compra, precio, moneda y cotización del día de la compra. El modelo se
autocompleta contra un catálogo de 180 piezas.

**Bitácora de Mantenimiento.** Registro cronológico de intervenciones por
componente: limpiezas, cambios de pasta térmica, inspecciones, upgrades.

**Seguimiento de Rendimiento.** Carga de sesiones con FPS promedio y
temperaturas, con historial gráfico. Los promedios se calculan por
configuración (juego, resolución y nivel de calidad) y nunca mezclando
configuraciones distintas.

**Motor de Análisis Proactivo.** Cuatro reglas que corren como tarea
programada y generan avisos con su fundamento verificable:

| Regla | Qué detecta |
|---|---|
| `ReglaAntiguedad` | El componente supera el umbral de meses y el catálogo registra una generación posterior |
| `ReglaDegradacion` | Tres sesiones consecutivas de la misma configuración bajo el umbral de tolerancia |
| `DetectorDesbalance` | La diferencia de categoría entre CPU y GPU alcanza el umbral configurado |
| `ReglaMantenimiento` | Pasó el umbral de meses sin intervenciones registradas |

**Cotizador Multimoneda.** Conversión entre ARS, USD y BRL contra una API
pública de tipos de cambio, con la última cotización persistida para seguir
operando si el servicio no responde. Estima el valor de reventa aplicando
depreciación por categoría y ajuste por condición declarada.

**Dashboard.** Vista consolidada: costo total del garaje, componentes en
uso, intervenciones registradas y avisos activos.

**API REST.** Consumida por la aplicación móvil Android.

---

## Requisitos

| Herramienta | Versión |
|---|---|
| PHP | 8.3 o superior |
| Composer | 2.x |
| MySQL | 8.x |
| Node.js | 20 LTS o superior |

El proyecto se desarrolla sobre Laragon, que provee PHP, MySQL y Composer
en un solo paquete.

---

## Instalación

```bash
git clone https://github.com/1NixZ0/garagepc.git
cd garagepc
```

**Dependencias de PHP:**

```bash
composer install
```

**Dependencias de JavaScript:**

```bash
npm install
```

**Archivo de entorno:**

```bash
copy .env.example .env
php artisan key:generate
```

En Linux o macOS, `cp .env.example .env`.

**Base de datos.** Crear una base vacía llamada `garagepc` y ajustar las
credenciales en `.env`. Después:

```bash
php artisan migrate
php artisan db:seed
```

El seeder carga los datos de referencia: 180 modelos de hardware, 182
juegos, los factores de depreciación por categoría y los coeficientes de
condición. Es idempotente: se puede volver a correr sin duplicar nada.

---

## Cómo correr el proyecto en local

Hacen falta dos procesos a la vez, en dos terminales distintas.

**Compilador de recursos:**

```bash
npm run dev
```

**Servidor de la aplicación:**

```bash
php artisan serve
```

El sitio queda en `http://localhost:8000`. La primera pantalla es el
registro; la cuenta se crea desde ahí.

**Datos de demostración.** Para ver el Motor de Análisis funcionando sin
esperar a que una pieza envejezca:

```bash
php artisan garagepc:demo
php artisan analisis:evaluar
```

El primer comando crea un equipo de ejemplo que dispara las cuatro reglas.
Se borra con `php artisan garagepc:demo --borrar`.

---

## Variables de entorno

Las que hay que revisar antes de levantar el proyecto. El archivo
`.env.example` trae el resto con valores por defecto.

| Variable | Para qué sirve | Valor de ejemplo |
|---|---|---|
| `APP_KEY` | Clave de cifrado de la aplicación | La genera `php artisan key:generate` |
| `APP_URL` | URL base del sitio | `http://localhost:8000` |
| `DB_CONNECTION` | Motor de base de datos | `mysql` |
| `DB_HOST` | Servidor de la base | `127.0.0.1` |
| `DB_PORT` | Puerto | `3306` |
| `DB_DATABASE` | Nombre de la base | `garagepc` |
| `DB_USERNAME` | Usuario | `root` |
| `DB_PASSWORD` | Contraseña | *(vacía en Laragon)* |
| `COTIZADOR_URL` | API de tipos de cambio | `https://dolarapi.com` |
| `COTIZADOR_SERIE` | Serie de cotización de referencia | `blue` |

El archivo `.env` **no se versiona**. El `.gitignore` lo excluye junto con
`.env.backup`, `.env.production`, `/vendor`, `/node_modules`, las claves de
`storage` y `auth.json`. Ninguna credencial vive en el código.

---

## Tareas programadas

| Comando | Frecuencia | Qué hace |
|---|---|---|
| `analisis:evaluar` | Diaria, 03:00 | Evalúa las condiciones de alerta de todos los equipos |
| `cotizaciones:actualizar` | Cada hora | Consulta el servicio externo de tipos de cambio |

Ambas se pueden correr a mano. Para que se ejecuten solas, el programador
de Laravel necesita estar activo:

```bash
php artisan schedule:work
```

---

## Pruebas

```bash
php artisan test
```

Solo las del Motor de Análisis:

```bash
php artisan test --filter=Analisis
```

Las pruebas unitarias de las reglas no tocan la base de datos: los
repositorios se inyectan por interfaz y se sustituyen por dobles, de modo
que cada regla se prueba con series de datos armadas a mano.

---

## Arquitectura técnica

Aplicación monolítica en Laravel con renderizado del lado del servidor, más
una API REST que consume la aplicación móvil.

```
app/
├── Console/Commands/      Tareas programadas y utilidades de línea de comandos
├── Http/
│   ├── Controllers/       Controladores web
│   │   └── Api/           Controladores de la API REST
│   ├── Requests/          Reglas de validación, compartidas entre web y API
│   └── Resources/         Serialización JSON de la API
├── Models/                Entidades de Eloquent
├── Repositories/          Interfaces de acceso a datos y su implementación
├── Services/
│   ├── Analisis/          Motor de Análisis Proactivo: las cuatro reglas
│   └── Cotizador/         Conversión de monedas y estimación de reventa
└── Support/               Utilidades compartidas

config/
├── analisis.php           Umbrales del motor y encendido de cada regla
└── cotizador.php          Serie de cotización y límites de antigüedad

database/
├── migrations/            Esquema: 12 tablas del dominio
└── seeders/               Catálogos y tablas de referencia

resources/
├── css/                   Tailwind y variables de diseño
├── js/                    Autocompletado de los catálogos
└── views/                 Plantillas Blade, una carpeta por módulo

routes/
├── web.php                Rutas de la aplicación web
├── api.php                Rutas de la API, autenticadas con Sanctum
└── console.php            Programación de las tareas automáticas
```

**Tres decisiones que explican la estructura:**

La lógica de análisis vive en `app/Services/Analisis` y no en los
controladores. Cada regla es una clase con una responsabilidad, y se puede
probar sin levantar una petición HTTP.

Los umbrales están en archivos de configuración, no en el código. Cambiar
el umbral de desbalance de 2 a 3 niveles no requiere tocar ninguna clase, y
cada regla se puede apagar por separado para contener una falla sin frenar
el motor entero.

Las reglas de validación están en clases `FormRequest` que usan **tanto la
web como la API**. No hay un juego de reglas para cada cliente, de modo que
no pueden desincronizarse.

---

## Librerías principales

| Capa | Tecnología | Por qué |
|---|---|---|
| Framework | Laravel 13 | Versión mayor vigente, con soporte durante todo el desarrollo y la defensa |
| Lenguaje | PHP 8.3 | Rama con soporte de seguridad hasta diciembre de 2027 |
| Base de datos | MySQL 8 | Integridad referencial para el historial por componente |
| ORM | Eloquent | Consultas parametrizadas por defecto, lo que mitiga la inyección SQL |
| Plantillas | Blade | Integrado al framework; evita sumar un framework de frontend |
| Estilos | Tailwind CSS 4 | Utilidades sobre un sistema de diseño propio definido en `app.css` |
| Compilación | Vite 8 | Compilación de estilos y scripts |
| API | Laravel Sanctum | Tokens por dispositivo para la aplicación móvil |
| Cliente HTTP | Guzzle | Consulta al servicio externo de tipos de cambio |
| Pruebas | PHPUnit 12 y Mockery | Pruebas unitarias con dobles para aislar la base y los servicios externos |
| Estilo de código | Laravel Pint, preset PSR-12 | Verificación automática del estándar |

No se usa ninguna librería de gráficos: el historial de rendimiento se
dibuja como SVG generado en el servidor.

---

## API REST

Todos los endpoints responden JSON. Salvo el de login, requieren el
encabezado `Authorization: Bearer <token>`.

| Método | Ruta | Qué hace |
|---|---|---|
| `POST` | `/api/sesion` | Entrega un token a cambio de credenciales válidas |
| `DELETE` | `/api/sesion` | Revoca el token del dispositivo actual |
| `GET` | `/api/perfil` | Datos del usuario autenticado |
| `GET` | `/api/equipos` | Equipos del usuario |
| `GET` | `/api/equipos/{id}/componentes` | Componentes de un equipo |
| `GET` | `/api/componentes/{id}` | Ficha del componente, su bitácora y su promedio de rendimiento |
| `POST` | `/api/componentes/{id}/intervenciones` | Registra una intervención |
| `POST` | `/api/equipos/{id}/sesiones` | Registra una sesión de rendimiento |
| `GET` | `/api/alertas` | Alertas activas con su fundamento |
| `GET` | `/api/cotizacion` | Paridades vigentes, su antigüedad y una conversión opcional |
| `GET` | `/api/catalogo/hardware` | Búsqueda en el catálogo de hardware |
| `GET` | `/api/catalogo/juegos` | Búsqueda en el catálogo de juegos |

Cada consulta queda acotada al usuario autenticado: pedir un recurso ajeno
devuelve 403.

---

## Aplicación móvil

El cliente Android nativo vive en la carpeta [`movil/`](movil) de este mismo
repositorio, con su propio `README.md`. Está escrito en Kotlin con Jetpack
Compose y consume la API REST documentada arriba.

Antes de compilarlo hay que indicarle la dirección de red del servidor: las
instrucciones están en [`movil/README.md`](movil/README.md).

---

## Estado del proyecto

**Implementado.** Seguridad y Acceso, Garaje Virtual, Bitácora de
Mantenimiento, Seguimiento de Rendimiento, Motor de Análisis Proactivo,
Cotizador Multimoneda, Dashboard General, la API REST y la aplicación Android
en sus funciones de campo.

El Catálogo de Hardware anda como fuente de datos: alimenta el autocompletado
del alta de componentes y las reglas del Motor de Análisis. Lo que todavía no
está es la pantalla con la que el Administrador lo mantiene, así que hoy se
carga por seeder.

**Pendiente en la aplicación web.** El Panel de Administración del Catálogo, la
separación de roles entre Administrador y Usuario Estándar, y el Marketplace
Interno, que el diseño original clasifica como módulo complementario.

**Pendiente en la aplicación móvil.** La fotografía tomada con la cámara, el
trabajo sin conexión con sincronización posterior y las notificaciones push.
El detalle requisito por requisito está en [`movil/README.md`](movil/README.md).
