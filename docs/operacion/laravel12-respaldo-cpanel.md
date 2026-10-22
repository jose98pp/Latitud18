# Laravel 12, respaldo y despliegue en cPanel

La aplicación se ha validado con Laravel 12.70.1 y PHP 8.2.34. Laravel 12 recibe correcciones de seguridad hasta el 24 de febrero de 2027. Planificar Laravel 13 antes de esa fecha; requiere PHP 8.3 o superior.

## Antes de actualizar producción

Esta sesión no tiene conexión a cPanel. Las pruebas descritas en el informe se hicieron en el entorno local aislado. Comprobar PHP tanto en Terminal como en **MultiPHP Manager** para el dominio. Ambos deben usar PHP 8.2 o superior. No continuar si faltan extensiones o si Composer informa incompatibilidades.

1. En cPanel → Copias de seguridad, generar una copia completa y descargarla a un lugar privado fuera del servidor. Incluir base de datos, imágenes, PDFs, configuración y archivos del sitio. Anotar el commit desplegado con `git rev-parse HEAD`.
2. Comprobar que el archivo se puede abrir y que incluye los datos. La existencia del ZIP no prueba que se pueda restaurar. Conservar la contraseña y la copia en ubicaciones separadas.
3. Preferir un subdominio de pruebas con base de datos y archivos separados. No apuntar la copia de pruebas a la base de producción.
4. Tener Composer 2 disponible. `composer --version` y `php -v` permiten comprobarlo. Node no es necesario en cPanel: los assets compilados están en Git.

## Actualización desde Terminal

Ejecutar bloque por bloque, comprobando que cada comando termina correctamente. Si falla alguno, detenerse y corregirlo antes de continuar. La copia anterior debe estar terminada antes del `pull`.

```bash
cd /home/ultimahora58tv/public_html/latitud18.ultimahora-tv.com
php -v
composer --version
git status --short
git rev-parse HEAD
php artisan down
git pull --ff-only origin main
php scripts/check-production.php
composer install --no-dev --prefer-dist --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan optimize:clear
php artisan migrate --force
php artisan view:cache
php artisan up
```

Un árbol de Git con cambios locales requiere revisarlos antes del pull. No usar `reset --hard` ni borrar `.env` o archivos subidos. Si la instalación falla con el sitio en mantenimiento, recuperar primero el código y dependencias anteriores o corregir la causa; no dar por terminada la actualización.

Después abrir portada, administración, una noticia, `/reportajes`, Contraataque y periódico digital. Editar una edición de prueba, guardar, descargar PDF, comprobar imágenes y posiciones y abrirla en el visor. Verificar programación con la zona horaria configurada y comprobar el cron `php artisan schedule:run` cada minuto. No borrar el respaldo hasta completar estas comprobaciones.

## Respaldo cifrado de la nueva versión

Incluye SQL, `storage/app/public`, `public/images`, `public/ediciones_pdf`, `public/periodico`, `.env`, lock de Composer y el archivo histórico de ediciones si existe. No incluye todo el código: conservar el commit de Git y una copia completa de cPanel. Necesita `mysqldump`, `mysql` y ZIP con AES-256.

Para que SQL, recuentos e imágenes correspondan al mismo estado, ejecutar durante una ventana de mantenimiento y pausar escritores externos, cron y trabajadores de colas durante la copia. No eliminar ni reemplazar archivos mientras se construye el respaldo. Un respaldo realizado con escrituras concurrentes puede fallar su comprobación o reflejar instantes distintos.

```bash
cd /home/ultimahora58tv/public_html/latitud18.ultimahora-tv.com
read -rsp 'Contraseña de respaldo (mínimo 16 caracteres): ' SITE_BACKUP_PASSWORD
export SITE_BACKUP_PASSWORD
echo
php artisan down
php artisan site:backup --destination=/home/ultimahora58tv/latitud18-backups
php artisan up
unset SITE_BACKUP_PASSWORD
```

Si falla la copia, el comando devuelve un código de error: no hay un respaldo válido hasta que informe que se verificó. Volver a levantar el sitio con `php artisan up` y revisar la causa. El comando no imprime contraseñas y las credenciales temporales se eliminan. No guardar la carpeta de respaldo en `public_html` ni en el repositorio. El ZIP y su manifiesto están cifrados con AES-256; los archivos y el checksum tienen permisos 600 y la carpeta 700. Copiar ZIP y `.sha256` a almacenamiento externo privado. El checksum detecta corrupción; conservar también su copia externa para poder compararlo. No se aplica retención automática: establecer una política según espacio y frecuencia editorial.

Para MySQL remoto configurar `MYSQL_BACKUP_SSL_CA` con la ruta privada al certificado CA correcto y usar un `DB_HOST` que coincida con el certificado. No desactivar la verificación TLS. Los binarios alternativos pueden configurarse con `MYSQLDUMP_BINARY` y `MYSQL_BINARY`.

## Ensayo de restauración sin tocar producción

Crear desde cPanel otra base de datos **vacía** y conceder acceso al usuario configurado. Sustituir `USUARIO_base_prueba` y el nombre del ZIP por los reales. El comando rechaza la base activa, la base original del manifiesto y un destino con tablas. La carpeta debe estar vacía y fuera de la web.

```bash
read -rsp 'Contraseña del respaldo: ' SITE_BACKUP_PASSWORD
export SITE_BACKUP_PASSWORD
echo
php artisan site:restore-check /home/ultimahora58tv/latitud18-backups/NOMBRE.zip \
  --database=USUARIO_base_prueba \
  --directory=/home/ultimahora58tv/latitud18-restauracion-prueba
unset SITE_BACKUP_PASSWORD
```

Valida hashes SHA-256 de todos los archivos, importa el SQL en la base aislada y compara el número de filas de todas las tablas. Verifica después noticias concretas, imágenes y PDFs, y levanta una copia separada del código con su propio `.env` apuntando a esa base para comprobar navegación y autenticación. La configuración extraída es confidencial: no publicarla ni activarla automáticamente. El comando no hace una recuperación sobre producción y no elimina la base de prueba al finalizar.

Una recuperación real exige seleccionar la copia y el commit correspondientes, detener escrituras, preparar un destino limpio, restaurar archivos y base, configurar `.env`, instalar dependencias y comprobar la copia antes de cambiar el dominio. Practicar este procedimiento en cPanel sigue pendiente; el ensayo local no acredita la recuperación del hosting.

## Capacidad del periódico

El PDF usa copias optimizadas de imágenes locales, conservando los originales. Máximo 2400 px por lado; con más de 8 imágenes distintas baja a 2000 y con más de 16 a 1600. Puede ajustarse `PERIODICO_PDF_MAX_IMAGE_DIMENSION` (mínimo 1200). Las cargas del periódico admiten hasta 10 MB y 24 megapíxeles. Imágenes remotas y base64 no reciben esta optimización; preferir la carga local.

Los avisos naranjas del editor detectan desbordamiento del texto y elementos fuera del papel, también en páginas ocultas. Revisarlos en Exportar PDF. La comparación se basa en el diseño del navegador; DomPDF tiene otro motor de fuentes y columnas: comprobar visualmente el PDF antes de publicar.

Para ediciones grandes prever al menos 256 MB de memoria PHP y suficiente tiempo de ejecución. La prueba de 48 fotografías de ruido de 12 MP consumió 174 MB y unos 47 segundos con caché de imágenes fría. Estos valores dependen de CPU, fotos y fuentes; no garantizan capacidad ilimitada en un hosting compartido. El visor dispone de alternativa 2D y enlace al PDF cuando WebGL falla.
