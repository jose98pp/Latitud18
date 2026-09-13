# Design Document

## Overview

El diseño se enfoca en asegurar la correcta integración del Rich Text Editor existente en las vistas de administración de noticias y categorías. El editor ya está implementado como un componente React, pero necesita optimizaciones y correcciones para funcionar de manera consistente en todas las vistas administrativas.

## Architecture

### Current State Analysis

El sistema actual tiene:
- Rich Text Editor implementado en React (`public/js/rich-text-editor.js`)
- Integración parcial en vistas de noticias (create.blade.php y edit.blade.php)
- Dependencias de React cargadas desde CDN
- Estilos mixtos entre Bootstrap y Tailwind CSS

### Target Architecture

```
┌─────────────────────────────────────────┐
│           Admin Panel Views             │
├─────────────────────────────────────────┤
│  Noticias CRUD    │   Categorías CRUD   │
│  ┌─────────────┐  │   ┌─────────────┐   │
│  │ Rich Text   │  │   │ Rich Text   │   │
│  │ Editor      │  │   │ Editor      │   │
│  └─────────────┘  │   └─────────────┘   │
└─────────────────────────────────────────┘
           │                    │
           ▼                    ▼
┌─────────────────────────────────────────┐
│        Shared Editor Component          │
│  ┌─────────────────────────────────┐    │
│  │     RichTextEditor.js           │    │
│  │  - React Component              │    │
│  │  - Toolbar Functions            │    │
│  │  - Image Upload Handler         │    │
│  │  - Auto-save Feature            │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│         Backend Services                │
│  ┌─────────────┐  ┌─────────────────┐   │
│  │ Image       │  │ Content         │   │
│  │ Upload API  │  │ Storage         │   │
│  └─────────────┘  └─────────────────┘   │
└─────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Rich Text Editor Component

**Location:** `public/js/rich-text-editor.js`

**Current Features:**
- Toolbar with formatting options (bold, italic, underline, etc.)
- Color picker for text
- Font size selection
- Text alignment options
- List creation (ordered/unordered)
- Image upload functionality
- Undo/Redo operations
- Auto-save capability
- Preview mode

**Required Enhancements:**
- Better error handling for image uploads
- Consistent styling integration
- Improved accessibility features
- Mobile responsiveness

### 2. Blade Template Integration

**Noticias Views:**
- `resources/views/admin/noticias/create.blade.php` ✅ (Partially implemented)
- `resources/views/admin/noticias/edit.blade.php` ✅ (Partially implemented)

**Categorías Views:**
- `resources/views/admin/categorias/create.blade.php` ❌ (Needs implementation)
- `resources/views/admin/categorias/edit.blade.php` ❌ (Needs implementation)

### 3. Backend API Endpoints

**Required Endpoints:**
- `POST /api/upload-image` - For image upload functionality
- Image storage in `storage/app/public/images/` directory

### 4. Database Schema

**Noticias Table:**
- `contenido` field should store HTML content

**Categorías Table:**
- May need `descripcion` field for rich content (if not exists)

## Data Models

### Rich Text Editor Props Interface

```javascript
{
  initialContent: string,     // HTML content to load
  onChange: function,         // Callback when content changes
  onAutoSave: function,      // Callback for auto-save feature
  placeholder: string,       // Placeholder text
  minHeight: string,         // Minimum editor height
  maxHeight: string,         // Maximum editor height
  enableImageUpload: boolean // Enable/disable image upload
}
```

### Content Storage Format

```json
{
  "content": "<p>HTML formatted content with <strong>bold</strong> and <em>italic</em> text</p>",
  "images": [
    {
      "url": "/storage/images/filename.jpg",
      "alt": "Image description"
    }
  ]
}
```

## Error Handling

### Client-Side Error Handling

1. **Image Upload Errors:**
   - File size validation (max 5MB)
   - File type validation (jpg, png, gif, webp)
   - Network error handling
   - Display user-friendly error messages

2. **Editor Initialization Errors:**
   - React library loading failures
   - Component mounting errors
   - Fallback to plain textarea

3. **Content Validation:**
   - HTML sanitization
   - Maximum content length validation
   - Required field validation

### Server-Side Error Handling

1. **Image Upload API:**
   - File validation
   - Storage permission errors
   - Disk space validation
   - Return appropriate HTTP status codes

2. **Content Storage:**
   - Database connection errors
   - Content size limitations
   - HTML sanitization on server

## Testing Strategy

### Unit Testing

1. **Rich Text Editor Component:**
   - Toolbar functionality tests
   - Content formatting tests
   - Image upload simulation tests
   - Undo/Redo functionality tests

2. **Backend API Tests:**
   - Image upload endpoint tests
   - File validation tests
   - Error response tests

### Integration Testing

1. **Form Submission Tests:**
   - Content saving in noticias
   - Content saving in categorías
   - Image integration tests

2. **Cross-Browser Testing:**
   - Chrome, Firefox, Safari, Edge compatibility
   - Mobile browser testing

### User Acceptance Testing

1. **Admin Workflow Tests:**
   - Create noticia with formatted content
   - Edit existing noticia content
   - Create categoría with description
   - Image upload and insertion workflow

## Implementation Phases

### Phase 1: Fix Current Implementation
- Resolve React library conflicts
- Fix CSS styling issues in noticias views
- Implement proper error handling
- Add image upload API endpoint

### Phase 2: Extend to Categorías
- Add descripcion field to categorías table (if needed)
- Implement rich text editor in categorías create/edit views
- Update categorías controller to handle HTML content

### Phase 3: Enhancements
- Improve mobile responsiveness
- Add more formatting options
- Implement content templates
- Add keyboard shortcuts documentation

## Security Considerations

1. **HTML Sanitization:**
   - Server-side HTML purification
   - XSS prevention
   - Allowed HTML tags whitelist

2. **Image Upload Security:**
   - File type validation
   - File size limits
   - Secure file naming
   - Directory traversal prevention

3. **Content Validation:**
   - Input length limits
   - SQL injection prevention
   - CSRF token validation

## Performance Considerations

1. **Client-Side Performance:**
   - Lazy loading of React libraries
   - Debounced auto-save functionality
   - Optimized image preview generation

2. **Server-Side Performance:**
   - Efficient image storage
   - Content compression
   - Database query optimization

3. **Caching Strategy:**
   - Browser caching for static assets
   - CDN for React libraries
   - Image optimization and caching