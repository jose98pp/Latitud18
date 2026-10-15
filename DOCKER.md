# Guía de Entorno Docker — Latitud 18

Esta guía permite a cualquier desarrollador clonar y levantar el proyecto completo de **Latitud 18** en su computadora en cuestión de minutos, con todas las dependencias, extensiones de PHP, servidor web y base de datos preconfigurados e idénticos al entorno oficial.

---

## 📋 Requisitos Previos

Solo necesitas tener instalado en tu sistema operativo (Windows, macOS o Linux):
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (con Docker Compose v2 habilitado).
- [Git](https://git-scm.com/).

> **Nota:** No necesitas tener instalado PHP, Composer, MySQL ni Nginx en tu máquina local. Docker se encarga de todo.

---

## 🚀 Inicio Rápido (Solo 3 Pasos)

### 1. Clonar el repositorio
```bash
git clone https://github.com/jose98pp/Latitud18.git
cd Latitud18
```

### 2. Iniciar los contenedores
```bash
docker compose up -d
```
*La primera vez que lo ejecutes, Docker construirá la imagen con PHP 8.2 y todas las librerías necesarias (GD, Dompdf, Zip, PDO MySQL, etc.) e inicializará automáticamente la base de datos con los datos actuales del proyecto.*

### 3. Abrir en el navegador
Una vez que los contenedores estén activos:
- **Sitio Web Principal (Portada):** [http://localhost:8000](http://localhost:8000)
- **Panel de Administración (Login):** [http://localhost:8000/admin/login](http://localhost:8000/admin/login)

---

## 🔑 Credenciales de Acceso

La base de datos se inicializa automáticamente con los usuarios y roles oficiales:

| Rol | Correo Electrónico | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin@latitud18.com` *(o `admin@uhtv.com`)* | `admin123` |
| **Usuario de prueba** | `test@latitud18.com` *(o `test@uhtv.com`)* | `test123` |

---

## 🗄️ Base de Datos MySQL

- **Servicio interno en Docker:** `db:3306`
- **Acceso desde herramientas de tu computadora (DBeaver, TablePlus, Workbench):**
  - **Host:** `localhost` o `127.0.0.1`
  - **Puerto:** `3307` *(mapeado a 3307 para no entrar en conflicto con instalaciones locales de MySQL o Laragon)*
  - **Base de datos:** `latitud18`
  - **Usuario:** `latitud18`
  - **Contraseña:** `secret`
  - **Usuario Root:** `root`
  - **Contraseña Root:** `rootsecret`

> **Importación automática:** El archivo `docker/mysql/init.sql` contiene la estructura y datos iniciales de las 20 tablas del proyecto. Se importa solo la primera vez que se crea el volumen de base de datos.

---

## 🛠️ Comandos Frecuentes de Desarrollo

Todos los comandos de Laravel se pueden ejecutar dentro del contenedor `app`:

### Ejecutar pruebas automatizadas (Tests)
```bash
docker compose exec app php artisan test
```

### Ejecutar comandos de Artisan
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan route:list
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear
```

### Acceder a la terminal interactiva del contenedor
```bash
docker compose exec app bash
```

### Ver logs en tiempo real
```bash
docker compose logs -f
```

### Detener los contenedores
```bash
docker compose down
```

### Detener y reiniciar borrando el volumen de base de datos (para volver al estado inicial del dump SQL)
```bash
docker compose down -v
docker compose up -d
```

---

## ⚙️ Estructura del Entorno Docker

```
Latitud18/
├── docker-compose.yml           # Definición de servicios (app, web, db)
├── .dockerignore                # Archivos excluidos del build
├── .env.docker                  # Variables de entorno preconfiguradas para Docker
├── DOCKER.md                    # Esta guía
└── docker/
    ├── entrypoint.sh            # Script de inicialización automática de Laravel
    ├── nginx/
    │   └── default.conf         # Configuración del servidor Nginx
    ├── php/
    │   └── Dockerfile           # Imagen PHP 8.2-FPM con GD, Zip, IntCl, PDO
    └── mysql/
        └── init.sql             # Volcado inicial con las tablas y datos del proyecto
```

---

## ❓ Solución de Problemas Comunes

### 1. El puerto 8000 ya está en uso en mi máquina
Puedes cambiar el puerto en el que escucha el proyecto creando una variable en tu terminal antes de iniciar:
```bash
APP_PORT=8080 docker compose up -d
```
Luego podrás acceder en `http://localhost:8080`.

### 2. Problemas de permisos en `storage` o `bootstrap/cache`
Ejecuta el siguiente comando para restaurar los permisos dentro del contenedor:
```bash
docker compose exec app chmod -R 777 storage bootstrap/cache
```

### 3. Las vistas o la configuración no reflejan cambios recientes
Limpia las cachés internas de Laravel:
```bash
docker compose exec app php artisan optimize:clear
```
