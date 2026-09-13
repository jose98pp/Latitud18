# Guía Técnica - Editor de Texto Enriquecido

## Arquitectura del Sistema

### Componentes Principales

#### 1. Rich Text Editor Component (`public/js/rich-text-editor.js`)
- **Tecnología**: React 17
- **Funcionalidad**: Componente principal del editor
- **Características**:
  - Toolbar con herramientas de formato
  - Área de edición contentEditable
  - Manejo de imágenes
  - Validación de contenido
  - Auto-guardado
  - Undo/Redo

#### 2. Editor Manager (`public/js/rich-text-editor-init.js`)
- **Funcionalidad**: Inicialización y gestión del editor
- **Características**:
  - Carga lazy de librerías
  - Manejo de errores y fallbacks
  - Métricas de rendimiento
  - Atajos de teclado
  - Sistema de ayuda

#### 3. Backend API (`app/Http/Controllers/Api/ImageUploadController.php`)
- **Funcionalidad**: Manejo de subida de imágenes
- **Endpoints**: `POST /api/upload-image`
- **Validaciones**: Tipo, tamaño, seguridad

#### 4. Content Sanitization (`app/Services/ContentSanitizationService.php`)
- **Funcionalidad**: Limpieza y validación de contenido HTML
- **Características**:
  - Filtrado XSS
  - Validación de longitud
  - Detección de contenido peligroso

### Flujo de Datos

```
Usuario → Editor React → Hidden Input → Form Submit → Controller → Sanitization → Database
                ↓
            Image Upload API → Storage → URL Return → Editor
```

## Configuración e Instalación

### Dependencias

#### Frontend
- React 17 (CDN)
- ReactDOM 17 (CDN)
- Bootstrap 5 (para estilos y modales)
- Font Awesome (para iconos)

#### Backend
- Laravel 10+
- HTMLPurifier (para sanitización)
- Intervention Image (para procesamiento de imágenes)

### Archivos de Configuración

#### 1. Rutas (`routes/api.php`)
```php
Route::middleware('auth')->group(function () {
    Route::post('/upload-image', [ImageUploadController::class, 'upload']);
});
```

#### 2. Configuración de Storage (`config/filesystems.php`)
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

#### 3. Validación de Imágenes
- Tipos permitidos: `jpeg,jpg,png,gif,webp`
- Tamaño máximo: `5120` KB (5 MB)
- Directorio: `storage/app/public/images/`

## Integración en Vistas

### Estructura HTML Requerida

```html
<!-- Contenedor del editor -->
<div id="editor-container"></div>

<!-- Input oculto para el contenido -->
<input type="hidden" name="contenido" id="contenido-hidden" value="">

<!-- Contenedor para errores de validación -->
<div id="content-validation-error" class="text-danger" style="display: none;"></div>
```

### Inicialización JavaScript

```javascript
document.addEventListener('DOMContentLoaded', function() {
    window.RichTextEditorManager.initializeEditor({
        containerId: 'editor-container',
        hiddenInputId: 'contenido-hidden',
        validationErrorId: 'content-validation-error',
        initialContent: '',
        required: true,
        lazyLoad: true,
        placeholder: 'Escriba su contenido aquí...',
        minHeight: '300px',
        onChange: function(content) {
            console.log('Content changed');
        },
        onAutoSave: function(content) {
            console.log('Auto-saved');
        }
    });
});
```

### Parámetros de Configuración

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `containerId` | string | Sí | ID del contenedor del editor |
| `hiddenInputId` | string | Sí | ID del input oculto |
| `validationErrorId` | string | No | ID del contenedor de errores |
| `initialContent` | string | No | Contenido inicial |
| `required` | boolean | No | Si el contenido es requerido |
| `lazyLoad` | boolean | No | Carga lazy de librerías |
| `placeholder` | string | No | Texto de placeholder |
| `minHeight` | string | No | Altura mínima del editor |
| `onChange` | function | No | Callback de cambio |
| `onAutoSave` | function | No | Callback de auto-guardado |

## API de Subida de Imágenes

