# Guía de Despliegue de Prueba en Vercel - Latitud 18

Esta guía detalla cómo realizar una prueba de despliegue de **Latitud 18** en [Vercel](https://vercel.com/).

---

## 1. Archivos Ya Preparados en el Repositorio

Para que Laravel funcione en la arquitectura Serverless de Vercel, ya se han configurado e integrado los siguientes archivos:

1. [**`vercel.json`**](file:///c:/laragon/www/UHTV-master/vercel.json):
   - Configura el runtime serverless de PHP (`vercel-php@0.7.3`).
   - Redirige las solicitudes estáticas a `/public/build/` y `/public/`.
   - Dirige todas las peticiones dinámicas a la función serverless `api/index.php`.
   - Enruta el almacenamiento de vistas y caché hacia `/tmp` (la única partición con permisos de escritura en Vercel).
2. [**`api/index.php`**](file:///c:/laragon/www/UHTV-master/api/index.php):
   - Punto de entrada serverless que inicializa los directorios temporales de `/tmp` y delega la ejecución al `public/index.php` de Laravel.
3. [**`.vercelignore`**](file:///c:/laragon/www/UHTV-master/.vercelignore):
   - Evita subir archivos innecesarios de tests, logs o desarrollo local para reducir el tamaño del despliegue.

---

## 2. Pasos para Desplegar en Vercel

### Paso 1: Conectar Vercel con GitHub
1. Ingresa a [https://vercel.com](https://vercel.com) e inicia sesión con tu cuenta de GitHub.
2. En el panel principal (Dashboard), haz clic en **"Add New..."** ➔ **"Project"**.
3. En la lista de repositorios, selecciona **`jose98pp/Latitud18`** y haz clic en **"Import"**.

---

### Paso 2: Configuración del Proyecto en Vercel
En la pantalla de configuración:
- **Project Name**: `latitud18` (o el nombre que prefieras).
- **Framework Preset**: Selecciona **Other** (importante: no selecciones Vite ni Next.js, ya que `vercel.json` se encarga de la orquestación).
- **Root Directory**: Deja `./` (la raíz).
- **Build and Output Settings**:
  - **Build Command**: `npm run build`
  - **Output Directory**: Activa el switch **Override** y escribe **`public`** (para que Vercel sirva `public` en lugar de buscar la carpeta predeterminada `dist`).

---

### Paso 3: Variables de Entorno (Environment Variables)
Despliega la sección **"Environment Variables"** y añade las siguientes claves obligatorias:

| Variable | Valor Recomendado | Descripción |
| :--- | :--- | :--- |
| `APP_NAME` | `Latitud 18` | Nombre de la aplicación |
| `APP_ENV` | `production` | Entorno de ejecución |
| `APP_KEY` | *(Tu clave de `.env` actual)* | Clave de encriptación `base64:...` |
| `APP_DEBUG` | `true` (en la prueba) | Mostrar detalles de error en caso de fallo |
| `APP_URL` | `https://tu-proyecto.vercel.app` | URL que Vercel te asigne |
| `DB_CONNECTION` | `mysql` | Conexión de base de datos |
| `DB_HOST` | *(Host de tu base de datos remota)* | IP o dominio del servidor MySQL |
| `DB_PORT` | `3306` | Puerto MySQL |
| `DB_DATABASE` | *(Nombre de la BD remota)* | Base de datos |
| `DB_USERNAME` | *(Usuario de la BD)* | Usuario MySQL |
| `DB_PASSWORD` | *(Contraseña de la BD)* | Contraseña MySQL |

> [!IMPORTANT]
> **Base de Datos en Vercel:** Vercel es una plataforma de funciones serverless y **no incluye un servidor MySQL local** (`localhost` no funcionará). Para la prueba requieres que `DB_HOST` apunte a una base de datos accesible por internet (por ejemplo tu servidor remoto, o servicios gratuitos en la nube como Aiven MySQL, Railway MySQL, Clever Cloud o Supabase).

---

### Paso 4: Desplegar (Deploy)
1. Haz clic en el botón **"Deploy"**.
2. Vercel compilará los assets con Vite (`npm run build`), descargará las dependencias y publicará tu aplicación.
3. Una vez completado, verás una pantalla con la animación de confeti y el enlace directo (`https://tu-proyecto.vercel.app`).

---

## 3. Consideraciones Técnicas de Laravel en Vercel

1. **Sistema de Archivos Efímero (Read-only):**
   - Vercel es de solo lectura. Los archivos subidos a `storage/app/public` (como fotos de noticias o PDFs de periódicos) se borran cuando la función serverless se apaga.
   - En un despliegue de producción definitivo, las imágenes deben almacenarse en un servicio en la nube como **AWS S3** o **Cloudflare R2**.
2. **Alternativas recomendadas para Laravel:**
   - Si requieres un hosting con base de datos MySQL integrada, soporte nativo de subida de imágenes y tareas programadas (cron jobs), plataformas como **Railway** o **Render** son más naturales para proyectos Laravel que Vercel.
