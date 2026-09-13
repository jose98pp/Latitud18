@extends('layouts.main')

@section('title', 'Opinión & Análisis | Latitud 18 - Columnistas y Perspectivas')

@section('content')
<style>
/* ==========================================================================
   OPINIÓN & COLUMNISTAS — Página Pública
   ========================================================================== */

/* Reset anti-overflow horizontal */
*, *::before, *::after { box-sizing: border-box; }

.opinion-page { padding: 20px 0 40px; min-width: 0; }

/* --- Page Header --- */
.opinion-page-header {
  border-bottom: 3px solid var(--color-navy);
  padding-bottom: 12px;
  margin-bottom: 24px;
  position: relative;
}
.opinion-page-header::after {
  content: '';
  position: absolute;
  bottom: -3px; left: 0;
  width: 54px; height: 3px;
  background: var(--color-red);
}
.opinion-page-header h1 {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: clamp(1.4rem, 3vw, 2rem);
  color: var(--color-navy);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0;
}
[data-theme="dark"] .opinion-page-header h1 { color: #fff; }
.opinion-page-header p {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin-top: 6px;
}

/* --- Columnistas Grid --- */
.section-label {
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
.section-label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--color-border);
}

.columnistas-strip {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 40px;
}

.columnist-profile-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 20px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  min-width: 0;
}
.columnist-profile-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}
.columnist-profile-card:hover .columnist-profile-name {
  color: var(--color-red);
}

.columnist-profile-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--color-navy);
  margin-bottom: 10px;
  flex-shrink: 0;
}
[data-theme="dark"] .columnist-profile-avatar { border-color: var(--color-red); }

