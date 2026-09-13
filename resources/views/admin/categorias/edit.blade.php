@extends('layouts.admin')

@push('styles')
<style>
    /* Estilos específicos para el editor en la vista de editar categoría */
    #editor-container {
        min-height: 300px;
        margin-bottom: 1rem;
    }
    
    #editor-container .prose {
        font-family: inherit;
        max-width: none;
    }

    #editor-container [contenteditable] {
        min-height: 200px;
    }

    /* Asegurar que los estilos de Bootstrap no interfieran con Tailwind en el editor */
    #editor-container .p-2 {
        padding: 0.5rem !important;
    }
    
    #editor-container .p-4 {
        padding: 1rem !important;
    }

    #editor-container button {
        background: none;
        border: none;
        font-size: 14px;
    }

    #editor-container button:hover {
        background-color: #e5e7eb !important;
    }
    
    /* Responsive toolbar */
    @media (max-width: 640px) {
        #editor-container .flex-wrap {
            gap: 0.25rem;
        }
        
        #editor-container button,
        #editor-container label {
            padding: 0.375rem !important;
            font-size: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    <h1 class="my-4">Editar Categoría</h1>

    <form action="{{ route('admin.categorias.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            @error('name')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="descripcion" class="form-label">Descripción (opcional)</label>
            
            {{-- Include help card --}}
            @include('admin.partials.rich-text-editor-help')
            
            <input type="hidden" name="descripcion" id="descripcion-hidden" value="{{ old('descripcion', $category->descripcion) }}">
            <div id="editor-container"></div>
            @error('descripcion')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
    </form>
</div>

@push('scripts')
<!-- React libraries (carga tradicional para mayor estabilidad) -->
<script crossorigin src="https://unpkg.com/react@17/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js"></script>

<!-- Rich Text Editor -->
<script src="{{ asset('js/rich-text-editor.js') }}"></script>
<script src="{{ asset('js/rich-text-editor-init.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Rich Text Editor with enhanced features
        window.RichTextEditorManager.initializeEditor({
            containerId: 'editor-container',
            hiddenInputId: 'descripcion-hidden',
            initialContent: document.getElementById('descripcion-hidden').value || '',
            required: false,
            lazyLoad: false, // Usar librerías pre-cargadas
            placeholder: 'Escriba una descripción para la categoría (opcional)...',
            minHeight: '200px',
            onChange: function(content) {
                console.log('Description changed, length:', content.length);
            },
            onAutoSave: function(content) {
                console.log('Auto-guardado:', new Date().toLocaleTimeString());
            }
        });
    });
</script>
@endpush

@endsection

