@extends('layouts.main')

@section('title', $articulo->titulo . ' | ' . $articulo->columnista->nombre . ' - Latitud 18 Opinión')

@section('content')
<style>
*, *::before, *::after { box-sizing: border-box; }

.opinion-read-page { padding: 24px 0 48px; }

/* --- Layout two-col --- */
.opinion-read-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 28px;
  align-items: start;
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
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.15s;
}
.back-link:hover { color: var(--color-red); }

/* --- Article main --- */
.opinion-article-main {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  overflow: hidden;
}

.opinion-article-tipo-banner {
  padding: 10px 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.tipo-banner-EDITORIAL  { background: var(--color-red); }
.tipo-banner-COLUMNA    { background: #1a5fa3; }
.tipo-banner-ANÁLISIS   { background: #0d6e47; }
.tipo-banner-COMENTARIO { background: #6b35a3; }

.tipo-banner-label {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 0.72rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #fff;
}

.opinion-article-head { padding: 28px 28px 20px; border-bottom: 1px solid var(--color-border); }

.opinion-article-titulo {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: clamp(1.3rem, 3vw, 2rem);
  color: var(--color-text-main);
  line-height: 1.22;
  margin-bottom: 20px;
}

.opinion-article-byline {
  display: flex;
  align-items: center;
  gap: 14px;
}
.byline-avatar {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--color-navy);
  flex-shrink: 0;
}
[data-theme="dark"] .byline-avatar { border-color: var(--color-red); }
.byline-info {}
.byline-name {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.9rem;
  color: var(--color-navy);
  text-decoration: none;
  transition: color 0.15s;
}
.byline-name:hover { color: var(--color-red); }
[data-theme="dark"] .byline-name { color: #e0eaf8; }
.byline-meta {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  margin-top: 2px;
}
.byline-meta span { margin-right: 10px; }

/* --- Article Body --- */
.opinion-article-body {
  padding: 28px;
  font-family: var(--font-body);
  font-size: 1rem;
  line-height: 1.8;
  color: var(--color-text-secondary);
}
.opinion-article-body p { margin-bottom: 1.2rem; }
.opinion-article-body p:last-child { margin-bottom: 0; }
.opinion-article-body blockquote {
  border-left: 4px solid var(--color-red);
  padding: 10px 18px;
  margin: 20px 0;
  color: var(--color-text-muted);
  font-style: italic;
  background: var(--color-navy-subtle);
  border-radius: 0 4px 4px 0;
}
.opinion-article-body h2, .opinion-article-body h3 {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  color: var(--color-navy);
  margin: 1.6rem 0 0.8rem;
}
[data-theme="dark"] .opinion-article-body h2,
[data-theme="dark"] .opinion-article-body h3 { color: #e0eaf8; }

/* --- View counter strip --- */
.opinion-article-stats {
  padding: 12px 28px;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 20px;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-family: var(--font-title-montserrat);
  font-weight: 600;
}
.opinion-article-stats i { color: var(--color-red); margin-right: 4px; }

/* --- Share strip --- */
.opinion-share-strip {
  padding: 16px 28px;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.share-label {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.72rem;
  text-transform: uppercase;
  color: var(--color-text-muted);
  letter-spacing: 0.5px;
}
.share-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 14px;
  border-radius: 2px;
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.7rem;
  text-decoration: none;
  color: #fff;
  cursor: pointer;
  transition: opacity 0.15s;
  border: none;
}
.share-btn:hover { opacity: 0.85; }
.share-tw { background: #1DA1F2; }
.share-wa { background: #25D366; }
.share-copy { background: var(--color-navy); cursor: pointer; }

/* --- Sidebar --- */
.opinion-sidebar {}
.sidebar-columnist-card {
  background: var(--color-navy);
  border-radius: 4px;
  padding: 20px;
  text-align: center;
  margin-bottom: 20px;
}
.sidebar-col-avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--color-red);
  margin-bottom: 10px;
}
.sidebar-col-name {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 0.88rem;
  color: #fff;
  margin-bottom: 4px;
}
.sidebar-col-cargo {
  font-size: 0.72rem;
  color: var(--color-red);
  margin-bottom: 10px;
}
.sidebar-col-bio {
  font-size: 0.77rem;
  color: #94A3B8;
  line-height: 1.5;
  margin-bottom: 14px;
}
.sidebar-col-btn {
  display: block;
  background: var(--color-red);
  color: #fff;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 8px;
  border-radius: 2px;
  text-decoration: none;
  text-align: center;
  transition: background 0.15s;
}
.sidebar-col-btn:hover { background: var(--color-red-dark); }

.sidebar-title {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--color-red);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.sidebar-title::after { content: ''; flex: 1; height: 1px; background: var(--color-border); }

.sidebar-rel-list { display: flex; flex-direction: column; gap: 10px; }
.sidebar-rel-item {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 12px 14px;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.15s;
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.sidebar-rel-item:hover { border-color: var(--color-red); }
.sidebar-rel-badge {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.6rem;
  text-transform: uppercase;
  padding: 1px 6px;
  border-radius: 2px;
  color: #fff;
  align-self: flex-start;
}
.sidebar-rel-titulo {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.8rem;
  line-height: 1.3;
  color: var(--color-text-main);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.sidebar-rel-autor {
  font-size: 0.68rem;
  color: var(--color-text-muted);
}

/* Responsive */
@media (max-width: 900px) {
  .opinion-read-layout { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .opinion-article-head, .opinion-article-body { padding: 18px 16px; }
  .opinion-article-titulo { font-size: 1.2rem; }
}
</style>

<div class="container opinion-read-page">

  <a href="{{ route('opinion.index') }}" class="back-link">
    <i class="fa-solid fa-arrow-left"></i> Volver a Opinión
  </a>

  <div class="opinion-read-layout">

    {{-- === ARTÍCULO PRINCIPAL === --}}
    <article class="opinion-article-main">
      {{-- Tipo Banner --}}
      <div class="opinion-article-tipo-banner tipo-banner-{{ $articulo->tipo }}">
        <i class="fa-solid fa-pen-nib" style="color:rgba(255,255,255,0.7);"></i>
        <span class="tipo-banner-label">{{ $articulo->tipo }}</span>
      </div>

      {{-- Head --}}
      <div class="opinion-article-head">
        <h1 class="opinion-article-titulo">{{ $articulo->titulo }}</h1>
        <div class="opinion-article-byline">
          <img src="{{ $articulo->columnista->avatar_url }}"
               alt="{{ $articulo->columnista->nombre }}"
               class="byline-avatar"
               onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($articulo->columnista->nombre) }}&background=0B1F3A&color=fff&size=120'">
          <div class="byline-info">
            <a href="{{ route('opinion.columnista', $articulo->columnista->id) }}" class="byline-name">
              {{ $articulo->columnista->nombre }}
            </a>
            <div class="byline-meta">
              <span>{{ $articulo->columnista->cargo }}</span>
              <span><i class="fa-regular fa-calendar" style="margin-right:3px;"></i>{{ \Carbon\Carbon::parse($articulo->created_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Body --}}
      <div class="opinion-article-body">
        {!! $articulo->contenido !!}
      </div>

      {{-- Stats --}}
      <div class="opinion-article-stats">
        <span><i class="fa-solid fa-eye"></i> {{ number_format($articulo->vistas) }} lecturas</span>
        <span><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($articulo->created_at)->locale('es')->diffForHumans() }}</span>
      </div>

      {{-- Share --}}
      <div class="opinion-share-strip">
        <span class="share-label">Compartir:</span>
        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($articulo->titulo) }}"
           target="_blank" rel="noopener" class="share-btn share-tw">
          <i class="fab fa-x-twitter"></i> Twitter/X
        </a>
        <a href="https://api.whatsapp.com/send?text={{ urlencode($articulo->titulo . ' ' . request()->url()) }}"
           target="_blank" rel="noopener" class="share-btn share-wa">
          <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
        <button onclick="navigator.clipboard.writeText('{{ request()->url() }}').then(()=>this.textContent='¡Copiado!')" class="share-btn share-copy">
          <i class="fa-solid fa-link"></i> Copiar enlace
        </button>
      </div>
    </article>

    {{-- === SIDEBAR === --}}
    <aside class="opinion-sidebar">

      {{-- Columnist card --}}
      <div class="sidebar-columnist-card">
        <img src="{{ $articulo->columnista->avatar_url }}"
             alt="{{ $articulo->columnista->nombre }}"
             class="sidebar-col-avatar"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($articulo->columnista->nombre) }}&background=0B1F3A&color=fff&size=150'">
        <div class="sidebar-col-name">{{ $articulo->columnista->nombre }}</div>
        <div class="sidebar-col-cargo">{{ $articulo->columnista->cargo }}</div>
        @if($articulo->columnista->bio)
          <div class="sidebar-col-bio">{{ Str::limit($articulo->columnista->bio, 130) }}</div>
        @endif
        <a href="{{ route('opinion.columnista', $articulo->columnista->id) }}" class="sidebar-col-btn">
          Ver todos sus artículos
        </a>
      </div>

      {{-- Artículos relacionados --}}
      @if($relacionados->isNotEmpty())
        <div class="sidebar-title"><i class="fa-solid fa-newspaper"></i> Más Opinión</div>
        <div class="sidebar-rel-list">
          @foreach($relacionados as $rel)
            <a href="{{ route('opinion.articulo', $rel->id) }}" class="sidebar-rel-item">
              <span class="sidebar-rel-badge tipo-{{ $rel->tipo }}">{{ $rel->tipo }}</span>
              <span class="sidebar-rel-titulo">{{ $rel->titulo }}</span>
              <span class="sidebar-rel-autor">Por {{ $rel->columnista->nombre }}</span>
            </a>
          @endforeach
        </div>
      @endif

    </aside>

  </div>

</div>
@endsection
