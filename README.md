# Latitud 18 - Portal de Noticias y Periódico Digital

Plataforma integral de medios de comunicación digital, streaming en vivo y periodismo independiente de Santa Cruz de la Sierra, Bolivia.

---

## 🌟 Características Principales

- **Diseño Editorial de Alto Rendimiento**: Portada modular dinámica con carrusel principal, rankings "Lo Más Leído", bloques temáticos y ticker de última hora.
- **ContraAtaque Deportes**: Portal deportivo independiente con identidad gráfica propia, fixtures, tablas de posiciones y cobertura multimedia.
- **Motor de Búsqueda FullText (MySQL)**: Búsqueda indexada de alta velocidad con ranking por relevancia y fallback para acrónimos.
- **Rutas Canónicas SEO**: URLs amigables `/{categoria}/{slug}/{id}` y `/categoria/{slug}` con redirecciones 301 para retrocompatibilidad.
- **Periódico Digital Tabloide**: Lector interactivo de periódico semanal de 12 páginas con visor PDF e impresión.
- **Streaming Multimedia**: Transmisión en vivo de TV HD y Radio Online con visualizador de audio y selector de cintillos informativos.
- **Sección de Opinión & Columnistas**: Módulo dedicado a columnas de opinión, análisis y perfiles de editorialistas.
- **Fotogalerías Interactivas (Lightbox)**: Soporte para múltiples fotografías de alta resolución por noticia.
- **Publicación Programada**: Publicación diferida con fecha y hora programada automática.
- **Boletín / Newsletter**: Suscripción vía AJAX y panel administrativo con exportación CSV.
- **Progressive Web App (PWA)**: App web instalable con Service Worker para lectura offline y temas Claro / Oscuro.

---

## 🐳 Instalación con Docker (Recomendada)

La forma más rápida para que cualquier desarrollador clone y ejecute el proyecto sin configurar PHP, MySQL ni extensiones manualmente:

```bash
# 1. Clonar repositorio
git clone https://github.com/jose98pp/Latitud18.git
cd Latitud18

# 2. Iniciar contenedores (PHP 8.2, Nginx y MySQL con datos iniciales)
docker compose up -d
```

Una vez levantado, ingresa a:
- **Portada:** [http://localhost:8000](http://localhost:8000)
- **Panel Admin:** [http://localhost:8000/admin/login](http://localhost:8000/admin/login)
  - Usuario: `admin@latitud18.com`
  - Contraseña: `admin123`

Para instrucciones completas, solución de problemas y comandos útiles, consulta [DOCKER.md](file:///e:/Latitud18/DOCKER.md).

---

## 💻 Instalación Local Alternativa (Sin Docker)

```bash
# 1. Clonar repositorio
git clone https://github.com/jose98pp/Latitud18.git
cd Latitud18

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno y generar clave
cp .env.example .env
php artisan key:generate

# 4. Enlace de almacenamiento y migraciones
php artisan storage:link
php artisan migrate

# 5. Compilar assets y arrancar
npm run build
php artisan serve
```

---

## 📚 Documentación Técnica

Para información técnica detallada, consulta los documentos de la carpeta [`docs/`](file:///e:/Latitud18/docs):

1. [**Arquitectura del Sistema**](file:///e:/Latitud18/docs/architecture.md): Modelos, servicios, repositorios, enrutamiento SEO y motor de búsqueda.
2. [**Manual de Desarrollo**](file:///e:/Latitud18/docs/README-technical.md): Guía de instalación local, requisitos y comandos de desarrollo con Vite.
3. [**Guía de Despliegue en Producción**](file:///e:/Latitud18/docs/deployment.md): Checklist para servidores Nginx, SSL, cachés y políticas de seguridad.
4. [**Historial de Cambios (Changelog)**](file:///e:/Latitud18/docs/changelog.md): Registro de versiones y mejoras implementadas.