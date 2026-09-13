@extends('layouts.admin')

@section('title', 'Editar Banner - Latitud18 Admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Banner</h1>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver
        </a>
    </div>

    <!-- Banner Location Guide -->
    <div class="card shadow mb-4">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-map-marked-alt me-2"></i>Guía de Ubicaciones de Banners
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Header Principal</small>
                            <br><small class="text-muted"><code>portada_top</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Portada Medio</small>
                            <br><small class="text-muted"><code>portada_middle</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Sidebar Derecho</small>
                            <br><small class="text-muted"><code>sidebar</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Footer Horizontal</small>
                            <br><small class="text-muted"><code>footer</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Categoría Arriba</small>
                            <br><small class="text-muted"><code>category_top</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-secondary">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Categoría Abajo</small>
                            <br><small class="text-muted"><code>category_bottom</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-dark">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #343a40 0%, #23272b 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Noticia Arriba</small>
                            <br><small class="text-muted"><code>show_top</code></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-light">
                        <div class="card-body p-2 text-center">
                            <div class="mb-2" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); height: 60px; border-radius: 4px;"></div>
                            <small class="fw-bold">Noticia Abajo</small>
                            <br><small class="text-muted"><code>show_bottom</code></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información del Banner</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Título (Referencia interna)</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $banner->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="link" class="form-label">Enlace (Opcional)</label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" id="link" name="link" value="{{ old('link', $banner->link) }}" placeholder="https://ejemplo.com">
                            @error('link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Ubicación</label>
                                <select class="form-select @error('location') is-invalid @enderror" id="location" name="location" required>
                                    <option value="">Seleccionar ubicación...</option>
                                    <option value="portada_top" {{ old('location', $banner->location) == 'portada_top' ? 'selected' : '' }}>Portada - Arriba (Header Principal)</option>
                                    <option value="portada_middle" {{ old('location', $banner->location) == 'portada_middle' ? 'selected' : '' }}>Portada - Medio</option>
                                    <option value="sidebar" {{ old('location', $banner->location) == 'sidebar' ? 'selected' : '' }}>Barra Lateral (Sidebar)</option>
                                    <option value="footer" {{ old('location', $banner->location) == 'footer' ? 'selected' : '' }}>Pie de Página (Footer)</option>
                                    <option value="category_top" {{ old('location', $banner->location) == 'category_top' ? 'selected' : '' }}>Categoría - Arriba</option>
                                    <option value="category_bottom" {{ old('location', $banner->location) == 'category_bottom' ? 'selected' : '' }}>Categoría - Abajo</option>
                                    <option value="show_top" {{ old('location', $banner->location) == 'show_top' ? 'selected' : '' }}>Noticia - Arriba</option>
                                    <option value="show_bottom" {{ old('location', $banner->location) == 'show_bottom' ? 'selected' : '' }}>Noticia - Abajo</option>
                                    <option value="popup" {{ old('location', $banner->location) == 'popup' ? 'selected' : '' }}>Publicidad Emergente (Popup - Portada)</option>
                                </select>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Posición (Orden)</label>
                                <input type="number" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', $banner->position) }}">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="active" name="active" value="1" {{ old('active', $banner->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Banner Activo</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="image" class="form-label">Imagen del Banner</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-3 border rounded p-2 text-center bg-light">
                                <img id="imagePreview" src="{{ asset($banner->image_path) }}" alt="Vista previa" style="max-width: 100%; max-height: 200px;">
                                <p id="noImageText" class="text-muted mb-0" style="display: none;">Sin imagen seleccionada</p>
                            </div>
                            <small class="text-muted d-block mt-2">Deja vacío para mantener la imagen actual.</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const noImageText = document.getElementById('noImageText');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                noImageText.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
