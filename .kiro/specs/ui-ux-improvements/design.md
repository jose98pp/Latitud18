# Design Document

## Overview

Este diseño especifica la implementación de mejoras significativas en la interfaz de usuario del sistema de gestión de noticias, transformando el editor de texto enriquecido en una experiencia similar a Microsoft Word y modernizando el índice de noticias con cards interactivas y múltiples vistas.

## Architecture

### Component Structure

```
UI/UX Improvements
├── Rich Text Editor (Microsoft Word Style)
│   ├── Ribbon Interface
│   │   ├── Tab Navigation (Inicio, Insertar)
│   │   ├── Tool Groups (Portapapeles, Fuente, Párrafo, Enlaces)
│   │   └── Button Components
│   └── Editor Area
├── News Index Enhancement
│   ├── Statistics Bar
│   ├── Filter Panel
│   ├── View Toggle (Grid/List)
│   ├── News Cards
│   └── Enhanced Pagination
└── Shared Components
    ├── Animation System
    ├── Responsive Layout
    └── State Management
```

## Components and Interfaces

### 1. Rich Text Editor Ribbon Interface

#### Ribbon Structure
```html
<div class="word-toolbar">
  <div class="ribbon-tabs">
    <button class="ribbon-tab active">Inicio</button>
    <button class="ribbon-tab">Insertar</button>
  </div>
  <div class="ribbon-content">
    <!-- Tool Groups -->
  </div>
</div>
```

#### Tool Groups Design
- **Portapapeles**: Copiar, Cortar, Pegar
- **Fuente**: Negrita, Cursiva, Subrayado, Tachado, Subíndice, Superíndice
- **Párrafo**: Listas, Sangría, Alineación
- **Enlaces**: Insertar enlace, Quitar enlace

#### CSS Framework
```css
.word-toolbar {
  border-radius: 8px 8px 0 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  background: white;
}

.ribbon-tabs {
  background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #dee2e6;
}

.ribbon-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  border-right: 1px solid #dee2e6;
  padding: 0 1rem;
}

.word-button {
  width: 24px;
  height: 24px;
  border: 1px solid transparent;
  border-radius: 3px;
  transition: all 0.2s ease;
}

.word-button:hover {
  background-color: #e3f2fd;
  border-color: #90caf9;
}
```

### 2. News Index Cards System

#### Card Layout Design
```html
<div class="news-card">
  <img class="news-image" />
  <div class="card-content">
    <div class="card-header">
      <span class="status-badge"></span>
      <small class="date"></small>
    </div>
    <h5 class="card-title"></h5>
    <p class="card-description"></p>
    <div class="card-footer">
      <span class="category-badge"></span>
      <div class="actions-dropdown"></div>
    </div>
  </div>
</div>
```

#### Grid vs List Views
- **Grid View**: 3-4 columns responsive, cards con imágenes prominentes
- **List View**: Formato horizontal compacto, imagen pequeña a la izquierda

### 3. Statistics Bar Component

```html
<div class="stats-bar">
  <div class="stat-item">
    <div class="stat-number">{{ total }}</div>
    <div class="stat-label">Total Noticias</div>
  </div>
  <!-- Más estadísticas -->
</div>
```

### 4. Enhanced Filter System

```html
<div class="filter-panel">
  <form class="filter-form">
    <div class="search-input">
      <input type="text" placeholder="Buscar..." />
    </div>
    <div class="category-select">
      <select>Categorías</select>
    </div>
    <div class="status-select">
      <select>Estado</select>
    </div>
    <div class="view-toggle">
      <button id="grid-view">Grid</button>
      <button id="list-view">List</button>
    </div>
  </form>
</div>
```

## Data Models

### News Card Data Structure
```javascript
{
  id: number,
  titulo: string,
  contenido: string,
  imagen: string|null,
  publicada: boolean,
  category: {
    id: number,
    name: string
  },
  created_at: datetime,
  updated_at: datetime
}
```

### Filter State
```javascript
{
  search: string,
  category: number|null,
  status: 'published'|'draft'|null,
  view: 'grid'|'list',
  page: number
}
```

## Error Handling

### Rich Text Editor
- Validación de comandos de formato antes de aplicar
- Fallback para navegadores sin soporte completo
- Manejo de errores en inserción de enlaces

### News Index
- Loading states durante filtrado
- Manejo de errores en carga de imágenes
- Fallback para cards sin imagen

### Responsive Behavior
- Colapso de ribbon en móviles
- Cambio automático a vista de lista en pantallas pequeñas
- Optimización de imágenes para diferentes tamaños

## Testing Strategy

### Unit Tests
- Componentes de botones del editor
- Funciones de filtrado
- Utilidades de animación

### Integration Tests
- Flujo completo de edición de noticias
- Cambio entre vistas y persistencia
- Filtrado y paginación combinados

### Visual Regression Tests
- Apariencia del ribbon en diferentes navegadores
- Layout de cards en diferentes resoluciones
- Estados hover y activos

### Performance Tests
- Tiempo de carga con muchas noticias
- Fluidez de animaciones
- Memoria utilizada por el editor

## Implementation Notes

### CSS Framework Integration
- Utilizar Bootstrap 5 para componentes base
- CSS Grid para layout de cards
- Flexbox para ribbon interface
- CSS Custom Properties para theming

### JavaScript Architecture
- Vanilla JS para máximo rendimiento
- Event delegation para botones dinámicos
- LocalStorage para persistencia de preferencias
- Intersection Observer para lazy loading

### Accessibility Considerations
- ARIA labels en todos los botones
- Navegación por teclado en ribbon
- Alto contraste en estados activos
- Screen reader support

### Browser Compatibility
- IE11+ para funcionalidades básicas
- Modern browsers para animaciones avanzadas
- Progressive enhancement approach