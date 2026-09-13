@extends('layouts.main')

@section('title', 'Búsqueda: ' . $query . ' - Latitud18')

@section('content')
<!-- Barra de Búsqueda -->
<style>
  .search-results-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 32px;
  }
  @media (max-width: 991px) {
    .search-results-grid {
      grid-template-columns: repeat(2, 1fr) !important;
    }
  }
  @media (max-width: 640px) {
    .search-results-grid {
      grid-template-columns: 1fr !important;
    }
  }
</style>

<section style="background:var(--color-navy);padding:24px 0;">
  <div class="container">
    <div style="max-width:600px;margin:0 auto;position:relative;">
      <h1 style="font-family:var(--font-title-bebas);font-size:2rem;color:#fff;text-align:center;margin-bottom:16px;letter-spacing:1px;">BUSCADOR DE NOTICIAS</h1>
      <form action="{{ route('search') }}" method="GET" style="display:flex;">
        <input type="text" name="q" value="{{ $query }}" placeholder="Buscar noticias..." style="flex:1;padding:10px 16px 10px 16px;font-family:var(--font-body);font-size:0.9rem;background:var(--color-card-bg);color:var(--color-text-main);border:2px solid var(--color-red);border-right:none;border-radius:2px 0 0 2px;outline:none;">
        <button type="submit" style="padding:10px 24px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.8rem;text-transform:uppercase;border:none;border-radius:0 2px 2px 0;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
          <i class="fas fa-search" style="margin-right:6px;"></i> BUSCAR
        </button>
      </form>
    </div>
  </div>
</section>

<!-- Resultados -->
<section style="padding:28px 0 40px;overflow-x:hidden;">
  <div class="container">
    <div style="max-width:960px;margin:0 auto;">
      
      <!-- Header de Resultados -->
      <div style="margin-bottom:24px;padding-bottom:14px;border-bottom:2px solid var(--color-navy);">
        <h2 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1.1rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--color-navy);margin-bottom:6px;">Resultados de Búsqueda</h2>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <p style="font-size:0.88rem;color:var(--color-text-muted);">
            <span style="font-weight:700;color:var(--color-red);">{{ $total }}</span>
            {{ $total == 1 ? 'resultado encontrado' : 'resultados encontrados' }}
            para "<span style="font-weight:700;color:var(--color-text-main);">{{ $query }}</span>"
          </p>
          @if($total > 0)
            <span style="font-size:0.75rem;color:var(--color-text-muted);">Mostrando {{ $noticias->firstItem() }} - {{ $noticias->lastItem() }} de {{ $total }}</span>
          @endif
        </div>
      </div>

      @if($noticias->count() > 0)
        <!-- Grid de Resultados -->
        <div class="search-results-grid">
          @foreach($noticias as $noticia)
            <a href="{{ $noticia->url }}" style="display:block;text-decoration:none;border-radius:2px;overflow:hidden;background:var(--color-card-bg);border:1px solid var(--color-border);transition:all 0.3s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
              <div style="position:relative;overflow:hidden;">
                <img src="{{ $noticia->imagenUrl ?? asset('images/default-news.svg') }}" alt="{{ $noticia->titulo }}" style="width:100%;height:170px;object-fit:cover;transition:transform 0.5s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onerror="handleImageError(this)">
                <span class="card-cat-tag" style="position:absolute;top:8px;left:8px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.6rem;text-transform:uppercase;padding:2px 7px;border-radius:2px;">{{ $noticia->category->name ?? 'General' }}</span>
                @if(\Carbon\Carbon::parse($noticia->created_at)->diffInHours() < 6)
                  <span style="position:absolute;top:8px;right:8px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.55rem;padding:2px 6px;border-radius:2px;animation:pulse 1.5s infinite;">NUEVO</span>
                @endif
              </div>
              <div style="padding:14px 16px;">
                <span style="font-size:0.72rem;color:var(--color-text-muted);display:flex;align-items:center;gap:4px;margin-bottom:8px;"><i class="far fa-clock" style="color:var(--color-red);"></i> {{ \Carbon\Carbon::parse($noticia->created_at)->locale('es')->diffForHumans() }}</span>
                <h3 style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.92rem;color:var(--color-text-main);line-height:1.35;margin-bottom:6px;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'">{{ Str::limit($noticia->titulo, 70) }}</h3>
                <p style="font-size:0.8rem;color:var(--color-text-muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 100) }}</p>
                <span style="display:inline-flex;align-items:center;gap:4px;margin-top:10px;font-family:var(--font-title-montserrat);font-weight:700;font-size:0.7rem;color:var(--color-red);text-transform:uppercase;">Leer más <i class="fas fa-arrow-right" style="font-size:0.6rem;"></i></span>
              </div>
            </a>
          @endforeach
        </div>

        <!-- Paginación -->
        <div style="text-align:center;">
          {{ $noticias->appends(['q' => $query])->links() }}
        </div>
      @else
        <!-- Sin Resultados -->
        <div style="text-align:center;padding:48px 0;">
          <div style="width:64px;height:64px;background:var(--color-navy-subtle);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
            <i class="fas fa-search" style="font-size:1.5rem;color:var(--color-text-muted);"></i>
          </div>
          <h2 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1.2rem;color:var(--color-navy);margin-bottom:8px;">No se encontraron resultados</h2>
          <p style="font-size:0.9rem;color:var(--color-text-muted);margin-bottom:24px;max-width:400px;margin-left:auto;margin-right:auto;">
            No pudimos encontrar noticias que coincidan con "<strong style="color:var(--color-text-main);">{{ $query }}</strong>". Intenta con otros términos.
          </p>
          <div style="background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;padding:20px;max-width:360px;margin:0 auto 24px;text-align:left;">
            <h4 style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.82rem;color:var(--color-navy);margin-bottom:10px;">Sugerencias:</h4>
            <ul style="font-size:0.82rem;color:var(--color-text-muted);padding-left:16px;line-height:1.8;">
              <li>Verifica la ortografía</li>
              <li>Intenta con términos más generales</li>
              <li>Usa palabras clave diferentes</li>
              <li>Explora nuestras categorías</li>
            </ul>
          </div>
          <a href="{{ route('portada') }}" style="display:inline-flex;align-items:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.8rem;text-transform:uppercase;padding:10px 24px;border-radius:2px;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
            <i class="fas fa-home"></i> Volver al Inicio
          </a>
        </div>
      @endif
    </div>
  </div>
</section>

<!-- Categorías Sugeridas -->
@if($noticias->count() == 0)
<section style="padding:24px 0;background:var(--color-navy-subtle);border-top:1px solid var(--color-border);">
  <div class="container">
    <div style="max-width:600px;margin:0 auto;text-align:center;">
      <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-navy);margin-bottom:14px;">Explora nuestras categorías</h3>
      <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px;">
        @foreach($categorias as $categoria)
          <a href="{{ route('categoria.noticias', $categoria->id) }}" style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--color-navy);background:var(--color-card-bg);padding:6px 16px;border-radius:2px;text-decoration:none;border:1px solid var(--color-border);transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff';this.style.borderColor='var(--color-red)'" onmouseout="this.style.background='var(--color-card-bg)';this.style.color='var(--color-navy)';this.style.borderColor='var(--color-border)'">
            {{ $categoria->name }}
          </a>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif
@endsection
