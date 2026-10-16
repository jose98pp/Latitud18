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
