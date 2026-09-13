@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/word-style-editor.css') }}">
<style>
    /* Estilos específicos para el editor en la vista de editar */
    #editor-container {
        min-height: 400px;
        margin-bottom: 1rem;
    }
    
    #editor-container .prose {
        font-family: inherit;
        max-width: none;
    }

    #editor-container [contenteditable] {
        min-height: 300px;
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
        <h1 class="my-4">Editar Noticia</h1>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Errores de validación:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Display success/error messages -->
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data" id="noticia-form">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="titulo" class="form-label">Título: <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="titulo" 
                    id="titulo" 
                    class="form-control @error('titulo') is-invalid @enderror" 
                    value="{{ old('titulo', $noticia->titulo) }}" 
                    required
                    maxlength="255"
                    placeholder="Ingrese el título de la noticia">
                @error('titulo')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
                <small class="form-text text-muted">Mínimo 5 caracteres, máximo 255 caracteres.</small>
            </div>

            <div class="form-group mb-3">
                <label for="contenido" class="form-label">Contenido: <span class="text-danger">*</span></label>
                
                {{-- Include help card --}}
                @include('admin.partials.rich-text-editor-help')
                
                <input type="hidden" name="contenido" id="contenido-hidden" value="{{ old('contenido', $noticia->contenido) }}" required>
                <div id="editor-container"></div>
                <div id="content-validation-error" class="text-danger mt-2" style="display: none;"></div>
                @if ($errors->has('contenido'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('contenido') }}
                    </div>
                @endif
            </div>

            <div class="form-group mb-3">
                <label for="category_id" class="form-label">Categoría: <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                    <option value="">Selecciona una categoría</option>
                    @foreach($categories as $category)
                        <option 
                            value="{{ $category->id }}" 
                            {{ old('category_id', $noticia->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="imagen" class="form-label">Imagen:</label>
                <input 
                    type="file" 
                    name="imagen" 
                    id="imagen" 
                    class="form-control" 
                    accept="image/jpeg,image/png,image/jpg,image/webp" 
                    onchange="previewImage(event)">
                <small class="form-text text-muted">
                    Formatos permitidos: JPEG, PNG, JPG, WEBP. Tamaño máximo: 2MB. Deje vacío para mantener la imagen actual.
                </small>
                
                <!-- Current image display -->
                @if(isset($noticia->image_info) && $noticia->image_info['exists'])
                    <div id="current-image-container" class="mt-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Imagen actual</span>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Válida
                                </span>
                            </div>
                            <div class="card-body text-center">
                                <img 
                                    src="{{ $noticia->image_info['url'] }}" 
                                    alt="Imagen actual" 
                                    style="max-width: 100%; max-height: 300px; border-radius: 8px;"
                                    onerror="this.onerror=null; this.src='{{ asset('images/default-news.svg') }}'; this.parentElement.innerHTML='<p class=\'text-danger\'>Error al cargar la imagen</p>';">
                                <div class="mt-2 text-muted small">
                                    @if($noticia->image_info['size'])
                                        <strong>Tamaño:</strong> {{ number_format($noticia->image_info['size'] / 1024, 1) }} KB<br>
                                    @endif
                                    @if($noticia->image_info['extension'])
                                        <strong>Formato:</strong> {{ strtoupper($noticia->image_info['extension']) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($noticia->imagen && !$noticia->has_valid_image)
                    <div id="current-image-container" class="mt-3">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Imagen no encontrada:</strong> {{ $noticia->imagen }}
                            <br><small>La imagen original no se encuentra en el servidor. Suba una nueva imagen.</small>
                        </div>
                    </div>
                @else
                    <div id="current-image-container" class="mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Esta noticia no tiene imagen asignada.
                        </div>
                    </div>
                @endif
                
                <!-- New image preview container -->
                <div id="image-preview-container" style="display: none; margin-top: 15px;">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span>Nueva imagen seleccionada</span>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="removeImagePreview()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                        <div class="card-body text-center">
                            <img 
                                id="image-preview" 
                                src="#" 
                                alt="Vista previa" 
                                style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                            <div id="image-info" class="mt-2 text-muted small"></div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function previewImage(event) {
                    const output = document.getElementById('image-preview');
                    const container = document.getElementById('image-preview-container');
                    const info = document.getElementById('image-info');
                    const file = event.target.files[0];

                    if (file && file.type.startsWith('image/')) {
                        // Validate file size (2MB = 2048KB)
                        if (file.size > 2048 * 1024) {
                            alert('El archivo es demasiado grande. El tamaño máximo permitido es 2MB.');
                            event.target.value = '';
                            container.style.display = 'none';
                            return;
                        }

                        // Validate file type
                        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Tipo de archivo no permitido. Use JPEG, PNG, JPG o WEBP.');
                            event.target.value = '';
                            container.style.display = 'none';
                            return;
                        }

                        output.src = URL.createObjectURL(file);
                        container.style.display = 'block';
                        
                        // Show file information
                        const sizeKB = (file.size / 1024).toFixed(1);
                        info.innerHTML = `
                            <strong>Archivo:</strong> ${file.name}<br>
                            <strong>Tamaño:</strong> ${sizeKB} KB<br>
                            <strong>Tipo:</strong> ${file.type}<br>
                            <small class="text-info">Esta imagen reemplazará la imagen actual al guardar.</small>
                        `;
                    } else {
                        container.style.display = 'none';
                        output.src = '';
                    }
                }

                function removeImagePreview() {
                    const input = document.getElementById('imagen');
                    const container = document.getElementById('image-preview-container');
                    const output = document.getElementById('image-preview');
                    
                    input.value = '';
                    container.style.display = 'none';
                    output.src = '';
                }
            </script>

            <div class="form-group mb-3">
                <label for="video_youtube" class="form-label">Video de YouTube (opcional):</label>
                <input 
                    type="url" 
                    name="video_youtube" 
                    id="video_youtube" 
                    class="form-control @error('video_youtube') is-invalid @enderror" 
                    value="{{ old('video_youtube', $noticia->video_youtube) }}"
                    placeholder="https://www.youtube.com/watch?v=...">
                @error('video_youtube')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
                <small class="form-text text-muted">Pegue la URL completa del video de YouTube.</small>
            </div>

        <!-- Fotogalería Múltiple -->
        <div class="card mb-4 border-info shadow-sm">
            <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                <div><i class="fas fa-images me-2"></i> <strong>Fotogalería del Artículo</strong></div>
                <span class="badge bg-light text-dark">{{ $noticia->galeria->count() }} Fotos actuales</span>
            </div>
            <div class="card-body">
                @if($noticia->galeria->count() > 0)
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-photo-video text-primary me-1"></i> Fotos actuales en la galería:</h6>
                    <div class="row g-3 mb-4" id="existing-gallery-row">
                        @foreach($noticia->galeria as $foto)
                            <div class="col-md-3 col-sm-6" id="gallery-item-{{ $foto->id }}">
                                <div class="card h-100 shadow-sm border">
                                    <div class="position-relative">
                                        <img src="{{ $foto->image_url }}" class="card-img-top" style="height: 140px; object-fit: cover;" alt="Foto galería">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle" style="width: 28px; height: 28px; padding: 0;" title="Eliminar foto" onclick="deleteGalleryPhoto({{ $foto->id }})">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-2">
                                        <small class="text-muted d-block text-truncate" title="{{ $foto->pie_de_foto }}">
                                            <strong>Pie:</strong> {{ $foto->pie_de_foto ?: 'Sin descripción' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr>
                @endif

                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-plus-circle text-success me-1"></i> Agregar más fotos a la galería:</h6>
                <div class="mb-3">
                    <input type="file" name="galeria_fotos[]" id="galeria_fotos" class="form-control" multiple accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewGalleryImages(event)">
                    <small class="form-text text-muted">Formatos: JPG, PNG, WEBP. Máximo 2MB por imagen.</small>
                </div>
                <div id="gallery-preview-container" class="row g-3 mt-2" style="display: none;"></div>
            </div>
        </div>

        <script>
            function previewGalleryImages(event) {
                const container = document.getElementById('gallery-preview-container');
                container.innerHTML = '';
                const files = event.target.files;

                if (files.length > 0) {
                    container.style.display = 'flex';
                    Array.from(files).forEach((file, index) => {
                        if (!file.type.startsWith('image/')) return;

                        const col = document.createElement('div');
                        col.className = 'col-md-4 col-sm-6';
                        col.innerHTML = `
                            <div class="card h-100 border">
                                <img src="${URL.createObjectURL(file)}" class="card-img-top" style="height: 140px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <label class="form-label small fw-bold mb-1">Pie de Foto Nueva #${index + 1}:</label>
                                    <input type="text" name="galeria_pie[]" class="form-control form-control-sm" placeholder="Descripción de la foto (opcional)">
                                    <div class="text-muted small mt-1">${(file.size / 1024).toFixed(1)} KB</div>
                                </div>
                            </div>
                        `;
                        container.appendChild(col);
                    });
                } else {
                    container.style.display = 'none';
                }
            }

            function deleteGalleryPhoto(fotoId) {
                if (!confirm('¿Seguro que deseas eliminar esta fotografía de la galería?')) {
                    return;
                }

                fetch(`/admin/noticias/foto/${fotoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.getElementById(`gallery-item-${fotoId}`);
                        if (item) {
                            item.remove();
                        }
                    } else {
                        alert(data.message || 'Error al eliminar la imagen');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de conexión al eliminar la fotografía');
                });
            }
        </script>

        <!-- Opciones de Publicación y Destacados en Portada -->
        <div class="card mb-4 border-primary shadow-sm">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="fas fa-sliders-h me-2"></i> <strong>Configuración de Publicación y Portada</strong>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="publicada" id="publicada" class="form-check-input" value="1" {{ old('publicada', $noticia->publicada) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="publicada">
                                <i class="fas fa-globe text-success me-1"></i> ¿Publicada en el sitio?
                            </label>
                            <div class="form-text text-muted">Si no se marca, se guardará como borrador visible solo para administradores.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="publicar_en" class="form-label fw-bold small text-dark">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Programar fecha/hora de publicación:
                        </label>
                        <input type="datetime-local" name="publicar_en" id="publicar_en" class="form-control form-control-sm" value="{{ old('publicar_en', $noticia->publicar_en ? $noticia->publicar_en->format('Y-m-d\TH:i') : '') }}">
                        <div class="form-text text-muted small">
                            @if($noticia->publicar_en && $noticia->publicar_en->isFuture())
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Programada para: {{ $noticia->publicar_en->format('d/m/Y H:i') }}</span>
                            @else
                                Deja vacío para publicar inmediatamente al marcar "Publicada".
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <div class="form-check mb-3">
                    <input type="checkbox" name="destacada_hero" id="destacada_hero" class="form-check-input" value="1" {{ old('destacada_hero', $noticia->destacada_hero) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="destacada_hero">
                        <i class="fas fa-star text-warning me-1"></i> Destacar en Carrusel de Portada (Hero Principal)
                    </label>
                    <div class="form-text text-muted">Esta noticia se incluirá en el carrusel de gran formato superior de la portada.</div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="es_investigacion" id="es_investigacion" class="form-check-input" value="1" {{ old('es_investigacion', $noticia->es_investigacion) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="es_investigacion">
                        <i class="fas fa-search text-danger me-1"></i> Marcar como Reportaje de Investigación
                    </label>
                    <div class="form-text text-muted">Aparecerá en la tarjeta exclusiva azul marino "LATITUD 18 INVESTIGA".</div>
                </div>

                <div class="form-check mb-2">
                    <input type="checkbox" name="es_urgente" id="es_urgente" class="form-check-input" value="1" {{ old('es_urgente', $noticia->es_urgente) ? 'checked' : '' }} onchange="document.getElementById('urgente_hasta_container').style.display = this.checked ? 'block' : 'none';">
                    <label class="form-check-label fw-bold" for="es_urgente">
                        <i class="fas fa-bolt text-danger me-1"></i> Noticia de Última Hora (Breaking News / Ticker)
                    </label>
                    <div class="form-text text-muted">Se priorizará en la cinta horizontal de "ÚLTIMA HORA" en la portada.</div>
                </div>

                <div id="urgente_hasta_container" class="ms-4 p-3 bg-light rounded border mt-2" style="display: {{ old('es_urgente', $noticia->es_urgente) ? 'block' : 'none' }};">
                    <label for="urgente_hasta" class="form-label small fw-bold text-dark">
                        <i class="far fa-clock text-danger me-1"></i> Caducidad de la Alerta (Opcional):
                    </label>
                    <input type="datetime-local" name="urgente_hasta" id="urgente_hasta" class="form-control form-control-sm" style="max-width: 280px;" value="{{ old('urgente_hasta', $noticia->urgente_hasta ? $noticia->urgente_hasta->format('Y-m-d\TH:i') : '') }}">
                    <small class="text-muted d-block mt-1">Una vez cumplida esta fecha/hora, la noticia dejará de mostrarse como alerta urgente y pasará al orden regular.</small>
                </div>
            </div>
        </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                    <i class="fas fa-save"></i> Actualizar Noticia
                </button>
                <a href="{{ route('admin.noticias.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                @if($noticia->publicada)
                    <a href="{{ $noticia->url }}" target="_blank" class="btn btn-info btn-lg ml-2">
                        <i class="fas fa-eye"></i> Ver Noticia
                    </a>
                @endif
            </div>
        </form>
    </div>
@push('scripts')
<!-- React libraries (carga tradicional para mayor estabilidad) -->
<script crossorigin src="https://unpkg.com/react@17/umd/react.production.min.js"></script>
<script crossorigin src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js"></script>

<!-- Rich Text Editor -->
<script src="{{ asset('js/rich-text-editor.js') }}"></script>
<script src="{{ asset('js/word-style-editor.js') }}"></script>
<script src="{{ asset('js/rich-text-editor-init.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Decode HTML entities in the initial content
        let initialContent = document.getElementById('contenido-hidden').value;
        if (initialContent) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = initialContent;
            initialContent = tempDiv.innerHTML;
        }

        // Initialize Rich Text Editor with enhanced features
        window.RichTextEditorManager.initializeEditor({
            containerId: 'editor-container',
            hiddenInputId: 'contenido-hidden',
            validationErrorId: 'content-validation-error',
            initialContent: initialContent,
            required: true,
            lazyLoad: false, // Usar librerías pre-cargadas
            placeholder: 'Escriba el contenido de la noticia aquí...',
            minHeight: '300px',
            useWordStyle: true, // Usar el editor estilo Microsoft Word
            onChange: function(content) {
                console.log('Content changed, length:', content.length);
            },
            onAutoSave: function(content) {
                console.log('Auto-guardado:', new Date().toLocaleTimeString());
            }
        });

        // Enhanced form validation
        const form = document.getElementById('noticia-form');
        const submitBtn = document.getElementById('submit-btn');
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate title
            const titulo = document.getElementById('titulo');
            if (titulo.value.trim().length < 5) {
                isValid = false;
                titulo.classList.add('is-invalid');
            } else {
                titulo.classList.remove('is-invalid');
            }
            
            // Validate category
            const category = document.getElementById('category_id');
            if (!category.value) {
                isValid = false;
                category.classList.add('is-invalid');
            } else {
                category.classList.remove('is-invalid');
            }
            
            // Validate content from rich text editor
            const contenidoHidden = document.getElementById('contenido-hidden');
            const textContent = contenidoHidden.value.replace(/<[^>]*>/g, '').trim();
            if (textContent.length < 30) {
                isValid = false;
                alert('El contenido debe tener al menos 30 caracteres de texto real.');
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        });
    });
</script>
@endpush
@endsection
