@extends('layouts.main')

@section('title', ($edicionActiva['titulo'] ?? 'La Estrella') . ' - Periódico Digital Semanal')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@500;700;800;900&display=swap" rel="stylesheet">

<style>
:root {
    --reader-red: #D71920;
    --reader-navy: #0B1F3A;
    --reader-bg: #1A202C;
}

.newspaper-reader-container {
    background: #2D3748;
    background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 16px 16px;
    border-radius: 8px;
    overflow: hidden;
    margin-top: -8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.reader-control-header {
    background: #1A202C;
    color: #FFF;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 2px solid var(--reader-red);
}

.reader-edition-badge {
    background: var(--reader-red);
    color: #FFF;
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 3px;
    text-transform: uppercase;
}

.reader-pages-ribbon {
    background: #171923;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.reader-page-btn {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: #CBD5E0;
    padding: 6px 14px;
    border-radius: 4px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.reader-page-btn:hover {
    background: rgba(255,255,255,0.15);
    color: #FFF;
}

.reader-page-btn.active {
    background: var(--reader-red);
    color: #FFF;
    border-color: var(--reader-red);
    box-shadow: 0 2px 10px rgba(215,25,32,0.4);
}

.reader-stage-viewport {
    padding: 30px 16px 60px;
    display: flex;
    justify-content: center;
    overflow-y: auto;
    min-height: 80vh;
}

/* REALISTIC PAPER SHEET */
.reader-paper-sheet {
    width: 820px;
    min-height: 1160px;
    background: #FFFFFF;
    color: #111827;
    box-shadow: 0 12px 40px rgba(0,0,0,0.4);
    padding: 28px 32px 36px;
    display: none;
    flex-direction: column;
    position: relative;
    font-family: 'Source Sans 3', sans-serif;
    animation: fadeIn 0.25s ease;
}

/* When using new frames-based layout, remove padding so frames position absolutely */
.reader-paper-sheet.frames-mode {
    padding: 0;
    width: 794px;
    min-height: 1123px;
    overflow: hidden;
}

.reader-paper-sheet.active {
    display: flex;
}

/* Responsive viewport handling for smaller screens */
@media (max-width: 900px) {
    .reader-stage-viewport {
        padding: 12px 6px 30px;
        overflow-x: hidden;
    }
    .reader-paper-sheet, .reader-paper-sheet.frames-mode {
        transform-origin: top center;
        transition: transform 0.2s ease;
        margin-left: auto;
        margin-right: auto;
    }
}



/* NEWSPAPER STYLES */
.reader-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 8px;
    border-bottom: 3px solid #000;
    margin-bottom: 10px;
}

.reader-masthead-title {
    font-family: 'Anton', Impact, sans-serif;
    font-size: 3.4rem;
    line-height: 0.95;
    color: #0B1F3A;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.reader-masthead-sub {
    background: var(--reader-red);
    color: #FFF;
    font-family: 'Source Sans 3', sans-serif;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 2px 10px;
    border-radius: 2px;
    margin-left: 6px;
}

.reader-rate-box {
    background: #F8FAFC;
    border: 2px solid #0B1F3A;
    padding: 6px 12px;
    text-align: right;
    min-width: 170px;
}

.reader-meta-ribbon {
    background: #000;
    color: #FFF;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.62rem;
    font-weight: 700;
    padding: 4px 10px;
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.reader-slogan {
    background: #F9FAFB;
    border-left: 4px solid var(--reader-red);
    padding: 4px 10px;
    font-size: 0.68rem;
    font-weight: 600;
    color: #4B5563;
    margin-bottom: 14px;
}

.reader-headline {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 900;
    line-height: 1.05;
    color: #000;
    margin-bottom: 10px;
}

.reader-4-cols {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    font-size: 0.76rem;
    line-height: 1.45;
    text-align: justify;
    border-bottom: 2px solid #000;
    padding-bottom: 14px;
    margin-bottom: 16px;
}

.reader-4-cols strong {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.72rem;
    color: #000;
}

.reader-center-grid {
    display: grid;
    grid-template-columns: 2.2fr 1fr;
    gap: 16px;
    margin-bottom: 14px;
}

.reader-hero-card {
    border: 3px solid var(--reader-red);
    position: relative;
    background: #000;
}

.reader-hero-wrap {
    height: 310px;
    position: relative;
    overflow: hidden;
}

.reader-hero-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.reader-hero-overlay {
    position: absolute;
    top: 14px;
    left: 14px;
    right: 14px;
    background: rgba(0, 0, 0, 0.85);
    color: #FFF;
    padding: 10px 14px;
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 900;
    line-height: 1.15;
    border-left: 4px solid var(--reader-red);
}

.reader-hero-caption {
    background: #FFF;
    padding: 10px 14px;
    font-size: 0.75rem;
    line-height: 1.4;
    color: #1F2937;
}

.reader-side-item {
    border: 1px solid #E5E7EB;
    background: #FFF;
    padding: 8px;
    margin-bottom: 12px;
}

.reader-side-item:last-child { margin-bottom: 0; }

.reader-side-tag {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 0.58rem;
    color: var(--reader-red);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.reader-side-img {
    height: 90px;
    margin-bottom: 6px;
    overflow: hidden;
}

.reader-side-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.reader-side-title {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 0.8rem;
    line-height: 1.2;
    color: #000;
    margin-bottom: 4px;
}

.reader-breaking-bar {
    background: var(--reader-red);
    color: #FFF;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: auto;
}

.reader-page-badge {
    background: #000;
    color: #FFF;
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 0.58rem;
    padding: 1px 5px;
    border-radius: 2px;
    display: inline-block;
}

.reader-inner-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 6px;
    border-bottom: 2px solid #000;
    margin-bottom: 14px;
}

.reader-inner-tag {
    font-family: 'Anton', Impact, sans-serif;
    font-size: 1.6rem;
    color: #0B1F3A;
    background: #F3F4F6;
    padding: 2px 14px;
    border-left: 5px solid var(--reader-red);
}

.reader-pull-quote {
    border-top: 2px solid var(--reader-red);
    border-bottom: 2px solid var(--reader-red);
    padding: 10px 14px;
    margin: 12px 0;
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 0.88rem;
    line-height: 1.35;
    color: #0B1F3A;
    background: #FFF5F5;
}

.reader-dropcap::first-letter {
    font-family: 'Anton', Impact, sans-serif;
    font-size: 2.8rem;
    float: left;
    line-height: 0.8;
    margin-right: 6px;
    color: #0B1F3A;
}

.reader-staff-footer {
    border-top: 2px solid #000;
    padding-top: 10px;
    margin-top: auto;
    font-size: 0.6rem;
    line-height: 1.4;
    color: #4B5563;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    background: #FAFAFA;
    padding: 8px 12px;
}

/* ====================================================
   TOOLBAR EDITORIAL (Controles: Zoom, Doble Página, Fullscreen)
   ==================================================== */
.reader-toolbar-strip {
    background: #111827;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    padding: 8px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.reader-tool-group {
    display: flex;
    align-items: center;
    gap: 6px;
}
.reader-tool-label {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.6rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
    margin-right: 4px;
}
.reader-tool-btn {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: #CBD5E0;
    padding: 5px 10px;
    border-radius: 4px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.72rem;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.reader-tool-btn:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border-color: rgba(255,255,255,0.3);
}
.reader-tool-btn.active {
    background: var(--reader-red);
    color: #fff;
    border-color: var(--reader-red);
}
.reader-tool-btn.zoom-val {
    background: transparent;
    border-color: transparent;
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 800;
    min-width: 46px;
    text-align: center;
    cursor: default;
    pointer-events: none;
}
/* MODO DOBLE PÁGINA */
.reader-stage-viewport.double-page-mode {
    padding: 24px 8px 60px;
}
.reader-stage-viewport.double-page-mode .reader-paper-sheet.active {
    flex-direction: row;
    gap: 2px;
    width: auto;
    max-width: 1640px;
}
/* ZOOM */
.reader-stage-viewport .reader-zoom-wrapper {
    transform-origin: top center;
    transition: transform 0.2s ease;
}
/* FULLSCREEN */
.newspaper-reader-container:-webkit-full-screen { width: 100vw !important; height: 100vh !important; border-radius: 0; overflow-y: auto; }
.newspaper-reader-container:-moz-full-screen { width: 100vw !important; height: 100vh !important; border-radius: 0; overflow-y: auto; }
.newspaper-reader-container:fullscreen { width: 100vw !important; height: 100vh !important; border-radius: 0; overflow-y: auto; }

/* ====================================================
   FLIPBOOK 3D PURO (sin dependencias externas)
   ==================================================== */
.l18-flipbook-wrap {
    background: radial-gradient(ellipse at center, #1e2a3a 0%, #0d1117 100%);
    min-height: 640px;
    padding: 32px 16px 40px;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 24px;
}
.l18-flipbook-wrap.active { display: flex; }

/* STAGE DEL LIBRO */
.l18-book-stage {
    perspective: 2400px;
    perspective-origin: 50% 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* EL LIBRO ABIERTO */
.l18-book-open {
    position: relative;
    display: flex;
    box-shadow:
        -20px 20px 60px rgba(0,0,0,0.8),
        20px 20px 60px rgba(0,0,0,0.8),
        0 4px 20px rgba(0,0,0,0.5);
    border-radius: 2px;
}

/* LOMO DEL LIBRO */
.l18-book-spine {
    width: 20px;
    flex-shrink: 0;
    background: linear-gradient(to right, #0a0f18 0%, #1e3050 30%, #2a4070 50%, #1e3050 70%, #0a0f18 100%);
    box-shadow: inset 2px 0 6px rgba(255,255,255,0.06), inset -2px 0 6px rgba(255,255,255,0.06);
    z-index: 10;
    align-self: stretch;
    border-left: 1px solid rgba(255,255,255,0.08);
    border-right: 1px solid rgba(255,255,255,0.08);
}

/* PÁGINA IZQUIERDA */
.l18-left-page, .l18-right-page {
    width: var(--fb-page-w, 380px);
    height: var(--fb-page-h, 540px);
    overflow: hidden;
    position: relative;
    background: #fff;
}
.l18-left-page {
    border-right: 1px solid rgba(0,0,0,0.12);
    box-shadow: inset -10px 0 30px rgba(0,0,0,0.12);
}
.l18-right-page {
    border-left: 1px solid rgba(0,0,0,0.06);
    box-shadow: inset 10px 0 30px rgba(0,0,0,0.06);
}

/* PÁGINA QUE SE DOBLA (animación 3D) */
.l18-flip-page {
    position: absolute;
    top: 0;
    width: var(--fb-page-w, 380px);
    height: var(--fb-page-h, 540px);
    transform-style: preserve-3d;
    transform-origin: left center;
    transition: transform 0.7s cubic-bezier(0.645, 0.045, 0.355, 1);
    z-index: 20;
    pointer-events: none;
}
.l18-flip-page.is-flipping { transform: rotateY(-180deg); }
.l18-flip-page.flipped { transform: rotateY(-180deg); }

.l18-flip-front, .l18-flip-back {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    overflow: hidden;
}
.l18-flip-front {
    background: #fff;
    box-shadow: -5px 0 20px rgba(0,0,0,0.25);
}
.l18-flip-back {
    background: #fff;
    transform: rotateY(180deg);
    box-shadow: 5px 0 20px rgba(0,0,0,0.15);
    border-left: 1px solid rgba(0,0,0,0.06);
}

/* Sombra de curvatura del papel durante el giro */
.l18-flip-front::after {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0;
    width: 40px;
    background: linear-gradient(to left, rgba(0,0,0,0.18) 0%, transparent 100%);
    pointer-events: none;
}
.l18-flip-back::before {
    content: '';
    position: absolute;
    top: 0; left: 0; bottom: 0;
    width: 40px;
    background: linear-gradient(to right, rgba(0,0,0,0.1) 0%, transparent 100%);
    pointer-events: none;
}

/* Contenido escalado dentro de cada hoja */
.l18-page-content {
    width: 794px;
    height: 1123px;
    transform-origin: top left;
    transform: scale(var(--fb-scale, 0.478));
    overflow: hidden;
    pointer-events: none;
    user-select: none;
}

/* CONTROLES INFERIORES */
.l18-flipbook-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 40px;
    padding: 8px 20px;
    backdrop-filter: blur(10px);
}
.l18-fb-btn {
    width: 38px; height: 38px;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.08);
    color: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
    flex-shrink: 0;
}
.l18-fb-btn:hover:not(:disabled) {
    background: var(--reader-red);
    border-color: var(--reader-red);
    color: #fff;
    transform: scale(1.08);
}
.l18-fb-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.l18-fb-page-indicator {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.78rem;
    color: #e2e8f0;
    min-width: 90px;
    text-align: center;
    letter-spacing: 0.5px;
}
.l18-fb-thumbnails {
    display: flex;
    gap: 6px;
    align-items: center;
}
.l18-fb-thumb {
    width: 26px; height: 36px;
    border-radius: 2px;
    background: rgba(255,255,255,0.12);
    border: 1.5px solid rgba(255,255,255,0.15);
    cursor: pointer;
    transition: all 0.15s;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.5rem;
    font-weight: 800;
    color: rgba(255,255,255,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}
.l18-fb-thumb:hover { border-color: var(--reader-red); color: #fff; }
.l18-fb-thumb.active {
    background: var(--reader-red);
    border-color: var(--reader-red);
    color: #fff;
    transform: scaleY(1.1);
}
</style>


<div class="container py-3">
    <!-- CONTENEDOR PRINCIPAL DEL LECTOR -->
    <div class="newspaper-reader-container">
        {{-- HEADER DE CONTROLES --}}
        <div class="reader-control-header">
            <div class="d-flex align-items-center gap-3">
                <span class="reader-edition-badge">EDICIÓN SEMANAL DIGITAL</span>
                <div>
                    <h1 class="h6 mb-0 fw-bold text-white" style="font-family: 'Anton', sans-serif; letter-spacing: 1px;">
                        {{ $edicionActiva['titulo'] ?? 'Latitud 18' }} <span style="color:var(--reader-red);">{{ $edicionActiva['subtitulo'] ?? 'Información Sin Ruido' }}</span>
                    </h1>
                    <small class="text-muted" style="font-size: 0.72rem;">{{ $edicionActiva['numero_edicion'] }} • {{ $edicionActiva['fecha'] }} • {{ count($edicionActiva['paginas'] ?? []) }} páginas</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Selector de edición si hay más de una -->
                @if(count($ediciones) > 1)
                    <div class="dropdown me-2">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-archive me-1"></i> Otras Ediciones
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            @foreach($ediciones as $ed)
                                <li>
                                    <a class="dropdown-item {{ $ed['id'] === $edicionActiva['id'] ? 'active' : '' }}" href="{{ route('periodico.public.show', $ed['id']) }}">
                                        {{ $ed['numero_edicion'] }} ({{ $ed['fecha'] }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button class="btn btn-sm btn-outline-light" onclick="prevPage()" id="btnPrevPage" title="Página Anterior">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="text-white small fw-bold px-2" id="pageIndicator">Pág 1 / {{ count($edicionActiva['paginas'] ?? []) }}</span>
                <button class="btn btn-sm btn-outline-light" onclick="nextPage()" id="btnNextPage" title="Página Siguiente">
                    <i class="fas fa-chevron-right"></i>
                </button>

                @if(!empty($edicionActiva['pdf_url']))
                    <a href="{{ $edicionActiva['pdf_url'] }}" target="_blank" class="btn btn-sm btn-danger ms-2" title="Descargar PDF Oficial">
                        <i class="fas fa-download me-1"></i> Descargar PDF
                    </a>
                @else
                    <a href="{{ route('periodico.public.pdf', $edicionActiva['id']) }}" target="_blank" class="btn btn-sm btn-danger ms-2" title="Descargar PDF / Imprimir">
                        <i class="fas fa-file-pdf me-1"></i> Descargar / Imprimir
                    </a>
                @endif
            </div>
        </div>

        {{-- TABS: Flipbook 3D vs Lector Editorial --}}
        <div style="display:flex; background:#0d1117; border-bottom:1px solid rgba(255,255,255,0.08);">
          <button style="flex:1;padding:11px 16px;font-family:'Montserrat',sans-serif;font-weight:700;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:#fff;border:none;background:rgba(215,25,32,0.08);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;border-bottom:2px solid var(--reader-red);transition:all 0.2s;" id="tabFlipbook" onclick="switchReaderMode('flipbook')">
            <i class="fas fa-book"></i> Flipbook 3D
            <span style="background:var(--reader-red);color:#fff;font-size:0.5rem;padding:1px 5px;border-radius:2px;font-weight:800;letter-spacing:0.5px;">3D</span>
          </button>
          <button style="flex:1;padding:11px 16px;font-family:'Montserrat',sans-serif;font-weight:700;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;border-bottom:2px solid transparent;transition:all 0.2s;" id="tabEditorial" onclick="switchReaderMode('editorial')">
            <i class="fas fa-newspaper"></i> Lector Editorial
          </button>
        </div>

        {{-- FLIPBOOK 3D PURO: Visor con animación CSS de libro real --}}
        <div class="l18-flipbook-wrap active" id="flipbookSection"
             style="--fb-page-w:380px; --fb-page-h:540px; --fb-scale:0.478;">

          {{-- Título editorial del libro --}}
          <div style="text-align:center; margin-bottom:4px;">
            <div style="font-family:'Anton',sans-serif; font-size:1rem; color:#fff; letter-spacing:2px; line-height:1;">
              {{ strtoupper($edicionActiva['titulo'] ?? 'LATITUD 18') }}
              <span style="background:var(--reader-red);color:#fff;font-family:'Montserrat',sans-serif;font-size:0.55rem;font-weight:800;padding:2px 6px;border-radius:2px;vertical-align:middle;margin-left:4px;">{{ $edicionActiva['subtitulo'] ?? 'Información Sin Ruido' }}</span>
            </div>
            <div style="font-family:'Montserrat',sans-serif;font-size:0.62rem;color:#64748b;font-weight:600;margin-top:2px;">
              {{ $edicionActiva['numero_edicion'] ?? '' }} · {{ $edicionActiva['fecha'] ?? '' }}
            </div>
          </div>

          {{-- EL LIBRO 3D --}}
          <div class="l18-book-stage">
            <div class="l18-book-open" id="l18Book">
              {{-- Página izquierda (par o portada izquierda) --}}
              <div class="l18-left-page" id="l18LeftPage">
                <div class="l18-page-content" id="l18LeftContent"></div>
              </div>
              {{-- Lomo central --}}
              <div class="l18-book-spine"></div>
              {{-- Página derecha --}}
              <div class="l18-right-page" id="l18RightPage">
                <div class="l18-page-content" id="l18RightContent"></div>
              </div>
              {{-- Hoja que se dobla (se construye por JS) --}}
              <div class="l18-flip-page" id="l18FlipPage" style="right:0; left:auto; transform-origin:left center;">
                <div class="l18-flip-front" id="l18FlipFront">
                  <div class="l18-page-content" id="l18FlipFrontContent"></div>
                </div>
                <div class="l18-flip-back" id="l18FlipBack">
                  <div class="l18-page-content" id="l18FlipBackContent"></div>
                </div>
              </div>
            </div>
          </div>

          {{-- CONTROLES INFERIORES --}}
          <div class="l18-flipbook-controls">
            <button class="l18-fb-btn" id="l18BtnFirst" onclick="fbGoToSpread(0)" title="Primera página">
              <i class="fas fa-step-backward"></i>
            </button>
            <button class="l18-fb-btn" id="l18BtnPrev" onclick="fbPrevSpread()" title="Página anterior">
              <i class="fas fa-chevron-left"></i>
            </button>
            <div class="l18-fb-thumbnails" id="l18Thumbs">
              @foreach($edicionActiva['paginas'] as $fi => $fp)
                <div class="l18-fb-thumb {{ $fi === 0 ? 'active' : '' }}" onclick="fbGoToSpread({{ $fi }})" title="Pág {{ $fp['numero'] ?? ($fi+1) }}">
                  {{ $fp['numero'] ?? ($fi+1) }}
                </div>
              @endforeach
            </div>
            <span class="l18-fb-page-indicator" id="l18FbIndicator">Pág 1–2 / {{ count($edicionActiva['paginas'] ?? []) }}</span>
            <button class="l18-fb-btn" id="l18BtnNext" onclick="fbNextSpread()" title="Página siguiente">
              <i class="fas fa-chevron-right"></i>
            </button>
            <button class="l18-fb-btn" id="l18BtnLast" onclick="fbGoToSpread({{ count($edicionActiva['paginas'] ?? []) - 1 }})" title="Última página">
              <i class="fas fa-step-forward"></i>
            </button>
            <button class="l18-fb-btn" onclick="readerToggleFullscreen()" title="Pantalla completa (F)">
              <i class="fas fa-expand" id="fbFsIcon"></i>
            </button>
          </div>

          {{-- Instrucción de navegación --}}
          <div style="font-family:'Montserrat',sans-serif;font-size:0.6rem;color:#475569;text-align:center;margin-top:-8px;">
            <i class="fas fa-hand-point-right"></i> Usa las flechas, swipe o teclas ← → para pasar páginas
          </div>
        </div>

        {{-- PÁGINAS OCULTAS (fuente de contenido para el flipbook JS) --}}
        <div id="l18PagesSource" style="display:none; visibility:hidden; position:absolute; pointer-events:none; width:0; height:0; overflow:hidden;">
          @foreach($edicionActiva['paginas'] as $pi => $p)
            <div id="l18-src-page-{{ $pi }}">
              @if(!empty($p['frames']) && count($p['frames']) > 0)
                <div style="position:relative; width:794px; min-height:1123px; background:#fff; overflow:hidden;">
                  @foreach($p['frames'] as $frame)
                    @php
                      $fx = $frame['x'] ?? 0; $fy = $frame['y'] ?? 0;
                      $fw = $frame['w'] ?? 200; $fh = $frame['h'] ?? 100;
                      $fz = $frame['z'] ?? 10; $fop = $frame['opacity'] ?? 1;
                      $ftype = $frame['type'] ?? 'text';
                    @endphp
                    <div style="position:absolute;left:{{$fx}}px;top:{{$fy}}px;width:{{$fw}}px;height:{{$fh}}px;z-index:{{$fz}};opacity:{{$fop}};overflow:hidden;box-sizing:border-box;">
                      @if($ftype === 'image' && !empty($frame['src']))
                        <img src="{{ $frame['src'] }}" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
                      @elseif($ftype === 'text' || $ftype === 'article')
                        <div style="font-family:'Source Sans 3',sans-serif;font-size:{{ $frame['styles']['fontSize'] ?? '11px' }};line-height:1.45;color:#111;padding:4px;overflow:hidden;height:100%;">
                          @if(!empty($frame['headline']))<strong style="font-family:'Playfair Display',serif;font-size:16px;display:block;margin-bottom:4px;">{{ $frame['headline'] }}</strong>@endif
                          {!! nl2br(e($frame['content'] ?? '')) !!}
                        </div>
                      @elseif($ftype === 'masthead')
                        <div style="background:#D71920;color:#fff;text-align:center;padding:6px;height:100%;display:flex;flex-direction:column;justify-content:center;">
                          <div style="font-family:'Anton',sans-serif;font-size:36px;letter-spacing:1px;line-height:1;">{{ $frame['newspaperName'] ?? 'LATITUD 18' }}</div>
                          <div style="font-size:10px;font-weight:700;opacity:0.9;">{{ $frame['subBadge'] ?? 'Información Sin Ruido' }}</div>
                        </div>
                      @endif
                    </div>
                  @endforeach
                </div>
              @else
                <div style="width:794px;min-height:1123px;background:#fff;padding:24px 32px;display:flex;flex-direction:column;font-family:'Source Sans 3',sans-serif;">
                  <div style="border-bottom:3px solid #D71920;padding-bottom:8px;margin-bottom:12px;font-family:'Anton',sans-serif;font-size:28px;color:#0B1F3A;letter-spacing:1px;">
                    {{ $p['nombre'] ?? 'Página ' . ($pi+1) }}
                  </div>
                  @if(!empty($p['titular_principal']['titulo']))
                    <h2 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:900;color:#000;margin-bottom:8px;">{{ $p['titular_principal']['titulo'] }}</h2>
                  @endif
                </div>
              @endif
            </div>
          @endforeach
        </div>


        {{-- LECTOR EDITORIAL: visor HTML de páginas (modo alternativo) --}}
        <div id="editorialSection" style="display:none;">

        {{-- RIBBON DE MINIATURAS / PÁGINAS (solo en modo editorial) --}}
        <div class="reader-pages-ribbon">
            @foreach($edicionActiva['paginas'] as $idx => $pag)
                <button class="reader-page-btn {{ $idx === 0 ? 'active' : '' }}" onclick="goToPage({{ $idx }})" id="reader-tab-{{ $idx }}">
                    Pág {{ $pag['numero'] ?? ($idx + 1) }}: {{ $pag['nombre'] ?? 'Página ' . ($idx + 1) }}
                </button>
            @endforeach
        </div>

        {{-- TOOLBAR EDITORIAL: ZOOM + DOBLE PÁGINA + PANTALLA COMPLETA --}}
        <div class="reader-toolbar-strip" id="readerToolbar">
          <div class="reader-tool-group">
            <span class="reader-tool-label"><i class="fas fa-search-plus"></i> Zoom:</span>
            <button class="reader-tool-btn" onclick="readerZoomOut()" title="Reducir">
              <i class="fas fa-minus"></i>
            </button>
            <span class="reader-tool-btn zoom-val" id="readerZoomLabel">100%</span>
            <button class="reader-tool-btn" onclick="readerZoomIn()" title="Ampliar">
              <i class="fas fa-plus"></i>
            </button>
            <button class="reader-tool-btn" onclick="readerZoomReset()" title="Tamaño real">
              <i class="fas fa-redo-alt"></i> Reset
            </button>
          </div>
          <div class="reader-tool-group">
            <span class="reader-tool-label"><i class="fas fa-columns"></i> Vista:</span>
            <button class="reader-tool-btn active" id="btnSinglePage" onclick="readerSetSinglePage()" title="Vista de página individual">
              <i class="fas fa-file-alt"></i> Página Simple
            </button>
            <button class="reader-tool-btn" id="btnDoublePage" onclick="readerSetDoublePage()" title="Vista de doble página">
              <i class="fas fa-book-open"></i> Doble Página
            </button>
          </div>
          <div class="reader-tool-group">
            <button class="reader-tool-btn" onclick="readerToggleFullscreen()" id="btnReaderFullscreen" title="Pantalla completa (F)">
              <i class="fas fa-expand" id="readerFsIcon"></i> Pantalla Completa
            </button>
          </div>
        </div>

        {{-- STAGE HTML --}}
        <div class="reader-stage-viewport" id="readerViewport">
            @foreach($edicionActiva['paginas'] as $pIndex => $p)
                <div class="reader-paper-sheet {{ $pIndex === 0 ? 'active' : '' }} {{ !empty($p['frames']) && count($p['frames']) > 0 ? 'frames-mode' : '' }}" id="reader-sheet-{{ $pIndex }}">

                    {{-- ▶ NEW: InDesign-style frames rendering --}}
                    @if(!empty($p['frames']) && count($p['frames']) > 0)
                        <div style="position:relative; width:100%; min-height:1040px; background:#fff; overflow:hidden; font-family:'Source Sans 3',sans-serif;">
                            @foreach($p['frames'] as $frame)
                                @php
                                    $ftype  = $frame['type']    ?? 'text';
                                    $fx     = $frame['x']       ?? 0;
                                    $fy     = $frame['y']       ?? 0;
                                    $fw     = $frame['w']       ?? 200;
                                    $fh     = $frame['h']       ?? 100;
                                    $fz     = $frame['z']       ?? 10;
                                    $fop    = $frame['opacity'] ?? 1;
                                    $styles = $frame['styles']  ?? [];
                                @endphp

                                <div style="position:absolute;
                                    left:{{ $fx }}px; top:{{ $fy }}px;
                                    width:{{ $fw }}px; height:{{ $fh }}px;
                                    z-index:{{ $fz }};
                                    opacity:{{ $fop }}; box-sizing:border-box;">

                                    @if($ftype === 'masthead')
                                        <div style="border-bottom:3px solid #0284c7; padding-bottom:4px; font-family:'Inter', sans-serif;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                                <div style="background:#fef9c3; color:#854d0e; padding:4px 8px; border-radius:2px; font-size:9px; font-weight:700; width:140px; line-height:1.2;">
                                                    {!! nl2br(e($frame['leftEar'] ?? 'CRE 100%')) !!}
                                                </div>
                                                <div style="text-align:center; flex:1;">
                                                    <span style="font-family:'Anton', sans-serif; font-size:46px; color:#0284c7; line-height:1; letter-spacing:1px;">{{ $frame['newspaperName'] ?? 'LATITUD 18' }}</span>
                                                    <span style="background:#D71920; color:#fff; font-family:'Anton', sans-serif; font-size:18px; padding:2px 8px; border-radius:2px; margin-left:4px; vertical-align:middle;">{{ $frame['subBadge'] ?? 'Información Sin Ruido' }}</span>
                                                    <div style="font-size:9px; font-weight:800; letter-spacing:1.5px; color:#64748b; text-transform:uppercase; margin-top:2px;">{{ $frame['motto'] ?? 'EL PERIÓDICO DIGITAL DE LATITUD 18' }}</div>
                                                </div>
                                                <div style="background:#0284c7; color:#fff; padding:4px 8px; border-radius:2px; font-size:9px; font-weight:800; width:130px; text-align:right; line-height:1.2;">
                                                    {!! nl2br(e($frame['rightEar'] ?? 'DÓLAR: Bs 12,58')) !!}
                                                </div>
                                            </div>
                                            <div style="display:flex; justify-content:space-between; border-top:1px solid #e2e8f0; padding-top:3px; margin-top:4px; font-size:9px; color:#64748b; font-weight:600;">
                                                <span>{{ $frame['editionDate'] ?? ($edicionActiva['fecha'] ?? 'Santa Cruz de la Sierra') }}</span>
                                                <span><strong>{{ $frame['editionNumber'] ?? ($edicionActiva['numero_edicion'] ?? 'N° 11.986') }}</strong></span>
                                                <span>{{ $frame['price'] ?? ($edicionActiva['precio'] ?? 'Bs 7,00') }}</span>
                                            </div>
                                        </div>

                                    @elseif($ftype === 'headline')
                                        <div style="width:100%; height:100%; display:flex; flex-direction:column; justify-content:center;">
                                            @if(!empty($frame['kicker']))
                                                <span style="font-size:11px; font-weight:800; color:#D71920; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px; font-family:'Inter', sans-serif;">{{ $frame['kicker'] }}</span>
                                            @endif
                                            <div style="padding:0; font-family:{{ $styles['fontFamily'] ?? "'Oswald', sans-serif" }}; font-size:{{ $styles['fontSize'] ?? 28 }}px; font-weight:{{ $styles['fontWeight'] ?? '700' }}; line-height:1.1; color:{{ $styles['color'] ?? '#0f172a' }}; text-align:{{ $styles['textAlign'] ?? 'left' }};">
                                                {!! $frame['content'] ?? 'Titular de Noticia' !!}
                                            </div>
                                        </div>

                                    @elseif($ftype === 'image')
                                        <div style="width:100%; height:100%; display:flex; flex-direction:column;">
                                            <div style="flex:1; position:relative; overflow:hidden; border-radius:{{ $frame['borderRadius'] ?? 0 }}px; {{ isset($frame['borderWidth']) && $frame['borderWidth'] > 0 ? 'border:'.$frame['borderWidth'].'px solid '.($frame['borderColor']??'#000').';' : '' }}">
                                                @if(!empty($frame['src']))
                                                    <img src="{{ $frame['src'] }}"
                                                         style="width:100%; height:100%; object-fit:{{ $frame['imgFit'] ?? 'cover' }}; display:block;"
                                                         alt="{{ $frame['caption'] ?? '' }}">
                                                @else
                                                    <div style="width:100%; height:100%; background:#F3F4F6; display:flex; align-items:center; justify-content:center; color:#9CA3AF; font-size:0.75rem;">
                                                        <i class="fas fa-image" style="font-size:1.5rem;"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            @if(!empty($frame['caption']))
                                                <div style="font-size:9.5px; color:#475569; line-height:1.3; padding-top:4px; font-style:italic;">
                                                    {{ $frame['caption'] }}
                                                </div>
                                            @endif
                                        </div>

                                    @elseif($ftype === 'quote')
                                        <div style="width:100%; height:100%; border-top:2px solid #D71920; border-bottom:2px solid #D71920; padding:10px 14px; background:#fff7ed; font-family:'Playfair Display', serif; font-style:italic; font-size:15px; font-weight:700; line-height:1.4; color:#1e293b; box-sizing:border-box;">
                                            {!! $frame['content'] ?? '"Cita destacada..."' !!}
                                        </div>

                                    @elseif($ftype === 'box')
                                        <div style="width:100%; height:100%; background:#f8fafc; border:1px solid #cbd5e1; border-radius:4px; padding:12px; font-size:12px; line-height:1.5; color:#334155; box-sizing:border-box;">
                                            {!! $frame['content'] ?? 'Caja de contenido' !!}
                                        </div>

                                    @elseif($ftype === 'divider' || $ftype === 'line')
                                        <div style="width:100%; height:100%; display:flex; align-items:center;">
                                            <div style="width:100%; height:{{ $frame['lineWidth'] ?? 2 }}px; background:{{ $frame['color'] ?? ($frame['lineColor'] ?? '#cbd5e1') }};"></div>
                                        </div>

                                    @elseif($ftype === 'rect' || $ftype === 'shape')
                                        <div style="width:100%; height:100%;
                                            background:{{ $frame['fillColor'] ?? 'transparent' }};
                                            border-radius:{{ $frame['borderRadius'] ?? 0 }}px;
                                            {{ isset($frame['borderWidth']) && $frame['borderWidth'] > 0 ? 'border:'.$frame['borderWidth'].'px solid '.($frame['borderColor']??'#000').';' : '' }}">
                                        </div>

                                    @else
                                        @php
                                            $cols = $frame['columns'] ?? ($styles['columns'] ?? 1);
                                        @endphp
                                        <div style="width:100%; height:100%; overflow:hidden;
                                            font-family:{{ $styles['fontFamily'] ?? "'Source Sans 3', sans-serif" }};
                                            font-size:{{ $styles['fontSize'] ?? 12 }}px;
                                            font-weight:{{ $styles['fontWeight'] ?? '400' }};
                                            line-height:{{ $styles['lineHeight'] ?? '1.5' }};
                                            color:{{ $styles['color'] ?? '#1e293b' }};
                                            text-align:{{ $styles['textAlign'] ?? 'justify' }};
                                            background-color:{{ $styles['backgroundColor'] ?? 'transparent' }};
                                            {{ $cols > 1 ? 'column-count:'.$cols.'; column-gap:14px;' : '' }}
                                            padding:4px; box-sizing:border-box;">
                                            {!! $frame['content'] ?? 'Texto periodístico...' !!}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    {{-- ▶ LEGACY: tipo-based template rendering --}}
                    @elseif(($p['tipo'] ?? '') === 'portada')
                        <!-- PÁGINA 1: PORTADA -->
                        <div class="reader-top-bar">
                            <div class="reader-masthead-title">
                                {{ $edicionActiva['titulo'] ?? 'La Estrella' }}
                                <span class="reader-masthead-sub">{{ $edicionActiva['subtitulo'] ?? 'del Oriente' }}</span>
                            </div>
                            <div class="reader-rate-box">
                                <div style="font-family:'Montserrat',sans-serif; font-size:0.6rem; font-weight:800; color:#0B1F3A;">TIPO DE CAMBIO DÓLAR EN BOLIVIA</div>
                                <div style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:#0B1F3A; line-height:1;">Bs {{ $p['dolar_venta'] ?? '12,58' }}</div>
                            </div>
                        </div>

                        <div class="reader-meta-ribbon">
                            <span>{{ $edicionActiva['ciudad'] ?? 'Santa Cruz de la Sierra' }}</span>
                            <span>•</span>
                            <span>{{ $edicionActiva['fecha'] ?? 'Domingo 6 de septiembre de 2026' }}</span>
                            <span>•</span>
                            <span>{{ $edicionActiva['numero_edicion'] ?? 'N° 11.986' }}</span>
                            <span>•</span>
                            <span>{{ count($edicionActiva['paginas']) }} páginas</span>
                            <span>•</span>
                            <span>Precio en todo el país: <strong>{{ $edicionActiva['precio'] ?? 'Bs 7,00' }}</strong></span>
                        </div>

                        <div class="reader-slogan">{{ $edicionActiva['slogan'] ?? 'El 100% de los hogares atendidos por CRE pagan la misma tarifa equitativa' }}</div>

                        <!-- TITULAR PRINCIPAL -->
                        <div class="reader-headline">{{ $p['titular_principal']['titulo'] ?? '' }}</div>
                        <div class="reader-4-cols">
                            @foreach($p['titular_principal']['columnas'] ?? [] as $col)
                                <div>
                                    <strong>{{ $col['destacado'] ?? '' }}</strong> {{ $col['texto'] ?? '' }}
                                    @if(!empty($col['pagina_ref']))
                                        <span class="reader-page-badge">▶ {{ $col['pagina_ref'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- CUERPO CENTRAL -->
                        <div class="reader-center-grid">
                            <div class="reader-hero-card">
                                <div class="reader-hero-wrap">
                                    <img src="{{ $p['noticia_central']['imagen'] ?? '' }}">
                                    <div class="reader-hero-overlay">{{ $p['noticia_central']['titulo_sobre_foto'] ?? '' }}</div>
                                </div>
                                <div class="reader-hero-caption">
                                    {{ $p['noticia_central']['epigrafe'] ?? '' }}
                                    <span class="reader-page-badge">▶ {{ $p['noticia_central']['pagina_ref'] ?? 'PÁG. 3' }}</span>
                                </div>
                            </div>

                            <div>
                                @foreach($p['lateral_noticias'] ?? [] as $lat)
                                    <div class="reader-side-item">
                                        <div class="reader-side-tag">{{ $lat['categoria'] ?? '' }}</div>
                                        <div class="reader-side-img"><img src="{{ $lat['imagen'] ?? '' }}"></div>
                                        <div class="reader-side-title">{{ $lat['titulo'] ?? '' }}</div>
                                        <div style="font-size:0.7rem; line-height:1.35; color:#4B5563;">
                                            {{ $lat['texto'] ?? '' }} <span class="reader-page-badge">▶ {{ $lat['pagina_ref'] ?? '' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- CINTILLO INFERIOR -->
                        <div class="reader-breaking-bar">
                            <span style="background:#FFF; color:var(--reader-red); font-family:'Montserrat',sans-serif; font-weight:900; font-size:0.6rem; padding:2px 8px; border-radius:2px;">{{ $p['cintillo_inferior']['categoria'] ?? 'SEGURIDAD' }}</span>
                            <span style="font-family:'Playfair Display',serif; font-weight:900; font-size:0.88rem; flex:1;">{{ $p['cintillo_inferior']['texto'] ?? '' }}</span>
                            <span style="background:#FFF; color:var(--reader-red); font-family:'Montserrat',sans-serif; font-weight:900; font-size:0.6rem; padding:2px 8px; border-radius:2px;">▶ {{ $p['cintillo_inferior']['pagina_ref'] ?? 'PÁG. 9' }}</span>
                        </div>

                    @elseif(($p['tipo'] ?? '') === 'editorial')
                        <!-- PÁGINA 2: EDITORIAL & SERVICIOS -->
                        <div class="reader-inner-header">
                            <div class="reader-inner-tag">EDITORIAL</div>
                            <div style="font-family:'Montserrat',sans-serif; font-size:0.65rem; color:#6B7280; font-weight:700;">
                                {{ $edicionActiva['fecha'] ?? '' }} // latitud18.com // <strong>PÁG. 2</strong>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 16px; border-bottom: 1px solid #000; padding-bottom: 14px;">
                            <div>
                                <div style="font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.6rem; color:var(--reader-red); margin-bottom:2px;">EDITORIAL</div>
                                <h2 style="font-family:'Playfair Display',serif; font-size:1.45rem; font-weight:900; line-height:1.15; margin-bottom:10px;">{{ $p['editorial']['titulo'] ?? '' }}</h2>
                                <div style="column-count:2; column-gap:16px; font-size:0.74rem; line-height:1.5; text-align:justify;">
                                    @foreach($p['editorial']['parrafos_col1'] ?? [] as $par) <p style="margin-bottom:6px;">{{ $par }}</p> @endforeach
                                </div>
                                <div class="reader-pull-quote">"{{ $p['editorial']['cita_destacada'] ?? '' }}"</div>
                                <div style="column-count:2; column-gap:16px; font-size:0.74rem; line-height:1.5; text-align:justify;">
                                    @foreach($p['editorial']['parrafos_col2'] ?? [] as $par) <p style="margin-bottom:6px;">{{ $par }}</p> @endforeach
                                </div>
                            </div>

                            <div>
                                <!-- PRINCIPIOS -->
                                <div style="background:#F8FAFC; border:1px solid #E5E7EB; padding:10px; margin-bottom:12px;">
                                    <div style="background:#000; color:#FFF; font-family:'Montserrat',sans-serif; font-size:0.65rem; font-weight:800; padding:3px 8px; display:inline-block; margin-bottom:6px;">TODOS SOMOS IGUALES</div>
                                    <p style="font-size:0.68rem; line-height:1.35; color:#374151;">{{ $p['principios']['texto'] ?? '' }}</p>
                                </div>

                                <!-- CLIMA -->
                                <div style="background:#F8FAFC; border:1px solid #E5E7EB; padding:10px; margin-bottom:12px;">
                                    <div style="background:#0B1F3A; color:#FFF; font-family:'Montserrat',sans-serif; font-size:0.65rem; font-weight:800; padding:3px 8px; display:inline-block; margin-bottom:8px;">Pronóstico del Tiempo</div>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:0.7rem; text-align:center;">
                                        <div style="background:#FFF; border:1px solid #E5E7EB; padding:6px;">
                                            <strong>HOY</strong> ☀️<br><strong>{{ $p['servicios']['clima']['hoy']['min'] ?? '17°C' }}</strong> / <strong>{{ $p['servicios']['clima']['hoy']['max'] ?? '22°C' }}</strong>
                                        </div>
                                        <div style="background:#FFF; border:1px solid #E5E7EB; padding:6px;">
                                            <strong>MAÑANA</strong> ⛅<br><strong>{{ $p['servicios']['clima']['manana']['min'] ?? '16°C' }}</strong> / <strong>{{ $p['servicios']['clima']['manana']['max'] ?? '26°C' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- COTIZACIONES -->
                                <div style="background:#F8FAFC; border:1px solid #E5E7EB; padding:10px;">
                                    <div style="background:#0B1F3A; color:#FFF; font-family:'Montserrat',sans-serif; font-size:0.65rem; font-weight:800; padding:3px 8px; display:inline-block; margin-bottom:6px;">Cotización del Boliviano</div>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px; font-size:0.68rem;">
                                        <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">DÓLARES: <strong>{{ $p['servicios']['cotizaciones']['dolar_compra'] ?? '12,58' }}</strong></div>
                                        <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">UFV: <strong>{{ $p['servicios']['cotizaciones']['ufv'] ?? '3.34041' }}</strong></div>
                                        <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">REAL: <strong>{{ $p['servicios']['cotizaciones']['real'] ?? '2.45272' }}</strong></div>
                                        <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">EURO: <strong>{{ $p['servicios']['cotizaciones']['euro'] ?? '14.60922' }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OPINIÓN -->
                        <div style="margin-bottom:14px;">
                            <div style="background:var(--reader-red); color:#FFF; font-family:'Montserrat',sans-serif; font-weight:900; font-size:0.75rem; padding:3px 10px; display:flex; justify-content:space-between; margin-bottom:10px;">
                                <span>OPINIÓN</span>
                                <span>{{ $p['opinion']['autor'] ?? '' }}</span>
                            </div>
                            <h3 style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:900; line-height:1.15; margin-bottom:10px;">{{ $p['opinion']['titulo'] ?? '' }}</h3>
                            <div style="column-count:4; column-gap:14px; font-size:0.72rem; line-height:1.48; text-align:justify;" class="reader-dropcap">
                                @foreach($p['opinion']['columnas'] ?? [] as $col) <p style="margin-bottom:6px;">{{ $col }}</p> @endforeach
                            </div>
                        </div>

                        <!-- STAFF -->
                        <div class="reader-staff-footer">
                            <div><strong>Director:</strong> {{ $p['staff']['director'] ?? 'Dr. Carlos Subirana' }}<br><strong>Subdirectora:</strong> {{ $p['staff']['subdirectora'] ?? 'Lic. Ximena Suárez' }}</div>
                            <div><strong>Gerente:</strong> {{ $p['staff']['gerente'] ?? 'Dr. Pedro Alberto Subirana' }}<br><strong>Seguridad:</strong> {{ $p['staff']['seguridad'] ?? 'Carol Suárez' }}</div>
                            <div><strong>Oficina Central:</strong> {{ $p['staff']['central'] ?? 'Calle Celso Castedo Nro. 46' }}<br>Santa Cruz de la Sierra</div>
                            <div><strong>Edita e Imprime:</strong><br>{{ $p['staff']['editorial_imprenta'] ?? 'Editorial CSS Ltda.' }}</div>
                        </div>

                    @elseif(($p['tipo'] ?? '') === 'negocios')
                        <!-- PÁGINA NEGOCIOS -->
                        <div class="reader-inner-header">
                            <div class="reader-inner-tag" style="border-left-color:var(--reader-red);">{{ $p['seccion_titulo'] ?? 'NEGOCIOS' }}</div>
                            <div style="font-family:'Montserrat',sans-serif; font-size:0.65rem; color:#6B7280; font-weight:700;">
                                {{ $edicionActiva['fecha'] ?? '' }} // latitud18.com // <strong>PÁG. {{ $p['numero'] ?? ($pIndex + 1) }}</strong>
                            </div>
                        </div>

                        <!-- ARTÍCULO SUPERIOR -->
                        <div style="margin-bottom:18px; border-bottom:2px solid #000; padding-bottom:14px;">
                            <div style="color:var(--reader-red); font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.65rem; text-transform:uppercase;">{{ $p['articulo_superior']['antetitulo'] ?? '' }}</div>
                            <h2 style="font-family:'Playfair Display',serif; font-weight:900; font-size:2.1rem; line-height:1.05; color:#000; margin-bottom:8px;">{{ $p['articulo_superior']['titulo'] ?? '' }}</h2>
                            <div style="font-size:0.85rem; font-weight:600; line-height:1.4; color:#374151; margin-bottom:10px;">{{ $p['articulo_superior']['bajada'] ?? '' }}</div>

                            <div style="display:grid; grid-template-columns: 1fr 1.8fr; gap:16px; margin-bottom:10px;">
                                <div>
                                    <img src="{{ $p['articulo_superior']['imagen'] ?? '' }}" style="width:100%; height:190px; object-fit:cover; display:block;">
                                    <small style="font-size:0.65rem; color:#6B7280; display:block; margin-top:4px;">{{ $p['articulo_superior']['pie_foto'] ?? '' }}</small>
                                </div>
                                <div style="column-count:3; column-gap:16px; font-size:0.76rem; line-height:1.5; text-align:justify;" class="reader-dropcap">
                                    @foreach($p['articulo_superior']['columnas'] ?? [] as $col) <p style="margin-bottom:6px;">{{ $col }}</p> @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- ARTÍCULO INFERIOR -->
                        <div>
                            <div style="color:var(--reader-red); font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.65rem; text-transform:uppercase;">{{ $p['articulo_inferior']['antetitulo'] ?? '' }}</div>
                            <h3 style="font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:900; color:#000; margin-bottom:8px;">{{ $p['articulo_inferior']['titulo'] ?? '' }}</h3>
                            <div style="display:grid; grid-template-columns: 1.2fr 2.5fr; gap:16px;">
                                <div>
                                    <img src="{{ $p['articulo_inferior']['imagen'] ?? '' }}" style="width:100%; height:140px; object-fit:cover; display:block;">
                                </div>
                                <div style="column-count:3; column-gap:16px; font-size:0.76rem; line-height:1.5; text-align:justify;">
                                    @foreach($p['articulo_inferior']['columnas'] ?? [] as $col) <p style="margin-bottom:6px;">{{ $col }}</p> @endforeach
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- PÁGINA COMUNIDAD / DEPORTES -->
                        <div class="reader-inner-header">
                            <div class="reader-inner-tag">{{ $p['seccion_titulo'] ?? 'COMUNIDAD' }}</div>
                            <div style="font-family:'Montserrat',sans-serif; font-size:0.65rem; color:#6B7280; font-weight:700;">
                                {{ $edicionActiva['fecha'] ?? '' }} // latitud18.com // <strong>PÁG. {{ $p['numero'] ?? ($pIndex + 1) }}</strong>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 2.2fr 1fr; gap: 18px; margin-bottom: 14px;">
                            <div>
                                <div style="color:var(--reader-red); font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.65rem; text-transform:uppercase;">{{ $p['articulo_principal']['antetitulo'] ?? '' }}</div>
                                <h2 style="font-family:'Playfair Display',serif; font-weight:900; font-size:1.9rem; line-height:1.08; color:#000; margin-bottom:8px;">{{ $p['articulo_principal']['titulo'] ?? '' }}</h2>
                                <div style="font-size:0.85rem; font-weight:600; line-height:1.4; color:#374151; margin-bottom:10px;">{{ $p['articulo_principal']['bajada'] ?? '' }}</div>

                                <img src="{{ $p['articulo_principal']['imagen'] ?? '' }}" style="width:100%; height:250px; object-fit:cover; display:block; margin-bottom:4px;">
                                <div style="font-size:0.68rem; color:#6B7280; margin-bottom:8px;">{{ $p['articulo_principal']['pie_foto'] ?? '' }}</div>
                                <div style="font-size:0.65rem; font-weight:700; color:#374151; margin-bottom:8px; border-bottom:1px solid #E5E7EB; padding-bottom:3px;">{{ $p['articulo_principal']['autor'] ?? '' }}</div>

                                <div style="column-count:4; column-gap:14px; font-size:0.74rem; line-height:1.5; text-align:justify;" class="reader-dropcap">
                                    @foreach($p['articulo_principal']['columnas'] ?? [] as $col) <p style="margin-bottom:6px;">{{ $col }}</p> @endforeach
                                </div>
                            </div>

                            <div>
                                @foreach($p['lateral_noticias'] ?? [] as $lat)
                                    <div class="reader-side-item">
                                        <div class="reader-side-tag">{{ $lat['antetitulo'] ?? '' }}</div>
                                        <h4 style="font-family:'Playfair Display',serif; font-size:0.88rem; font-weight:900; line-height:1.2; color:#000; margin-bottom:6px;">{{ $lat['titulo'] ?? '' }}</h4>
                                        @if(!empty($lat['imagen']))
                                            <div class="reader-side-img"><img src="{{ $lat['imagen'] }}"></div>
                                        @endif
                                        <p style="font-size:0.7rem; line-height:1.4; color:#4B5563; margin-bottom:0;">{{ $lat['texto'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if(!empty($p['bloques_adicionales']))
                            <div style="margin-top:14px; border-top:1px dashed #CBD5E0; padding-top:12px; display:flex; flex-direction:column; gap:12px;">
                                @foreach($p['bloques_adicionales'] as $blk)
                                    <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:12px; border-radius:4px;">
                                        <div style="color:var(--reader-red); font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.62rem; text-transform:uppercase;">{{ $blk['antetitulo'] ?? 'DESTACADO' }}</div>
                                        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:900; margin-bottom:4px;">{{ $blk['titulo'] ?? '' }}</h3>
                                        <p style="font-size:0.74rem; line-height:1.45; color:#374151; margin-bottom:0;">{{ $blk['contenido'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif{{-- /if frames --}}
                    </div>{{-- /reader-paper-sheet --}}
            @endforeach
        </div>{{-- /reader-stage-viewport --}}
        </div>{{-- /editorialSection --}}
    </div>{{-- /newspaper-reader-container --}}
</div>{{-- /container --}}

<script>
// ============================================================
// SWITCH DE MODO: Flipbook 3D <-> Lector Editorial
// ============================================================
function switchReaderMode(mode) {
    const flipbookSection = document.getElementById('flipbookSection');
    const editorialSection = document.getElementById('editorialSection');
    const tabFlipbook = document.getElementById('tabFlipbook');
    const tabEditorial = document.getElementById('tabEditorial');

    if (mode === 'flipbook') {
        if (flipbookSection) { flipbookSection.classList.add('active'); flipbookSection.style.display = 'flex'; }
        if (editorialSection) editorialSection.style.display = 'none';
        if (tabFlipbook) {
            tabFlipbook.style.color = '#fff';
            tabFlipbook.style.background = 'rgba(215,25,32,0.08)';
            tabFlipbook.style.borderBottomColor = 'var(--reader-red)';
        }
        if (tabEditorial) {
            tabEditorial.style.color = '#64748b';
            tabEditorial.style.background = 'transparent';
            tabEditorial.style.borderBottomColor = 'transparent';
        }
    } else {
        if (flipbookSection) { flipbookSection.classList.remove('active'); flipbookSection.style.display = 'none'; }
        if (editorialSection) editorialSection.style.display = 'block';
        if (tabFlipbook) {
            tabFlipbook.style.color = '#64748b';
            tabFlipbook.style.background = 'transparent';
            tabFlipbook.style.borderBottomColor = 'transparent';
        }
        if (tabEditorial) {
            tabEditorial.style.color = '#fff';
            tabEditorial.style.background = 'rgba(215,25,32,0.08)';
            tabEditorial.style.borderBottomColor = 'var(--reader-red)';
        }
        setTimeout(updateMobileScale, 50);
    }
}

// ============================================================
// MOTOR DEL FLIPBOOK 3D PURO (sin dependencias externas)
// ============================================================
(function() {
    const TOTAL_PAGES = {{ count($edicionActiva['paginas'] ?? []) }};
    let fbSpread = 0;          // índice de la página izquierda actual (0, 2, 4…)
    let fbAnimating = false;   // evita doble click durante animación
    let fbTouchX = 0;

    function getSrcPage(idx) {
        if (idx < 0 || idx >= TOTAL_PAGES) return null;
        const el = document.getElementById('l18-src-page-' + idx);
        return el ? el.innerHTML : null;
    }

    function setPageContent(containerId, html) {
        const c = document.getElementById(containerId);
        if (!c) return;
        c.innerHTML = html || '';
    }

    function renderBlank(containerId) {
        const c = document.getElementById(containerId);
        if (!c) return;
        c.innerHTML = '<div style="width:794px;height:1123px;background:#f9fafb;display:flex;align-items:center;justify-content:center;"><span style=\'font-family:Montserrat,sans-serif;font-size:18px;color:#cbd5e0;\'>·</span></div>';
    }

    // Renderiza las páginas estáticas del spread actual
    function renderCurrentSpread() {
        const leftHtml = getSrcPage(fbSpread);
        const rightHtml = getSrcPage(fbSpread + 1);
        setPageContent('l18LeftContent', leftHtml || '');
        if (!leftHtml) renderBlank('l18LeftContent');
        setPageContent('l18RightContent', rightHtml || '');
        if (!rightHtml) renderBlank('l18RightContent');
        updateFbControls();
    }

    function updateFbControls() {
        const indicator = document.getElementById('l18FbIndicator');
        const btnPrev = document.getElementById('l18BtnPrev');
        const btnNext = document.getElementById('l18BtnNext');
        const btnFirst = document.getElementById('l18BtnFirst');
        const btnLast = document.getElementById('l18BtnLast');
        const thumbs = document.querySelectorAll('.l18-fb-thumb');

        if (indicator) {
            const right = Math.min(fbSpread + 2, TOTAL_PAGES);
            indicator.textContent = 'Pág ' + (fbSpread + 1) + (right > fbSpread + 1 ? '–' + right : '') + ' / ' + TOTAL_PAGES;
        }
        if (btnPrev) btnPrev.disabled = (fbSpread === 0);
        if (btnFirst) btnFirst.disabled = (fbSpread === 0);
        const lastSpread = TOTAL_PAGES % 2 === 0 ? TOTAL_PAGES - 2 : TOTAL_PAGES - 1;
        if (btnNext) btnNext.disabled = (fbSpread >= lastSpread);
        if (btnLast) btnLast.disabled = (fbSpread >= lastSpread);

        thumbs.forEach((t, i) => {
            t.classList.toggle('active', i === fbSpread || i === fbSpread + 1);
        });
    }

    // Animación de volteo de hoja hacia adelante (siguiente spread)
    function flipForward() {
        if (fbAnimating || fbSpread + 2 >= TOTAL_PAGES) return;
        fbAnimating = true;

        const flipPage = document.getElementById('l18FlipPage');
        const flipFront = document.getElementById('l18FlipFrontContent');
        const flipBack  = document.getElementById('l18FlipBackContent');

        // La hoja que se dobla muestra: frente = página derecha actual, dorso = página izquierda siguiente spread
        setPageContent(flipFront.id, getSrcPage(fbSpread + 1) || '');
        setPageContent(flipBack.id,  getSrcPage(fbSpread + 2) || '');

        // Posicionamos la hoja encima de la página derecha
        flipPage.style.left = 'auto';
        flipPage.style.right = '0';
        flipPage.style.transformOrigin = 'left center';
        flipPage.style.transform = 'rotateY(0deg)';
        flipPage.style.zIndex = '20';

        // Forzamos reflow para que empiece la transición
        void flipPage.offsetWidth;
        flipPage.classList.add('is-flipping');

        setTimeout(() => {
            fbSpread += 2;
            renderCurrentSpread();
            flipPage.classList.remove('is-flipping');
            flipPage.style.transform = '';
            fbAnimating = false;
        }, 720);
    }

    // Animación de volteo hacia atrás (spread anterior)
    function flipBackward() {
        if (fbAnimating || fbSpread === 0) return;
        fbAnimating = true;

        const flipPage = document.getElementById('l18FlipPage');
        const flipFront = document.getElementById('l18FlipFrontContent');
        const flipBack  = document.getElementById('l18FlipBackContent');

        // Hoja ya doblada (parte de la derecha, mirando hacia izquierda)
        setPageContent(flipFront.id, getSrcPage(fbSpread) || '');        // dorso (lo que se ve girando)
        setPageContent(flipBack.id,  getSrcPage(fbSpread - 1) || '');    // frente desde atrás

        flipPage.style.left = '0';
        flipPage.style.right = 'auto';
        flipPage.style.transformOrigin = 'right center';
        flipPage.style.transform = 'rotateY(180deg)';
        flipPage.style.zIndex = '20';
        void flipPage.offsetWidth;

        requestAnimationFrame(() => {
            flipPage.style.transition = 'transform 0.7s cubic-bezier(0.645,0.045,0.355,1)';
            flipPage.style.transform = 'rotateY(0deg)';
        });

        setTimeout(() => {
            fbSpread -= 2;
            renderCurrentSpread();
            flipPage.style.transform = '';
            flipPage.style.transition = '';
            flipPage.style.transformOrigin = 'left center';
            fbAnimating = false;
        }, 720);
    }

    // API pública
    window.fbNextSpread = flipForward;
    window.fbPrevSpread = flipBackward;
    window.fbGoToSpread = function(targetPage) {
        const normalizedSpread = targetPage % 2 === 0 ? targetPage : targetPage - 1;
        if (normalizedSpread === fbSpread) return;
        fbSpread = Math.max(0, Math.min(normalizedSpread, TOTAL_PAGES - 1));
        renderCurrentSpread();
    };

    // Soporte táctil en el libro
    const bookEl = document.getElementById('l18Book');
    if (bookEl) {
        bookEl.addEventListener('touchstart', (e) => { fbTouchX = e.touches[0].clientX; }, { passive: true });
        bookEl.addEventListener('touchend', (e) => {
            const diff = fbTouchX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) { diff > 0 ? flipForward() : flipBackward(); }
        }, { passive: true });
        bookEl.style.cursor = 'ew-resize';
    }

    // Escalar el libro responsivo
    function scaleFb() {
        const wrap = document.getElementById('flipbookSection');
        if (!wrap) return;
        const available = Math.min(wrap.offsetWidth - 32, 900);
        const bookWidth = 780 + 20; // 2 páginas + lomo
        const scale = Math.min(1, available / bookWidth);
        wrap.style.setProperty('--fb-page-w', Math.round(380 * scale) + 'px');
        wrap.style.setProperty('--fb-page-h', Math.round(540 * scale) + 'px');
        wrap.style.setProperty('--fb-scale', (0.478 * scale).toFixed(3));
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', () => {
        renderCurrentSpread();
        scaleFb();
        setTimeout(scaleFb, 100);
    });
    window.addEventListener('resize', scaleFb);
})();

</script>

<script>
let currentPageIndex = 0;
const totalPages = {{ count($edicionActiva['paginas'] ?? []) }};

function updateMobileScale() {
    const viewportWidth = window.innerWidth;
    const viewport = document.querySelector('.reader-stage-viewport');
    if (!viewport) return;

    if (viewportWidth < 860) {
        const targetWidth = 820;
        const availableWidth = viewportWidth - 20;
        const scale = Math.min(1, Math.max(0.35, availableWidth / targetWidth));
        
        const sheets = document.querySelectorAll('.reader-paper-sheet');
        sheets.forEach(sheet => {
            sheet.style.transform = `scale(${scale})`;
            sheet.style.transformOrigin = 'top center';
        });

        const activeSheet = document.querySelector('.reader-paper-sheet.active');
        if (activeSheet) {
            const rawHeight = activeSheet.offsetHeight || 1160;
            const scaledHeight = rawHeight * scale;
            viewport.style.height = `${Math.round(scaledHeight) + 40}px`;
        }
    } else {
        const sheets = document.querySelectorAll('.reader-paper-sheet');
        sheets.forEach(sheet => {
            sheet.style.transform = '';
            sheet.style.transformOrigin = '';
        });
        viewport.style.height = '';
    }
}

function goToPage(index) {
    if (index < 0 || index >= totalPages) return;
    currentPageIndex = index;

    document.querySelectorAll('.reader-paper-sheet').forEach((sheet, idx) => {
        sheet.classList.toggle('active', idx === index);
    });

    document.querySelectorAll('.reader-page-btn').forEach((btn, idx) => {
        btn.classList.toggle('active', idx === index);
    });

    document.getElementById('pageIndicator').textContent = `Pág ${index + 1} / ${totalPages}`;
    document.getElementById('btnPrevPage').disabled = (index === 0);
    document.getElementById('btnNextPage').disabled = (index === totalPages - 1);

    window.scrollTo({ top: 120, behavior: 'smooth' });
    setTimeout(updateMobileScale, 50);
}

function prevPage() {
    if (currentPageIndex > 0) goToPage(currentPageIndex - 1);
}

function nextPage() {
    if (currentPageIndex < totalPages - 1) goToPage(currentPageIndex + 1);
}

// Teclas izquierda/derecha para pasar páginas, y F para fullscreen
document.addEventListener('keydown', (e) => {
    const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
    if (tag === 'input' || tag === 'textarea') return;
    if (e.key === 'ArrowLeft') prevPage();
    if (e.key === 'ArrowRight') nextPage();
    if (e.key === 'f' || e.key === 'F') { e.preventDefault(); readerToggleFullscreen(); }
    if (e.key === '+' || e.key === '=') { e.preventDefault(); readerZoomIn(); }
    if (e.key === '-') { e.preventDefault(); readerZoomOut(); }
    if (e.key === '0') { e.preventDefault(); readerZoomReset(); }
});

// Soporte para gestos táctiles Swipe en smartphones y tablets
let touchStartX = 0;
let touchStartY = 0;
const stageViewport = document.querySelector('.reader-stage-viewport');
if (stageViewport) {
    stageViewport.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }
    }, { passive: true });

    stageViewport.addEventListener('touchend', (e) => {
        if (e.changedTouches.length === 1) {
            const diffX = touchStartX - e.changedTouches[0].clientX;
            const diffY = touchStartY - e.changedTouches[0].clientY;
            // Deslizamiento horizontal prioritario mayor a 45px
            if (Math.abs(diffX) > 45 && Math.abs(diffX) > Math.abs(diffY) * 1.3) {
                if (diffX > 0) {
                    nextPage();
                } else {
                    prevPage();
                }
            }
        }
    }, { passive: true });
}

// ============================================================
// CONTROLES DE TOOLBAR EDITORIAL: ZOOM + DOBLE PÁGINA + FULLSCREEN
// ============================================================

// -- ZOOM --
const ZOOM_LEVELS = [0.5, 0.65, 0.8, 1.0, 1.2, 1.4, 1.6, 1.8, 2.0];
let currentZoomIdx = 3; // 1.0 = 100%

function applyReaderZoom() {
    const scale = ZOOM_LEVELS[currentZoomIdx];
    const label = document.getElementById('readerZoomLabel');
    if (label) label.textContent = Math.round(scale * 100) + '%';
    document.querySelectorAll('.reader-paper-sheet').forEach(sheet => {
        sheet.style.transform = `scale(${scale})`;
        sheet.style.transformOrigin = 'top center';
    });
}

function readerZoomIn() {
    if (currentZoomIdx < ZOOM_LEVELS.length - 1) {
        currentZoomIdx++;
        applyReaderZoom();
    }
}

function readerZoomOut() {
    if (currentZoomIdx > 0) {
        currentZoomIdx--;
        applyReaderZoom();
    }
}

function readerZoomReset() {
    currentZoomIdx = 3; // back to 100%
    applyReaderZoom();
}

// -- DOBLE PÁGINA --
let isDoublePageMode = false;

function readerSetSinglePage() {
    isDoublePageMode = false;
    const vp = document.getElementById('readerViewport');
    if (vp) vp.classList.remove('double-page-mode');
    // Mostrar solo la página activa
    document.querySelectorAll('.reader-paper-sheet').forEach((sheet, idx) => {
        sheet.classList.toggle('active', idx === currentPageIndex);
    });
    document.getElementById('btnSinglePage').classList.add('active');
    document.getElementById('btnDoublePage').classList.remove('active');
    applyReaderZoom();
}

function readerSetDoublePage() {
    isDoublePageMode = true;
    const vp = document.getElementById('readerViewport');
    if (vp) vp.classList.add('double-page-mode');
    // Mostrar la página actual y la siguiente (spread abierto)
    const spreadIdx1 = currentPageIndex % 2 === 0 ? currentPageIndex : currentPageIndex - 1;
    const spreadIdx2 = spreadIdx1 + 1;
    document.querySelectorAll('.reader-paper-sheet').forEach((sheet, idx) => {
        sheet.classList.toggle('active', idx === spreadIdx1 || idx === spreadIdx2);
    });
    document.getElementById('btnDoublePage').classList.add('active');
    document.getElementById('btnSinglePage').classList.remove('active');
    // Ajustar zoom para que quepan dos páginas
    if (currentZoomIdx > 2) { currentZoomIdx = 2; } // max 80% en doble página
    applyReaderZoom();
}

// -- PANTALLA COMPLETA --
function readerToggleFullscreen() {
    const container = document.querySelector('.newspaper-reader-container') || document.getElementById('readerViewport');
    const icon = document.getElementById('readerFsIcon');
    const btn = document.getElementById('btnReaderFullscreen');
    const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement;

    if (!isFs) {
        if (container.requestFullscreen) container.requestFullscreen();
        else if (container.webkitRequestFullscreen) container.webkitRequestFullscreen();
        else if (container.mozRequestFullScreen) container.mozRequestFullScreen();
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
        else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
    }
}

function updateReaderFsBtn() {
    const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement;
    const icon = document.getElementById('readerFsIcon');
    const btn = document.getElementById('btnReaderFullscreen');
    if (icon) icon.className = isFs ? 'fas fa-compress' : 'fas fa-expand';
    if (btn) {
        const span = btn.childNodes[1];
        if (span) span.textContent = isFs ? ' Salir de Pantalla Completa' : ' Pantalla Completa';
    }
}

document.addEventListener('fullscreenchange', updateReaderFsBtn);
document.addEventListener('webkitfullscreenchange', updateReaderFsBtn);
document.addEventListener('mozfullscreenchange', updateReaderFsBtn);

window.addEventListener('resize', updateMobileScale);
window.addEventListener('orientationchange', updateMobileScale);
document.addEventListener('DOMContentLoaded', () => {
    updateMobileScale();
    setTimeout(updateMobileScale, 300);
});
</script>
@endsection
