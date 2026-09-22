<!-- ========== MODAL: EN VIVO TV & RADIO ========== -->
@php
  $tvActive = setting('streaming_tv_active', '1') == '1';
  $rawTvId = setting('streaming_tv_youtube_id', 'lvbgd2JETfI');
  $tvYtId = extract_youtube_id($rawTvId) ?: ($rawTvId ?: 'lvbgd2JETfI');
  $tvTitle = setting('streaming_tv_title', 'Latitud 18 TV — Transmisión Digital En Vivo 24/7');
  $radioActive = setting('streaming_radio_active', '1') == '1';
  $radioTitle = setting('streaming_radio_title', 'Radio Latitud 18 FM — Señal Online');
  $radioUrl = setting('streaming_radio_url', 'https://stream.zeno.fm/f3wvbbqmdg8uv');
@endphp

<div class="live-stream-modal-overlay" id="live-streaming-modal-suite">
  <!-- Cabecera del Modal -->
  <div class="live-modal-header">
    <div style="display:flex;align-items:center;gap:12px;min-width:0;">
      <span style="background:var(--color-red);color:#fff;font-weight:800;font-size:.68rem;padding:4px 10px;border-radius:2px;animation:pulse 1.5s infinite;letter-spacing:0.5px;flex-shrink:0;">
        <i class="fas fa-circle" style="font-size:6px;margin-right:4px;vertical-align:middle;"></i>EN VIVO HD
      </span>
      <h2 style="font-family:var(--font-title-anton);font-size:1.3rem;color:#fff;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" id="liveModalTitleHeader">
        {{ strtoupper($tvTitle) }}
      </h2>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <button class="btn-close-newspaper-modal" onclick="closeLiveModal()" title="Cerrar modal (Esc)" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#fff;width:38px;height:38px;border-radius:4px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;">
        <i class="fas fa-times" style="font-size:1.1rem;"></i>
      </button>
    </div>
  </div>

  <!-- Contenido con scroll -->
  <div class="live-modal-body-scroll">
    <div class="container">
      <div class="studio-grid" style="margin-bottom:24px;">

        <!-- Set de Televisión -->
        <div class="tv-set-wrapper" id="tvSetWrapper">
          <div class="tv-screen-frame" id="tvScreenFrame">
            <!-- Contenedor del reproductor de video -->
            <div class="tv-video-container" id="tvVideoContainer">
              @if($tvActive && !empty($tvYtId))
                <iframe 
                  id="liveTvIframe"
                  data-default-id="{{ $tvYtId }}"
                  src="https://www.youtube-nocookie.com/embed/{{ $tvYtId }}?autoplay=1&mute=0&enablejsapi=1&rel=0&playsinline=1&controls=1" 
                  style="width:100%;height:100%;border:none;display:block;" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen" 
                  allowfullscreen>
                </iframe>
              @else
                <div style="color:#94A3B8;text-align:center;padding:30px;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#050B14;">
                  <i class="fas fa-tv" style="font-size:3.5rem;margin-bottom:14px;color:#D71920;opacity:0.8;"></i>
                  <h4 style="font-family:var(--font-title-montserrat);font-weight:800;color:#fff;font-size:1.2rem;margin-bottom:6px;">Transmisión en Pausa</h4>
                  <p style="font-size:0.88rem;max-width:400px;line-height:1.5;color:#94A3B8;">La señal en vivo iniciará en breve. Si eres administrador, puedes configurar el ID del video o directo desde el panel de control.</p>
                </div>
              @endif
            </div>

            <!-- Rótulo "En Vivo" decorativo (no bloquea clics) -->
            <div class="tv-live-badge">
              <span style="width:7px;height:7px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> SEÑAL MATRIZ HD
            </div>

            <!-- Marca de Agua (no bloquea clics) -->
            <div class="tv-watermark">LATITUD<span style="color:var(--color-red)">18</span> TV</div>

            <!-- Zócalo de Noticias Inferior (Chyron / Lower Third) con soporte para ocultar/mostrar -->
            <div class="lower-third-overlay" id="lower-third-box">
              <div class="lt-badge" id="lt-badge-text">ÚLTIMO MOMENTO</div>
              <div>
                <div class="lt-headline" id="lt-headline-text">COBERTURA ESPECIAL EN VIVO</div>
                <div class="lt-subheadline" id="lt-subheadline-text">{{ $tvTitle }}</div>
                <div class="lt-location">BOLIVIA • {{ date('H:i') }}</div>
              </div>
            </div>
          </div>

          <!-- BARRA PRINCIPAL DE CONTROLES: REPRODUCCIÓN, AUDIO, PANTALLA COMPLETA Y GRÁFICOS -->
          <div class="tv-playback-toolbar">
            <div class="tv-controls-group">
              <!-- Botón Pausar / Reanudar -->
              <button type="button" class="tv-ctrl-btn primary" id="btnTvPlayPause" onclick="toggleTvPlayPause()" title="Pausar o Reanudar reproducción (Espacio)">
                <i class="fas fa-pause"></i> <span>Pausar</span>
              </button>

              <!-- Botón Silenciar / Sonido -->
              <button type="button" class="tv-ctrl-btn" id="btnTvMute" onclick="toggleTvMute()" title="Silenciar / Activar sonido (M)">
                <i class="fas fa-volume-up"></i> <span>Audio</span>
              </button>

              <!-- Botón Pantalla Completa -->
              <button type="button" class="tv-ctrl-btn highlight" id="btnTvFullscreen" onclick="toggleTvFullscreen()" title="Pantalla completa (F)">
                <i class="fas fa-expand"></i> <span>Pantalla Completa</span>
              </button>

              <!-- Botón Ocultar / Mostrar Zócalo -->
              <button type="button" class="tv-ctrl-btn" id="btnToggleGraphics" onclick="toggleTvGraphics()" title="Ocultar rótulos para ver el video limpio">
                <i class="fas fa-eye-slash"></i> <span>Ocultar Zócalo</span>
              </button>
            </div>

            <div class="tv-controls-group">
              <!-- Botón Recargar Señal -->
              <button type="button" class="tv-ctrl-btn" onclick="reloadLiveTv()" title="Recargar transmisión si se detuvo">
                <i class="fas fa-sync-alt"></i> <span>Recargar</span>
              </button>

              <!-- Enlace directo a YouTube -->
              <a id="btnTvExternal" href="https://www.youtube.com/watch?v={{ $tvYtId }}" target="_blank" rel="noopener noreferrer" class="tv-ctrl-btn youtube" title="Ver en aplicación o web de YouTube">
                <i class="fab fa-youtube"></i> <span>YouTube</span>
              </a>
            </div>
          </div>

          <!-- BARRA DE RÓTULOS TELEVISIVOS (ESTUDIO EN VIVO) -->
          <div class="studio-control-bar">
            <span style="font-weight:700;letter-spacing:0.5px;"><i class="fas fa-sliders-h" style="margin-right:4px"></i> Rótulos:</span>
            <button class="btn-lt-switcher active" onclick="switchLowerThird('ÚLTIMO MOMENTO','COBERTURA ESPECIAL EN VIVO','Transmisión continua de noticias y análisis',this)"><i class="fas fa-circle" style="font-size:.45rem;vertical-align:middle;margin-right:3px;"></i> Último Momento</button>
            <button class="btn-lt-switcher" onclick="switchLowerThird('NOTICIAS','RESUMEN INFORMATIVO DEL DÍA','Cobertura completa de los acontecimientos nacionales',this)"><i class="fas fa-circle" style="font-size:.45rem;vertical-align:middle;margin-right:3px;"></i> Noticias</button>
            <button class="btn-lt-switcher" onclick="switchLowerThird('ENTREVISTA','ESPACIO DE DIÁLOGO Y ANÁLISIS','Espacio de opinión, debate y actualidad',this)"><i class="fas fa-circle" style="font-size:.45rem;vertical-align:middle;margin-right:3px;"></i> Entrevistas</button>
            <button class="btn-lt-switcher" onclick="switchLowerThird('DEPORTES','CONTRA ATAQUE EN DIRECTO','El acontecer deportivo nacional e internacional',this)"><i class="fas fa-bolt" style="font-size:.65rem;vertical-align:middle;margin-right:3px;color:#00FF87;"></i> Deportes</button>
          </div>
        </div>

        <!-- Panel Lateral: Chat Comunitario en Vivo -->
        <div class="live-chat-panel">
          <div class="chat-header">
            <h3><i class="fas fa-comments" style="color:var(--color-red);margin-right:6px"></i> Comunidad en Vivo</h3>
            <div class="chat-viewers-count"><i class="fas fa-broadcast-tower text-danger"></i> Señal Online</div>
          </div>
          <div class="chat-messages-scroll" id="chat-messages-container">
            <div class="chat-msg admin">
              <strong style="color:var(--color-red)">LATITUD 18:</strong> ¡Bienvenidos a la transmisión digital en directo de Latitud 18! Déjanos tus comentarios y opiniones aquí.
            </div>
          </div>
          <form class="chat-input-form" onsubmit="sendChatMessage(event)">
            <input type="text" class="chat-input" id="chat-input-text" placeholder="Escribe tu mensaje en vivo..." autocomplete="off">
            <button type="submit" class="btn-send-chat" title="Enviar"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>

      </div>

      <!-- Tarjeta Radio Online -->
      <div class="radio-studio-card">
        <div class="radio-player-box">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
            <div class="radio-icon-circle"><i class="fas fa-radio"></i></div>
            <div>
              <div style="font-family:var(--font-title-montserrat);font-weight:900;font-size:.9rem;color:var(--color-navy)">{{ $radioTitle }}</div>
              <div style="font-size:.7rem;color:var(--color-text-muted)">FRECUENCIA DIGITAL ONLINE • 24/7 SIN RUIDO</div>
            </div>
          </div>
          @if($radioActive && !empty($radioUrl))
            <audio id="global-radio-audio" src="{{ $radioUrl }}" preload="none"></audio>
          @endif
          <canvas id="radio-visualizer" class="radio-visualizer-canvas"></canvas>
          <div style="display:flex;align-items:center;gap:12px">
            <button class="btn-radio-play" id="btn-main-radio-play" onclick="toggleRadioPlay()"><i class="fas fa-play" id="radio-play-icon"></i></button>
            <div>
              <div style="font-family:var(--font-title-montserrat);font-weight:700;font-size:.85rem;color:var(--color-text-main)">Transmisión de Audio en Directo</div>
              <div style="font-size:.72rem;color:var(--color-text-muted)">Señal continua vía streaming online</div>
            </div>
          </div>
        </div>
        <div>
          <div style="font-family:var(--font-title-montserrat);font-weight:800;font-size:.82rem;text-transform:uppercase;color:var(--color-navy);margin-bottom:10px">
            <i class="fas fa-podcast" style="color:var(--color-red);margin-right:4px"></i> Información en Directo
          </div>
          <p style="font-size:0.82rem;color:var(--color-text-muted);line-height:1.6;">
            Escucha la señal de radio en vivo desde cualquier dispositivo móvil o computadora con calidad de audio digital HD.
          </p>
          <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <span class="badge bg-danger text-white px-2 py-1" style="font-size:0.7rem;"><i class="fas fa-wifi me-1"></i> SEÑAL EN LÍNEA</span>
            <span class="badge bg-dark text-white px-2 py-1" style="font-size:0.7rem;"><i class="fas fa-headphones me-1"></i> 128 KBPS STEREO</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
