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

/* Scale down paper to fit viewport on smaller screens */
@media (max-width: 900px) {
    .reader-stage-viewport { padding: 10px 0; }
    .reader-paper-sheet, .reader-paper-sheet.frames-mode {
        transform-origin: top center;
        transform: scale(calc((100vw - 20px) / 820));
        margin-bottom: calc(((100vw - 20px) / 820 - 1) * 1160px);
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
</style>

<div class="container py-3">
    <!-- CONTENEDOR PRINCIPAL DEL LECTOR -->
    <div class="newspaper-reader-container">
        <!-- HEADER DE CONTROLES -->
        <div class="reader-control-header">
            <div class="d-flex align-items-center gap-3">
                <span class="reader-edition-badge">EDICIÓN SEMANAL DIGITAL</span>
                <div>
                    <h1 class="h6 mb-0 fw-bold text-white" style="font-family: 'Anton', sans-serif; letter-spacing: 1px;">
                        {{ $edicionActiva['titulo'] ?? 'La Estrella' }} <span style="color:var(--reader-red);">{{ $edicionActiva['subtitulo'] ?? 'del Oriente' }}</span>
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

        <!-- RIBBON DE MINIATURAS / PÁGINAS -->
        <div class="reader-pages-ribbon">
            @foreach($edicionActiva['paginas'] as $idx => $pag)
                <button class="reader-page-btn {{ $idx === 0 ? 'active' : '' }}" onclick="goToPage({{ $idx }})" id="reader-tab-{{ $idx }}">
                    Pág {{ $pag['numero'] ?? ($idx + 1) }}: {{ $pag['nombre'] ?? 'Página ' . ($idx + 1) }}
                </button>
            @endforeach
        </div>

        <!-- STAGE DONDE SE RENDERIZAN LAS PÁGINAS DEL PERIÓDICO -->
        <div class="reader-stage-viewport" id="readerViewport">
            @foreach($edicionActiva['paginas'] as $pIndex => $p)
                <div class="reader-paper-sheet {{ $pIndex === 0 ? 'active' : '' }} {{ !empty($p['frames']) && count($p['frames']) > 0 ? 'frames-mode' : '' }}" id="reader-sheet-{{ $pIndex }}">

                    {{-- ▶ NEW: InDesign-style frames rendering --}}
                    @if(!empty($p['frames']) && count($p['frames']) > 0)
                        <div style="position:relative; width:794px; min-height:1123px; background:#fff; overflow:hidden; font-family:'Source Sans 3',sans-serif;">
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
                                    opacity:{{ $fop }};">

                                    @if($ftype === 'text')
                                        <div style="width:100%;height:100%;overflow:hidden;
                                            font-family:{{ $styles['fontFamily'] ?? "'Source Sans 3',sans-serif" }};
                                            font-size:{{ $styles['fontSize'] ?? 14 }}px;
                                            font-weight:{{ $styles['fontWeight'] ?? '400' }};
                                            line-height:{{ $styles['lineHeight'] ?? '1.5' }};
                                            color:{{ $styles['color'] ?? '#111111' }};
                                            text-align:{{ $styles['textAlign'] ?? 'left' }};
                                            background-color:{{ $styles['backgroundColor'] ?? 'transparent' }};
                                            {{ isset($styles['columns']) && $styles['columns'] > 1 ? 'column-count:'.$styles['columns'].';column-gap:14px;' : '' }}
                                            padding:6px;">
                                            {!! $frame['content'] ?? '' !!}
                                        </div>

                                    @elseif($ftype === 'image')
                                        @if(!empty($frame['src']))
                                            <img src="{{ $frame['src'] }}"
                                                 style="width:100%;height:100%;
                                                    object-fit:{{ $frame['imgFit'] ?? 'cover' }};
                                                    display:block;
                                                    border-radius:{{ $frame['borderRadius'] ?? 0 }}px;
                                                    {{ isset($frame['borderWidth']) && $frame['borderWidth'] > 0 ? 'border:'.$frame['borderWidth'].'px solid '.($frame['borderColor']??'#000').';' : '' }}"
                                                 alt="">
                                        @else
                                            <div style="width:100%;height:100%;background:#F3F4F6;display:flex;align-items:center;justify-content:center;color:#9CA3AF;font-size:0.75rem;">
                                                <i class="fas fa-image" style="font-size:1.5rem;"></i>
                                            </div>
                                        @endif

                                    @elseif($ftype === 'line')
                                        <div style="width:100%;height:{{ $frame['lineWidth'] ?? 2 }}px;
                                            background:{{ $frame['lineColor'] ?? '#000000' }};
                                            position:absolute;top:50%;transform:translateY(-50%);"></div>

                                    @elseif($ftype === 'rect' || $ftype === 'shape')
                                        <div style="width:100%;height:100%;
                                            background:{{ $frame['fillColor'] ?? 'transparent' }};
                                            border-radius:{{ $frame['borderRadius'] ?? 0 }}px;
                                            {{ isset($frame['borderWidth']) && $frame['borderWidth'] > 0 ? 'border:'.$frame['borderWidth'].'px solid '.($frame['borderColor']??'#000').';' : '' }}">
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
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
let currentPageIndex = 0;
const totalPages = {{ count($edicionActiva['paginas'] ?? []) }};

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
}

function prevPage() {
    if (currentPageIndex > 0) goToPage(currentPageIndex - 1);
}

function nextPage() {
    if (currentPageIndex < totalPages - 1) goToPage(currentPageIndex + 1);
}

// Teclas izquierda/derecha para pasar páginas
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') prevPage();
    if (e.key === 'ArrowRight') nextPage();
});
</script>
@endsection
