# Guía Completa de Despliegue en cPanel — Latitud 18

Esta guía detalla paso a paso cómo subir y configurar **Latitud 18** en cualquier hosting compartido o servidor con **cPanel**.

---

## 1. Requisitos Previos en cPanel

1. **Versión de PHP**:
   * Dirígete a **cPanel > Administrador MultiPHP (MultiPHP Manager)**.
   * Selecciona tu dominio y asegúrate de elegir **PHP 8.2** o **PHP 8.3**.
2. **Extensiones de PHP requeridas**:
   * En **cPanel > Seleccionar Versión de PHP > Extensiones**:
     * `pdo_mysql`
     * `mbstring`
     * `fileinfo`
     * `gd` o `imagick`
     * `bcmath`
     * `xml` / `dom`
     * `curl`
     * `zip`

---

## 2. Crear la Base de Datos MySQL en cPanel

1. Ve a **cPanel > Asistente de bases de datos MySQL** (o *Bases de datos MySQL*).
2. **Paso 1 - Crear base de datos**: asígnale un nombre (ej. `tuusuario_latitud18`).
3. **Paso 2 - Crear usuario**: crea un usuario con contraseña segura (ej. `tuusuario_admin`).
4. **Paso 3 - Asignar permisos**: selecciona **TODOS LOS PRIVILEGIOS** y confirma.
5. Anota estos 3 datos para el archivo `.env`:
   * Nombre de BD: `tuusuario_latitud18`
   * Usuario: `tuusuario_admin`
   * Contraseña: `tu_password`
   * Servidor/Host: `localhost` (o `127.0.0.1`)

---

## 3. Preparar los Archivos para Subir

Los assets de estilos y javascript ya se encuentran compilados para producción en `public/build/`.

### Archivos a comprimir en un ZIP:
Comprime todo el proyecto **EXCLUYENDO**:
* ❌ `node_modules/` (no es necesario en producción)
* ❌ `.git/` (no es necesario en producción)
* ❌ `.env` local (lo crearemos directamente en cPanel)

---

## 4. Opciones de Estructura en el Administrador de Archivos

Tienes dos formas de organizar tus archivos en cPanel:

### Opción A (Recomendada y Más Segura): Dominio apuntando a `/public`
1. Sube tu archivo ZIP a la raíz de tu cuenta de hosting: `/home/tuusuario/latitud18/` y extráelo allí.
2. En **cPanel > Dominios**:
   * Edita el **Document Root** (Directorio raíz) de tu dominio para que apunte a:
     `/home/tuusuario/latitud18/public`
3. De esta forma, el código de la aplicación queda totalmente fuera del acceso web público, protegiendo todos los archivos del sistema.

---

### Opción B (Directa dentro de `public_html` con `.htaccess`):
Si tu hosting no te permite cambiar el Document Root del dominio principal:
1. Sube y extrae todos los archivos del proyecto directamente en `public_html/`.
2. El proyecto ya incluye un archivo [`.htaccess`](file:///c:/laragon/www/UHTV-master/.htaccess) en la raíz que:
   * Redirige automáticamente todo el tráfico web a la carpeta `public/` sin que el visitante tenga que escribir `/public` en la barra de direcciones.
   * Bloquea el acceso directo a archivos sensibles como `.env`, `.git`, `composer.json`, etc.

---

## 5. Configurar el Archivo `.env` en cPanel

1. En el **Administrador de Archivos**, activa la casilla **"Mostrar archivos ocultos (dotfiles)"** en la configuración superior derecha.
2. Copia o renombra `.env.example` como **`.env`**.
3. Haz clic derecho en `.env` y selecciona **Edit (Editar)**:
4. Configura los parámetros clave de producción:

```dotenv
APP_NAME="Latitud 18"
APP_ENV=production
APP_KEY=base64:mPCjX8N6p8WhD0qDwa/F7TdldgPtNpnRoYywNPKII1Q=
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tuusuario_latitud18
DB_USERNAME=tuusuario_admin
DB_PASSWORD=tu_password_seguro

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file
FILESYSTEM_DISK=public
```

> [!IMPORTANT]
> Recuerda cambiar `APP_URL` con tu dominio real (con `https://`) y asegurarte de que `APP_DEBUG=false` para mayor seguridad y velocidad en producción.

---

## 6. Permisos de Carpetas

En el Administrador de Archivos de cPanel, asegúrate de que las siguientes carpetas tengan permisos de escritura (`775` o `755`):
* `storage/` y todas sus subcarpetas (`storage/app`, `storage/framework`, `storage/logs`).
* `bootstrap/cache/`.

---

## 7. Migración de Base de Datos y Enlace Simbólico (`storage:link`)

Para que las imágenes subidas al panel administrativo (noticias, periódicos PDF, banners) se visualicen públicamente, Laravel requiere crear el enlace simbólico `storage`.

Tienes dos formas de hacerlo:

### Método 1: Desde la Terminal de cPanel (Si tu hosting la tiene habilitada)
Abre **cPanel > Terminal** y ejecuta:
```bash
# Ir a la carpeta del proyecto
cd public_html # o cd latitud18

# Ejecutar migraciones si la base de datos está vacía
php artisan migrate --force

# Crear el enlace simbólico de imágenes
php artisan storage:link

# Optimizar cachés para producción
php artisan optimize
```

### Método 2: Desde el Navegador Web (Sin necesidad de Terminal)
Si tu cuenta de hosting compartido **no tiene acceso a la Terminal**, se han preparado rutas de mantenimiento seguras en el panel administrativo:
1. Inicia sesión en tu panel administrativo: `https://tudominio.com/login`
2. Visita las siguientes URLs para ejecutar las tareas con un solo clic:
   * **Crear enlace de almacenamiento**: `https://tudominio.com/admin/system/storage-link`
   * **Ejecutar migraciones de base de datos**: `https://tudominio.com/admin/system/migrate`
   * **Limpiar y optimizar cachés**: `https://tudominio.com/admin/system/clear-cache`

---

## 8. Verificación Final

Una vez completados los pasos anteriores:
1. Accede a tu portal principal: `https://tudominio.com`
2. Verifica la sección deportiva: `https://tudominio.com/contraataque`
3. Verifica el visor interactivo de periódicos: `https://tudominio.com/periodico`
4. Ingresa al panel administrativo: `https://tudominio.com/admin/dashboard`
