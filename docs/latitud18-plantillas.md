# Plantillas editables de Latitud18

## Instalar

Con las migraciones existentes aplicadas, ejecutar:

```sh
php artisan db:seed --class=Latitud18ReferenciaSeeder
php artisan view:clear
```

El catálogo incorpora 12 plantillas de página y 6 variantes publicitarias. En plantillas de edición aparece **Latitud18 · Edición semanal de 12 páginas**. El seeder es repetible y conserva las plantillas que el usuario haya editado. No publica ediciones ni modifica ediciones anteriores.

Se puede crear una edición completa o aplicar una página individual. Los archivos de `database/data/latitud18/*.latitud-template` también se pueden importar por separado; la importación asigna un ID nuevo. La edición completa instalada por el seeder utiliza los IDs estables del catálogo.

Las fotografías son marcadores reemplazables y las noticias son textos de ejemplo. Los marcos de texto y las columnas se editan por separado; no hay flujo automático de texto entre marcos.

## Medidas

Tabloide: **280 × 430 mm**. Márgenes: **12 mm**. Área útil: **256 × 406 mm**. Lienzo: **720 × 1106 unidades**. La exportación convierte cada coordenada a milímetros. Las páginas anteriores mantienen sus dimensiones y el PDF anterior si no tienen la configuración del nuevo tabloide.

| Formato | Dimensiones (cm) |
| --- | --- |
| A | 24,7 × 6 |
| B | 12,6 × 26 |
| C | 25,6 × 12,8 |
| D | 4,8 × 13 |
| E | 12,6 × 6,2 |
| F | 25,6 × 4,8 |

Actualizar el tarifario comercial: C y F ya no tienen 26,6 cm de ancho; B y E representan media retícula y D una columna. Las seis variantes son alternativas de distribución; no se colocan todos los formatos en la misma página.

Las fotografías locales se ajustan con recorte centrado o completas, según la propiedad `image_fit`. Los anuncios locales se muestran completos, sin estirarlos. Subir las fotos al proyecto antes de exportar garantiza que el PDF pueda calcular su proporción; los recursos remotos no tienen esa garantía. No se genera PDF/X, CMYK ni sangrado de imprenta.

## Validación

Ejecutado con PHP 8.3.6, Laravel 10.48.25, SQLite y Node:

- Suite del módulo: 40 pruebas, incluida la suite nueva de 7 pruebas de integración.
- JavaScript del editor: 6 pruebas sobre límites, aplicación, exportación, imágenes y previsualización.
- PDF: 12 páginas de 280 × 430 mm; carga de imágenes y fuentes locales sin errores.
- Compilación Vite, compilación Blade, sintaxis PHP/JavaScript y revisión del diff.
- Inspección visual del PDF exportado.

```sh
php vendor/bin/phpunit --filter='Latitud18Referencia|Periodico|Plantilla|EdicionService|FrameValidation|PdfService|Publicidad'
node --test tests/Frontend/periodico-layout.test.cjs
npm run build
php artisan view:cache
```

La suite existente utiliza transacciones y espera una base de prueba migrada y el seeder `PeriodicoPlantillasSeeder`. Nunca apuntar las pruebas a producción. La migración histórica `change_contenido_column_type_in_noticias_table` requiere Doctrine DBAL para SQLite; la prueba aislada del periódico usa las migraciones necesarias para el módulo, sin ejecutar esa alteración ajena al cambio.

La prueba visual de navegador completo queda pendiente: Chromium no se pudo instalar en este entorno. Antes de fusionar, verificar arrastre, edición de textos, sustitución de una foto horizontal y otra vertical y guardado/recarga desde el panel real.
