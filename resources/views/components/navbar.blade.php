<!-- NAV -->
<nav class="site-nav" style="background:var(--color-navy);position:sticky;top:0;z-index:100;border-bottom:2px solid var(--color-red);transition:background .3s">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between">
    <ul class="site-nav-categories">
      <li><a href="/" class="nav-item-link" style="display:block;padding:12px 16px;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:border-color .2s"><i class="fas fa-home" style="margin-right:4px"></i> INICIO</a></li>
      @php
        $menuCats = $navCategorias ?? $categorias ?? \App\Models\Category::orderBy('name', 'asc')->get();
      @endphp
      @forelse($menuCats as $categoria)
        @php
          $isSports = str_contains(strtolower($categoria->name), 'deporte') || str_contains(strtolower($categoria->name), 'futbol');
        @endphp
        @if(!$isSports)
          <li><a href="{{ route('categoria.noticias', $categoria->slug) }}" class="nav-item-link" style="display:block;padding:12px 16px;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:border-color .2s">{{ strtoupper($categoria->name) }}</a></li>
        @endif
      @empty
        @foreach(['politica' => 'Política','pais' => 'País','santa-cruz' => 'Santa Cruz','economia' => 'Economía'] as $cSlug => $cat)
          <li><a href="{{ route('categoria.noticias', $cSlug) }}" class="nav-item-link" style="display:block;padding:12px 16px;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent">{{ $cat }}</a></li>
        @endforeach
      @endforelse
      <li style="background:linear-gradient(90deg, #090d16, #162032); border-left:1px solid #00FF87; border-right:1px solid #00FF87;">
        <a href="{{ route('contraataque.index') }}" class="nav-item-link" style="display:flex;align-items:center;gap:5px;padding:12px 14px;color:#00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:all .2s"><i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE</a>
      </li>
      <li><a href="{{ route('opinion.index') }}" class="nav-item-link" style="display:block;padding:12px 16px;color:#fca5a5;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:border-color .2s"><i class="fa-solid fa-pen-nib" style="margin-right:4px"></i> OPINIÓN</a></li>
    </ul>
    <button id="mobileMenuToggle" onclick="openMobileMenu()" aria-label="Abrir Menú"><i class="fas fa-bars"></i></button>
  </div>
</nav>

<!-- MOBILE MENU DRAWER -->
<div id="mobileMenu" style="position:fixed;top:0;left:0;width:320px;max-width:86vw;height:100%;background:var(--color-card-bg);box-shadow:6px 0 30px rgba(0,0,0,.35);transform:translateX(-100%);transition:transform .3s ease;z-index:9999;overflow-y:auto;-webkit-overflow-scrolling:touch;padding-bottom:max(24px, env(safe-area-inset-bottom))">
  <div style="background:var(--color-navy);padding:18px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid var(--color-red)">
    <div style="display:flex;align-items:center;gap:8px">
      <span style="font-family:var(--font-title-anton);font-size:1.4rem;color:#fff">LATITUD<span style="color:var(--color-red)">18</span></span>
    </div>
    <button onclick="closeMobileMenu()" style="background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:4px" aria-label="Cerrar Menú"><i class="fas fa-times"></i></button>
  </div>
  <div style="padding:16px 20px">
    <a href="/" style="display:flex;align-items:center;gap:10px;padding:12px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-weight:700;font-size:.95rem"><i class="fas fa-home" style="color:var(--color-red)"></i> Inicio</a>
    
    <!-- CONTRA ATAQUE PROMINENTE EN MOVIL -->
    <a href="{{ route('contraataque.index') }}" style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:linear-gradient(90deg, #090d16, #162032);color:#00FF87;text-decoration:none;border-radius:4px;margin:12px 0;border:1px solid #00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.88rem;box-shadow:0 0 12px rgba(0,255,135,0.25)">
      <i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE (DEPORTES)
    </a>

    <!-- ACCESOS RÁPIDOS MÓVIL -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
      <button onclick="closeMobileMenu();openSearchModal()" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 8px;background:var(--color-navy-subtle);border:1px solid var(--color-border);border-radius:4px;font-size:.8rem;font-weight:700;color:var(--color-navy);cursor:pointer">
        <i class="fas fa-search" style="color:var(--color-red)"></i> Buscar
      </button>
      <button onclick="closeMobileMenu();openLiveModal()" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 8px;background:var(--color-red);border:none;border-radius:4px;font-size:.8rem;font-weight:800;color:#fff;cursor:pointer">
        <span style="width:6px;height:6px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> En Vivo
      </button>
    </div>

    <div>
      <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-text-muted);font-weight:800;margin-bottom:8px">Secciones</p>
      @forelse($menuCats as $categoria)
        @php
          $isSportsMob = str_contains(strtolower($categoria->name), 'deporte') || str_contains(strtolower($categoria->name), 'futbol');
        @endphp
        @if($isSportsMob)
          <a href="{{ route('contraataque.index') }}" style="display:flex;align-items:center;gap:8px;padding:11px 0;color:#00FF87;text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-weight:800"><i class="fas fa-bolt" style="font-size:.7rem;color:#FF3B30"></i> {{ $categoria->name }} (Contra Ataque)</a>
        @else
          <a href="{{ route('categoria.noticias', $categoria->slug) }}" style="display:flex;align-items:center;gap:8px;padding:11px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-weight:600;transition:color .2s" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'"><i class="fas fa-chevron-right" style="font-size:.6rem;color:var(--color-red)"></i> {{ $categoria->name }}</a>
        @endif
      @empty
      @endforelse
    </div>
    <div style="margin-top:14px;display:flex;flex-direction:column;gap:4px">
      <a href="{{ route('periodico.public.index') }}" style="display:flex;align-items:center;gap:8px;padding:11px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-weight:600"><i class="fas fa-newspaper" style="color:var(--color-red)"></i> Periódico Digital</a>
      <a href="{{ route('opinion.index') }}" style="display:flex;align-items:center;gap:8px;padding:11px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-weight:600"><i class="fa-solid fa-pen-nib" style="color:var(--color-red)"></i> Opinión & Análisis</a>
    </div>
    <div style="margin-top:16px">
      <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-text-muted);font-weight:800;margin-bottom:10px">Síguenos en Redes</p>
      <div style="display:flex;gap:8px">
        <a href="https://facebook.com/uhtvbolivia" target="_blank" style="width:36px;height:36px;background:var(--color-navy-subtle);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-facebook-f" style="font-size:.85rem"></i></a>
        <a href="https://www.youtube.com/@UHTVBolivia" target="_blank" style="width:36px;height:36px;background:var(--color-navy-subtle);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-youtube" style="font-size:.85rem"></i></a>
        <a href="https://instagram.com/uhtvbolivia" target="_blank" style="width:36px;height:36px;background:var(--color-navy-subtle);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-instagram" style="font-size:.85rem"></i></a>
      </div>
    </div>
  </div>
</div>
<div id="mobileMenuOverlay" onclick="closeMobileMenu()" style="position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(3px);opacity:0;pointer-events:none;transition:opacity .3s;z-index:9998"></div>

