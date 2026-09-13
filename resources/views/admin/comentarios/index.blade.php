@extends('layouts.admin')

@section('title', 'Moderación de Comentarios - Panel de Administración')
@section('page-title', 'Moderación de Comentarios de Lectores')

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tarjetas de Estadísticas Rápidas --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.comentarios.index') }}" class="card shadow-sm border-0 text-decoration-none {{ !request('estado') ? 'border-start border-4 border-danger' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Todos los Comentarios</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totalAprobados + $totalPendientes }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-secondary">
                        <i class="fas fa-comments fs-4"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.comentarios.index', ['estado' => 'aprobados']) }}" class="card shadow-sm border-0 text-decoration-none {{ request('estado') === 'aprobados' ? 'border-start border-4 border-success' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Visibles / Aprobados</div>
                        <div class="fs-4 fw-bold text-success">{{ $totalAprobados }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-success">
                        <i class="fas fa-check-circle fs-4"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.comentarios.index', ['estado' => 'pendientes']) }}" class="card shadow-sm border-0 text-decoration-none {{ request('estado') === 'pendientes' ? 'border-start border-4 border-warning' : '' }}">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Ocultos / Pendientes</div>
                        <div class="fs-4 fw-bold text-warning">{{ $totalPendientes }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-warning">
                        <i class="fas fa-clock fs-4"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabla de Comentarios --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                <i class="fas fa-comment-dots text-danger me-2"></i> Listado de Comentarios
            </h5>

            <form action="{{ route('admin.comentarios.index') }}" method="GET" class="d-flex gap-2" style="max-width: 320px;">
                @if(request('estado'))
                    <input type="hidden" name="estado" value="{{ request('estado') }}">
                @endif
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar por autor, texto...">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($comentarios->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-comments fa-3x mb-3 text-secondary opacity-50"></i>
                    <p class="mb-0">No se encontraron comentarios.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 220px;">Autor & Contacto</th>
                                <th>Comentario</th>
                                <th style="width: 260px;">Noticia</th>
                                <th style="width: 120px;" class="text-center">Estado</th>
                                <th style="width: 140px;" class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comentarios as $c)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-danger text-white fw-bold d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($c->nombre, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $c->nombre }}</div>
                                                <small class="text-muted d-block text-truncate" style="max-width: 160px;">{{ $c->email ?? 'Sin correo' }}</small>
                                                <small class="text-muted" style="font-size: 0.7rem;">IP: {{ $c->ip_address ?? '—' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark py-1" style="font-size: 0.88rem; line-height: 1.45;">
                                            {{ $c->contenido }}
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($c->created_at)->locale('es')->diffForHumans() }} ({{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y H:i') }})
                                        </small>
                                    </td>
                                    <td>
                                        @if($c->noticia)
                                            <a href="{{ $c->noticia->url }}" target="_blank" class="text-decoration-none fw-semibold text-dark text-truncate d-block" style="max-width: 240px;" title="{{ $c->noticia->titulo }}">
                                                <i class="fas fa-external-link-alt text-muted me-1" style="font-size: 0.75rem;"></i> {{ $c->noticia->titulo }}
                                            </a>
                                            <small class="badge bg-light text-muted border mt-1">
                                                {{ $c->noticia->category->name ?? 'General' }}
                                            </small>
                                        @else
                                            <span class="text-muted small">Noticia eliminada</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($c->aprobado)
                                            <span class="badge bg-success" style="font-size: 0.72rem;">
                                                <i class="fas fa-check me-1"></i> Visible
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="font-size: 0.72rem;">
                                                <i class="fas fa-eye-slash me-1"></i> Oculto
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Toggle Aprobar/Ocultar --}}
                                            <form action="{{ route('admin.comentarios.toggle', $c->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $c->aprobado ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $c->aprobado ? 'Ocultar comentario' : 'Aprobar comentario' }}">
                                                    <i class="fas {{ $c->aprobado ? 'fa-eye-slash' : 'fa-check' }}"></i>
                                                </button>
                                            </form>

                                            {{-- Eliminar --}}
                                            <form action="{{ route('admin.comentarios.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este comentario permanentemente?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
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
            @endif
        </div>

        @if($comentarios->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $comentarios->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
