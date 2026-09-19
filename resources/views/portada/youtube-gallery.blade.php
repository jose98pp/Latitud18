@php
  $galleryActive = setting('youtube_gallery_active', '1') == '1';
  $channelName = setting('youtube_channel_name', 'Latitud 18 TV');
  $channelHandle = setting('youtube_channel_handle', '@Latitud18TV');
  $channelBadge = setting('youtube_channel_badge', 'Canal Oficial • Cobertura 24/7');
  $channelUrl = setting('youtube_channel_url', 'https://www.youtube.com/@UHTVBolivia');
  $rawVideos = setting('youtube_gallery_videos', '');

  $galleryItems = [];

  // 1. Si hay videos personalizados configurados en el panel de administración
  if (!empty(trim($rawVideos))) {
      $lines = preg_split('/[\r\n,]+/', $rawVideos);
      $vCount = 1;
      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) continue;
          $ytId = extract_youtube_id($line) ?: $line;
          if ($ytId) {
              $galleryItems[] = [
                  'id' => $ytId,
                  'title' => 'Producción Especial #' . $vCount . ' — ' . $channelName,
                  'thumbnail' => "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg",
                  'badge' => 'OFICIAL',
                  'date' => null,
              ];
              $vCount++;
          }
      }
  }

  // 2. Fallback dinámico con noticias reales que tienen video y señal en vivo
  if (count($galleryItems) < 4) {
      if (isset($noticiasConVideo) && $noticiasConVideo->isNotEmpty()) {
          foreach ($noticiasConVideo as $nVideo) {
              if (count($galleryItems) >= 5) break;
              $yId = $nVideo->youtube_id;
              if ($yId) {
                  $already = false;
                  foreach ($galleryItems as $item) {
                      if ($item['id'] === $yId) { $already = true; break; }
                  }
                  if (!$already) {
                      $galleryItems[] = [
                          'id' => $yId,
                          'title' => $nVideo->titulo,
                          'thumbnail' => $nVideo->youtube_thumbnail ?: ($nVideo->imagenUrl ?: "https://img.youtube.com/vi/{$yId}/hqdefault.jpg"),
                          'badge' => $nVideo->categoria->nombre ?? 'NOTICIAS',
                          'date' => $nVideo->fecha_formateada ?? null,
                      ];
                  }
              }
          }
      }

      $liveId = setting('streaming_tv_youtube_id', 'jfKfPfyJRdk');
      if ($liveId && count($galleryItems) < 4) {
          $liveCleanId = extract_youtube_id($liveId) ?: $liveId;
          $already = false;
          foreach ($galleryItems as $item) {
              if ($item['id'] === $liveCleanId) { $already = true; break; }
          }
          if (!$already) {
              $galleryItems[] = [
                  'id' => $liveCleanId,
                  'title' => setting('streaming_tv_title', 'UHTV Transmisión Digital En Vivo'),
                  'thumbnail' => "https://img.youtube.com/vi/{$liveCleanId}/hqdefault.jpg",
                  'badge' => 'EN VIVO',
                  'date' => 'Transmisión 24/7',
              ];
          }
      }
  }

  $featuredVideo = !empty($galleryItems) ? $galleryItems[0] : null;
  $secondaryVideos = count($galleryItems) > 1 ? array_slice($galleryItems, 1, 4) : [];
@endphp

