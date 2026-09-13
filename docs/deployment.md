# Guía de Despliegue en Producción - Latitud 18

## 1. Requisitos del Servidor de Producción
- **Sistema Operativo**: Ubuntu 22.04 LTS o superior / Debian 12 / Rocky Linux.
- **Servidor Web**: Nginx con HTTP/2 o HTTP/3 y certificado SSL (Let's Encrypt / Cloudflare).
- **PHP**: PHP 8.2-FPM con `opcache` habilitado.
- **Base de Datos**: MySQL 8.0+ o MariaDB 10.6+.
- **Node.js**: Node 18+ para compilar assets (o compilados previamente en CI/CD).

---

## 2. Checklist de Despliegue Paso a Paso

### 2.1 Clonar o Actualizar el Código
```bash
git pull origin main
```

### 2.2 Instalar Dependencias PHP sin paquetes de Desarrollo
```bash
composer install --no-dev --optimize-autoloader
```

### 2.3 Compilar Assets Frontend
```bash
npm ci
npm run build
```

### 2.4 Ejecutar Migraciones de Base de Datos
```bash
php artisan migrate --force
```

### 2.5 Optimización y Caching de Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 2.6 Permisos de Carpetas
Asegura que el usuario del servidor web (`www-data` o `nginx`) tenga permisos de escritura en `storage` y `bootstrap/cache`:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 2.7 Enlace de Storage
```bash
php artisan storage:link
```

---

## 3. Seguridad y Respaldo de Base de Datos

### 3.1 Política de Archivos SQL
> [!CAUTION]
> **NUNCA** subas archivos de volcado de base de datos (`.sql`) al repositorio público o de control de versiones. Contienen hashes de contraseñas, correos y datos sensibles.

### 3.2 Respaldo Automatizado
Para generar respaldos seguros en producción, utiliza un cron job fuera de la raíz pública del sitio:
```bash
# Crontab diario a las 02:00 AM
0 2 * * * mysqldump -u latitud18_user -p'PASSWORD' latitud18_prod | gzip > /var/backups/latitud18_$(date +\%Y\%m\%d).sql.gz
```

---

## 4. Configuración de Cron / Tareas Programadas
Para noticias programadas (`publicar_en`) y limpieza de caché:
```bash
* * * * * cd /var/www/latitud18 && php artisan schedule:run >> /dev/null 2>&1
```
