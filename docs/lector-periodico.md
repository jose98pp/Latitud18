# Lector PDF 3D de Latitud 18

El lector usa el PDF real con DearFlip ya incluido en public/vendor/dflip. Así conserva la tipografía, las fotografías y la proporción de las páginas, incluidas las de 28 × 43 cm. No requiere servicios externos ni nuevas claves API.

Incluye portada centrada, paso de hojas, miniaturas, número de página, zoom, pantalla completa y selector de una/dos páginas. En pantallas estrechas inicia con una página. La barra permanece disponible. Ofrece enlaces al PDF continuo y a la descarga; si no carga el motor muestra reintento y alternativa PDF. No se activa reproducción ni sonido automático.

El endpoint /periodico/edicion/{id}/documento sólo sirve ediciones publicadas, con PDF inline y validación ETag. Al cambiar el contenido o el archivo PDF cambia la clave de caché del servidor. El service worker excluye /periodico para evitar servir archivos antiguos o conservar ediciones retiradas.

## Aplicar en el alojamiento existente

Después de revisar y fusionar el PR, actualizar el repositorio en el servidor y ejecutar:

```sh
php artisan optimize:clear
php artisan view:cache
```

No hay migraciones ni compilación de Node para este cambio. Conservar public/vendor/dflip y las fuentes/configuración que ya usa DomPDF. Verificar permisos de escritura de storage y acceso público a los archivos del motor. La primera carga de una edición compuesta puede tardar mientras se genera el PDF; luego se reutiliza durante diez minutos.

## Verificación

```sh
php vendor/bin/phpunit tests/Feature/PeriodicoReaderTest.php
node --test tests/js/periodico-reader.test.cjs
```

Antes de publicar, revisar en navegador de escritorio y móvil: portada, páginas 2–3, última página, saltos por número y miniaturas, zoom/desplazamiento, pantalla completa, PDF continuo, cambio de edición y edición con PDF subido sin páginas de editor. Comprobar también una edición de 28 páginas y un móvil en horizontal.

Las pruebas automáticas no sustituyen esta revisión visual del motor 3D. El navegador remoto de esta sesión no tuvo acceso al servidor local (ERR_CONNECTION_REFUSED), por lo que el PR queda como borrador. La suite general del repositorio presenta fallos ajenos al lector, entre ellos migraciones SQLite que requieren Doctrine DBAL y comprobaciones antiguas de assets; no se ha considerado verde.

## Corrección de carga del PR #5

DearFlip 1.7 usa `DFLIP.defaults` para cargar PDF.js, el worker y Three/Mockup. Los parámetros de cada libro no bastan. El lector ahora espera el ready de jQuery/auto-detección y fija las rutas globales absolutas antes de iniciar. Carga las herramientas con control de error y valida el PDF antes de entregarlo al motor, evitando peticiones duplicadas.

Los errores HTTP (incluidos 404/503), respuestas HTML y herramientas que no cargan se muestran inmediatamente. Hay un límite de 60 segundos para preparar el PDF y de 25 para dibujar las páginas. Una falla cancela el motor y oculta su contenedor, incluido el spinner. Si ya se obtuvo un PDF válido, ofrece lectura continua embebida. El botón «Usar modo 2D» permite reintentar sin WebGL. Una respuesta tardía de un intento cancelado no sustituye el estado del nuevo intento.

Se actualizan las versiones de los assets del lector (`?v=2`). Después de subir esta corrección ejecutar `php artisan optimize:clear` y `php artisan view:cache`. La versión de producción consultada todavía mostraba el lector HTML anterior, por lo que la captura recibida se utilizó para comprobar el síntoma del PR y no como prueba del código desplegado.

## Guardado de la maqueta y PDF actualizado

La descarga desde el editor guarda primero la edición y espera la confirmación del servidor. Los errores de validación mantienen el aviso de cambios pendientes. Titular y cuerpo se guardan por separado, junto con las fotografías, los estilos de texto y las coordenadas de cada marco.

Un PDF subido se conserva mientras no se modifique la maqueta. Al guardar cambios de páginas o marcos, la edición pasa a generar su PDF desde esos datos, sin borrar el archivo subido. El lector recibe una revisión nueva y las descargas no conservan una copia antigua en la caché del navegador. Las imágenes locales, incluidas las de `/storage`, se incrustan desde disco; no necesitan una petición al dominio público del sitio.

Después de desplegar esta corrección, ejecutar `php artisan optimize:clear` y `php artisan view:cache`, y recargar el editor y el lector. No requiere migraciones ni instalar dependencias.
