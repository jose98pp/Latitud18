# Mejoras del editor de Latitud18

## Páginas

Cada miniatura incluye controles para renombrar, duplicar, mover antes/después y eliminar. La edición conserva al menos una página. Eliminar pide confirmación; deshacer y rehacer recuperan el diseño mientras se trabaja. Al guardar, se elimina la página y sus elementos de la base de datos, se renumeran las páginas restantes y se conservan sus referencias de plantilla. El guardado utiliza una transacción: un fallo no deja una edición parcialmente eliminada.

## Cabecera

Las nuevas cabeceras usan LATITUD con insignia 18 y el lema Información sin ruido. El inspector permite editar nombre, insignia, lema, número, fecha, ciudad, precio, web, bloques laterales, URL/subida de logotipo, tamaño del nombre, ancho del logotipo y colores. También incluye Restaurar marca Latitud18. Las cabeceras guardadas no se reemplazan automáticamente.

Editor y previsualización comparten el mismo renderizador. El lector y el PDF comparten una plantilla Blade de cabecera que también se utiliza en páginas de 28 × 43 cm. Los cambios se guardan en las propiedades del marco. Si se cambia la selección durante una subida de imagen, la subida sigue apuntando al marco original.

## Pantalla completa y zoom

El modo expandido ocupa la ventana del navegador e incluye el editor, noticias, catálogo, importación, subida de imágenes y demás ventanas. Ya no oculta estos paneles ni el contenedor que los incluye. Escape cierra primero las ventanas abiertas, después el panel de noticias y finalmente el modo expandido. Los atajos de insertar elementos no se ejecutan mientras una ventana está abierta o se edita un campo.

Se restauran las funciones faltantes de zoom, cuya ausencia interrumpía la inicialización del editor. El zoom funciona entre 25 % y 200 %, se puede restablecer y las coordenadas de arrastre/redimensionamiento tienen en cuenta la escala.

## Validación

- 46 pruebas PHP del módulo: correctas, 2.805 comprobaciones.
- 14 pruebas JavaScript: correctas.
- Vista Blade completa ejecutada con un DOM simulado: abrir noticias y plantillas en modo expandido; Escape por capas; duplicar, eliminar, deshacer, rehacer; editar cabecera y previsualizar: correctos. El DOM simulado no comprueba la distribución visual real ni el arrastre de Interact.js.
- Compilación Vite, compilación Blade, sintaxis PHP/JavaScript y revisión del diff.
- PDF de cabecera generado e inspeccionado visualmente.

La prueba se ejecutó con una base SQLite aislada, sin modificar el sitio publicado. Chrome se pudo descargar, pero el proceso no arrancó en el entorno; queda pendiente una prueba de navegación y arrastre en el panel real antes de fusionar/desplegar.

```sh
node --test tests/Frontend/*.test.cjs
php vendor/bin/phpunit --filter='Periodico|Latitud18Referencia|Plantilla|EdicionService|PdfService|Publicidad|FrameValidation'
php artisan view:cache
npm run build
```

## Despliegue

Después de fusionar, actualizar los archivos PHP y Blade del proyecto desplegado en `latitud18.ultimahora-tv.com` y ejecutar `php artisan view:clear`. No añade tablas ni migraciones. El catálogo nuevo se instala con `php artisan db:seed --class=Latitud18EditorialV2Seeder --force`; la guía [latitud18-editorial-v2.md](latitud18-editorial-v2.md) explica las doce composiciones y su uso. Las funciones del editor se incluyen en Blade y no necesitan un servicio de Vercel.

Comprobar en el panel: eliminar una página y recargar; duplicar/mover; guardar todos los campos de cabecera; sustituir el logotipo; abrir noticias/plantillas sin salir de pantalla completa; probar zoom y arrastre a 50 %, 100 % y 150 %; y comparar la previsualización con el PDF.

## Corrección del movimiento y tamaño con el mouse

Se reprodujo el fallo con Interact 1.10.27 y eventos de mouse en un DOM de prueba: `stopPropagation()` del mousedown del cuadro impedía que el motor recibiera el inicio del arrastre. Se conserva la selección del cuadro y se deja pasar el evento. El motor y su licencia quedan incluidos en `public/vendor/interact`, sin depender de jsDelivr en cada apertura del editor.

- Arrastrar el contenido mueve el cuadro. Seleccionarlo muestra sus ocho tiradores para cambiar el tamaño.
- Doble clic sobre un texto activa la escritura. Mientras se escribe no se arrastra el cuadro; al salir del texto vuelve al modo de movimiento.
- Las imágenes desactivan el arrastre nativo del navegador. Se configura touch-action para permitir la interacción en el lienzo.
- La posición y el tamaño se convierten según el zoom del editor. Se conservan los márgenes existentes y los bloqueos explícitos; un objeto bloqueado se desbloquea desde el inspector. Los espacios publicitarios siguen usando sus medidas comerciales protegidas.
- Se sincronizan los textos antes de guardar una plantilla propia. El titular y cuerpo de un artículo se guardan por separado.

Después de actualizar el código en el alojamiento, subir también `public/vendor/interact` y ejecutar `php artisan view:clear`. No requiere compilación de Node ni migraciones.

Pruebas de DOM con el motor real (entorno de desarrollo Node 24, no en cPanel):

```sh
npm --prefix tests/Frontend install
npm --prefix tests/Frontend run test:pointer
node --test tests/Frontend/editorial-v2.test.cjs
php vendor/bin/phpunit tests/Feature/PeriodicoEditorToolsTest.php
```

Se prueban nueve tipos de cuadro, arrastre, reducción desde un tirador al 50/100/150% de zoom, edición sin arrastre, cuadros añadidos después de inicializar y persistencia al guardar/recrear una plantilla personalizada. Son pruebas de DOM y servidor; la revisión visual dentro de la sesión administrativa publicada queda pendiente.
