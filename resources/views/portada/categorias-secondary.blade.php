{{-- =========================================================================
     4. FILA 2 DE CATEGORÍAS (4 COLUMNAS: 3 SECCIONES + LATITUD 18 INVESTIGA)
     ========================================================================= --}}
<section class="cat-row-4cols">
  @foreach($categorias->slice(3, 3) as $categoria)
    @php
      $catNews = $noticiasPorCategoria[$categoria->id] ?? collect();
      $catMain = $catNews->first();
      $catBullets = $catNews->slice(1, 2);
    @endphp
    <div class="card-cat-sm-box">
      <div class="cat-block-head">
        <h3 class="cat-block-title" style="font-size: 0.95rem;">{{ strtoupper($categoria->name) }}</h3>
        <a href="{{ route('categoria.noticias', $categoria->slug) }}" class="cat-ver-mas-link">
          VER MÁS &gt;
        </a>
      </div>

      @if($catMain)
        <a href="{{ $catMain->url }}" style="text-decoration:none;">
          <img src="{{ $catMain->imagenUrl ?? asset('images/default-news.svg') }}"
               alt="{{ $catMain->titulo }}"
               class="card-cat-sm-img"
               onerror="handleImageError(this)">
          <h4 class="card-cat-sm-title">{{ $catMain->titulo }}</h4>
        </a>
      @endif

      @if($catBullets->count() > 0)
        <ul class="block-bullet-points">
          @foreach($catBullets as $bullet)
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

  {{-- Columna 4: Tarjeta Especial LATITUD 18 INVESTIGA / EDICIÓN ESPECIAL --}}
  @php
    $investigaNoticia = $noticiaInvestigacion ?? $noticias->slice(3, 1)->first() ?? $noticias->first();
  @endphp
  <div class="investiga-special-card">
    <div>
      <div class="investiga-badge-title">
        <i class="fa-solid fa-magnifying-glass red"></i> LATITUD<span class="red">18</span> INVESTIGA
      </div>
      @if($investigaNoticia)
        <a href="{{ $investigaNoticia->url }}" style="text-decoration:none; color:inherit;">
          <img src="{{ $investigaNoticia->imagenUrl ?? asset('images/default-news.svg') }}"
               alt="{{ $investigaNoticia->titulo }}"
               class="investiga-img"
               onerror="handleImageError(this)">
          <h4 class="investiga-headline">{{ $investigaNoticia->titulo }}</h4>
        </a>
      @endif
    </div>
    @if($investigaNoticia)
      <a href="{{ $investigaNoticia->url }}" class="btn-investiga-action">
        VER INVESTIGACIÓN &gt;
      </a>
    @else
      <a href="{{ route('periodico.public.index') }}" class="btn-investiga-action">
        VER EDICIÓN DIGITAL &gt;
      </a>
    @endif
  </div>
</section>
