@extends('layouts.admin')

@section('title', ($isEdit ? 'Editar' : 'Publicar') . ' Artículo de Opinión - Latitud 18 Admin')
@section('page-title', ($isEdit ? 'Editar' : 'Publicar') . ' Artículo de Opinión')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-pen-nib text-danger me-2"></i> {{ $isEdit ? 'Editar Artículo de Opinión' : 'Redactar Artículo de Opinión' }}
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

                    <form action="{{ $isEdit ? route('admin.opinion.articulos.update', $articulo->id) : route('admin.opinion.articulos.store') }}" 
                          method="POST">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label for="columnista_id" class="form-label fw-bold">Columnista / Autor <span class="text-danger">*</span></label>
                                    <select name="columnista_id" id="columnista_id" class="form-select" required>
                                        <option value="">Selecciona el autor de la columna...</option>
                                        @foreach($columnistas as $col)
                                            <option value="{{ $col->id }}" {{ old('columnista_id', $articulo->columnista_id) == $col->id ? 'selected' : '' }}>
                                                {{ $col->nombre }} ({{ $col->cargo ?? 'Columnista' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label fw-bold">Tipo de Artículo <span class="text-danger">*</span></label>
                                    <select name="tipo" id="tipo" class="form-select" required>
                                        @foreach(['COLUMNA' => 'Columna', 'EDITORIAL' => 'Editorial', 'ANÁLISIS' => 'Análisis', 'COMENTARIO' => 'Comentario'] as $key => $label)
                                            <option value="{{ $key }}" {{ old('tipo', $articulo->tipo ?? 'COLUMNA') == $key ? 'selected' : '' }}>
                                                {{ $label }} ({{ $key }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">Aparecerá en la viñeta roja sobre el título en la portada.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label fw-bold">Título de la Columna <span class="text-danger">*</span></label>
                                    <input type="text" name="titulo" id="titulo" class="form-control form-control-lg" 
                                           value="{{ old('titulo', $articulo->titulo) }}" required placeholder="ej. Bolivia necesita instituciones fuertes y reglas claras">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="contenido" class="form-label fw-bold">Texto de la Columna <span class="text-danger">*</span></label>
                                    <textarea name="contenido" id="contenido" rows="8" class="form-control" required placeholder="Escribe el texto de la opinión o columna editorial...">{{ old('contenido', $articulo->contenido) }}</textarea>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" name="publicado" id="publicado" class="form-check-input" value="1" {{ old('publicado', $articulo->publicado ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="publicado">¿Publicar inmediatamente en portada?</label>
                                    <div class="form-text text-muted">Si se desmarca, se guardará en borrador.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('admin.opinion.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Actualizar Artículo' : 'Publicar Artículo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
