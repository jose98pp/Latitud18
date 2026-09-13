@extends('layouts.admin')

@section('title', 'Dashboard - Latitud18 Admin')
@section('page-title', 'Panel de Administración')

@push('styles')
<style>
    .dashboard-card {
        background: var(--lat-bg-card, #FFFFFF);
        border-radius: 12px;
        border: 1px solid var(--lat-border, #E5E7EB);
        transition: all 0.3s ease;
        animation: fadeInUp 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(11, 31, 58, 0.08);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-number {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        color: var(--lat-text, #1A1A2E);
    }
    
    .quick-action-card {
        border-radius: 12px;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30px, -30px);
    }
    
    .quick-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .activity-item {
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    
    .activity-item:hover {
        background-color: var(--lat-bg, #F0F2F5) !important;
        transform: translateX(5px);
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
    .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
    .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
    .dashboard-card:nth-child(4) { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-card p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="h3 mb-2 fw-bold" style="font-family:'Montserrat',sans-serif;color:var(--lat-text);">
                            ¡Bienvenido de vuelta, {{ auth()->user()->name }}!
                        </h2>
                        <p class="mb-0" style="font-family:'Montserrat',sans-serif;color:var(--lat-text-muted);">
                            Aquí tienes un resumen de tu actividad y accesos rápidos para gestionar tu contenido de <strong>Latitud18</strong>.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.noticias.create') }}" class="btn btn-latitud">
                                <i class="fas fa-plus me-2"></i>Nueva Noticia
                            </a>
                            <a href="{{ route('portada') }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-external-link-alt me-2"></i>Ver Sitio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-bold text-danger text-uppercase mb-1">
                            Total Noticias
                        </div>
                        <div class="h4 mb-0 stat-number">{{ $stats['total_published'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-bold text-warning text-uppercase mb-1">
                            Borradores
                        </div>
                        <div class="h4 mb-0 stat-number">{{ $stats['total_draft'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-bold text-success text-uppercase mb-1">
                            Categorías
                        </div>
                        <div class="h4 mb-0 stat-number">{{ $stats['total_categories'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="dashboard-card p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-bold text-info text-uppercase mb-1">
                            Esta Semana
                        </div>
                        <div class="h4 mb-0 stat-number">{{ $stats['recent_news'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="quick-action-card p-4 h-100 position-relative" style="background: linear-gradient(135deg, #D71920 0%, #a81319 100%);">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-newspaper fa-2x me-3"></i>
                    <h5 class="mb-0 fw-bold" style="font-family:'Montserrat',sans-serif;">Noticias</h5>
                </div>
                <p class="mb-4 opacity-90" style="font-family:'Source Sans 3',sans-serif;">Administra todas las noticias de tu sitio web.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.noticias.index') }}" class="btn btn-light">
                        <i class="fas fa-list me-2"></i>Ver Todas
                    </a>
                    <a href="{{ route('admin.noticias.create') }}" class="btn btn-outline-light">
                        <i class="fas fa-plus me-2"></i>Nueva Noticia
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="quick-action-card p-4 h-100 position-relative" style="background: linear-gradient(135deg, #0B1F3A 0%, #071322 100%);">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-file-pdf fa-2x me-3"></i>
                    <h5 class="mb-0 fw-bold" style="font-family:'Montserrat',sans-serif;">Periódico Digital</h5>
                </div>
                <p class="mb-4 opacity-90" style="font-family:'Source Sans 3',sans-serif;">Diseña el periódico semanal descargable.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.periodico.index') }}" class="btn btn-light">
                        <i class="fas fa-edit me-2"></i>Abrir Editor
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="quick-action-card p-4 h-100 position-relative" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-tags fa-2x me-3"></i>
                    <h5 class="mb-0 fw-bold" style="font-family:'Montserrat',sans-serif;">Categorías</h5>
                </div>
                <p class="mb-4 opacity-90" style="font-family:'Source Sans 3',sans-serif;">Organiza tus noticias por categorías.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.categorias.index') }}" class="btn btn-light">
                        <i class="fas fa-list me-2"></i>Ver Categorías
                    </a>
                    <a href="{{ route('admin.categorias.create') }}" class="btn btn-outline-light">
                        <i class="fas fa-plus me-2"></i>Nueva
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="quick-action-card p-4 h-100 position-relative" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-images fa-2x me-3"></i>
                    <h5 class="mb-0 fw-bold" style="font-family:'Montserrat',sans-serif;">Banners</h5>
                </div>
                <p class="mb-4 opacity-90" style="font-family:'Source Sans 3',sans-serif;">Gestiona los banners publicitarios.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-light">
                        <i class="fas fa-list me-2"></i>Ver Banners
                    </a>
                    <a href="{{ route('admin.banners.create') }}" class="btn btn-outline-light">
                        <i class="fas fa-plus me-2"></i>Nuevo
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 fw-bold" style="font-family:'Montserrat',sans-serif;color:var(--lat-text);">
                            <i class="fas fa-clock me-2 text-danger"></i>Actividad Reciente
                        </h5>
                        <a href="{{ route('admin.noticias.index') }}" class="btn btn-sm btn-outline-secondary">
                            Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(isset($recentNews) && $recentNews->count() > 0)
                        <div class="row">
                            @foreach($recentNews as $noticia)
                                <div class="col-12 mb-3">
                                    <div class="activity-item d-flex align-items-center p-3" style="background:var(--lat-bg,#F0F2F5);">
                                        <div class="flex-shrink-0 me-3">
                                            @if($noticia->imagen)
                                                <img src="{{ $noticia->imagenUrl }}" 
                                                     alt="{{ $noticia->titulo }}" 
                                                     class="rounded" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold" style="font-family:'Montserrat',sans-serif;">{{ Str::limit($noticia->titulo, 60) }}</h6>
                                            <p class="mb-1 small" style="font-family:'Montserrat',sans-serif;color:var(--lat-text-muted);">
                                                <i class="fas fa-tag me-1"></i>{{ $noticia->category->name ?? 'Sin categoría' }}
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-calendar me-1"></i>{{ $noticia->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $noticia->publicada ? 'bg-success' : 'bg-warning' }}">
                                                {{ $noticia->publicada ? 'Publicada' : 'Borrador' }}
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.noticias.edit', $noticia->id) }}">
                                                            <i class="fas fa-edit me-2"></i>Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('show', $noticia->id) }}" target="_blank">
                                                            <i class="fas fa-eye me-2"></i>Ver
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper" style="font-size:4rem;color:var(--lat-border,#D1D5DB);"></i>
                            <h5 class="mt-3" style="font-family:'Montserrat',sans-serif;color:var(--lat-text-muted);">No hay noticias recientes</h5>
                            <p style="font-family:'Source Sans 3',sans-serif;color:var(--lat-text-muted);">Comienza creando tu primera noticia para ver la actividad aquí.</p>
                            <a href="{{ route('admin.noticias.create') }}" class="btn btn-latitud mt-3">
                                <i class="fas fa-plus me-2"></i>Crear Primera Noticia
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
