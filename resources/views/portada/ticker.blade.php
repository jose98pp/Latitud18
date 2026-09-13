{{-- =========================================================================
     2. TICKER DE ÚLTIMAS NOTICIAS (SEGUNDA FRANJA - DINÁMICO Y CLIQUEABLE)
     ========================================================================= --}}
<div class="ticker-strip-bar">
  <div class="ticker-label-badge">
    <i class="fa-solid fa-circle"></i> ÚLTIMAS NOTICIAS
  </div>
  <div class="ticker-news-items-row" id="tickerRow">
    @foreach($ultimasNoticias->take(10) as $ultima)
      <a href="{{ $ultima->url }}" class="ticker-snippet">
        {{ $ultima->titulo }}
      </a>
    @endforeach
  </div>
  <a href="{{ route('categoria.noticias', $categorias->first()->id ?? 1) }}" class="ticker-ver-mas">
    VER MÁS &gt;
  </a>
</div>
