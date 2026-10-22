# Reportajes y clasificación de Contra Ataque

## Reportajes

La nueva ruta pública `/reportajes` reúne las noticias que tienen marcada la opción **Publicar en Investigación y reportajes**. Incluye noticia destacada, tarjetas con imagen, fecha, categoría y resumen; búsqueda, filtro por categoría y paginación. Solo aparecen noticias publicadas cuya fecha programada ya haya llegado.

Hay accesos en el menú principal, el menú móvil, el pie y la tarjeta LATITUD 18 INVESTIGA de la portada. Esta última sigue destacando el reportaje más reciente y ahora permite abrir el archivo completo. La página también se incluye en el sitemap.

## Publicar noticias deportivas

En Noticias → Crear/Editar, elegir **Deportes** como categoría y después **Sección de Contra Ataque**. El selector aparece al elegir una categoría deportiva y es obligatorio al guardar.

| Opción del editor y del nav | Contenido que debe asignar la redacción |
| --- | --- |
| Fútbol Boliviano | Competiciones nacionales, clubes bolivianos, fútbol local |
| Selección La Verde | Selección nacional, convocatorias y eliminatorias de Bolivia |
| Internacional | Fútbol internacional, Champions, Libertadores, Sudamericana, ligas extranjeras |
| Motores / Dakar | Dakar, rally, Fórmula 1 y otras competiciones de motor |
| Polideportivo | Básquet, vóley, tenis, atletismo, ciclismo y otras disciplinas |

La asignación es editorial y explícita. Por ejemplo, una noticia sobre Bolívar en Libertadores puede asignarse a Internacional; una mención a Bolívar en el título no la moverá a Fútbol Boliviano. La categoría general Internacional del portal no se considera una categoría deportiva.

Una noticia publicada aparece en la portada de Contra Ataque y en la subsección seleccionada. Los borradores y las publicaciones futuras quedan fuera. Si se cambia a una categoría general, se elimina la asignación deportiva al guardar. Las categorías se reconocen por su nombre deportivo, sin suponer que el ID 9 sea Deportes.

Las noticias antiguas mantienen sus datos y sus enlaces. Las que no tienen subsección siguen disponibles en el portal deportivo y su flujo reciente, pero no se asignan automáticamente a una disciplina. El listado administrativo indica **Pendiente de clasificar**. Abrirlas en edición, seleccionar la sección y guardar para incluirlas en una subsección.

## El Ojo de Contraataque

Se gestiona desde **Opinión & Columnistas**, no desde Noticias. Al crear o editar un artículo, marcar **Opinión deportiva — El Ojo de Contraataque**. Aparece en esta sección y en los destacados de opinión de la portada deportiva si está publicado y su columnista está activo. Los artículos generales de opinión siguen en Opinión y no se mezclan con deportes.

## Mejoras de vista

- Las subsecciones dejan de adivinar por palabras del título y de rellenar una sección vacía con noticias de otra disciplina.
- Se añaden búsqueda dentro de la sección, contador de resultados, estado vacío claro y paginación que conserva la búsqueda.
- La subsección queda resaltada en el nav tanto en el listado como al abrir una noticia. El enlace de categoría del artículo vuelve a la subsección correcta.
- Motores y Polideportivo no muestran el marcador ni la tabla de fútbol, tampoco en el detalle de sus noticias. Sus noticias relacionadas se limitan a la misma disciplina.
- Los módulos de fútbol usan el estado de la fuente y no un rótulo EN VIVO permanente.
- Se incluye Bootstrap 5.3.0 en `public/vendor/bootstrap`, con su licencia MIT. Las vistas usaban su rejilla y clases sin cargar esos estilos. Se elimina el CDN de Tailwind del diseño deportivo: no suministraba la rejilla Bootstrap que esas vistas necesitan.
- Los enlaces de equipos del pie ahora abren una búsqueda en Fútbol Boliviano, y los títulos de opinión abren el artículo real.

## Validación

Pruebas PHP: archivo público y exclusión de borradores/programadas; filtros y paginación; separación de las cinco disciplinas; secciones vacías sin sustitución; ID 9 de una categoría general; selección obligatoria y persistencia desde el formulario; limpieza al cambiar de categoría; opiniones deportivas y autores activos. Se ejecutan también las regresiones de noticias, editor y datos deportivos. Resultado: 67 pruebas PHP (384 comprobaciones), 37 pruebas JavaScript y recorridos de navegación con Chromium en escritorio y móvil correctos.

Chromium: reportajes en escritorio y móvil sin desbordamiento horizontal; búsqueda, limpiar y segunda página; las cinco entradas del nav; sección activa; ausencia de marcadores en Motores/Polideportivo; opinión deportiva; selector visible/obligatorio según categoría; creación real y apertura de la noticia desde la sección. Se utilizan datos de demostración en el entorno local, que se eliminan al terminar.

## Actualizar cPanel

Incluye una migración aditiva: `noticias.seccion_deportiva` nullable y `articulos_opinion.es_deportiva` false por defecto. No reclasifica ni elimina registros existentes.

```bash
cd /home/ultimahora58tv/public_html/latitud18.ultimahora-tv.com &&
git pull --ff-only origin main &&
php artisan migrate --force &&
php artisan optimize:clear &&
php artisan view:cache
```

Subir también `public/vendor/bootstrap` si se despliega por archivos. No se necesita ejecutar npm en cPanel para estos cambios.
