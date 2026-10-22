# Noticias: creación, formato, imágenes y programación

La revisión corrige la carga de imágenes del cuerpo usando la sesión administrativa y CSRF, conserva el formato permitido al guardar y muestra únicamente publicaciones vigentes en las URLs públicas, las reacciones, los comentarios, el sitemap y Contra Ataque. La portada se invalida al crear, editar o eliminar noticias y su caché vence al llegar la siguiente publicación programada.

## Redactar y guardar

Crear y editar usan el editor visual con React 17.0.2 incluido en `public/vendor/react`; no necesitan descargar React de un CDN. Las licencias MIT se incluyen junto a los archivos.

Seleccionar texto permite aplicar negrita, cursiva, fuente, tamaño en puntos, color, alineación y listas. Se conservan estos formatos tras guardar y al abrir la noticia pública. Deshacer y rehacer sincronizan también el campo que se envía al servidor. Los botones Inicio e Insertar no envían el formulario. Ctrl+S envía el formulario de la noticia, no el de cierre de sesión del menú.

La ayuda indica que hay que pulsar Guardar/Actualizar noticia o Ctrl+S. El antiguo aviso de guardado automático cada tres segundos era incorrecto: ese callback solo escribía en la consola y no guardaba la noticia en la base de datos.

El contenido requiere 50 caracteres de texto real, tanto en creación como en edición. El servidor cuenta el texto después de sanear el HTML; las etiquetas vacías no permiten saltar el mínimo.

## Imágenes dentro del texto

En Insertar → Imagen se admiten JPEG, PNG, GIF y WEBP, hasta 5 MB. La subida conserva el punto de inserción mientras espera la respuesta del servidor.

Al hacer clic en una imagen aparecen controles para:

- Cambiar el ancho a 25 %, 50 %, 75 % o 100 %.
- Alinear a izquierda, centro o derecha, con ajuste del texto a los lados.
- Mover antes o después de un párrafo y quitar la imagen.
- Escribir una descripción accesible.

También se puede arrastrar una imagen a otro punto del mismo editor. Son herramientas de edición web: no incluyen todas las opciones de maquetación, anclaje y arrastre libre de Word. La posición se expresa dentro del flujo del artículo para adaptarse al teléfono y al escritorio. La imagen principal y la galería son campos independientes del formulario.

## Programar una noticia

Marcar Publicada y elegir una fecha futura en Publicación programada. Guardar. La zona horaria configurada es America/La_Paz. La noticia queda fuera de las consultas públicas hasta esa fecha; desde ese momento se muestra en nuevas solicitudes. No requiere cron para este cambio de visibilidad. Esto es distinto de la máquina de estados del periódico digital.

La programación no sube archivos más tarde: el contenido y las imágenes se guardan ahora, y cambia su visibilidad cuando llega la fecha. Una página ya abierta necesita recargarse para mostrar publicaciones nuevas.

## Investigación y reportajes

Marcar **Reportaje de Investigación**, marcar Publicada y guardar. En la portada, la tarjeta **LATITUD 18 INVESTIGA** muestra la noticia marcada más reciente que ya pueda publicarse. El formulario usa una marca conjunta para reportajes de investigación; no existe un tipo independiente llamado Reportaje ni un archivo público específico de todos los reportajes. Los reportajes sin esa marca aparecen en su categoría habitual.

Se elimina la sustitución anterior que mostraba una noticia general como investigación cuando no había ninguna marcada. Los borradores y las investigaciones programadas para el futuro no aparecen en esa tarjeta.

## Validación y despliegue

Las pruebas PHP cubren subida autenticada y permisos, creación/edición, saneado de formato e imágenes, longitud real, invalidación de caché, investigación y programación antes/después de la hora. Se usa la base de pruebas separada. Resultado: 50 pruebas PHP (267 comprobaciones) y 37 pruebas JavaScript correctas.

La comprobación con Chromium recorre el formulario real: pestañas sin envío accidental, negrita, tamaño 18 pt, subida autenticada, ancho y alineación de imágenes, movimiento, deshacer/rehacer, creación, reapertura, Ctrl+S, el resultado del artículo público y su aparición en la tarjeta de investigación. Se ejecuta contra el entorno local con datos de prueba, sin acceder a producción.

Al desplegar, incluir `public/vendor/react` y los archivos PHP, Blade, JavaScript y CSS modificados. Ejecutar `php artisan optimize:clear` y `php artisan view:cache`. No hay migraciones ni compilación de Node obligatoria para estos cambios. Comprobar también el formulario y el resultado público en cPanel.
