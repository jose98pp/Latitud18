@extends('layouts.admin')

@push('styles')
<style>
    .category-card {
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: 1px solid #e3e6f0;
        border-radius: 1rem;
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fc 100%);
        position: relative;
        overflow: hidden;
    }
    
    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4e73df, #224abe);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .category-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(58, 59, 69, 0.2);
        border-color: #4e73df;
    }
    
    .category-card:hover::before {
        transform: scaleX(1);
    }
    
    .category-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        color: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .category-stats::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 0.35rem;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border-left: 4px solid;
    }
    
    .stat-card.primary { 
        border-left-color: #4e73df; 
        background: linear-gradient(135deg, #ffffff 0%, #f0f3ff 100%);
    }
    .stat-card.success { 
        border-left-color: #1cc88a; 
        background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
    }
    .stat-card.info { 
        border-left-color: #36b9cc; 
        background: linear-gradient(135deg, #ffffff 0%, #f0fdff 100%);
    }
    .stat-card.warning { 
        border-left-color: #f6c23e; 
        background: linear-gradient(135deg, #ffffff 0%, #fffdf0 100%);
    }
    
    .stat-card .h5 {
        background: linear-gradient(45deg, #4e73df, #224abe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 800;
    }
    
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .category-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        margin-top: 1rem;
    }
    
    .search-container {
        position: relative;
        max-width: 400px;
    }
    
    .search-container .fas {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .search-container input {
        padding-left: 45px;
    }
    
    /* Gradientes para botones */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #224abe 0%, #1a365d 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(78, 115, 223, 0.3);
    }
    
    .btn-outline-warning:hover {
        background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        transform: translateY(-1px);
    }
    
    .btn-outline-info:hover {
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        transform: translateY(-1px);
    }
    
    /* Animaciones de entrada */
    .category-card {
        animation: slideInUp 0.6s ease-out;
        animation-fill-mode: both;
    }
    
    .category-card:nth-child(1) { animation-delay: 0.1s; }
    .category-card:nth-child(2) { animation-delay: 0.2s; }
    .category-card:nth-child(3) { animation-delay: 0.3s; }
    .category-card:nth-child(4) { animation-delay: 0.4s; }
    .category-card:nth-child(5) { animation-delay: 0.5s; }
    .category-card:nth-child(6) { animation-delay: 0.6s; }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @media (max-width: 768px) {
        .category-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
        
        .category-actions {
            flex-direction: column;
        }
        
        .category-actions .btn {
            width: 100%;
        }
        
        .category-card {
            animation-delay: 0s !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tags text-primary"></i> Gestión de Categorías
            </h1>
            <p class="mb-0 text-muted">Administra las categorías de noticias del sistema</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.categorias.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Categoría
            </a>
        </div>
    </div>

    <!-- Mensajes de éxito o error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                Total Categorías
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $categories->count() }}
            </div>
            <div class="mt-2">
                <i class="fas fa-tags fa-2x text-gray-300"></i>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                Categorías Activas
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $categories->count() }}
            </div>
            <div class="mt-2">
                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                Noticias Totales
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $categories->sum(function($category) { return $category->noticias->count(); }) }}
            </div>
            <div class="mt-2">
                <i class="fas fa-newspaper fa-2x text-gray-300"></i>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                Promedio por Categoría
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $categories->count() > 0 ? round($categories->sum(function($category) { return $category->noticias->count(); }) / $categories->count(), 1) : 0 }}
            </div>
            <div class="mt-2">
                <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-search"></i> Buscar y Filtrar
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="search-container">
                        <i class="fas fa-search"></i>
                        <input type="text" id="category-search" class="form-control" 
                               placeholder="Buscar categorías por nombre...">
                    </div>
                </div>
                <div class="col-md-6">
                    <select id="sort-categories" class="form-control">
                        <option value="name-asc">Ordenar por Nombre (A-Z)</option>
                        <option value="name-desc">Ordenar por Nombre (Z-A)</option>
                        <option value="news-desc">Más Noticias Primero</option>
                        <option value="news-asc">Menos Noticias Primero</option>
                        <option value="id-asc">Más Antiguas Primero</option>
                        <option value="id-desc">Más Recientes Primero</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    @if($categories->isEmpty())
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-tags fa-3x text-gray-300 mb-3"></i>
                <h4 class="text-gray-600">No hay categorías disponibles</h4>
                <p class="text-muted mb-4">Comience creando su primera categoría para organizar las noticias.</p>
                <a href="{{ route('admin.categorias.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Primera Categoría
                </a>
            </div>
        </div>
    @else
        <div class="category-grid" id="categories-container">
            @foreach($categories as $category)
                <div class="category-card card shadow-sm" data-category-name="{{ strtolower($category->name) }}" 
                     data-news-count="{{ $category->noticias->count() }}" data-category-id="{{ $category->id }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-gradient-primary text-white me-3">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold text-primary">{{ $category->name }}</h6>
                                <small class="text-muted">ID: {{ $category->id }}</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.categorias.edit', $category->id) }}">
                                        <i class="fas fa-edit text-warning"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.noticias.index', ['category' => $category->id]) }}">
                                        <i class="fas fa-newspaper text-info"></i> Ver Noticias
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.categorias.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" 
                                                onclick="return confirm('¿Estás seguro de eliminar esta categoría?\n\nEsto también eliminará todas las noticias asociadas.')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="h4 mb-0 text-primary">{{ $category->noticias->count() }}</div>
                                    <small class="text-muted">Noticias</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0 text-success">
                                    {{ $category->noticias->where('publicada', true)->count() }}
                                </div>
                                <small class="text-muted">Publicadas</small>
                            </div>
                        </div>
                        
                        @if($category->noticias->count() > 0)
                            <div class="mt-3">
                                <small class="text-muted">Última noticia:</small>
                                <div class="text-truncate">
                                    <strong>{{ $category->noticias->sortByDesc('created_at')->first()->titulo ?? 'Sin noticias' }}</strong>
                                </div>
                                <small class="text-muted">
                                    {{ $category->noticias->sortByDesc('created_at')->first()->created_at->diffForHumans() ?? '' }}
                                </small>
                            </div>
                        @endif
                        
                        <div class="category-actions">
                            <a href="{{ route('admin.categorias.edit', $category->id) }}" 
                               class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="{{ route('admin.noticias.index', ['category' => $category->id]) }}" 
                               class="btn btn-sm btn-outline-info">
                                <i class="fas fa-newspaper"></i> Noticias
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Paginación -->
    @if($categories->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $categories->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('category-search');
    const sortSelect = document.getElementById('sort-categories');
    const categoriesContainer = document.getElementById('categories-container');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const categoryCards = document.querySelectorAll('.category-card');
        
        categoryCards.forEach(card => {
            const categoryName = card.dataset.categoryName;
            if (categoryName.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Sort functionality
    sortSelect.addEventListener('change', function() {
        const sortBy = this.value;
        const categoryCards = Array.from(document.querySelectorAll('.category-card'));
        
        categoryCards.sort((a, b) => {
            switch(sortBy) {
                case 'name-asc':
                    return a.dataset.categoryName.localeCompare(b.dataset.categoryName);
                case 'name-desc':
                    return b.dataset.categoryName.localeCompare(a.dataset.categoryName);
                case 'news-desc':
                    return parseInt(b.dataset.newsCount) - parseInt(a.dataset.newsCount);
                case 'news-asc':
                    return parseInt(a.dataset.newsCount) - parseInt(b.dataset.newsCount);
                case 'id-desc':
                    return parseInt(b.dataset.categoryId) - parseInt(a.dataset.categoryId);
                case 'id-asc':
                    return parseInt(a.dataset.categoryId) - parseInt(b.dataset.categoryId);
                default:
                    return 0;
            }
        });
        
        // Re-append sorted cards
        categoryCards.forEach(card => {
            categoriesContainer.appendChild(card);
        });
    });
    
    // Add hover effects and animations
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endpush

<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
</style>

@endsection
