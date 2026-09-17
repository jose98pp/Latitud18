{{-- =========================================================================
     2. TICKER DE ÚLTIMAS NOTICIAS (SEGUNDA FRANJA - DINÁMICO Y CLIQUEABLE)
     ========================================================================= --}}
<div class="ticker-strip-bar">
  <div class="ticker-label-badge">
    <i class="fa-solid fa-circle"></i> 
    <span class="ticker-badge-text-full">ÚLTIMAS NOTICIAS</span>
    <span class="ticker-badge-text-short">AL DÍA</span>
  </div>
  <div class="ticker-news-items-row" id="tickerRow">
    @foreach($ultimasNoticias->take(10) as $ultima)
      <a href="{{ $ultima->url }}" class="ticker-snippet">
        {{ $ultima->titulo }}
      </a>
    @endforeach
  </div>
  <a href="{{ route('categoria.noticias', $categorias->first()->slug ?? 'noticias') }}" class="ticker-ver-mas">
    VER MÁS &gt;
  </a>
</div>
