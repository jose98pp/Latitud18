@extends('layouts.main')

@section('title', $columnista->nombre . ' | Opinión & Análisis - Latitud 18')

@section('content')
<style>
*, *::before, *::after { box-sizing: border-box; }

.columnist-page { padding: 24px 0 40px; }

/* --- Hero del Columnista --- */
.columnist-hero {
  background: var(--color-navy);
  border-radius: 4px;
  padding: 32px 24px;
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 32px;
  position: relative;
  overflow: hidden;
}
.columnist-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(11,31,58,0.9) 60%, rgba(215,25,32,0.25));
  pointer-events: none;
}
.columnist-hero-avatar {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--color-red);
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}
.columnist-hero-info { position: relative; z-index: 1; min-width: 0; }
.columnist-hero-nombre {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: clamp(1.2rem, 3vw, 1.8rem);
  color: #fff;
  margin-bottom: 4px;
  line-height: 1.1;
}
.columnist-hero-cargo {
  font-family: var(--font-title-montserrat);
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--color-red);
  margin-bottom: 10px;
}
.columnist-hero-bio {
  font-size: 0.85rem;
  color: #94A3B8;
  line-height: 1.55;
  max-width: 560px;
}
.columnist-article-count-badge {
  display: inline-block;
  background: var(--color-red);
  color: #fff;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.68rem;
  padding: 3px 12px;
  border-radius: 20px;
  margin-top: 10px;
  letter-spacing: 0.5px;
}

/* --- Back link --- */
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-decoration: none;
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.15s;
}
.back-link:hover { color: var(--color-red); }

/* --- Section label --- */
.section-label {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--color-red);
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.section-label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--color-border);
}

