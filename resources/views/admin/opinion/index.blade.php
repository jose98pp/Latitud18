@extends('layouts.admin')

@section('title', 'Opinión y Columnistas - Panel de Administración')
@section('page-title', 'Gestión de Opinión y Columnistas')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- SECCIÓN 1: COLUMNISTAS --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fas fa-user-tie text-danger me-2"></i> Columnistas Registrados
                    </h5>
                    <a href="{{ route('admin.opinion.columnistas.create') }}" class="btn btn-sm btn-danger d-flex align-items-center">
                        <i class="fas fa-plus me-1"></i> Nuevo Columnista
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($columnistas->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-user-edit fa-3x mb-3 text-secondary opacity-50"></i>
                            <p class="mb-2">No hay columnistas registrados aún.</p>
                            <a href="{{ route('admin.opinion.columnistas.create') }}" class="btn btn-sm btn-outline-danger">
                                Registrar Primer Columnista
                            </a>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($columnistas as $col)
                                <div class="list-group-item p-3 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $col->avatar_url }}" alt="{{ $col->nombre }}" 
                                             class="rounded-circle me-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $col->nombre }}</h6>
                                            <small class="text-muted">{{ $col->cargo ?? 'Columnista' }}</small>
                                            <div class="mt-1">
                                                <span class="badge {{ $col->activo ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                                    {{ $col->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                                <span class="badge bg-light text-dark border ms-1" style="font-size: 0.7rem;">
                                                    {{ $col->articulos_count }} columnas
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.opinion.columnistas.edit', $col->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.opinion.columnistas.destroy', $col->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro de eliminar este columnista y todas sus columnas?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: ARTÍCULOS DE OPINIÓN --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fas fa-pen-nib text-danger me-2"></i> Artículos y Columnas de Opinión
                    </h5>
                    <a href="{{ route('admin.opinion.articulos.create') }}" class="btn btn-sm btn-danger d-flex align-items-center">
                        <i class="fas fa-plus me-1"></i> Publicar Columna
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($articulos->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-newspaper fa-3x mb-3 text-secondary opacity-50"></i>
                            <p class="mb-2">No hay artículos de opinión publicados aún.</p>
                            @if($columnistas->isNotEmpty())
                                <a href="{{ route('admin.opinion.articulos.create') }}" class="btn btn-sm btn-outline-danger">
                                    Publicar Primer Artículo
                                </a>
                            @else
                                <small class="text-muted d-block">Primero registra al menos un columnista a la izquierda.</small>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Título / Columna</th>
                                        <th>Autor</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($articulos as $art)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 260px;" title="{{ $art->titulo }}">
                                                    {{ $art->titulo }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($art->created_at)->format('d/m/Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $art->columnista->avatar_url ?? '' }}" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;">
                                                    <span class="small fw-semibold">{{ $art->columnista->nombre ?? 'Sin asignar' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.7rem;">
                                                    {{ $art->tipo }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($art->publicado)
                                                    <span class="badge bg-success" style="font-size: 0.7rem;">Publicado</span>
                                                @else
                                                    <span class="badge bg-secondary" style="font-size: 0.7rem;">Borrador</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.opinion.articulos.edit', $art->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.opinion.articulos.destroy', $art->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro de eliminar este artículo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 border-top">
                            {{ $articulos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
