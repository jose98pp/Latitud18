# Latitud18: ediciones Clásica Octubre

Se añaden dos modelos completos, inspirados en la distribución del periódico local adjunto y con la identidad de Latitud18. Conservan 28 × 43 cm, Montserrat, Bebas Neue, azul #0B1F3A, rojo #D71920, márgenes de 12 mm y retícula de cinco columnas. Los bloques pueden ocupar varias columnas o dividirse en columnas de lectura dentro de una noticia.

La portada presenta el titular antes de la fotografía principal, una columna lateral de llamadas y un anuncio inferior. Las interiores combinan aperturas, fotografías, pies, columnas de texto, breves y noticias secundarias. Cada página tiene una geometría diferente dentro de su edición: 332 marcos en la semanal y 770 en la extendida.

## Modelos y secciones

| Páginas | Semanal de 12 | Extendida de 28 |
| --- | --- | --- |
| 1 | Portada | Portada |
| 2 | Editorial / Opinión | Editorial / Opinión |
| 3–4 | Política | Política |
| 5–6 | Santa Cruz | Santa Cruz |
| 7–8 | País | País |
| 9–10 | Economía | Economía |
| 11 | Seguridad / Judicial | Seguridad / Judicial |
| 12 | Mundo / Deportes / Cultura | Mundo |
| 13 | — | Mundo |
| 14–15 | — | Deportes |
| 16 | — | Cultura |
| 17–28 | — | Avisos / Edictos: apertura del suplemento, comunicados y documentos completos o apilados |

Los avisos contienen texto editable en marcos independientes: no son capturas del PDF. Todas las fotografías son marcadores reemplazables. Los textos son muestras; no se copian noticias, fotografías, personas ni documentos legales del ejemplar adjunto. El administrador debe introducir los contenidos de su edición y ajustar el espacio si exceden el marco.

## Instalación en el servidor

Después de fusionar el PR y actualizar el código de `latitud18.ultimahora-tv.com`, ejecutar desde la raíz del proyecto:

```bash
php artisan db:seed --class=Latitud18OctubreSeeder --force
php artisan view:clear
```

No requiere migraciones, Node ni Vercel. El seeder es idempotente: crea 40 plantillas individuales y dos modelos de edición sin sobrescribir las plantillas anteriores, las plantillas personalizadas ni las ediciones guardadas. La actualización del servicio es necesaria: la versión anterior creaba siempre doce páginas aunque el modelo tuviera veintiocho.

## Uso en el editor

1. Abrir **Plantilla de Edición Completa**.
2. En **Plantillas Guardadas**, elegir **Latitud18 · Clásica Octubre · 12 páginas** o **Latitud18 · Clásica Octubre · 28 páginas**.
3. Pulsar **Crear Edición**, introducir número y fecha. La nueva edición se crea en borrador.
4. Seleccionar los textos, las fotografías y los títulos para reemplazarlos. Guardar y revisar el PDF.

También se puede construir y guardar un modelo propio con el selector de doce o veintiocho páginas. Las páginas individuales aparecen en el catálogo con el prefijo **Latitud18 · Clásica**. Los modelos anteriores de doce páginas conservan su selección predeterminada; las ediciones guardadas no reciben diseños nuevos automáticamente.

## Validación

- Creación de ambas ediciones con orden, secciones y medidas correctas.
- Creación y guardado de la edición de 28 páginas a través de HTTP.
- Rechazo de modelos incompletos o con páginas repetidas antes de escribir datos.
- Reinstalación sin sobrescribir modificaciones ni ediciones existentes.
- Geometrías distintas en cada edición y marcos dentro del área imprimible.
- Exportación con 12/28 páginas y fuentes incrustadas; revisión visual de ambas vistas previas.

```bash
node --test tests/Frontend/*.test.cjs
php vendor/bin/phpunit --filter='Periodico|Latitud18|Plantilla|EdicionService|PdfService|Publicidad|FrameValidation'
php scripts/preview-edicion-octubre.php 12 /ruta/semanal.pdf
php scripts/preview-edicion-octubre.php 28 /ruta/extendida.pdf
```

Los archivos generados están en `database/data/latitud18-octubre/`. `edicion-12.json` y `edicion-28.json` definen los modelos completos. `scripts/generate-edicion-octubre.py` es una herramienta de mantenimiento que utiliza ReportLab para medir la tipografía; no se ejecuta en producción.

Las pruebas usan SQLite aislada y un DOM simulado. La navegación y el arrastre con un navegador real deben comprobarse en el panel antes de publicar. No se modificó el servidor de producción.
