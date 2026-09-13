# Registro de Cambios (Changelog) - Latitud 18

## [2.0.0] - 2026-09-13

### Agregado
- **Portal de Deportes ContraAtaque**: Sección deportiva especializada con identidad gráfica independiente, branding dedicado, tabla de posiciones, fixtures y cobertura multimedia en `/contraataque`.
- **Búsqueda FullText (MySQL/MariaDB)**: Creación de índices `FULLTEXT(titulo, contenido)` en tabla `noticias` con ordenamiento por relevancia ponderada (doble peso en títulos) y fallback automático a búsquedas parciales para siglas y términos cortos.
- **Rutas Canónicas SEO**: Estructura canónica `/{categoria}/{slug}/{id}` y slug por categoría `/categoria/{slug}` con redirecciones 301 para retrocompatibilidad de IDs antiguos.
- **Documentación Técnica Estandarizada**: Creación de manuales técnicos en `docs/` (`architecture.md`, `README-technical.md`, `deployment.md`, `changelog.md`).

### Modificado / Refactorizado
- **Modularización Completa de Layout y Portada**:
  - `layouts/main.blade.php` reducido de 758 líneas a 81 líneas mediante la extracción de 9 componentes limpios en `resources/views/components/`.
  - `portada.blade.php` reducido de 1,650 líneas a 32 líneas mediante la extracción de 7 submódulos en `resources/views/portada/`.
- **Desacoplamiento del Periódico Digital**:
  - La interfaz y el visor del periódico digital ahora residen en su propio controlador y componente modal aislado sin sobrecargar el layout principal.
- **Unificación de Build Tools**:
  - Eliminación completa de artefactos y configuraciones heredadas de Webpack / Laravel Mix (`webpack.mix.js`), consolidando todo el flujo de compilación en Vite.
- **Limpieza de Dependencias**:
  - Eliminación de dependencias no utilizadas y duplicadas de React y librerías obsoletas en `package.json`.

### Seguridad
- **Exclusión de Bases de Datos del Repositorio**:
  - Eliminación de volcados SQL de producción (`*.sql`) del control de versiones y actualización estricta de `.gitignore`.
  - Eliminación de archivos temporales de desarrollo (`.vs/`, `.kiro/`, `homepage.html`, `test-autoload.php`, `php.ini` y notas sueltas).