### Endpoint
```
POST /api/upload-image
```

### Headers
```
Content-Type: multipart/form-data
X-CSRF-TOKEN: {token}
Authorization: Bearer {token} (si usa Sanctum)
```

### Request Body
```
image: File (imagen a subir)
```

### Response Success (200)
```json
{
    "success": true,
    "url": "/storage/images/filename.jpg",
    "filename": "filename.jpg",
    "size": 1024000,
    "message": "Imagen subida exitosamente"
}
```

### Response Error (422)
```json
{
    "success": false,
    "message": "El archivo es demasiado grande",
    "errors": {
        "image": ["El archivo no debe ser mayor a 5MB"]
    }
}
```

### Códigos de Error
- `400`: No se envió archivo
- `422`: Validación fallida (tamaño, tipo)
- `413`: Archivo demasiado grande
- `500`: Error interno del servidor
- `507`: Sin espacio en el servidor

## Sanitización de Contenido

### Configuración HTMLPurifier

```php
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', 'p,br,strong,em,u,s,h1,h2,h3,h4,h5,h6,ul,ol,li,a[href],img[src|alt],blockquote');
$config->set('HTML.AllowedAttributes', 'href,src,alt');
$config->set('URI.AllowedSchemes', 'http,https,mailto');
```

### Tags HTML Permitidos
- Texto: `p`, `br`, `strong`, `em`, `u`, `s`
- Encabezados: `h1`, `h2`, `h3`, `h4`, `h5`, `h6`
- Listas: `ul`, `ol`, `li`
- Enlaces: `a[href]`
- Imágenes: `img[src|alt]`
- Citas: `blockquote`

### Tags Bloqueados por Seguridad
- `script`, `style`, `iframe`, `object`, `embed`
- `form`, `input`, `button`, `select`
- `link`, `meta`, `base`
- Todos los event handlers (`onclick`, `onload`, etc.)

## Optimizaciones de Rendimiento

### Carga Lazy de Librerías

```javascript
async lazyLoadLibraries() {
    // Carga React solo cuando es necesario
    if (typeof React === 'undefined') {
        await this.loadScript('https://unpkg.com/react@17/umd/react.production.min.js');
    }
    // Similar para ReactDOM y el editor
}
```

### Métricas de Rendimiento

El sistema registra:
- Tiempo de carga de librerías
- Tiempo de inicialización del editor
- Tiempo total de carga
- Operaciones lentas (>100ms)

### Optimizaciones Implementadas

1. **Lazy Loading**: Librerías se cargan solo cuando se necesitan
2. **Debounced Auto-save**: Guardado automático con retraso de 3s
3. **Performance Observer**: Monitoreo de operaciones lentas
4. **Caching**: Reutilización de librerías cargadas
5. **Fallback Graceful**: Textarea simple si falla el editor

## Seguridad

### Validaciones Frontend
- Tipo de archivo (MIME type)
- Tamaño de archivo (5MB máximo)
- Longitud de contenido (50,000 caracteres)

### Validaciones Backend
- Validación de tipo de archivo real
- Sanitización HTML completa
- Protección CSRF
- Autenticación requerida

### Protecciones XSS
- HTMLPurifier para sanitización
- Whitelist de tags permitidos
- Filtrado de event handlers
- Validación de URLs

## Testing

### Tests Automatizados

#### Feature Tests (`tests/Feature/RichTextEditorTest.php`)
- Carga de vistas con editor
- CRUD de noticias con contenido rico
- CRUD de categorías con descripción
- Subida de imágenes
- Sanitización de contenido
- Validaciones de formulario

#### Casos de Prueba Cubiertos
- ✅ Creación y edición de contenido
- ✅ Subida de imágenes válidas e inválidas
- ✅ Sanitización XSS
- ✅ Validación de longitud
- ✅ Preservación de formato
- ✅ Manejo de caracteres especiales
- ✅ Contenido complejo HTML

### Testing Manual

#### Navegadores Soportados
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

#### Dispositivos
- Desktop (Windows, macOS, Linux)
- Tablet (iPad, Android)
- Mobile (limitado, recomendado solo para ediciones menores)

