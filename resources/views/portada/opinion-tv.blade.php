{{-- =========================================================================
     5. FILA 3: OPINIÓN & LATITUD 18 TV (SPLIT 1.3fr : 1fr)
     ========================================================================= --}}
<section class="row-opinion-tv-split">

  {{-- Bloque OPINIÓN (4 Columnistas Dinámicos) --}}
  <div class="opinion-block-container">
    <div class="cat-block-head">
      <h3 class="cat-block-title">OPINIÓN</h3>
      <a href="{{ route('opinion.index') }}" class="cat-ver-mas-link">
        VER MÁS &gt;
      </a>
    </div>

    <div class="columnists-grid-4">
      @if(isset($articulosOpinion) && $articulosOpinion->isNotEmpty())
        @foreach($articulosOpinion as $art)
          <a href="{{ route('opinion.articulo', $art->id) }}" class="columnist-mini-card" style="text-decoration:none; color:inherit;">
            <img src="{{ $art->columnista->avatar_url }}" alt="{{ $art->columnista->nombre }}" class="columnist-mini-avatar" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($art->columnista->nombre) }}&background=0B1F3A&color=fff'">
            <span class="columnist-tag">{{ $art->tipo ?? 'COLUMNA' }}</span>
            <h4 class="columnist-article-title" title="{{ $art->titulo }}">{{ $art->titulo }}</h4>
            <span class="columnist-name-author">Por {{ $art->columnista->nombre }}</span>
          </a>
        @endforeach
      @else
        {{-- Fallback si aún no hay artículos de opinión creados en el panel --}}
        @php
          $sampleColumnistas = [
            ['tag' => 'EDITORIAL', 'autor' => 'Redacción Latitud 18', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80'],
            ['tag' => 'COLUMNA', 'autor' => 'Lic. Roberto Vaca D.', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80'],
            ['tag' => 'ANÁLISIS', 'autor' => 'Dra. Verónica Zapana', 'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80'],
            ['tag' => 'COLUMNA', 'autor' => 'Jorge Richter R.', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80'],
          ];
          $opinionNoticias = $noticias->slice(4, 4);
        @endphp
        @foreach($sampleColumnistas as $idx => $col)
          @php $opItem = $opinionNoticias->values()->get($idx) ?? $noticias->first(); @endphp
          @if($opItem)
            <a href="{{ $opItem->url }}" class="columnist-mini-card">
              <img src="{{ $col['avatar'] }}" alt="{{ $col['autor'] }}" class="columnist-mini-avatar">
              <span class="columnist-tag">{{ $col['tag'] }}</span>
              <h4 class="columnist-article-title">{{ $opItem->titulo }}</h4>
              <span class="columnist-name-author">Por {{ $col['autor'] }}</span>
            </a>
          @endif
        @endforeach
      @endif
    </div>
  </div>

  {{-- Bloque LATITUD 18 TV (3 Video Cards Dinámicas) --}}
  <div class="tv-block-container">
    <div class="cat-block-head">
      <h3 class="cat-block-title">LATITUD 18 TV</h3>
      <button onclick="openLiveModal()" class="cat-ver-mas-link" style="background:none;border:none;cursor:pointer;">
        EN VIVO &gt;
      </button>
    </div>

    <div class="tv-cards-grid-3">
      @if(isset($noticiasConVideo) && $noticiasConVideo->isNotEmpty())
        @foreach($noticiasConVideo->take(3) as $videoNews)
          @php
            $thumb = $videoNews->youtube_thumbnail ?: ($videoNews->imagenUrl ?: asset('images/default-news.svg'));
          @endphp
          <a href="{{ $videoNews->url }}" class="tv-video-thumb-card" style="text-decoration:none; color:inherit;">
            <div class="tv-thumb-wrap">
              <img src="{{ $thumb }}" alt="{{ $videoNews->titulo }}" onerror="handleImageError(this)">
              <div class="tv-play-overlay-icon"><i class="fa-solid fa-play"></i></div>
              <div class="tv-duration-badge"><i class="fab fa-youtube"></i> VIDEO</div>
            </div>
            <h4 class="tv-video-title">{{ $videoNews->titulo }}</h4>
          </a>
        @endforeach
      @else
        {{-- Fallback si aún no hay noticias con enlace de YouTube guardado --}}
        @php
          $tvItems = [
            ['titulo' => 'Reportaje Especial: Balance económico y reactivación productiva', 'dur' => '12:45', 'img' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80'],
            ['titulo' => 'Entrevista exclusiva con analistas institucionales de Santa Cruz', 'dur' => '08:37', 'img' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=300&q=80'],
            ['titulo' => 'Edición central de noticias: Las claves de la jornada en vivo', 'dur' => '15:20', 'img' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80'],
          ];
        @endphp
        @foreach($tvItems as $tv)
          <div class="tv-video-thumb-card" onclick="openLiveModal()">
            <div class="tv-thumb-wrap">
              <img src="{{ $tv['img'] }}" alt="{{ $tv['titulo'] }}">
              <div class="tv-play-overlay-icon"><i class="fa-solid fa-play"></i></div>
              <div class="tv-duration-badge">{{ $tv['dur'] }}</div>
            </div>
            <h4 class="tv-video-title">{{ $tv['titulo'] }}</h4>
          </div>
        @endforeach
      @endif
    </div>
  </div>

</section>
