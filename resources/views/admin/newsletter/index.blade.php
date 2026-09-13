@extends('layouts.admin')

@section('title', 'Suscriptores del Boletín (Newsletter) - Panel de Administración')
@section('page-title', 'Suscriptores del Boletín Digital')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tarjetas de Estadísticas --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Suscriptores</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totalSubscribers }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fas fa-envelope-open-text fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Suscripciones Activas</div>
                        <div class="fs-4 fw-bold text-success">{{ $activeSubscribers }}</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fas fa-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-secondary">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Desactivados / Cancelados</div>
                        <div class="fs-4 fw-bold text-secondary">{{ $inactiveSubscribers }}</div>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary">
                        <i class="fas fa-user-slash fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barra de Herramientas y Filtros --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-md-8">
                    <form action="{{ route('admin.newsletter.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-auto flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Buscar por email o nombre..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-auto">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Solo Activos</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Solo Inactivos</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-dark">Filtrar</button>
                            @if(request()->hasAny(['search', 'status']))
                                <a href="{{ route('admin.newsletter.index') }}" class="btn btn-link text-muted text-decoration-none">Limpiar</a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('admin.newsletter.export', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-file-csv me-1"></i> Exportar Suscriptores (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Suscriptores --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">#</th>
                            <th>Correo Electrónico</th>
                            <th>Nombre</th>
                            <th>Origen</th>
                            <th>Fecha de Registro</th>
                            <th>Estado</th>
                            <th class="pe-4 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $subscriber)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $subscriber->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <a href="mailto:{{ $subscriber->email }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $subscriber->email }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $subscriber->nombre ?: '—' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-globe-americas me-1 text-muted"></i>{{ $subscriber->origen ?: 'Portada' }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $subscriber->created_at ? $subscriber->created_at->format('d/m/Y H:i') : '—' }}
                                </td>
                                <td>
                                    @if($subscriber->activo)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                            <i class="fas fa-ban me-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <form action="{{ route('admin.newsletter.toggle', $subscriber->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $subscriber->activo ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $subscriber->activo ? 'Desactivar suscriptor' : 'Activar suscriptor' }}">
                                            <i class="fas {{ $subscriber->activo ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST" class="d-inline-block ms-1" onsubmit="return confirm('¿Seguro que deseas eliminar este suscriptor?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar suscriptor">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-envelope-open text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                                    <div class="fs-5">No se encontraron suscriptores registrados</div>
                                    <p class="small mb-0">Los lectores que se suscriban desde el portal aparecerán aquí.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($subscribers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
