<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Contra Ataque — Deportes, Fútbol Boliviano y Pasión') | Latitud 18</title>
    
    <!-- Meta tags SEO -->
    <meta name="description" content="@yield('meta_description', 'Contra Ataque: La sección de deportes más completa de Bolivia. Fútbol Boliviano, Selección La Verde, Champions League, Liga Profesional, estadísticas en vivo y opinión.')">
    <meta property="og:title" content="@yield('title', 'Contra Ataque — Deportes')">
    <meta property="og:description" content="@yield('meta_description', 'Portal oficial de deportes Contra Ataque.')">
    <meta property="og:image" content="{{ asset('images/contraataque-logo.jpg') }}">
    
    <!-- Google Fonts Deportivas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,600;0,700;0,800;0,900;1,700;1,800&family=Oswald:wght@500;600;700&family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --ca-bg-main: #090D16;
            --ca-bg-card: #111827;
            --ca-bg-card-hover: #1E293B;
            --ca-bg-elevated: #162032;
            --ca-border: rgba(255, 255, 255, 0.1);
            --ca-border-active: #00FF87;
            --ca-volt: #00FF87;
            --ca-volt-glow: rgba(0, 255, 135, 0.35);
            --ca-red: #FF3B30;
            --ca-orange: #FF6B00;
            --ca-gold: #FBBF24;
            --ca-blue: #0284C7;
            --ca-text: #F3F4F6;
            --ca-text-muted: #94A3B8;
            --ca-font-display: 'Barlow Condensed', sans-serif;
            --ca-font-title: 'Oswald', sans-serif;
            --ca-font-body: 'Inter', system-ui, -apple-system, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: var(--ca-bg-main);
            color: var(--ca-text);
            font-family: var(--ca-font-body);
            font-size: 0.95rem;
            line-height: 1.6;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; transition: all 0.2s ease; }
        a:hover { color: var(--ca-volt); }

        /* ══════════════════════════════════════════════════════
           LIVE MATCHES RIBBON (TICKER)
        ══════════════════════════════════════════════════════ */
        .ca-live-ticker-wrap {
            background: #05080E;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 6px 0;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
            scrollbar-color: var(--ca-volt) #05080E;
        }
        .ca-live-ticker-wrap::-webkit-scrollbar { height: 4px; }
        .ca-live-ticker-wrap::-webkit-scrollbar-thumb { background: var(--ca-volt); border-radius: 4px; }

        .ca-ticker-container {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 0 16px;
        }

        .ca-match-chip {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 6px;
            padding: 4px 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.76rem;
            transition: all 0.2s;
        }
        .ca-match-chip:hover {
            background: rgba(255,255,255,0.08);
            border-color: var(--ca-volt);
            transform: translateY(-1px);
        }

        .ca-badge-live {
            background: var(--ca-red);
            color: #fff;
            font-family: var(--ca-font-display);
            font-weight: 800;
            font-size: 0.65rem;
            letter-spacing: 0.5px;
            padding: 1px 6px;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            animation: pulseLive 1.5s infinite;
        }
        @keyframes pulseLive {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.75; transform: scale(0.96); }
        }

        .ca-badge-ft {
            background: rgba(255,255,255,0.15);
            color: #ccc;
            font-family: var(--ca-font-display);
            font-weight: 800;
            font-size: 0.65rem;
            padding: 1px 5px;
            border-radius: 3px;
        }

        /* ══════════════════════════════════════════════════════
           SPORTS HEADER
        ══════════════════════════════════════════════════════ */
        .ca-header {
            background: linear-gradient(180deg, #0d1322 0%, #090d16 100%);
            border-bottom: 2px solid var(--ca-volt);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
        }

        .ca-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .ca-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #CBD5E1;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .ca-back-btn:hover {
            background: var(--ca-red);
            border-color: var(--ca-red);
            color: #fff;
        }

        .ca-brand-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ca-logo-img {
            height: 52px;
            width: auto;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 0 15px rgba(0,255,135,0.2);
            transition: transform 0.2s;
        }
        .ca-logo-img:hover {
            transform: scale(1.03);
        }

        .ca-brand-title {
            font-family: var(--ca-font-display);
            font-weight: 900;
            font-size: 2.2rem;
            line-height: 0.95;
            letter-spacing: -0.5px;
            text-transform: uppercase;
            color: #fff;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .ca-brand-title span.volt {
            color: var(--ca-volt);
            text-shadow: 0 0 10px var(--ca-volt-glow);
        }
        .ca-brand-motto {
            font-family: var(--ca-font-display);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--ca-text-muted);
            text-transform: uppercase;
        }

        /* ══════════════════════════════════════════════════════
           SPORTS NAVBAR
        ══════════════════════════════════════════════════════ */
        .ca-nav-bar {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            white-space: nowrap;
            padding: 6px 0;
        }
        .ca-nav-item {
            font-family: var(--ca-font-display);
            font-weight: 800;
            font-size: 0.92rem;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #E2E8F0;
            padding: 6px 12px;
            border-radius: 4px;
            transition: all 0.15s;
            position: relative;
        }
        .ca-nav-item:hover {
            color: var(--ca-volt);
            background: rgba(0, 255, 135, 0.08);
        }
        .ca-nav-item.active {
            color: #000;
            background: var(--ca-volt);
            font-weight: 900;
            box-shadow: 0 0 12px var(--ca-volt-glow);
        }

        /* ══════════════════════════════════════════════════════
           CARDS & COMPONENTS
        ══════════════════════════════════════════════════════ */
        .ca-card {
            background: var(--ca-bg-card);
            border: 1px solid var(--ca-border);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .ca-card:hover {
            border-color: rgba(0,255,135,0.4);
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
            transform: translateY(-2px);
        }

        .ca-kicker {
            font-family: var(--ca-font-display);
            font-weight: 800;
            font-size: 0.75rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--ca-volt);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .ca-kicker.red { color: var(--ca-red); }
        .ca-kicker.orange { color: var(--ca-orange); }

        .ca-title-hero {
            font-family: var(--ca-font-title);
            font-weight: 700;
            font-size: 2.2rem;
            line-height: 1.1;
            color: #fff;
            margin: 8px 0 12px;
        }
        .ca-title-card {
            font-family: var(--ca-font-title);
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1.2;
            color: #fff;
            margin: 6px 0 10px;
        }

        .ca-sec-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid var(--ca-border);
            padding-bottom: 8px;
            margin-bottom: 18px;
        }
        .ca-sec-title {
            font-family: var(--ca-font-display);
            font-weight: 900;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ca-sec-title::before {
            content: '';
            display: inline-block;
            width: 5px;
            height: 22px;
            background: var(--ca-volt);
            border-radius: 2px;
        }

        /* ══════════════════════════════════════════════════════
           SPORTS FOOTER
        ══════════════════════════════════════════════════════ */
        .ca-footer {
            background: #05080E;
            border-top: 3px solid var(--ca-volt);
            padding: 40px 0 20px;
            margin-top: 60px;
            color: var(--ca-text-muted);
            font-size: 0.85rem;
        }

        /* Responsiveness */
        @media (max-width: 768px) {
            .ca-brand-title { font-size: 1.6rem; }
            .ca-logo-img { height: 40px; }
            .ca-title-hero { font-size: 1.5rem; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- 1. LIVE SCORES TICKER -->
    <div class="ca-live-ticker-wrap">
        <div class="ca-ticker-container">
            <div style="font-family:var(--ca-font-display); font-weight:900; color:var(--ca-volt); font-size:0.8rem; letter-spacing:1px; text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                <i class="fas fa-bolt"></i> MINUTO A MINUTO:
            </div>
            @foreach($partidosVivo ?? [] as $partido)
                <div class="ca-match-chip">
                    @if($partido['estado'] === 'EN VIVO')
                        <span class="ca-badge-live"><i class="fas fa-circle" style="font-size:6px;"></i> VIVO {{ $partido['minuto'] }}</span>
                    @elseif($partido['estado'] === 'FINAL')
                        <span class="ca-badge-ft">FINAL</span>
                    @else
                        <span style="color:var(--ca-gold); font-weight:700; font-size:0.68rem;">{{ $partido['estado'] }}</span>
                    @endif

                    <strong style="color:#fff;">{{ $partido['local_code'] }}</strong>
                    <span style="font-weight:900; color:var(--ca-volt);">{{ $partido['goles_local'] }} - {{ $partido['goles_visitante'] }}</span>
                    <strong style="color:#fff;">{{ $partido['visitante_code'] }}</strong>
                    <span style="color:#64748B; font-size:0.7rem;">({{ $partido['estadio'] }})</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2. MAIN SPORTS HEADER -->
    <header class="ca-header">
        <div class="container">
            <div class="ca-header-top">
                <!-- Return link to Latitud 18 -->
                <a href="{{ route('portada') }}" class="ca-back-btn" title="Volver al portal principal de noticias">
                    <i class="fas fa-arrow-left"></i> Volver a Latitud 18
                </a>

                <!-- Brand Logo & Name -->
                <a href="{{ route('contraataque.index') }}" class="ca-brand-logo-wrap text-decoration-none">
                    <img src="{{ asset('images/contraataque-logo.jpg') }}" alt="Contra Ataque Deportes" class="ca-logo-img">
                    <div class="ca-brand-title">
                        <div>CONTRA <span class="volt">ATAQUE</span></div>
                        <span class="ca-brand-motto">PASIÓN • FÚTBOL • POLIDEPORTIVO</span>
                    </div>
                </a>

                <!-- Social & Search -->
                <div class="d-none d-md-flex align-items-center gap-3">
                    <div class="d-flex gap-2">
                        <a href="https://facebook.com" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:50%; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; color:#fff;"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://youtube.com" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:50%; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; color:#fff;"><i class="fab fa-youtube"></i></a>
                        <a href="https://tiktok.com" target="_blank" class="btn btn-sm btn-outline-secondary" style="border-radius:50%; width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; color:#fff;"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <!-- Sports Sub-Navigation -->
            <nav class="ca-nav-bar">
                <a href="{{ route('contraataque.index') }}" class="ca-nav-item {{ request()->routeIs('contraataque.index') ? 'active' : '' }}">
                    <i class="fas fa-home me-1"></i> Portada
                </a>
                <a href="{{ route('contraataque.seccion', 'futbol-boliviano') }}" class="ca-nav-item {{ request()->is('*futbol-boliviano*') ? 'active' : '' }}">
                    ⚽ Fútbol Boliviano
                </a>
                <a href="{{ route('contraataque.seccion', 'la-verde') }}" class="ca-nav-item {{ request()->is('*la-verde*') ? 'active' : '' }}">
                    🇧🇴 Selección La Verde
                </a>
                <a href="{{ route('contraataque.seccion', 'internacional') }}" class="ca-nav-item {{ request()->is('*internacional*') ? 'active' : '' }}">
                    🏆 Internacional
                </a>
                <a href="{{ route('contraataque.seccion', 'motores') }}" class="ca-nav-item {{ request()->is('*motores*') ? 'active' : '' }}">
                    🏁 Motores / Dakar
                </a>
                <a href="{{ route('contraataque.seccion', 'polideportivo') }}" class="ca-nav-item {{ request()->is('*polideportivo*') ? 'active' : '' }}">
                    🏀 Polideportivo
                </a>
                <a href="{{ route('contraataque.seccion', 'opinion') }}" class="ca-nav-item {{ request()->is('*opinion*') ? 'active' : '' }}">
                    ✍️ El Ojo de Contraataque
                </a>
            </nav>
        </div>
    </header>

    <!-- 3. MAIN CONTENT CONTAINER -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- 4. SPORTS FOOTER -->
    <footer class="ca-footer">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('images/contraataque-logo.jpg') }}" alt="Contra Ataque" style="height:48px; border-radius:6px;">
                        <div>
                            <h4 class="h5 mb-0 fw-bold text-white" style="font-family:var(--ca-font-display);">CONTRA <span style="color:var(--ca-volt);">ATAQUE</span></h4>
                            <small class="text-muted">El portal deportivo líder de Latitud 18</small>
                        </div>
                    </div>
                    <p style="font-size:0.82rem; line-height:1.5;">
                        Toda la cobertura en tiempo real de la División Profesional del Fútbol Boliviano, la Selección Nacional La Verde, copas continentales, automovilismo y el deporte mundial con el sello periodístico de Latitud 18.
                    </p>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="text-white fw-bold mb-3" style="font-family:var(--ca-font-display); letter-spacing:1px;">SECCIONES</h5>
                    <ul class="list-unstyled" style="font-size:0.82rem;">
                        <li class="mb-2"><a href="{{ route('contraataque.seccion', 'futbol-boliviano') }}">Fútbol Boliviano</a></li>
                        <li class="mb-2"><a href="{{ route('contraataque.seccion', 'la-verde') }}">Selección La Verde</a></li>
                        <li class="mb-2"><a href="{{ route('contraataque.seccion', 'internacional') }}">Champions & Conmebol</a></li>
                        <li class="mb-2"><a href="{{ route('contraataque.seccion', 'motores') }}">Motores & Rally</a></li>
                        <li class="mb-2"><a href="{{ route('contraataque.seccion', 'polideportivo') }}">Polideportivo</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h5 class="text-white fw-bold mb-3" style="font-family:var(--ca-font-display); letter-spacing:1px;">EQUIPOS DESTACADOS</h5>
                    <ul class="list-unstyled" style="font-size:0.82rem;">
                        <li class="mb-2"><a href="#">Oriente Petrolero</a></li>
                        <li class="mb-2"><a href="#">Blooming</a></li>
                        <li class="mb-2"><a href="#">The Strongest</a></li>
                        <li class="mb-2"><a href="#">Bolívar</a></li>
                        <li class="mb-2"><a href="#">Always Ready / Wilstermann</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5 class="text-white fw-bold mb-3" style="font-family:var(--ca-font-display); letter-spacing:1px;">RED DE LATITUD 18</h5>
                    <p style="font-size:0.82rem;">Visita también nuestras ediciones especiales:</p>
                    <a href="{{ route('portada') }}" class="btn btn-sm btn-outline-light w-100 mb-2 text-start">
                        <i class="fas fa-globe me-2 text-danger"></i> Portal General Latitud 18
                    </a>
                    <a href="{{ route('periodico.public.index') }}" class="btn btn-sm btn-outline-light w-100 text-start">
                        <i class="fas fa-newspaper me-2 text-warning"></i> Periódico Semanal Digital
                    </a>
                </div>
            </div>

            <div class="border-top border-secondary pt-3 text-center text-md-between d-md-flex justify-content-between align-items-center" style="font-size:0.75rem;">
                <div>© {{ date('Y') }} <strong>CONTRA ATAQUE</strong> — Marca Deportiva Oficial de Latitud 18 / UHTV. Todos los derechos reservados. Santa Cruz, Bolivia.</div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ route('portada') }}" class="text-muted me-3">Inicio</a>
                    <a href="{{ route('opinion.index') }}" class="text-muted me-3">Opinión</a>
                    <a href="{{ route('periodico.public.index') }}" class="text-muted">Edición Impresa</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
