<!-- ========== MODAL: EN VIVO TV & RADIO ========== -->
@php
  $tvActive = setting('streaming_tv_active', '1') == '1';
  $rawTvId = setting('streaming_tv_youtube_id', 'jfKfPfyJRdk');
  $tvYtId = extract_youtube_id($rawTvId) ?: $rawTvId;
  $tvTitle = setting('streaming_tv_title', 'UHTV En Vivo — Transmisión Digital 24/7');
  $radioActive = setting('streaming_radio_active', '1') == '1';
  $radioTitle = setting('streaming_radio_title', 'Radio Latitud 18 FM — Señal Online');
  $radioUrl = setting('streaming_radio_url', 'https://stream.zeno.fm/f3wvbbqmdg8uv');
@endphp
<div class="live-stream-modal-overlay" id="live-streaming-modal-suite">
  <div class="live-modal-header">
    <div style="display:flex;align-items:center;gap:12px">
      <span style="background:var(--color-red);color:#fff;font-weight:800;font-size:.65rem;padding:3px 10px;border-radius:2px;animation:pulse 1.5s infinite">EN VIVO HD</span>
      <h2 style="font-family:var(--font-title-anton);font-size:1.4rem;color:#fff">{{ strtoupper($tvTitle) }}</h2>
    </div>
    <button class="btn-close-newspaper-modal" onclick="closeLiveModal()" title="Cerrar"><i class="fas fa-times"></i></button>
  </div>
  <div class="live-modal-body-scroll">
    <div class="container">
      <div class="studio-grid" style="margin-bottom:30px">
        <div class="tv-set-wrapper">
          <div class="tv-screen-frame">
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
              @if($tvActive && !empty($tvYtId))
                <iframe src="https://www.youtube.com/embed/{{ $tvYtId }}?autoplay=1&mute=1&enablejsapi=1&rel=0" style="width:100%;height:100%;border:none" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture;web-share" allowfullscreen></iframe>
              @else
                <div style="color:#94A3B8;text-align:center;padding:20px;">
                  <i class="fas fa-tv" style="font-size:3rem;margin-bottom:12px;display:block;opacity:0.4;"></i>
                  <h4 style="font-family:var(--font-title-montserrat);font-weight:700;color:#fff;">Transmisión TV en pausa</h4>
                  <p style="font-size:0.85rem;">La señal en vivo iniciará en breve. Permanece atento.</p>
                </div>
              @endif
            </div>
            <div class="tv-live-badge"><span style="width:7px;height:7px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> SEÑAL MATRIZ HD</div>
            <div class="tv-watermark">LATITUD<span style="color:var(--color-red)">18</span> TV</div>
            <div class="lower-third-overlay" id="lower-third-box">
              <div class="lt-badge" id="lt-badge-text">ÚLTIMO MOMENTO</div>
              <div>
                <div class="lt-headline" id="lt-headline-text">COBERTURA ESPECIAL EN VIVO</div>
                <div class="lt-subheadline" id="lt-subheadline-text">{{ $tvTitle }}</div>
                <div class="lt-location">BOLIVIA • {{ date('H:i') }}</div>
              </div>
            </div>
          </div>
          <div class="studio-control-bar">
            <span><i class="fas fa-sliders" style="margin-right:4px"></i> Gráficos:</span>
            <button class="btn-lt-switcher active" onclick="switchLowerThird('ÚLTIMO MOMENTO','COBERTURA ESPECIAL EN VIVO','Transmisión continua de noticias',this)"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:3px;color:#fff"></i> Último Momento</button>
            <button class="btn-lt-switcher" onclick="switchLowerThird('NOTICIAS','RESUMEN INFORMATIVO DEL DÍA','Cobertura completa de los acontecimientos',this)"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:3px;color:#fff"></i> Noticias</button>
            <button class="btn-lt-switcher" onclick="switchLowerThird('ENTREVISTA','ESPACIO DE DIÁLOGO Y ANÁLISIS','Espacio de opinión y debate constructivo',this)"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:3px;color:#fff"></i> Entrevistas</button>
          </div>
        </div>
        <div class="live-chat-panel">
          <div class="chat-header"><h3><i class="fas fa-comments" style="color:var(--color-red);margin-right:4px"></i> Comunidad en Vivo</h3><div class="chat-viewers-count"><i class="fas fa-eye"></i> Transmisión Oficial</div></div>
          <div class="chat-messages-scroll" id="chat-messages-container">
            <div class="chat-msg admin"><strong style="color:var(--color-red)">LATITUD18:</strong> Bienvenidos a la transmisión en vivo de Latitud 18 / UHTV. ¡Participa con tus comentarios!</div>
          </div>
          <form class="chat-input-form" onsubmit="sendChatMessage(event)">
            <input type="text" class="chat-input" id="chat-input-text" placeholder="Escribe tu mensaje en vivo..." autocomplete="off">
            <button type="submit" class="btn-send-chat"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>
      <div class="radio-studio-card">
        <div class="radio-player-box">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
            <div class="radio-icon-circle"><i class="fas fa-radio"></i></div>
            <div><div style="font-family:var(--font-title-montserrat);font-weight:900;font-size:.9rem;color:var(--color-navy)">{{ $radioTitle }}</div><div style="font-size:.7rem;color:var(--color-text-muted)">FRECUENCIA DIGITAL ONLINE • 24/7 SIN RUIDO</div></div>
          </div>
          @if($radioActive && !empty($radioUrl))
            <audio id="global-radio-audio" src="{{ $radioUrl }}" preload="none"></audio>
          @endif
          <canvas id="radio-visualizer" class="radio-visualizer-canvas"></canvas>
          <div style="display:flex;align-items:center;gap:12px">
            <button class="btn-radio-play" id="btn-main-radio-play" onclick="toggleRadioPlay()"><i class="fas fa-play" id="radio-play-icon"></i></button>
            <div><div style="font-family:var(--font-title-montserrat);font-weight:700;font-size:.85rem;color:var(--color-text-main)">Transmisión de Audio en Directo</div><div style="font-size:.72rem;color:var(--color-text-muted)">Señal continua vía streaming online</div></div>
          </div>
        </div>
        <div>
          <div style="font-family:var(--font-title-montserrat);font-weight:800;font-size:.82rem;text-transform:uppercase;color:var(--color-navy);margin-bottom:10px"><i class="fas fa-podcast" style="color:var(--color-red);margin-right:4px"></i> Información en Directo</div>
          <p style="font-size:0.82rem;color:var(--color-text-muted);line-height:1.6;">
            Escucha la señal de radio en vivo desde cualquier dispositivo móvil o computadora con calidad digital HD.
          </p>
          <div style="margin-top:12px;">
            <span class="badge bg-danger text-white px-2 py-1" style="font-size:0.7rem;"><i class="fas fa-wifi me-1"></i> SEÑAL EN LÍNEA</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