.columnist-profile-name {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.9rem;
  color: var(--color-navy);
  margin-bottom: 4px;
  transition: color 0.15s;
  line-height: 1.2;
}
[data-theme="dark"] .columnist-profile-name { color: #fff; }

.columnist-profile-cargo {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  font-weight: 600;
  line-height: 1.3;
  margin-bottom: 10px;
}

.columnist-article-count {
  background: var(--color-navy-subtle);
  color: var(--color-navy);
  font-size: 0.68rem;
  font-weight: 700;
  font-family: var(--font-title-montserrat);
  padding: 3px 10px;
  border-radius: 20px;
  letter-spacing: 0.3px;
}
[data-theme="dark"] .columnist-article-count { background: #1e3a5f; color: #94b8e0; }

/* --- Opinion Type Filter Pills --- */
.tipo-filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 24px;
}
.tipo-pill {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 5px 14px;
  border-radius: 20px;
  text-decoration: none;
  border: 1px solid var(--color-border);
  background: var(--color-card-bg);
  color: var(--color-text-main);
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.tipo-pill:hover, .tipo-pill.active {
  background: var(--color-navy);
  color: #fff;
  border-color: var(--color-navy);
}
.tipo-pill.editorial.active  { background: var(--color-red); border-color: var(--color-red); }
.tipo-pill.columna.active    { background: #1a5fa3; border-color: #1a5fa3; }
.tipo-pill.analisis.active   { background: #0d6e47; border-color: #0d6e47; }
.tipo-pill.comentario.active { background: #6b35a3; border-color: #6b35a3; }

/* --- Articles Grid --- */
.opinion-articles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.opinion-article-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  transition: transform 0.2s, box-shadow 0.2s;
  min-width: 0;
}
.opinion-article-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.opinion-card-top {
  padding: 18px 16px 12px;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 10px;
}
.opinion-card-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 2px solid var(--color-navy);
}
[data-theme="dark"] .opinion-card-avatar { border-color: var(--color-red); }
.opinion-card-author-block { min-width: 0; }
.opinion-card-autor {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.78rem;
  color: var(--color-navy);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
[data-theme="dark"] .opinion-card-autor { color: #e0eaf8; }
.opinion-card-cargo {
  font-size: 0.68rem;
  color: var(--color-text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.opinion-card-body { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 8px; }

.opinion-tipo-badge {
  display: inline-block;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.62rem;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 2px 8px;
  border-radius: 2px;
  color: #fff;
  align-self: flex-start;
}
.tipo-EDITORIAL  { background: var(--color-red); }
.tipo-COLUMNA    { background: #1a5fa3; }
.tipo-ANÁLISIS   { background: #0d6e47; }
.tipo-COMENTARIO { background: #6b35a3; }

.opinion-card-titulo {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.9rem;
  line-height: 1.32;
  color: var(--color-text-main);
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}
.opinion-article-card:hover .opinion-card-titulo { color: var(--color-red); }

.opinion-card-footer {
  padding: 10px 16px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid var(--color-border);
}
.opinion-card-date {
  font-size: 0.7rem;
  color: var(--color-text-muted);
}
.opinion-card-cta {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.65rem;
  color: var(--color-red);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* --- Pagination --- */
.opinion-pagination {
  display: flex;
  justify-content: center;
  gap: 6px;
  flex-wrap: wrap;
}
.opinion-pagination a, .opinion-pagination span {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.75rem;
  padding: 6px 12px;
  border: 1px solid var(--color-border);
  border-radius: 2px;
  color: var(--color-text-main);
  text-decoration: none;
  background: var(--color-card-bg);
  transition: all 0.15s;
}
.opinion-pagination a:hover { background: var(--color-navy); color: #fff; border-color: var(--color-navy); }
.opinion-pagination .active-page { background: var(--color-red); color: #fff; border-color: var(--color-red); }

/* Responsive */
@media (max-width: 640px) {
  .columnistas-strip { grid-template-columns: repeat(2, 1fr); }
  .opinion-articles-grid { grid-template-columns: 1fr; }
}
</style>

<div class="container opinion-page">

  {{-- Page header --}}
  <div class="opinion-page-header">
    <h1><i class="fa-solid fa-pen-nib" style="color:var(--color-red); font-size:0.9em; margin-right:8px;"></i>Opinión & Análisis</h1>
    <p>Columnas, análisis y editoriales de nuestros colaboradores y redacción institucional.</p>
  </div>

  {{-- Columnistas activos --}}
  @if($columnistas->isNotEmpty())
    <div class="section-label">
      <i class="fa-solid fa-users"></i> Nuestros Columnistas
    </div>
    <div class="columnistas-strip">
      @foreach($columnistas as $col)
        <a href="{{ route('opinion.columnista', $col->id) }}" class="columnist-profile-card">
          <img src="{{ $col->avatar_url }}"
               alt="{{ $col->nombre }}"
               class="columnist-profile-avatar"
               onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($col->nombre) }}&background=0B1F3A&color=fff&size=150'">
          <div class="columnist-profile-name">{{ $col->nombre }}</div>
          <div class="columnist-profile-cargo">{{ $col->cargo }}</div>
          <span class="columnist-article-count">
            {{ $col->articulos->count() }} {{ $col->articulos->count() === 1 ? 'artículo' : 'artículos' }}
          </span>
        </a>
      @endforeach
    </div>
  @endif

  {{-- Tipo filter pills --}}
  <div class="section-label">
    <i class="fa-solid fa-newspaper"></i> Artículos Publicados
  </div>
  <div class="tipo-filter-bar">
    <button class="tipo-pill active" onclick="filterOpinion('todos', this)">Todos</button>
    <button class="tipo-pill editorial" onclick="filterOpinion('EDITORIAL', this)">Editorial</button>
    <button class="tipo-pill columna" onclick="filterOpinion('COLUMNA', this)">Columna</button>
    <button class="tipo-pill analisis" onclick="filterOpinion('ANÁLISIS', this)">Análisis</button>
    <button class="tipo-pill comentario" onclick="filterOpinion('COMENTARIO', this)">Comentario</button>
  </div>

  {{-- Articles grid --}}
  @if($articulosRecientes->isNotEmpty())
    <div class="opinion-articles-grid" id="opinionGrid">
      @foreach($articulosRecientes as $art)
        <a href="{{ route('opinion.articulo', $art->id) }}"
           class="opinion-article-card"
           data-tipo="{{ $art->tipo }}">
          <div class="opinion-card-top">
            <img src="{{ $art->columnista->avatar_url }}"
                 alt="{{ $art->columnista->nombre }}"
                 class="opinion-card-avatar"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($art->columnista->nombre) }}&background=0B1F3A&color=fff&size=80'">
            <div class="opinion-card-author-block">
              <div class="opinion-card-autor">{{ $art->columnista->nombre }}</div>
              <div class="opinion-card-cargo">{{ $art->columnista->cargo }}</div>
            </div>
          </div>
          <div class="opinion-card-body">
            <span class="opinion-tipo-badge tipo-{{ $art->tipo }}">{{ $art->tipo }}</span>
            <h2 class="opinion-card-titulo">{{ $art->titulo }}</h2>
          </div>
          <div class="opinion-card-footer">
            <span class="opinion-card-date">
              <i class="fa-regular fa-calendar" style="margin-right:4px;"></i>
              {{ \Carbon\Carbon::parse($art->created_at)->locale('es')->diffForHumans() }}
            </span>
            <span class="opinion-card-cta">Leer &rarr;</span>
          </div>
        </a>
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($articulosRecientes->hasPages())
      <div class="opinion-pagination">
        {{-- Previous --}}
        @if($articulosRecientes->onFirstPage())
          <span>&laquo;</span>
        @else
          <a href="{{ $articulosRecientes->previousPageUrl() }}">&laquo;</a>
        @endif

        @foreach($articulosRecientes->getUrlRange(1, $articulosRecientes->lastPage()) as $page => $url)
          @if($page == $articulosRecientes->currentPage())
            <span class="active-page">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach

        {{-- Next --}}
        @if($articulosRecientes->hasMorePages())
          <a href="{{ $articulosRecientes->nextPageUrl() }}">&raquo;</a>
        @else
          <span>&raquo;</span>
        @endif
      </div>
    @endif

  @else
    <div style="text-align:center; padding:48px 16px; color:var(--color-text-muted);">
      <i class="fa-solid fa-pen-nib" style="font-size:2rem; margin-bottom:12px; display:block; color:var(--color-border);"></i>
      <p style="font-family:var(--font-title-montserrat); font-weight:700;">No hay artículos de opinión publicados aún.</p>
    </div>
  @endif

</div>

<script>
function filterOpinion(tipo, btn) {
  // Update pill state
  document.querySelectorAll('.tipo-pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  // Filter cards
  document.querySelectorAll('#opinionGrid .opinion-article-card').forEach(card => {
    if (tipo === 'todos' || card.dataset.tipo === tipo) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}
</script>
@endsection
