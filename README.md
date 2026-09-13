# Latitud 18 - Portal de Noticias y Periódico Digital

Plataforma integral de medios de comunicación digital, streaming en vivo y periodismo independiente de Santa Cruz de la Sierra, Bolivia.

## Características Principales

- **Diseño Editorial Premium**: Portada dinámica inspirada en el estándar de Latitud 18 con carrusel principal, ticker de última hora y bloques temáticos.
- **Periódico Digital Semanal**: Visualizador interactivo de periódico tabloide de 12 páginas con selector de edición y visor PDF.
- **Transmisión y Streaming Multimedia**: Modal en vivo integrado para streaming de TV (YouTube Live) y Radio Online con reproductor y ecualizador visual.
- **Sección de Opinión & Columnistas**: Módulo dedicado a columnas de opinión, análisis y perfiles de columnistas destacados.
- **Fotogalerías Interactivas (Lightbox)**: Soporte para múltiples fotografías por noticia con visor a pantalla completa.
- **Publicación Programada**: Publicación diferida con fecha y hora programada automática.
- **Módulo de Boletín / Newsletter**: Suscripción vía AJAX con exportación de suscriptores a formato CSV en el panel de administración.
- **Reacciones y Comentarios de Lectores**: Sistema de reacciones en tiempo real y comentarios moderados.
- **Progressive Web App (PWA)**: App instalable para dispositivos móviles y de escritorio con soporte para modo sin conexión.
- **Panel de Administración Completo**: Gestión de noticias, categorías, banners publicitarios, ajustes del sitio, redes sociales y números de contacto.

## Requisitos
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL / MariaDB

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/jose98pp/Latitud18.git
```

2. Instalar dependencias de PHP y JavaScript:
```bash
composer install
npm install
```

3. Configurar variables de entorno:
```bash
cp .env.example .env
php artisan key:generate
```

4. Ejecutar migraciones:
```bash
php artisan migrate
```

5. Compilar assets y levantar el servidor:
```bash
npm run build
php artisan serve
```
