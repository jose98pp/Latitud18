# Manual Técnico de Desarrollo - Latitud 18

## 1. Requisitos del Sistema
- **PHP**: >= 8.1 (Recomendado 8.2+) con extensiones `pdo_mysql`, `mbstring`, `openssl`, `gd` o `imagick`, `fileinfo`, `curl`.
- **Base de Datos**: MySQL >= 5.7 o MariaDB >= 10.3 (soporte para índices `FULLTEXT` InnoDB).
- **Node.js**: >= 18.x y NPM >= 9.x.
- **Composer**: >= 2.x.
- **Servidor Web Local**: Laragon / Valet / XAMPP o Docker.

---

## 2. Instalación y Configuración Local

### 2.1 Clonar el Repositorio
```bash
git clone https://github.com/jose98pp/Latitud18.git
cd Latitud18
```

### 2.2 Dependencias PHP y Node
```bash
composer install
npm install
```

### 2.3 Variables de Entorno
Copia el archivo de ejemplo y genera la clave de aplicación:
```bash
cp .env.example .env
php artisan key:generate
```

Configura en tu `.env` la conexión de base de datos:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=latitud18_db
DB_USERNAME=root
DB_PASSWORD=
```

### 2.4 Enlace Simbólico de Almacenamiento
```bash
php artisan storage:link
```

### 2.5 Migraciones
```bash
php artisan migrate
```

---

## 3. Comandos de Desarrollo

### 3.1 Servidor de Desarrollo Laravel
```bash
php artisan serve
```

### 3.2 Servidor de Assets con Vite
Para recarga en vivo (HMR) durante el desarrollo de estilos o scripts:
```bash
npm run dev
```

### 3.3 Compilación para Producción
Genera los bundles minificados en `public/build/`:
```bash
npm run build
```

---

## 4. Estructura de Assets y Estilos (Vite)
- **Configuración**: `vite.config.js`
- **Entradas principales**:
  - `resources/js/app.js`
  - `resources/css/app.css`
  - `resources/css/main-layout.css`
  - `resources/css/portada.css`
- Toda regla CSS se organiza modularmente por vista o componente para evitar contaminación de estilos globales y garantizar tiempos de carga menores a 1.5s.
