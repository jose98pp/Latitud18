@extends('layouts.main')

@section('title', $categoria->name . ' - Latitud18')

@section('content')
<!-- Header de Categoría -->
<style>
  .category-layout-grid {
    display: grid;
    grid-template-columns: 2.5fr 1fr;
    gap: 28px;
    width: 100%;
  }
  .cat-feed-item-card {
    display: grid;
    grid-template-columns: 1.2fr 2fr;
    gap: 0;
    background: var(--color-card-bg);
    border: 1px solid var(--color-border);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 16px;
    transition: all 0.3s;
  }
  @media (max-width: 991px) {
    .category-layout-grid {
      grid-template-columns: 1fr !important;
    }
  }
  @media (max-width: 640px) {
    .cat-feed-item-card {
      grid-template-columns: 1fr !important;
    }
    .cat-banner-title {
      font-size: 2.2rem !important;
    }
  }
</style>

<section style="background:var(--color-navy);border-bottom:3px solid var(--color-red);padding:28px 0 24px;">
  <div class="container">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;font-size:0.78rem;">
      <a href="{{ route('portada') }}" style="color:#94A3B8;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fas fa-home" style="margin-right:4px;"></i> Inicio</a>
      <span style="color:#475569;">›</span>
      <span style="color:#fff;font-weight:600;">{{ $categoria->name }}</span>
    </div>
    <h1 class="cat-banner-title" style="font-family:var(--font-title-bebas);font-size:3rem;color:#fff;line-height:1;letter-spacing:1px;">{{ strtoupper($categoria->name) }}</h1>
    <p style="font-size:0.85rem;color:#94A3B8;margin-top:6px;">{{ $categoria->noticias->count() }} artículos disponibles</p>
  </div>
</section>

<!-- Filtros por Categoría -->
<section style="background:var(--color-card-bg);border-bottom:1px solid var(--color-border);padding:10px 0;">
  <div class="container">
    <div class="cat-tags-filter-bar" style="display:flex;gap:8px;flex-wrap:wrap;">
      @foreach($categorias as $cat)
        <a href="{{ route('categoria.noticias', $cat->id) }}" class="cat-tag-pill" style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;padding:5px 14px;border-radius:20px;text-decoration:none;transition:all 0.2s;{{ $cat->id === $categoria->id ? 'background:var(--color-red);color:#fff;' : 'background:var(--color-navy-subtle);color:var(--color-navy);border:1px solid var(--color-border);' }}" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff';this.style.borderColor='var(--color-red)'" onmouseout="{{ $cat->id !== $categoria->id ? "this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-navy)';this.style.borderColor='var(--color-border)'" : '' }}">
          {{ $cat->name }}
        </a>
      @endforeach
    </div>
  </div>
</section>

