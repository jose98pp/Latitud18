<!-- TOP BAR -->
<aside class="site-top-bar" style="background:var(--color-navy-dark);color:#94A3B8;font-size:.75rem;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.06)">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
    <div style="display:flex;align-items:center;gap:12px;min-width:0">
      <span id="topbar-date-text" style="font-weight:600;white-space:nowrap">Cargando fecha...</span>
      <span style="background:var(--color-red);color:#fff;font-weight:800;font-size:.6rem;padding:2px 8px;border-radius:2px;text-transform:uppercase;letter-spacing:.5px;animation:pulse 1.5s infinite;white-space:nowrap">
        <i class="fas fa-circle" style="font-size:5px;margin-right:3px;vertical-align:middle"></i>ÚLTIMA HORA
      </span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
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
      <a href="{{ route('periodico.public.index') }}" style="background:var(--color-navy);color:#fff;border:1px solid rgba(255,255,255,.15);padding:4px 10px;border-radius:2px;font-family:var(--font-title-montserrat);font-weight:700;font-size:.6rem;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;transition:all .2s;white-space:nowrap" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy)';this.style.color='#fff'">
        <i class="fas fa-newspaper" style="margin-right:4px;color:var(--color-red)"></i> PERIÓDICO DIGITAL
      </a>
    </div>
  </div>
</aside>

<!-- MASTHEAD -->
<header class="site-header" style="background:var(--color-card-bg);border-bottom:3px solid var(--color-red);padding:12px 0;transition:background .3s">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between">
    <a href="/" style="display:flex;align-items:center;gap:12px;text-decoration:none">
      <div>
        <div style="display:flex;align-items:baseline;gap:4px">
          <span style="font-family:var(--font-title-anton);font-size:2.6rem;color:var(--color-navy);line-height:1">LATITUD</span>
          <span style="font-family:var(--font-title-anton);font-size:1.8rem;background:var(--color-red);color:#fff;padding:2px 10px;border-radius:2px;line-height:1">18</span>
        </div>
        <p style="font-family:var(--font-title-montserrat);font-size:.55rem;font-weight:700;text-transform:uppercase;letter-spacing:3px;color:var(--color-text-muted);margin-top:2px">INFORMACIÓN SIN RUIDO</p>
      </div>
    </a>
    <div style="display:flex;align-items:center;gap:10px">
      <button onclick="openSearchModal()" style="display:flex;align-items:center;gap:6px;background:var(--color-navy-subtle);border:1px solid var(--color-border);color:var(--color-text-muted);padding:7px 14px;border-radius:2px;font-family:var(--font-title-montserrat);font-weight:700;font-size:.72rem;cursor:pointer;transition:all .2s" onmouseover="this.style.borderColor='var(--color-red)';this.style.color='var(--color-red)'" onmouseout="this.style.borderColor='var(--color-border)';this.style.color='var(--color-text-muted)'">
        <i class="fas fa-search"></i> BUSCAR
      </button>
      <button onclick="openLiveModal()" style="display:flex;align-items:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;padding:7px 14px;border-radius:2px;border:none;cursor:pointer;transition:background .2s" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
        <span style="width:8px;height:8px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> EN VIVO
      </button>
      <button id="btn-pwa-install" onclick="triggerPwaInstall()" style="display:none;align-items:center;gap:6px;background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2);font-family:var(--font-title-montserrat);font-weight:700;font-size:.7rem;text-transform:uppercase;padding:7px 12px;border-radius:2px;cursor:pointer;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.borderColor='var(--color-red)'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.borderColor='rgba(255,255,255,.2)'">
        <i class="fas fa-download"></i> INSTALAR APP
      </button>
      <a href="{{ route('contraataque.index') }}" style="display:flex;align-items:center;gap:6px;background:linear-gradient(135deg, #090d16 0%, #162032 100%);border:1px solid #00FF87;color:#00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.7rem;text-transform:uppercase;text-decoration:none;padding:7px 14px;border-radius:2px;box-shadow:0 0 10px rgba(0,255,135,0.25);transition:all .2s" onmouseover="this.style.background='#00FF87';this.style.color='#000'" onmouseout="this.style.background='linear-gradient(135deg, #090d16 0%, #162032 100%)';this.style.color='#00FF87'">
        <i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE
      </a>
      <a href="{{ route('periodico.public.index') }}" style="display:flex;align-items:center;gap:6px;background:var(--color-navy);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;text-decoration:none;padding:7px 14px;border-radius:2px;transition:background .2s" onmouseover="this.style.background='var(--color-navy-light)'" onmouseout="this.style.background='var(--color-navy)'">
        <i class="fas fa-newspaper"></i> PERIÓDICO DIGITAL
      </a>
      <button onclick="toggleTheme()" style="background:var(--color-navy-subtle);color:var(--color-navy);border:1px solid var(--color-border);padding:7px 9px;border-radius:2px;cursor:font-size:.85rem;transition:all .2s" onmouseover="this.style.background='var(--color-navy)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-navy)'">
        <i class="fas fa-sun" id="themeIconSun" style="display:none"></i>
        <i class="fas fa-moon" id="themeIconMoon"></i>
      </button>
    </div>
  </div>
</header>