@if($galleryActive && $featuredVideo)
<section class="yt-channel-showcase" id="yt-official-showcase">
  {{-- Header del Canal --}}
  <div class="yt-showcase-header">
    <div class="yt-channel-meta">
      <div class="yt-brand-icon-wrap">
        <i class="fab fa-youtube yt-brand-icon"></i>
      </div>
      <div class="yt-channel-text">
        <div class="yt-channel-title-row">
          <h3 class="yt-channel-name">{{ $channelName }}</h3>
          <span class="yt-verified-badge" title="Canal Verificado">
            <i class="fas fa-circle-check"></i>
          </span>
        </div>
        <div class="yt-channel-subtext">
          <span class="yt-handle">{{ $channelHandle }}</span>
          <span class="yt-dot-sep">•</span>
          <span class="yt-badge-tag">{{ $channelBadge }}</span>
        </div>
      </div>
    </div>

    <div class="yt-header-actions">
      <a href="{{ $channelUrl }}" target="_blank" rel="noopener noreferrer" class="yt-sub-btn" title="Suscribirse al canal oficial">
        <i class="fab fa-youtube"></i>
        <span>Suscribirse</span>
      </a>
      <a href="{{ $channelUrl }}" target="_blank" rel="noopener noreferrer" class="yt-visit-btn" title="Visitar canal en YouTube">
        <span>Canal</span>
        <i class="fas fa-arrow-up-right-from-square"></i>
      </a>
    </div>
  </div>

  {{-- Contenedor de la Galería Multimedia --}}
  <div class="yt-gallery-grid">
    {{-- Video Destacado / Principal --}}
    <div class="yt-featured-card" onclick="openCustomYTModal('{{ $featuredVideo['id'] }}', '{{ addslashes($featuredVideo['title']) }}')" role="button" tabindex="0">
      <div class="yt-thumb-wrapper">
        <img src="{{ $featuredVideo['thumbnail'] }}" alt="{{ $featuredVideo['title'] }}" class="yt-thumb-img" onerror="this.src='https://img.youtube.com/vi/{{ $featuredVideo['id'] }}/hqdefault.jpg'">
        <div class="yt-play-pulse-wrap">
          <div class="yt-play-btn-circle">
            <i class="fas fa-play"></i>
          </div>
        </div>
        <div class="yt-card-badge">{{ $featuredVideo['badge'] }}</div>
        @if(!empty($featuredVideo['date']))
          <div class="yt-time-badge">{{ $featuredVideo['date'] }}</div>
        @endif
      </div>
      <div class="yt-featured-content">
        <span class="yt-featured-tag"><i class="fas fa-fire me-1"></i> VIDEO DESTACADO</span>
        <h4 class="yt-featured-title">{{ $featuredVideo['title'] }}</h4>
        <p class="yt-featured-cta">
          <i class="fas fa-play-circle me-1"></i> Haz clic para reproducir en alta definición
        </p>
      </div>
    </div>

    {{-- Lista de Videos Secundarios (Grid de 3 o 4) --}}
    <div class="yt-secondary-grid">
      @forelse($secondaryVideos as $secVideo)
        <div class="yt-mini-card" onclick="openCustomYTModal('{{ $secVideo['id'] }}', '{{ addslashes($secVideo['title']) }}')" role="button" tabindex="0">
          <div class="yt-mini-thumb-wrap">
            <img src="{{ $secVideo['thumbnail'] }}" alt="{{ $secVideo['title'] }}" class="yt-mini-thumb" onerror="this.src='https://img.youtube.com/vi/{{ $secVideo['id'] }}/hqdefault.jpg'">
            <div class="yt-mini-play-icon">
              <i class="fas fa-play"></i>
            </div>
            <div class="yt-mini-badge">{{ $secVideo['badge'] }}</div>
          </div>
          <div class="yt-mini-info">
            <h5 class="yt-mini-title" title="{{ $secVideo['title'] }}">{{ $secVideo['title'] }}</h5>
            @if(!empty($secVideo['date']))
              <span class="yt-mini-date"><i class="far fa-clock me-1"></i>{{ $secVideo['date'] }}</span>
            @endif
          </div>
        </div>
      @empty
        {{-- En caso de que solo exista el video principal --}}
        <div class="yt-mini-card yt-empty-state">
          <div class="p-3 text-center w-100">
            <i class="fab fa-youtube text-danger mb-2 fs-3"></i>
            <p class="small text-muted mb-0">Visita nuestro canal oficial para ver más contenidos y transmisiones en vivo.</p>
          </div>
        </div>
      @endforelse
    </div>
  </div>
</section>

{{-- Reproductor Modal Nativo en HD (Sin redirecciones) --}}
<div id="custom-yt-modal" class="yt-modal-overlay" style="display: none;" onclick="handleModalOverlayClick(event)">
  <div class="yt-modal-container">
    <div class="yt-modal-header">
      <div class="yt-modal-title-wrap">
        <i class="fab fa-youtube text-danger me-2"></i>
        <span id="custom-yt-modal-title" class="yt-modal-title-text">Reproduciendo Video</span>
      </div>
      <button type="button" class="yt-modal-close-btn" onclick="closeCustomYTModal()" title="Cerrar video (Esc)">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="yt-modal-video-wrapper">
      <iframe id="custom-yt-iframe" src="" title="Reproductor de Video YouTube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
  </div>
</div>

<script>
  function openCustomYTModal(videoId, title) {
    if (!videoId) return;
    const modal = document.getElementById('custom-yt-modal');
    const iframe = document.getElementById('custom-yt-iframe');
    const titleEl = document.getElementById('custom-yt-modal-title');
    if (!modal || !iframe) return;

    if (titleEl) {
      titleEl.textContent = title || 'Reproduciendo Video Oficial';
    }

    // Usar YouTube Embed con autoplay
    iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0&modestbranding=1';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeCustomYTModal() {
    const modal = document.getElementById('custom-yt-modal');
    const iframe = document.getElementById('custom-yt-iframe');
    if (!modal || !iframe) return;

    // Detener la reproducción limpiando el src
    iframe.src = '';
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  function handleModalOverlayClick(e) {
    if (e.target.id === 'custom-yt-modal') {
      closeCustomYTModal();
    }
  }

  // Cerrar con tecla Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      const modal = document.getElementById('custom-yt-modal');
      if (modal && modal.style.display === 'flex') {
        closeCustomYTModal();
      }
    }
  });
</script>
@endif
