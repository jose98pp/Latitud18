{{-- =========================================================================
     3. FILA 1 DE CATEGORÍAS (3 COLUMNAS: POLÍTICA | NACIONAL | PAÍS)
     ========================================================================= --}}
<section class="cat-row-3cols">
  @foreach($categorias->take(3) as $categoria)
    @php
      $catNews = $noticiasPorCategoria[$categoria->id] ?? collect();
      $firstNews = $catNews->first();
      $subNews = $catNews->slice(1, 2);
      $bulletNews = $catNews->slice(3, 2);
    @endphp
    <div class="cat-block-box">
      <div class="cat-block-head">
        <h3 class="cat-block-title">{{ strtoupper($categoria->name) }}</h3>
        <a href="{{ route('categoria.noticias', $categoria->slug) }}" class="cat-ver-mas-link">
          VER MÁS &gt;
        </a>
      </div>

      @if($firstNews)
        <div class="block-layout-split">
          {{-- Noticia Principal de la Sección (Izquierda) --}}
          <a href="{{ $firstNews->url }}" class="split-left-main">
            <img src="{{ $firstNews->imagenUrl ?? asset('images/default-news.svg') }}"
                 alt="{{ $firstNews->titulo }}"
                 onerror="handleImageError(this)">
            <h4 class="split-headline">{{ $firstNews->titulo }}</h4>
          </a>

          {{-- Miniaturas de Noticias Secundarias (Derecha) --}}
          <div class="split-right-list">
            @foreach($subNews as $sub)
              <a href="{{ $sub->url }}" class="mini-thumb-item">
                <img src="{{ $sub->imagenUrl ?? asset('images/default-news.svg') }}"
                     alt="{{ $sub->titulo }}"
                     onerror="handleImageError(this)">
                <span>{{ $sub->titulo }}</span>
              </a>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Viñetas de Noticias Inferiores --}}
      @if($bulletNews->count() > 0)
        <ul class="block-bullet-points">
          @foreach($bulletNews as $bullet)
            <li>
              <a href="{{ $bullet->url }}">
                • {{ $bullet->titulo }}
              </a>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  @endforeach
</section>
