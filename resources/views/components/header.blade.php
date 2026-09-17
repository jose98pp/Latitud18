<!-- TOP BAR -->
<aside class="site-top-bar" style="background:var(--color-navy-dark);color:#94A3B8;font-size:.75rem;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.06)">
  <div class="container site-top-bar-inner">
    <div class="site-top-bar-left">
      <span id="topbar-date-text" style="font-weight:600;white-space:nowrap">Cargando fecha...</span>
      <span style="background:var(--color-red);color:#fff;font-weight:800;font-size:.6rem;padding:2px 8px;border-radius:2px;text-transform:uppercase;letter-spacing:.5px;animation:pulse 1.5s infinite;white-space:nowrap">
        <i class="fas fa-circle" style="font-size:5px;margin-right:3px;vertical-align:middle"></i>ÚLTIMA HORA
      </span>
    </div>
    <div class="topbar-socials">
      @if(setting('social_facebook'))
        <a href="{{ setting('social_facebook') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-facebook-f"></i></a>
      @endif
      @if(setting('social_youtube'))
        <a href="{{ setting('social_youtube') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-youtube"></i></a>
      @endif
      @if(setting('social_instagram'))
        <a href="{{ setting('social_instagram') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-instagram"></i></a>
      @endif
      @if(setting('social_twitter'))
        <a href="{{ setting('social_twitter') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-x-twitter"></i></a>
      @endif
      @if(setting('social_tiktok'))
        <a href="{{ setting('social_tiktok') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-tiktok"></i></a>
      @endif
      @if(setting('whatsapp_phone'))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('whatsapp_phone')) }}?text={{ urlencode(setting('whatsapp_message', 'Hola')) }}" target="_blank" style="color:#25D366" title="WhatsApp Denuncias"><i class="fab fa-whatsapp"></i></a>
      @endif
    </div>
  </div>
</aside>

<!-- MASTHEAD -->
<header class="site-header" style="background:var(--color-card-bg);border-bottom:3px solid var(--color-red);padding:10px 0;transition:background .3s">
  <div class="container site-header-inner">
    <a href="/" class="site-brand-link">
      <div>
        <div class="site-brand-logo">
          <span class="brand-main">LATITUD</span>
          <span class="brand-num">18</span>
        </div>
        <p class="site-brand-slogan">INFORMACIÓN SIN RUIDO</p>
      </div>
    </a>
    <div class="site-header-actions">
      <button onclick="openSearchModal()" class="header-btn header-btn-search" title="Buscar noticias">
        <i class="fas fa-search"></i> <span class="btn-label">BUSCAR</span>
      </button>
      <button onclick="openLiveModal()" class="header-btn header-btn-live" title="Señal de TV y Radio en Vivo">
        <span style="width:7px;height:7px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite;display:inline-block"></span> <span class="btn-label">EN VIVO</span>
      </button>
      <button id="btn-pwa-install" onclick="triggerPwaInstall()" class="header-btn header-btn-pwa" style="display:none;background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2)" title="Instalar Aplicación Web">
        <i class="fas fa-download"></i> <span class="btn-label">APP</span>
      </button>
      <a href="{{ route('contraataque.index') }}" class="header-btn header-btn-sports" title="Sección de Deportes Contra Ataque">
        <i class="fas fa-bolt" style="color:#FF3B30"></i> <span class="btn-label">CONTRA ATAQUE</span>
      </a>
      <a href="{{ route('periodico.public.index') }}" class="header-btn header-btn-newspaper" title="Ver Periódico Digital Semanal">
        <i class="fas fa-newspaper"></i> <span class="btn-label">PERIÓDICO DIGITAL</span>
      </a>
      <button onclick="toggleTheme()" class="header-btn-theme" title="Cambiar tema día/noche" aria-label="Cambiar tema día/noche">
        <i class="fas fa-sun" id="themeIconSun" style="display:none"></i>
        <i class="fas fa-moon" id="themeIconMoon"></i>
      </button>
      <button type="button" class="header-mobile-toggle-btn" onclick="openMobileMenu()" aria-label="Abrir Menú Principal" title="Menú">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </div>
</header>