/* --- Articles list --- */
.columnist-articles-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 32px;
}
.columnist-art-row {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 16px 20px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  text-decoration: none;
  color: inherit;
  transition: box-shadow 0.2s, transform 0.2s;
}
.columnist-art-row:hover {
  transform: translateX(3px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.columnist-art-row:hover .columnist-art-titulo { color: var(--color-red); }

.columnist-art-badge {
  display: inline-block;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.6rem;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 2px 7px;
  border-radius: 2px;
  color: #fff;
  flex-shrink: 0;
  margin-top: 2px;
}
.tipo-EDITORIAL  { background: var(--color-red); }
.tipo-COLUMNA    { background: #1a5fa3; }
.tipo-ANÁLISIS   { background: #0d6e47; }
.tipo-COMENTARIO { background: #6b35a3; }

.columnist-art-titulo {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.92rem;
  line-height: 1.3;
  color: var(--color-text-main);
  flex: 1;
  transition: color 0.15s;
}
.columnist-art-date {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  white-space: nowrap;
  flex-shrink: 0;
  margin-top: 3px;
}

/* --- Otros Columnistas --- */
.otros-columnistas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  margin-top: 16px;
}
.otro-col-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 14px 12px;
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: transform 0.15s, box-shadow 0.15s;
}
.otro-col-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.otro-col-card img {
  width: 52px; height: 52px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-navy);
  margin-bottom: 8px;
}
[data-theme="dark"] .otro-col-card img { border-color: var(--color-red); }
.otro-col-name {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.75rem;
  color: var(--color-navy);
  line-height: 1.2;
}
[data-theme="dark"] .otro-col-name { color: #e0eaf8; }
.otro-col-cargo {
  font-size: 0.66rem;
  color: var(--color-text-muted);
  margin-top: 3px;
}

/* Pagination */
.opinion-pagination {
  display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; margin-top: 24px;
}
.opinion-pagination a, .opinion-pagination span {
  font-family: var(--font-title-montserrat); font-weight: 700; font-size: 0.75rem;
  padding: 6px 12px; border: 1px solid var(--color-border); border-radius: 2px;
  color: var(--color-text-main); text-decoration: none; background: var(--color-card-bg); transition: all 0.15s;
}
.opinion-pagination a:hover { background: var(--color-navy); color: #fff; border-color: var(--color-navy); }
.opinion-pagination .active-page { background: var(--color-red); color: #fff; border-color: var(--color-red); }

@media (max-width: 640px) {
  .columnist-hero { flex-direction: column; text-align: center; }
  .columnist-hero-avatar { width: 80px; height: 80px; }
  .otros-columnistas-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="container columnist-page">

  <a href="{{ route('opinion.index') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Volver a Opinión
  </a>

  {{-- Columnista Hero --}}
  <div class="columnist-hero">
    <img src="{{ $columnista->avatar_url }}"
         alt="{{ $columnista->nombre }}"
         class="columnist-hero-avatar"
         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($columnista->nombre) }}&background=0B1F3A&color=fff&size=200'">
    <div class="columnist-hero-info">
      <div class="columnist-hero-nombre">{{ $columnista->nombre }}</div>
      <div class="columnist-hero-cargo">{{ $columnista->cargo }}</div>
      @if($columnista->bio)
        <div class="columnist-hero-bio">{{ $columnista->bio }}</div>
      @endif
      <span class="columnist-article-count-badge">
        {{ $articulos->total() }} {{ $articulos->total() === 1 ? 'artículo' : 'artículos' }} publicados
      </span>
    </div>
  </div>

  {{-- Artículos del columnista --}}
  <div class="section-label">
    <i class="fa-solid fa-pen-nib"></i> Artículos de {{ $columnista->nombre }}
  </div>

  @if($articulos->isNotEmpty())
    <div class="columnist-articles-list">
      @foreach($articulos as $art)
        <a href="{{ route('opinion.articulo', $art->id) }}" class="columnist-art-row">
          <span class="columnist-art-badge tipo-{{ $art->tipo }}">{{ $art->tipo }}</span>
          <span class="columnist-art-titulo">{{ $art->titulo }}</span>
          <span class="columnist-art-date">
            {{ \Carbon\Carbon::parse($art->created_at)->locale('es')->diffForHumans() }}
          </span>
        </a>
      @endforeach
    </div>

    @if($articulos->hasPages())
      <div class="opinion-pagination">
        @if($articulos->onFirstPage())<span>&laquo;</span>@else<a href="{{ $articulos->previousPageUrl() }}">&laquo;</a>@endif
        @foreach($articulos->getUrlRange(1, $articulos->lastPage()) as $page => $url)
          @if($page == $articulos->currentPage())<span class="active-page">{{ $page }}</span>
          @else<a href="{{ $url }}">{{ $page }}</a>@endif
        @endforeach
        @if($articulos->hasMorePages())<a href="{{ $articulos->nextPageUrl() }}">&raquo;</a>@else<span>&raquo;</span>@endif
      </div>
    @endif
  @else
    <div style="text-align:center; padding:32px; color:var(--color-text-muted);">
      <p style="font-family:var(--font-title-montserrat); font-weight:700;">Aún no hay artículos publicados de este columnista.</p>
    </div>
  @endif

  {{-- Otros columnistas --}}
  @if($otrosColumnistas->isNotEmpty())
    <div class="section-label" style="margin-top:40px;">
      <i class="fa-solid fa-users"></i> Otros Columnistas
    </div>
    <div class="otros-columnistas-grid">
      @foreach($otrosColumnistas as $otro)
        <a href="{{ route('opinion.columnista', $otro->id) }}" class="otro-col-card">
          <img src="{{ $otro->avatar_url }}"
               alt="{{ $otro->nombre }}"
               onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($otro->nombre) }}&background=0B1F3A&color=fff&size=100'">
          <div class="otro-col-name">{{ $otro->nombre }}</div>
          <div class="otro-col-cargo">{{ $otro->cargo }}</div>
        </a>
      @endforeach
    </div>
  @endif

</div>
@endsection