<!-- Contenido: Feed + Sidebar -->
<section style="padding:28px 0 40px;overflow-x:hidden;">
  <div class="container">
    <div class="category-layout-grid">

      <!-- Feed de Artículos -->
      <div class="category-articles-feed" style="min-width:0;">
        @forelse($noticiasCategoria as $index => $noticia)
          <article class="cat-feed-item-card" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
            <!-- Imagen -->
            <a href="{{ $noticia->url }}" class="cat-feed-media" style="position:relative;overflow:hidden;">
              <img src="{{ $noticia->imagenUrl ?? asset('images/default-news.svg') }}" alt="{{ $noticia->titulo }}" style="width:100%;height:100%;min-height:200px;object-fit:cover;transition:transform 0.5s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onerror="handleImageError(this)">
              <span class="card-cat-tag" style="position:absolute;top:8px;left:8px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.6rem;text-transform:uppercase;padding:2px 7px;border-radius:2px;">{{ $categoria->name }}</span>
            </a>
            
            <!-- Contenido -->
            <div style="padding:16px 20px;display:flex;flex-direction:column;justify-content:space-between;">
              <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                  <span style="font-size:0.72rem;color:var(--color-text-muted);display:flex;align-items:center;gap:4px;"><i class="far fa-clock" style="color:var(--color-red);"></i> {{ \Carbon\Carbon::parse($noticia->created_at)->locale('es')->diffForHumans() }}</span>
                </div>
                <a href="{{ $noticia->url }}" style="text-decoration:none;display:block;">
                  <h2 class="cat-feed-title" style="font-family:var(--font-title-montserrat);font-weight:800;font-size:1.15rem;color:var(--color-text-main);line-height:1.35;margin-bottom:8px;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'">{{ $noticia->titulo }}</h2>
                  <p class="cat-feed-excerpt" style="font-size:0.88rem;color:var(--color-text-muted);line-height:1.6;">{{ $noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 160) }}</p>
                </a>
              </div>
              <div class="cat-feed-foot" style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px solid var(--color-border);">
                <div style="display:flex;gap:8px;">
                  <button style="background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:0.85rem;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-muted)'"><i class="far fa-heart"></i></button>
                  <button style="background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:0.85rem;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-muted)'"><i class="far fa-share-square"></i></button>
                </div>
                <a href="{{ $noticia->url }}" style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--color-navy);background:var(--color-navy-subtle);padding:6px 14px;border-radius:2px;text-decoration:none;border:1px solid var(--color-border);transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff';this.style.borderColor='var(--color-red)'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-navy)';this.style.borderColor='var(--color-border)'">
                  LEER <i class="fas fa-arrow-right" style="font-size:0.6rem;margin-left:4px;"></i>
                </a>
              </div>
            </div>
          </article>
        @empty
          <div style="text-align:center;padding:48px 0;">
            <div style="width:64px;height:64px;background:var(--color-navy-subtle);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
              <i class="fas fa-newspaper" style="font-size:1.5rem;color:var(--color-text-muted);"></i>
            </div>
            <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1.1rem;color:var(--color-navy);margin-bottom:6px;">No hay noticias aún</h3>
            <p style="font-size:0.88rem;color:var(--color-text-muted);margin-bottom:20px;">Estamos trabajando en traer las últimas novedades de esta categoría.</p>
            <a href="{{ route('portada') }}" style="display:inline-flex;align-items:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.8rem;text-transform:uppercase;padding:10px 24px;border-radius:2px;text-decoration:none;">
              <i class="fas fa-home"></i> Volver al Inicio
            </a>
          </div>
        @endforelse

        <!-- Paginación -->
        @if($noticiasCategoria->hasPages())
          <div style="margin-top:24px;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;padding:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
              <span style="font-size:0.78rem;color:var(--color-text-muted);">Mostrando <strong style="color:var(--color-red);">{{ $noticiasCategoria->firstItem() }}</strong> - <strong style="color:var(--color-red);">{{ $noticiasCategoria->lastItem() }}</strong> de <strong style="color:var(--color-text-main);">{{ $noticiasCategoria->total() }}</strong></span>
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:0.75rem;color:var(--color-text-muted);">Por página:</span>
                <select onchange="changePerPage(this.value)" style="background:var(--color-navy-subtle);border:1px solid var(--color-border);color:var(--color-text-main);font-family:var(--font-body);font-size:0.78rem;padding:4px 8px;border-radius:2px;outline:none;">
                  <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                  <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                  <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
              </div>
            </div>
            <div style="border-top:1px solid var(--color-border);padding-top:12px;text-align:center;">
              {{ $noticiasCategoria->links() }}
            </div>
          </div>
        @endif
      </div>

      <!-- Sidebar -->
      <aside>
        <!-- Widget Categorías -->
        <div style="background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;overflow:hidden;margin-bottom:16px;">
          <div style="background:var(--color-navy);color:#fff;padding:12px 14px;font-family:var(--font-title-montserrat);font-weight:900;font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-folder" style="font-size:0.8rem;"></i> Secciones
          </div>
          <div style="padding:10px;">
            @foreach($categorias as $cat)
              <a href="{{ route('categoria.noticias', $cat->id) }}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;text-decoration:none;color:var(--color-text-main);font-size:0.82rem;border-bottom:1px solid var(--color-border);transition:all 0.2s;{{ $cat->id === $categoria->id ? 'background:var(--color-red);color:#fff;border-color:var(--color-red);font-weight:700;' : '' }}" onmouseover="{{ $cat->id !== $categoria->id ? "this.style.background='var(--color-navy-subtle)'" : '' }}" onmouseout="{{ $cat->id !== $categoria->id ? "this.style.background='none'" : '' }}">
                <i class="fas fa-chevron-right" style="font-size:0.6rem;color:{{ $cat->id === $categoria->id ? '#fff' : 'var(--color-red)' }};"></i>
                {{ $cat->name }}
              </a>
            @endforeach
          </div>
        </div>

        <!-- Widget Edición Semanal Tabloide (Latitud 18) -->
        <div style="background:var(--color-navy);color:#fff;border-radius:2px;padding:18px;margin-bottom:16px;box-shadow:var(--shadow-sm);border-top:3px solid var(--color-red);">
          <div style="display:flex;align-items:center;gap:8px;font-family:var(--font-title-montserrat);font-weight:900;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">
            <i class="fa-solid fa-newspaper" style="color:var(--color-red);"></i> EDICIÓN DIGITAL TABLOIDE
          </div>
          <p style="font-size:0.8rem;color:#CBD5E1;line-height:1.45;margin-bottom:14px;">
            Accede al periódico semanal maquetado en estilo tabloide listo para leer y descargar.
          </p>
          <a href="{{ route('periodico.public.index') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;text-transform:uppercase;padding:9px 16px;border-radius:2px;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
            <i class="fa-solid fa-file-pdf"></i> ABRIR PERIÓDICO DIGITAL
          </a>
        </div>

        <!-- Widget Últimas Noticias -->
        <div style="background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;overflow:hidden;box-shadow:var(--shadow-sm);">
          <div style="background:var(--color-navy);color:#fff;padding:12px 14px;font-family:var(--font-title-montserrat);font-weight:900;font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;display:flex;align-items:center;gap:6px;border-bottom:2px solid var(--color-red);">
            <i class="fa-solid fa-chart-line" style="color:var(--color-red);font-size:0.8rem;"></i> LO MÁS LEÍDO
          </div>
          <div style="padding:10px;">
            @foreach($noticias->take(5) as $index => $otraNoticia)
              <a href="{{ $otraNoticia->url }}" style="display:flex;gap:10px;padding:8px 0;text-decoration:none;{{ !$loop->last ? 'border-bottom:1px dashed var(--color-border);' : '' }}">
                <span style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1.2rem;color:var(--color-red);min-width:24px;line-height:1;">0{{ $index + 1 }}</span>
                <div>
                  <h4 style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.8rem;color:var(--color-text-main);line-height:1.3;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'">{{ Str::limit($otraNoticia->titulo, 60) }}</h4>
                  <span style="font-size:0.68rem;color:var(--color-text-muted);display:flex;align-items:center;gap:4px;margin-top:3px;"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($otraNoticia->created_at)->locale('es')->diffForHumans() }}</span>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      </aside>

    </div>
  </div>
</section>

<!-- Banner Publicitario -->
@if(isset($banners['category_bottom']) && $banners['category_bottom']->count() > 0)
  <section style="padding:20px 0;">
    <div class="container" style="text-align:center;">
      <span style="font-size:0.65rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">PUBLICIDAD</span>
      @foreach($banners['category_bottom'] as $banner)
        <a href="{{ $banner->link ?? '#' }}" target="_blank" style="display:block;max-width:800px;margin:8px auto 0;">
          <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width:100%;border-radius:2px;box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
        </a>
      @endforeach
    </div>
  </section>
@endif

<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>
@endsection