## Monitoreo y Logs

### Logs del Sistema

#### Frontend (Console)
```javascript
console.log('Rich Text Editor initialized successfully');
console.log('Performance metrics:', metrics);
console.warn('Slow editor operation detected:', operation);
console.error('Failed to initialize Rich Text Editor:', error);
```

#### Backend (Laravel Log)
```php
Log::info('Image uploaded successfully', ['filename' => $filename]);
Log::warning('Large file upload attempt', ['size' => $fileSize]);
Log::error('Image upload failed', ['error' => $exception->getMessage()]);
```

### Métricas de Rendimiento

```javascript
{
    totalTime: "1250.50ms",
    libraryLoadTime: "800.25ms", 
    editorInitTime: "450.25ms"
}
```

## Troubleshooting para Desarrolladores

### Problemas Comunes

#### 1. Editor No Carga
**Síntomas**: Aparece textarea en lugar del editor
**Diagnóstico**:
```javascript
// Verificar en consola del navegador
console.log('React loaded:', typeof React !== 'undefined');
console.log('ReactDOM loaded:', typeof ReactDOM !== 'undefined');
console.log('RichTextEditor loaded:', typeof RichTextEditor !== 'undefined');
```

#### 2. Imágenes No Suben
**Diagnóstico**:
```bash
# Verificar permisos de storage
ls -la storage/app/public/
# Verificar espacio en disco
df -h
# Verificar logs de Laravel
tail -f storage/logs/laravel.log
```

#### 3. Contenido Se Sanitiza Excesivamente
**Diagnóstico**:
```php
// En ContentSanitizationService
Log::debug('Original content:', ['content' => $content]);
Log::debug('Sanitized content:', ['content' => $sanitized]);
```

### Debugging

#### Habilitar Debug Mode
```javascript
// En rich-text-editor-init.js
const DEBUG = true;

if (DEBUG) {
    console.log('Editor config:', config);
    console.log('Performance metrics:', this.performanceMetrics);
}
```

#### Verificar Estado del Editor
```javascript
// En consola del navegador
window.RichTextEditorManager.performanceMetrics
window.RichTextEditorManager.librariesLoaded
window.RichTextEditorManager.fallbackActivated
```

## Extensibilidad

### Agregar Nuevas Herramientas

```javascript
// En rich-text-editor.js
const createCustomButton = (command, icon, title) => {
    return React.createElement('button', {
        className: 'p-2 hover:bg-gray-200 rounded',
        onClick: () => handleCommand(command),
        title: title
    }, icon);
};

// Agregar a la toolbar
createCustomButton('insertHorizontalRule', '—', 'Línea horizontal')
```

### Personalizar Sanitización

```php
// En ContentSanitizationService.php
protected function getCustomConfig()
{
    $config = HTMLPurifier_Config::createDefault();
    
    // Agregar nuevos tags permitidos
    $config->set('HTML.Allowed', $this->getAllowedTags() . ',table[class],tr,td,th');
    
    return $config;
}
```

### Hooks y Eventos

```javascript
// Eventos disponibles
editor.addEventListener('content-changed', (e) => {
    console.log('Content changed:', e.detail.content);
});

editor.addEventListener('image-uploaded', (e) => {
    console.log('Image uploaded:', e.detail.url);
});
```

## Mantenimiento

### Actualizaciones Regulares

#### Frontend
- Actualizar React cuando haya versiones LTS
- Revisar compatibilidad con navegadores
- Optimizar rendimiento según métricas

#### Backend
- Actualizar HTMLPurifier
- Revisar políticas de seguridad
- Monitorear uso de storage

### Backup y Recuperación

#### Contenido
- Backup regular de base de datos
- Versionado de contenido importante
- Logs de cambios para auditoría

#### Imágenes
- Backup de directorio `storage/app/public/images/`
- CDN para distribución (recomendado)
- Compresión automática de imágenes antiguas

---

*Documento técnico actualizado regularmente. Última actualización: Octubre 2024*