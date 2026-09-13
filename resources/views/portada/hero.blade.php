{{-- =========================================================================
     1. HERO 3 COLUMNAS: PRINCIPAL + 2 STACKED + LO MÁS LEÍDO
     ========================================================================= --}}
<section class="hero-news-section">
  <div class="hero-grid-3cols">

    {{-- Carrusel Principal (Columna Izquierda) con estilo Latitud 18 --}}
    <article class="hero-carousel-wrapper" id="heroNewsCarousel">
      <div class="hero-carousel-inner">
        @foreach($noticias->take(5) as $index => $noticiaItem)
          <div class="hero-carousel-slide @if($index === 0) active @endif" data-slide-index="{{ $index }}">
            <a href="{{ $noticiaItem->url }}">
              <div class="hero-main-media">
                <img src="{{ $noticiaItem->imagenUrl ?? asset('images/default-news.svg') }}"
                     alt="{{ $noticiaItem->titulo }}"
                     onerror="handleImageError(this)">
                <div class="hero-main-overlay-info">
                  <span class="card-cat-tag">{{ $noticiaItem->category->name ?? 'Destacado' }}</span>
                  <h2 class="hero-main-headline">{{ $noticiaItem->titulo }}</h2>
                  <p class="hero-main-subhead">{{ $noticiaItem->excerptLimpio ?? Str::limit(strip_tags($noticiaItem->contenido), 150) }}</p>
                  <div class="hero-main-meta">
                    <i class="far fa-clock"></i>
                    <span>{{ \Carbon\Carbon::parse($noticiaItem->created_at)->locale('es')->diffForHumans() }}</span>
                  </div>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>

      {{-- Flechas de Navegación --}}
      <button type="button" class="hero-car-arrow prev" id="heroPrevBtn" aria-label="Noticia anterior">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button type="button" class="hero-car-arrow next" id="heroNextBtn" aria-label="Noticia siguiente">
        <i class="fas fa-chevron-right"></i>
      </button>

      {{-- Indicadores Dots --}}
      <div class="hero-car-indicators">
        @foreach($noticias->take(5) as $index => $noticiaItem)
          <button type="button" class="hero-car-dot @if($index === 0) active @endif"
                  data-index="{{ $index }}"
                  aria-label="Ir a diapositiva {{ $index + 1 }}"></button>
        @endforeach
      </div>
    </article>

    {{-- Columna Central (2 Tarjetas Apiladas) --}}
    <div class="hero-stacked-col">
      @foreach($noticias->slice(1, 2) as $stackedItem)
        <article style="display:flex; flex:1;">
          <a href="{{ $stackedItem->url }}" class="stacked-news-card">
            <div class="stacked-media">
              <img src="{{ $stackedItem->imagenUrl ?? asset('images/default-news.svg') }}"
                   alt="{{ $stackedItem->titulo }}"
                   onerror="handleImageError(this)">
              <div class="stacked-overlay-info">
                <span class="card-cat-tag">{{ $stackedItem->category->name ?? 'Actualidad' }}</span>
                <h3 class="stacked-headline">{{ $stackedItem->titulo }}</h3>
              </div>
            </div>
          </a>
        </article>
      @endforeach
      @php $secondStacked = $noticias->slice(3, 1)->first(); @endphp
      @if($secondStacked)
        <article style="display:flex; flex:1;">
          <a href="{{ $secondStacked->url }}" class="stacked-news-card">
            <div class="stacked-media">
              <img src="{{ $secondStacked->imagenUrl ?? asset('images/default-news.svg') }}"
                   alt="{{ $secondStacked->titulo }}"
                   onerror="handleImageError(this)">
              <div class="stacked-overlay-info">
                <span class="card-cat-tag">{{ $secondStacked->category->name ?? 'Actualidad' }}</span>
                <h3 class="stacked-headline">{{ $secondStacked->titulo }}</h3>
              </div>
            </div>
          </a>
        </article>
      @endif
    </div>

    {{-- Columna Derecha (LO MÁS LEÍDO 01 - 05) --}}
    <aside class="hero-lo-mas-leido-col">
      <h3 class="box-title-sm">
        <i class="fa-solid fa-chart-line" style="color: var(--color-red);"></i> LO MÁS LEÍDO
      </h3>
      <div class="lo-mas-leido-list">
        @foreach($masLeidas->take(5) as $index => $leido)
          <a href="{{ $leido->url }}" class="leido-item-row">
            <div class="leido-num">0{{ $index + 1 }}</div>
            <div class="leido-text">{{ $leido->titulo }}</div>
          </a>
        @endforeach
      </div>
    </aside>

  </div>
</section>
