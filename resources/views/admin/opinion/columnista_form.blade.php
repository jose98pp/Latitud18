@extends('layouts.admin')

@section('title', ($isEdit ? 'Editar' : 'Nuevo') . ' Columnista - Latitud 18 Admin')
@section('page-title', ($isEdit ? 'Editar' : 'Nuevo') . ' Columnista')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-tie text-danger me-2"></i> {{ $isEdit ? 'Editar Columnista' : 'Registrar Nuevo Columnista' }}
                    </h5>
                    <a href="{{ route('admin.opinion.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ $isEdit ? route('admin.opinion.columnistas.update', $columnista->id) : route('admin.opinion.columnistas.store') }}" 
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" 
                                           value="{{ old('nombre', $columnista->nombre) }}" required placeholder="ej. Lic. Roberto Vaca D.">
                                </div>

                                <div class="mb-3">
                                    <label for="cargo" class="form-label fw-bold">Cargo o Especialidad</label>
                                    <input type="text" name="cargo" id="cargo" class="form-control" 
                                           value="{{ old('cargo', $columnista->cargo) }}" placeholder="ej. Analista Político, Jefe de Redacción, Economista">
                                </div>

                                <div class="mb-3">
                                    <label for="orden" class="form-label fw-bold">Orden de Aparición</label>
                                    <input type="number" name="orden" id="orden" class="form-control" 
                                           value="{{ old('orden', $columnista->orden ?? 0) }}" style="max-width: 140px;">
                                    <div class="form-text text-muted">Dígito numérico (0, 1, 2...) para ordenar quién aparece primero en la portada.</div>
                                </div>
                            </div>

                            <div class="col-md-4 text-center">
                                <label class="form-label fw-bold d-block">Foto de Avatar</label>
                                <div class="mb-3">
                                    <img id="avatarPreview" src="{{ $isEdit ? $columnista->avatar_url : 'https://ui-avatars.com/api/?name=Avatar&background=0B1F3A&color=fff&size=150' }}" 
                                         alt="Avatar" class="rounded-circle border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                                </div>
                                <div class="mb-2">
                                    <input type="file" name="avatar_file" id="avatar_file" class="form-control form-control-sm" accept="image/*" onchange="previewAvatar(event)">
                                    <div class="form-text text-muted">Subir imagen cuadrada (JPEG, PNG, WEBP máx 2MB)</div>
                                </div>
                                <div class="text-muted small my-1">— o ingresar URL directa —</div>
                                <input type="url" name="avatar_url" class="form-control form-control-sm" placeholder="https://..." value="{{ old('avatar_url', filter_var($columnista->avatar, FILTER_VALIDATE_URL) ? $columnista->avatar : '') }}">
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="bio" class="form-label fw-bold">Biografía Corta (Opcional)</label>
                                    <textarea name="bio" id="bio" rows="3" class="form-control" placeholder="Breve perfil profesional del columnista...">{{ old('bio', $columnista->bio) }}</textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" name="activo" id="activo" class="form-check-input" value="1" {{ old('activo', $columnista->activo ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="activo">Columnista Activo (Visible en el sitio)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.opinion.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Actualizar Columnista' : 'Guardar Columnista' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (file) {
        document.getElementById('avatarPreview').src = URL.createObjectURL(file);
    }
}
</script>
@endsection
