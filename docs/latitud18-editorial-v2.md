# Catálogo editorial Latitud18 v2

Las 12 composiciones usan la referencia del periódico local para la jerarquía editorial y la imagen de Latitud18 para la identidad: tabloide de 28 × 43 cm, cinco columnas, márgenes de 12 mm, calles de 4 mm, Montserrat y Bebas Neue; azul #0B1F3A, rojo #D71920 y negro. Cada página tiene una geometría diferente, con 292 marcos independientes de texto, imagen, separador y publicidad.

| Página | Sección | Composición |
| --- | --- | --- |
| 1 | Portada | Cabecera editable, foto principal con titular, tres llamadas y publicidad inferior |
| 2 | Editorial / Opinión | Editorial a tres columnas, tres columnistas con retrato y foto inferior |
| 3 | Política | Texto a la izquierda, fotografía a la derecha y tres claves numeradas |
| 4 | Política | Foto a la izquierda, contexto a la derecha y dos noticias inferiores |
| 5 | Santa Cruz | Foto panorámica y dos noticias con fotografía y texto |
| 6 | Santa Cruz | Noticia urbana, bloque de datos, barrios y anuncio inferior |
| 7 | País | Fotografía vertical, dos columnas de texto y dos breves |
| 8 | País | Reportaje con cita destacada y segunda historia |
| 9 | Economía | Foto principal, texto, exportaciones y tres indicadores |
| 10 | Economía | Apertura a tres columnas, foto lateral y tres análisis del sector |
| 11 | Seguridad / Judicial | Dos noticias independientes con foto y texto a dos columnas |
| 12 | Mundo / Deportes / Cultura | Tres secciones verticales con fotografías en posiciones diferentes |

## Instalación en el servidor existente

Después de fusionar y actualizar el código en `latitud18.ultimahora-tv.com`, ejecutar desde la raíz del proyecto:

```bash
php artisan db:seed --class=Latitud18EditorialV2Seeder --force
php artisan view:clear
```

No requiere migraciones. El seeder crea nuevos IDs `tpl-l18-v2-01` a `tpl-l18-v2-12` y el modelo de edición **Latitud18 · Editorial v2 · 12 páginas**. Es idempotente y conserva las plantillas antiguas y las ediciones guardadas.

En el editor se puede crear una edición completa con ese modelo o aplicar una página individual desde el catálogo. El selector de doce páginas prefiere v2 cuando está instalado. Una edición anterior conserva sus marcos hasta que el usuario elija reemplazar su diseño.

Cada texto, titular, sección, pie, fotografía y bloque de cabecera es seleccionable y editable. Los controles de fuente, tamaño y color actualizan el contenido que se guarda y exporta. Las imágenes de ejemplo son marcadores que deben sustituirse por fotografías propias. Los textos son muestras; no se importaron noticias ni fotografías del PDF de referencia.

La exportación respeta 280 × 430 mm: las fotografías conservan proporciones con recorte centrado; los anuncios completos usan `contain`. Los SVG locales también conservan sus proporciones. El límite de cada marco evita dibujar fuera de la página; al añadir un texto más largo es necesario ajustar su tamaño o su marco y revisar la exportación.

## Verificación

```bash
node --test tests/Frontend/*.test.cjs
php vendor/bin/phpunit --filter='Periodico|Latitud18|Plantilla|EdicionService|PdfService|Publicidad|FrameValidation'
php artisan view:cache
php scripts/preview-editorial-v2.php /ruta/latitud18-preview.pdf
```

La prueba de geometría exige doce distribuciones distintas y todos los marcos dentro de márgenes. También se verifican el modelo de edición, la conservación de datos, las fuentes incrustadas y el número de páginas del PDF. La vista previa usa el mismo servicio PDF de producción y se inspeccionaron sus doce páginas. Queda pendiente comprobar el arrastre y la distribución del editor en un navegador real, porque Chrome no arrancó en este entorno.

Los archivos `.latitud-template` están en `database/data/latitud18-v2/`; `edicion-completa.json` contiene el mapa de páginas. El generador Python es una herramienta opcional de mantenimiento y necesita ReportLab; el servidor solo necesita los archivos generados y PHP.
