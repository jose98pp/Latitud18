#!/bin/bash
set -e

cd /var/www/html

# 1. Configurar archivo .env si no existe
if [ ! -f .env ]; then
    echo "📋 Creando archivo .env a partir de .env.docker..."
    if [ -f .env.docker ]; then
        cp .env.docker .env
    elif [ -f .env.example ]; then
        cp .env.example .env
    fi
fi

# 2. Instalar dependencias de Composer si no existe la carpeta vendor
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "📦 Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# 3. Generar APP_KEY si no está configurada
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generando APP_KEY para Laravel..."
    php artisan key:generate --force
fi

# 4. Crear enlace simbólico de almacenamiento si no existe
if [ ! -L public/storage ]; then
    echo "🔗 Creando enlace de almacenamiento (storage:link)..."
    php artisan storage:link --quiet || true
fi

# 5. Ajustar permisos de directorios de escritura
echo "🔒 Verificando permisos de storage y bootstrap/cache..."
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo "🚀 Entorno Latitud 18 listo. Iniciando servicio..."

exec "$@"
