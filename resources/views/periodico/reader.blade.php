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
   FLIPBOOK 3D — VISOR LIBRO REAL (sin CDN)
   Periódico en doble página, contenido real del editor
   ==================================================== */

/* Contenedor principal del flipbook */
.fb-wrapper {
    display: none;
    background: linear-gradient(160deg, #0a0f1e 0%, #111827 50%, #0a0f1e 100%);
    min-height: 700px;
    padding: 20px 12px 32px;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 16px;
    position: relative;
    /* Con el libro más grande puede exceder el alto en ventanas bajas:
       permitir scroll vertical en vez de recortar la hoja */
    overflow-x: hidden;
    overflow-y: auto;
}
.fb-wrapper::before {
    content:'';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 50% at 50% 50%, rgba(215,25,32,0.06) 0%, transparent 70%);
    pointer-events: none;
}
.fb-wrapper.active { display: flex; }

/* Escenario 3D */
.fb-stage {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    perspective: 2800px;
    perspective-origin: 50% 40%;
}

/* Contenedor del libro abierto */
.fb-book {
    display: flex;
    align-items: stretch;
    position: relative;
    transform-style: preserve-3d;
    /* El zoom y la inclinación se combinan aquí. El hover solo cambia la
       variable --fbTilt; si se escribiera la transform completa en :hover,
       specificity (.fb-book:hover) anularía el scale() del zoom. */
    transform: scale(var(--fbZoom, 1)) rotateX(var(--fbTilt, 2deg));
    transition: transform .4s ease;
    filter: drop-shadow(0 40px 80px rgba(0,0,0,0.85)) drop-shadow(0 10px 30px rgba(0,0,0,0.5));
}
.fb-book:hover { --fbTilt: 0deg; }

/* El lomo del libro — sombra de encuadernación */
.fb-spine {
    width: 14px;
    background: linear-gradient(to right,
        rgba(0,0,0,0.30) 0%,
        rgba(0,0,0,0.06) 30%,
        rgba(255,255,255,0.06) 50%,
        rgba(0,0,0,0.06) 70%,
        rgba(0,0,0,0.30) 100%
    );
    flex-shrink: 0;
    align-self: stretch;
    z-index: 3;
    position: relative;
}
.fb-spine::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0; left: 5px; right: 5px;
    background: linear-gradient(to bottom,
        rgba(215,25,32,0.4) 0%, rgba(215,25,32,0.1) 15%,
        transparent 40%, transparent 80%,
        rgba(0,0,0,0.2) 100%);
}

/* Hoja del libro — proporciones exactas de la edición (720×1040 px) */
.fb-page {
    /* Proporción exacta 720 / 1040 = 0.6923 */
    width: min(46vw, calc((100vh - 170px) * (720 / 1040)));
    min-width: 240px;
    aspect-ratio: 720 / 1040;
    max-height: calc(100vh - 170px);
    background: #ffffff;
    position: relative;
    overflow: hidden;
    transform-style: preserve-3d;
    flex-shrink: 0;
}

/* Contenedor interno que escala el contenido del editor al 100% */
.fb-page-inner {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}
/* El contenido renderizado del periódico se escala para llenar la hoja */
.fb-page-inner > .fb-page-source,
.fb-page-inner > div {
    position: absolute;
    top: 0;
    left: 0;
    width: 720px;
    height: 1040px;
    transform-origin: top left;
}

/* Sombra interna (curva de página) */
.fb-page-left {
    border-radius: 3px 0 0 3px;
    border-right: 1.5px solid rgba(0,0,0,0.15);
    transform-origin: right center;
    box-shadow:
        -8px 0 24px rgba(0,0,0,0.4),
        inset -12px 0 20px -8px rgba(0,0,0,0.18),
        inset -1px 0 0 rgba(255,255,255,0.15);
}
.fb-page-right {
    border-radius: 0 3px 3px 0;
    border-left: 1.5px solid rgba(0,0,0,0.15);
    transform-origin: left center;
    box-shadow:
        8px 0 24px rgba(0,0,0,0.4),
        inset 12px 0 20px -8px rgba(0,0,0,0.18),
        inset 1px 0 0 rgba(255,255,255,0.15);
}

/* Esquinas de las páginas (efecto de papel con textura) */
.fb-page::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, transparent 60%);
    pointer-events: none;
    z-index: 4;
}
/* Línea de doblez del papel */
.fb-page-left::before {
    content: '';
    position: absolute;
    top: 0; bottom: 0; right: 0;
    width: 28px;
    background: linear-gradient(to left, rgba(0,0,0,0.08) 0%, transparent 100%);
    z-index: 3;
    pointer-events: none;
}
.fb-page-right::before {
    content: '';
    position: absolute;
    top: 0; bottom: 0; left: 0;
    width: 28px;
    background: linear-gradient(to right, rgba(0,0,0,0.08) 0%, transparent 100%);
    z-index: 3;
    pointer-events: none;
}

/* ======================== ANIMACIONES DE VOLTEO ======================== */
.fb-page.flipping-out {
    animation: fbFlipOut 0.36s cubic-bezier(0.55, 0, 0.45, 1) forwards;
    transform-origin: left center;
}
.fb-page-left.flipping-out {
    animation: fbFlipOutLeft 0.36s cubic-bezier(0.55, 0, 0.45, 1) forwards;
    transform-origin: right center;
}
.fb-page.flipping-in {
    animation: fbFlipIn 0.36s cubic-bezier(0.55, 0, 0.45, 1) forwards;
    transform-origin: left center;
}
.fb-page-left.flipping-in {
    animation: fbFlipInLeft 0.36s cubic-bezier(0.55, 0, 0.45, 1) forwards;
    transform-origin: right center;
}
.fb-page.flipping-out-left {
    animation: fbFlipOutLeft 0.36s cubic-bezier(0.55, 0, 0.45, 1) forwards;
    transform-origin: right center;
}
.fb-page.flipping-in-left {
    animation: fbFlipInLeft 0.36s cubic-bezier(0.55, 0, 0.45, 1) forwards;
    transform-origin: left center;
}

@keyframes fbFlipOut     { 0%{transform:rotateY(0deg) scale(1);}  50%{transform:rotateY(-70deg) scale(0.92);} 100%{transform:rotateY(-90deg) scale(0.88);} }
@keyframes fbFlipIn      { 0%{transform:rotateY(90deg) scale(0.88);} 50%{transform:rotateY(20deg) scale(0.96);} 100%{transform:rotateY(0deg) scale(1);} }
@keyframes fbFlipOutLeft { 0%{transform:rotateY(0deg) scale(1);}  50%{transform:rotateY(70deg) scale(0.92);}  100%{transform:rotateY(90deg) scale(0.88);} }
@keyframes fbFlipInLeft  { 0%{transform:rotateY(-90deg) scale(0.88);} 50%{transform:rotateY(-20deg) scale(0.96);} 100%{transform:rotateY(0deg) scale(1);} }

/* Placeholder cuando no hay página */
.fb-page-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(160deg, #f8fafc 0%, #f1f5f9 100%);
    color: #94a3b8;
}
.fb-page-placeholder .fp-name {
    font-family: 'Anton', sans-serif;
    font-size: 1.4rem;
    color: #0B1F3A;
    letter-spacing: 2px;
    line-height: 1;
}
.fb-page-placeholder .fp-sub {
    background: #D71920;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.58rem;
    font-weight: 800;
    padding: 2px 7px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}

/* ====================================================
   FLIPBUILDER LOOK — replica el visor del ePaper
   (epaper.la-razon.com: Flip PDF Corporate Edition)
   Valores tomados de su config.js: toolbar #000/iconos
   #ECF5FB, lomo saddle 2.4% + sombra 87px, stitch 0.5%,
   borde derecho 3px, sombras L90/R55 alpha .6, cornerRound 8
   ==================================================== */

/* ---------- Pantalla de carga (signo FlipBuilder) ---------- */
#fbLoadingScreen {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: #1F2232;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 18px;
    transition: opacity .55s ease, visibility .55s ease;
}
#fbLoadingScreen.hide { opacity: 0; visibility: hidden; pointer-events: none; }
#fbLoadingScreen .fls-logo {
    height: 40px;
    max-width: 220px;
    object-fit: contain;
}
#fbLoadingScreen .fls-caption {
    font-family: 'Montserrat', sans-serif;
    font-size: 20px;
    color: #DDDDDD;
    letter-spacing: .5px;
    text-align: center;
    padding: 0 20px;
}
#fbLoadingScreen .fls-bar {
    width: 190px;
    height: 2px;
    background: rgba(255,255,255,.14);
    overflow: hidden;
    border-radius: 2px;
}
#fbLoadingScreen .fls-bar span {
    display: block;
    height: 100%;
    width: 40%;
    background: #D71920;
    animation: flsSlide 1.1s ease-in-out infinite;
}
@keyframes flsSlide {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(250%); }
}

/* ---------- Fondo de escenario (bookConfig.backGroundImgURL) ---------- */
.fb-wrapper {
    background:
        radial-gradient(ellipse 120% 90% at 50% -10%, rgba(215,25,32,.10) 0%, transparent 60%),
        linear-gradient(180deg, #141A28 0%, #0A0F1E 55%, #060911 100%);
    /* Reservar espacio para la toolbar fija inferior */
    padding-bottom: 88px !important;
}

/* ---------- Toolbar inferior fija (toolbarColor #000) ---------- */
.fb-toolbar {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    z-index: 9000;
    background: #000;
    border-top: 1px solid rgba(255,255,255,.08);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 6px 10px;
    transition: transform .3s ease, opacity .3s ease;
    box-shadow: 0 -6px 22px rgba(0,0,0,.45);
}
/* toolbarAlwaysShow = "No": se oculta al inactivo y vuelve al mover el ratón */
.fb-toolbar.fb-tb-hidden {
    transform: translateY(110%);
    opacity: 0;
    pointer-events: none;
}
.fb-tb-btn {
    background: transparent;
    border: 0;
    color: #ECF5FB;
    width: 38px; height: 38px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: .95rem;
    transition: background .18s, color .18s;
    text-decoration: none;
}
.fb-tb-btn:hover { background: rgba(236,245,251,.14); color: #fff; }
.fb-tb-btn.active { background: var(--reader-red); color: #fff; }
.fb-tb-btn:disabled { opacity: .3; cursor: not-allowed; }
.fb-tb-btn.danger:hover { background: var(--reader-red); color: #fff; }
.fb-tb-sep { width: 1px; height: 22px; background: rgba(255,255,255,.16); margin: 0 6px; }
.fb-tb-counter {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: .74rem;
    color: #ECF5FB;
    padding: 0 10px;
    min-width: 78px;
    text-align: center;
    user-select: none;
    letter-spacing: .4px;
}
.fb-tb-zoomval {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: .68rem;
    color: #C6C6C6;
    min-width: 44px;
    text-align: center;
    user-select: none;
}
/* Caption de nombre de página (pageNumberCaption) */
.fb-page-caption {
    position: fixed;
    left: 50%;
    bottom: 58px;
    transform: translateX(-50%);
    z-index: 8990;
    font-family: 'Montserrat', sans-serif;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: rgba(236,245,251,.75);
    background: rgba(0,0,0,.62);
    padding: 5px 16px;
    border-radius: 20px;
    pointer-events: none;
    max-width: 80vw;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ---------- Paneles laterales (TOC / miniaturas / búsqueda) ---------- */
.fb-panel {
    position: fixed;
    top: 0; bottom: 0;
    width: 320px;
    max-width: 86vw;
    background: rgba(12,16,26,.97);
    border-right: 1px solid rgba(255,255,255,.1);
    z-index: 9500;
    transform: translateX(-100%);
    transition: transform .3s cubic-bezier(.4,0,.2,1);
    display: flex;
    flex-direction: column;
    box-shadow: 8px 0 30px rgba(0,0,0,.5);
}
.fb-panel.right { left: auto; right: 0; border-right: 0; border-left: 1px solid rgba(255,255,255,.1); transform: translateX(100%); box-shadow: -8px 0 30px rgba(0,0,0,.5); }
.fb-panel.open { transform: translateX(0); }
.fb-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255,255,255,.1);
    flex-shrink: 0;
}
.fb-panel-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: .74rem;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: #ECF5FB;
    margin: 0;
}
.fb-panel-close {
    background: transparent; border: 0; color: #C6C6C6;
    cursor: pointer; font-size: 1rem; padding: 4px 8px; border-radius: 4px;
}
.fb-panel-close:hover { background: rgba(255,255,255,.12); color: #fff; }
.fb-panel-body { overflow-y: auto; padding: 10px; flex: 1; }

/* TOC (tableofcontent_form) */
.fb-toc-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 5px;
    cursor: pointer;
    color: #cfd8e6;
    font-size: .78rem;
    font-family: 'Montserrat', sans-serif;
    transition: background .16s, color .16s;
}
.fb-toc-item:hover { background: rgba(255,255,255,.08); color: #fff; }
.fb-toc-item.active { background: var(--reader-red); color: #fff; font-weight: 700; }
.fb-toc-num {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: .64rem;
    background: rgba(255,255,255,.12);
    color: #ECF5FB;
    border-radius: 3px;
    padding: 2px 6px;
    min-width: 26px;
    text-align: center;
}
.fb-toc-item.active .fb-toc-num { background: rgba(0,0,0,.3); color: #fff; }

/* Miniaturas verticales dentro del panel (thumbnailColor #333, alpha 70) */
.fb-vthumb {
    display: flex;
    gap: 11px;
    align-items: center;
    padding: 7px;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 6px;
    border: 2px solid transparent;
    transition: background .16s, border-color .16s;
}
.fb-vthumb:hover { background: rgba(255,255,255,.07); }
.fb-vthumb.active { border-color: var(--reader-red); background: rgba(215,25,32,.12); }
.fb-vthumb .vt-frame {
    width: 54px; height: 75px;
    flex-shrink: 0;
    border-radius: 3px;
    overflow: hidden;
    background: #E8E8E8;
    position: relative;
}
.fb-vthumb .vt-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
.fb-vthumb .vt-label {
    font-family: 'Montserrat', sans-serif;
    font-size: .72rem;
    color: #cfd8e6;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.fb-vthumb.active .vt-label { color: #fff; }

/* Búsqueda (search_form; searchKeywordFontColor #FFB000) */
.fb-search-box { padding: 14px 16px; border-bottom: 1px solid rgba(255,255,255,.1); }
.fb-search-field {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 5px;
    padding: 8px 11px;
}
.fb-search-field i { color: #C6C6C6; font-size: .82rem; }
.fb-search-field input {
    flex: 1; background: transparent; border: 0; outline: 0;
    color: #ECF5FB; font-family: 'Montserrat', sans-serif; font-size: .8rem;
}
.fb-search-field input::placeholder { color: #8894a8; }
.fb-search-info {
    font-family: 'Montserrat', sans-serif;
    font-size: .66rem;
    color: #8894a8;
    margin-top: 8px;
}
/* searchHightlightColor #ffff00 */
.fb-search-hit { background: #ffff00; color: #000; border-radius: 2px; padding: 0 1px; }
.fb-search-kw { color: #FFB000; font-weight: 800; }

/* Modo selección de texto (SelectTextButtonVisible) */
.fb-select-mode .fb-page-inner { user-select: text; cursor: text; }
.fb-select-mode .fb-page-inner * { pointer-events: auto !important; }

/* ---------- Lomo y encuadernación (saddle / stitch) ---------- */
/* Sustituye el lomo rojo previo por el plano de FlipBuilder */
.fb-spine {
    width: 10px;
    background: linear-gradient(to right,
        rgba(0,0,0,0.42) 0%,
        rgba(0,0,0,0.16) 26%,
        rgba(255,255,255,0.55) 47%,
        rgba(255,255,255,0.55) 53%,
        rgba(0,0,0,0.16) 74%,
        rgba(0,0,0,0.42) 100%
    );
}
/* Anula el tinte rojo que el diseño anterior pintaba sobre el lomo */
.fb-spine::after { content: none !important; }
/* .stitch: costura fina + pliegue junto al lomo */
.fb-spine::before {
    content: '';
    position: absolute;
    top: 0; bottom: 0;
    left: 50%;
    width: 1px;
    transform: translateX(-50%);
    background: linear-gradient(to bottom,
        rgba(60,60,40,0.35), rgba(120,120,90,0.15));
    z-index: 4;
}
/* .saddle: sombra suave proyectada sobre la hoja izquierda */
.fb-page-left::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0; right: 0;
    width: 87px;
    pointer-events: none;
    z-index: 5;
    background: linear-gradient(to left,
        rgba(0,0,0,0.42) 0%,
        rgba(0,0,0,0.18) 38%,
        rgba(0,0,0,0.05) 70%,
        rgba(0,0,0,0) 100%
    );
}
/* .cover_shadow.flip_x hard_right_border: filo de 3px en el borde externo */
.fb-page-right::after {
    content: '';
    position: absolute;
    top: 1px; bottom: 0; right: 0;
    width: 3px;
    pointer-events: none;
    z-index: 5;
    background: linear-gradient(to left, rgba(220,220,180,.55), rgba(220,220,180,0));
}
/* ShowTopLeftShadow: sombra superior de la hoja izquierda */
.fb-page-left::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 14px;
    pointer-events: none;
    z-index: 5;
    background: linear-gradient(to bottom, rgba(0,0,0,.28), rgba(0,0,0,0));
}

/* ---------- Asimetría de sombras: L=90/0.6  R=55/0.6 ---------- */
.fb-page-left {
    border-radius: 8px 0 0 8px;
    box-shadow:
        -18px 0 42px rgba(0,0,0,.6),
        -4px 0 10px rgba(0,0,0,.35),
        inset 0 14px 18px -12px rgba(0,0,0,.35);
}
.fb-page-right {
    border-radius: 0 8px 8px 0;
    box-shadow:
        12px 0 26px rgba(0,0,0,.6),
        inset 0 14px 18px -12px rgba(0,0,0,.3);
}
.fb-book { filter: drop-shadow(0 30px 60px rgba(0,0,0,.6)); }

/* ----------.Page curl (CurlingPageCorner = Yes) ---------- */
.fb-curl {
    position: absolute;
    bottom: 0; right: 0;
    width: 62px; height: 62px;
    z-index: 8;
    cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='26' height='26'%3E%3Cpath d='M2 24 L24 2 L24 24 Z' fill='rgba(0,0,0,.55)'/%3E%3C/svg%3E") 2 24, pointer;
    background: linear-gradient(315deg,
        rgba(0,0,0,.30) 0%, rgba(0,0,0,.10) 26%,
        rgba(255,255,255,0) 52%);
    border-bottom-right-radius: 8px;
    opacity: .55;
    transition: opacity .2s;
}
.fb-curl::after {
    content: '';
    position: absolute;
    bottom: 8px; right: 8px;
    width: 0; height: 0;
    border-style: solid;
    border-width: 0 0 13px 13px;
    border-color: transparent transparent rgba(0,0,0,.42) transparent;
}
.fb-curl:hover { opacity: 1; }
.fb-page.curl-dragging .fb-curl { opacity: 1; }
.fb-page.curl-dragging { transition: none; }

/* Modo página única (DoubleSinglePageButtonVisible) */
.fb-wrapper.fb-single-mode .fb-page-left { display: none; }
.fb-wrapper.fb-single-mode .fb-spine { display: none; }
/* al haber una sola hoja, puede ocupar más ancho */
.fb-wrapper.fb-single-mode .fb-page {
    width: min(78vw, calc((100vh - 190px) * 0.7179));
}
.fb-wrapper.fb-single-mode .fb-page-right { border-radius: 8px; }
.fb-wrapper.fb-single-mode .fb-page-right::before { display: none; }

/* Zoom */
.fb-book { transition: transform .25s ease; }
.fb-stage { overflow: visible; }

@media (max-width: 768px) {
    .fb-tb-btn { width: 34px; height: 34px; font-size: .85rem; }
    .fb-tb-sep { margin: 0 3px; }
    .fb-tb-counter { min-width: 58px; padding: 0 5px; font-size: .68rem; }
    .fb-page-caption { bottom: 54px; font-size: .6rem; }
    .fb-panel { width: 88vw; }
}
.fb-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    flex-wrap: wrap;
}
.fb-ctrl-btn {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.14);
    color: #94a3b8;
    padding: 7px 16px;
    border-radius: 4px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 0.68rem;
    cursor: pointer;
    transition: all 0.18s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    text-decoration: none;
}
.fb-ctrl-btn:hover {
    background: rgba(255,255,255,0.16);
    color: #fff;
    border-color: rgba(255,255,255,0.28);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.fb-ctrl-btn:disabled {
    opacity: 0.28;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.fb-ctrl-btn.danger {
    background: rgba(215,25,32,0.8);
    border-color: rgba(215,25,32,0.6);
    color: #fff;
}
.fb-ctrl-btn.danger:hover { background: #c01118; }
.fb-page-counter {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 0.75rem;
    color: #64748b;
    padding: 0 4px;
    min-width: 72px;
    text-align: center;
    user-select: none;
}

/* ======================== MINIATURAS ======================== */
.fb-thumbnails {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding: 4px 4px 6px;
    width: 100%;
    max-width: 900px;
    justify-content: center;
    flex-wrap: nowrap;
    scrollbar-width: thin;
    scrollbar-color: rgba(215,25,32,0.4) transparent;
}
.fb-thumbnails::-webkit-scrollbar { height: 3px; }
.fb-thumbnails::-webkit-scrollbar-thumb { background: rgba(215,25,32,0.4); border-radius: 2px; }
.fb-thumb {
    flex-shrink: 0;
    width: 52px;
    height: 72px;
    border-radius: 2px;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,0.1);
    cursor: pointer;
    transition: all 0.18s;
    background: #1e293b;
    position: relative;
}
.fb-thumb:hover { border-color: rgba(215,25,32,0.5); transform: scale(1.06) translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.5); }
.fb-thumb.active { border-color: var(--reader-red); box-shadow: 0 0 16px rgba(215,25,32,0.5); }
.fb-thumb-frame {
    width: 100%; height: 100%;
    overflow: hidden;
    position: relative;
}
.fb-thumb-frame > div {
    width: 794px;
    height: 1123px;
    transform-origin: top left;
    transform: scale(0.065);
    pointer-events: none;
}
.fb-thumb-num {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: rgba(0,0,0,0.5);
    color: rgba(255,255,255,0.7);
    font-family: 'Montserrat',sans-serif;
    font-size: 0.38rem;
    font-weight: 800;
    text-align: center;
    padding: 1px;
    letter-spacing: 0.3px;
}
.fb-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.fb-thumb-no-img {
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 2px;
    background: linear-gradient(135deg, #1e3a5f, #0b1f3a);
    color: rgba(255,255,255,0.35);
}
.fb-thumb-no-img span { font-size: 0.38rem; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase; }

/* ======================== ZOOM HINT ======================== */
.fb-zoom-hint {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.6rem;
    color: rgba(255,255,255,0.25);
    text-align: center;
    letter-spacing: 0.3px;
}

/* ======================== RESPONSIVE ======================== */
/* El ancho de la hoja ya se calcula con min(46vw, alto disponible), así que
   aquí solo se corrige el alto disponible según el espacio que ocupa la UI. */
@media (max-width: 900px) {
    /* más espacio vertical porque el header y la toolbar ocupan más */
    .fb-page { width: min(46vw, calc((100vh - 215px) * 0.7179)); }
    .fb-wrapper { padding: 12px 6px 20px; gap: 10px; }
}
@media (max-width: 840px) {
    .fb-page {
        width: min(44vw, calc((100vh - 215px) * 0.7179));
        min-width: 200px;
    }
    /* la sombra del lomo (87px) se reduce junto con la hoja */
    .fb-page-left::after { width: 52px; }
    .fb-spine { width: 8px; }
}
@media (max-width: 560px) {
    /* Sólo una página en pantallas pequeñas: aquí SÍ es móvil */
    .fb-book { flex-direction: column; }
    .fb-page { width: 90vw; aspect-ratio: auto; min-height: 70vw; max-height: none; }
    .fb-spine { width: 100%; height: 6px; }
    .fb-page-left { border-radius: 8px 8px 0 0; border-right: none; }
    .fb-page-right { border-radius: 0 0 8px 8px; border-left: none; }
}

/* ============================================================
   PÁGINA DE DIARIO RENDERIZADA (contenido real, no imagen)
   Renderizada por resources/views/periodico/partials/pagina.blade.php
   ============================================================ */
.np-page {
    width: 100%;
    min-height: 100%;
    background: #fff;
    color: #14181D;
    font-family: 'Source Serif 4', Georgia, serif;
    font-size: 0.62rem;
    line-height: 1.38;
    padding: 14px 15px 10px;
    text-align: left;
    user-select: text;
    -webkit-user-select: text;
}

/* La columna izquierda del libro se refleja (efecto spine) */
.fb-page-left .np-page { box-shadow: inset -8px 0 14px -10px rgba(0,0,0,0.25); }
.fb-page-right .np-page { box-shadow: inset 8px 0 14px -10px rgba(0,0,0,0.25); }

/* --- Páginas maquetadas con el editor visual (frames absolutos) ---
   El lienzo se construye a 720x1040 px reales, igual que en el admin, y se
   escala con transform en el contenedor padre del libro al 100% de la hoja. */
.np-page.np-has-frames {
    width: 720px;
    height: 1040px;
    padding: 0;
    margin: 0;
    overflow: hidden;
    position: relative;
    background: #ffffff;
}
.np-canvas-wrap {
    position: relative;
    width: 720px;
    height: 1040px;
    margin: 0;
    padding: 0;
}
.np-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 720px;
    height: 1040px;
    transform-origin: top left;
    transform: none !important;
    overflow: hidden;
}
.np-frame { position: absolute; box-sizing: border-box; overflow: hidden; }
.np-ftext { font-family: 'Source Sans 3', sans-serif; font-size: 11px; line-height: 1.45; color: #1e293b; }
.np-ftext p { margin: 0 0 6px; }
.np-fquote { font-family: 'Source Serif 4', Georgia, serif; font-style: italic; font-size: 15px; line-height: 1.35; color: #334155; }
.np-fimg { width: 100%; height: 100%; object-fit: cover; display: block; }
.np-frame-headline .np-ftext { font-family: 'Oswald', sans-serif; font-size: 28px; font-weight: 700; line-height: 1.1; color: #0f172a; }
.np-frame-image, .np-frame-qr, .np-frame-ad, .np-frame-masthead { overflow: hidden; }

/* --- Masthead --- */
.np-masthead { text-align: center; border-bottom: 2px solid #0B1F3A; padding-bottom: 7px; margin-bottom: 9px; }
.np-slogan { font-family: 'Montserrat', sans-serif; font-size: 0.44rem; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase; color: #D71920; }
.np-logo {
    font-family: 'Anton', sans-serif; font-size: 2.3rem; line-height: 1;
    color: #0B1F3A; letter-spacing: 1.5px; margin: 2px 0 1px;
}
.np-logo span { color: #D71920; }
.np-sub { font-family: 'Montserrat', sans-serif; font-size: 0.5rem; font-weight: 600; letter-spacing: 2.2px; text-transform: uppercase; color: #0B1F3A; }
.np-meta {
    display: flex; justify-content: space-between; align-items: center;
    border-top: 1px solid #C8CED6; margin-top: 5px; padding-top: 3px;
    font-family: 'Montserrat', sans-serif; font-size: 0.44rem; color: #3A4450;
}
.np-meta-city { font-weight: 800; color: #0B1F3A; text-transform: uppercase; }

/* --- Folio de páginas interiores --- */
.np-folio {
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1.5px solid #0B1F3A; padding-bottom: 4px; margin-bottom: 9px;
    font-family: 'Montserrat', sans-serif;
}
.np-folio-brand { font-size: 0.72rem; font-weight: 800; color: #0B1F3A; letter-spacing: 0.5px; }
.np-folio-brand span { color: #D71920; }
.np-folio-sec { font-size: 0.55rem; font-weight: 800; color: #D71920; text-transform: uppercase; letter-spacing: 1px; }
.np-folio-num { font-size: 0.42rem; color: #5A6470; }

/* --- Títulos y kickers --- */
.np-kicker { font-family: 'Montserrat', sans-serif; font-size: 0.44rem; font-weight: 800; letter-spacing: 0.7px; text-transform: uppercase; color: #D71920; }
.np-titular-main {
    font-family: 'Montserrat', sans-serif; font-size: 1.32rem; font-weight: 900;
    line-height: 1.04; color: #0B1F3A; margin: 2px 0 6px;
}
.np-titular { font-family: 'Montserrat', sans-serif; font-size: 0.95rem; font-weight: 900; line-height: 1.1; color: #0B1F3A; margin: 2px 0 4px; }
.np-titular-sm { font-family: 'Montserrat', sans-serif; font-size: 0.76rem; font-weight: 800; line-height: 1.14; color: #0B1F3A; margin: 2px 0 4px; }
.np-bajada { font-size: 0.68rem; line-height: 1.32; color: #3A4450; margin-bottom: 5px; }
.np-destacado { font-family: 'Montserrat', sans-serif; font-size: 0.48rem; font-weight: 800; color: #D71920; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 1px; }

/* --- Figuras --- */
.np-fig { margin: 0 0 7px; }
.np-fig img { width: 100%; height: 88px; object-fit: cover; border: 1px solid #C8CED6; display: block; }
.np-fig figcaption { font-family: 'Montserrat', sans-serif; font-size: 0.42rem; line-height: 1.3; color: #5A6470; border-top: 1px solid #DDE2E8; margin-top: 3px; padding-top: 3px; }
.np-fig figcaption strong { display: block; color: #0B1F3A; font-size: 0.56rem; margin-bottom: 2px; }

/* --- Columnas --- */
.np-cols { display: flex; gap: 9px; align-items: flex-start; }
.np-col { flex: 1; min-width: 0; }
.np-col + .np-col, .np-cols > .np-col ~ .np-col { border-left: 1px solid #DDE2E8; padding-left: 9px; }
.np-col p { margin-bottom: 5px; text-align: justify; hyphens: auto; }

/* --- Distribución dos columnas --- */
.np-two { display: flex; gap: 10px; align-items: flex-start; }
.np-two-main { flex: 1 1 62%; min-width: 0; }
.np-two-side { flex: 0 1 36%; min-width: 0; border-left: 1px solid #DDE2E8; padding-left: 10px; }

/* --- Notas laterales --- */
.np-note { display: flex; gap: 7px; margin-bottom: 8px; }
.np-note img { width: 54px; height: 40px; object-fit: cover; border: 1px solid #C8CED6; flex-shrink: 0; }
.np-note-body { min-width: 0; }
.np-note h4 { font-family: 'Montserrat', sans-serif; font-size: 0.6rem; font-weight: 800; line-height: 1.14; color: #14181D; margin: 2px 0 2px; }
.np-note p { font-size: 0.52rem; line-height: 1.32; color: #232A32; text-align: justify; }
.np-tag { display: inline-block; font-family: 'Montserrat', sans-serif; font-size: 0.4rem; font-weight: 800; letter-spacing: 0.4px; text-transform: uppercase; color: #fff; background: #0B1F3A; padding: 1px 5px; }

/* --- Cajas de servicio --- */
.np-box { border: 1px solid #C8CED6; background: #FAFBFC; padding: 6px; margin-bottom: 7px; }
.np-box img { width: 100%; height: 52px; object-fit: cover; margin: 3px 0; }
.np-box h4 { font-family: 'Montserrat', sans-serif; font-size: 0.6rem; font-weight: 800; line-height: 1.16; color: #14181D; margin: 2px 0; }
.np-box p { font-size: 0.5rem; line-height: 1.32; color: #232A32; text-align: justify; }
.np-box-title { font-family: 'Montserrat', sans-serif; font-size: 0.48rem; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase; color: #0B1F3A; border-bottom: 1px solid #0B1F3A; padding-bottom: 2px; margin-bottom: 4px; }
.np-row { display: flex; justify-content: space-between; font-family: 'Montserrat', sans-serif; font-size: 0.48rem; color: #5A6470; padding: 1px 0; }
.np-row b { color: #0B1F3A; font-weight: 800; }

/* --- Cita --- */
.np-quote {
    border-left: 2.5px solid #D71920; background: #F6F7F9;
    padding: 6px 8px; margin: 0 0 6px;
    font-style: italic; font-size: 0.6rem; line-height: 1.36; color: #0B1F3A;
}

/* --- Reglas, refs, staff --- */
.np-rule { border-top: 1px solid #C8CED6; margin: 7px 0; }
.np-rule-strong { border-top: 2px solid #0B1F3A; }
.np-ref { font-family: 'Montserrat', sans-serif; font-size: 0.42rem; font-weight: 800; color: #D71920; text-transform: uppercase; letter-spacing: 0.3px; margin-top: 2px; }
.np-staff { display: flex; gap: 12px; font-family: 'Montserrat', sans-serif; font-size: 0.42rem; line-height: 1.5; color: #3A4450; }
.np-staff > div { flex: 1; }
.np-staff b { color: #0B1F3A; }
.np-staff-legal { color: #8A93A0; }
.np-legal { font-family: 'Montserrat', sans-serif; font-size: 0.38rem; color: #8A93A0; text-align: center; border-top: 1px solid #C8CED6; margin-top: 8px; padding-top: 4px; }

/* Sin paginación forzada: el contenido fluye dentro de la hoja */
.np-page p, .np-page h1, .np-page h2, .np-page h3, .np-page h4, .np-page figure { page-break-inside: avoid; }
</style>

<div class="container py-3">
    <!-- CONTENEDOR PRINCIPAL DEL LECTOR -->
    <div class="newspaper-reader-container">
        {{-- HEADER DE CONTROLES --}}
        {{-- CABECERA: solo identidad de la edición y selector.
             La navegación vive en la toolbar inferior estilo FlipBuilder. --}}
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
                @if(count($ediciones) > 1)
                    <div class="dropdown">
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
                <a href="{{ route('periodico.public.pdf', $edicionActiva['id']) }}"
                   class="btn btn-sm btn-danger"
                   title="Descargar PDF"
                   download>
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
            </div>
        </div>

        {{-- ================================================
             FLIPBOOK CSS3 PROPIO (sin CDN)
             Extrae la primera imagen de cada página del periódico
             ================================================ --}}
        @php
            // Construir array de páginas con imágenes para el flipbook
            $fbPages = [];
            foreach (($edicionActiva['paginas'] ?? []) as $pg) {
                $imgSrc = null;
                // Buscar imagen en frames
                foreach (($pg['frames'] ?? []) as $fr) {
                    if (($fr['type'] ?? '') === 'image' && !empty($fr['src'])) {
                        $imgSrc = $fr['src'];
                        break;
                    }
                }
                // Fallback: noticia_central imagen
                if (!$imgSrc && !empty($pg['noticia_central']['imagen'])) {
                    $imgSrc = $pg['noticia_central']['imagen'];
                }
                // Fallback: articulo_principal imagen
                if (!$imgSrc && !empty($pg['articulo_principal']['imagen'])) {
                    $imgSrc = $pg['articulo_principal']['imagen'];
                }
                $fbPages[] = [
                    'img'    => $imgSrc,
                    'nombre' => $pg['nombre'] ?? ('Pág. ' . ($pg['numero'] ?? (count($fbPages)+1))),
                    'numero' => $pg['numero'] ?? (count($fbPages)+1),
                ];
            }
        @endphp

        {{-- ============================================================
             CONTENIDO REAL DE CADA PÁGINA (renderizado en el servidor)
             El flipbook 3D clona estas hojas, por lo que se ve el
             periódico tal como lo compuso el administrador: titulares,
             columnas, fotos, notas, clima y cotizaciones.
             ============================================================ --}}
        <div id="fbPagesStore" aria-hidden="true"
             style="position:absolute;width:0;height:0;overflow:hidden;opacity:0;pointer-events:none;left:-9999px;">
            @foreach ($edicionActiva['paginas'] ?? [] as $pgIdx => $pgData)
                <div class="fb-page-source" data-fb-index="{{ $pgIdx }}">
                    @include('periodico.partials.pagina', [
                        'pg'       => $pgData,
                        'edicion'  => $edicionActiva,
                        'masthead' => ($pgData['tipo'] ?? '') === 'portada',
                    ])
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════
     PANTALLA DE CARGA (loadingBackground #1F2232)
     ══════════════════════════════════════════════════════ --}}
    <div id="fbLoadingScreen">
        <img src="/images/Logo.jpg" alt="" class="fls-logo" onerror="this.style.display='none'">
        <div class="fls-caption">{{ $edicionActiva['titulo'] ?? 'Latitud 18' }} — {{ $edicionActiva['fecha'] ?? '' }}</div>
        <div class="fls-bar"><span></span></div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         PANELES LATERALES: ÍNDICE, MINIATURAS Y BÚSQUEDA
         ══════════════════════════════════════════════════════ --}}
    <div class="fb-panel" id="fbTocPanel">
        <div class="fb-panel-head">
            <h3 class="fb-panel-title">Índice de la edición</h3>
            <button class="fb-panel-close" onclick="fbTogglePanel('fbTocPanel')" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="fb-panel-body">
            @foreach($fbPages as $tIdx => $tp)
                <div class="fb-toc-item {{ $tIdx === 0 ? 'active' : '' }}" id="fb-toc-{{ $tIdx }}"
                     onclick="fbGoToSpread({{ $tIdx }}); fbTogglePanel('fbTocPanel');">
                    <span class="fb-toc-num">{{ $tp['numero'] }}</span>
                    <span>{{ $tp['nombre'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="fb-panel" id="fbThumbPanel">
        <div class="fb-panel-head">
            <h3 class="fb-panel-title">Miniaturas</h3>
            <button class="fb-panel-close" onclick="fbTogglePanel('fbThumbPanel')" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="fb-panel-body">
            @foreach($fbPages as $tIdx => $tp)
                <div class="fb-vthumb {{ $tIdx === 0 ? 'active' : '' }}" id="fb-vthumb-{{ $tIdx }}"
                     onclick="fbGoToSpread({{ $tIdx }}); fbTogglePanel('fbThumbPanel');">
                    <div class="vt-frame" id="fb-thumb-frame-{{ $tIdx }}">
                        @if($tp['img'])
                            <img src="{{ $tp['img'] }}" alt="{{ $tp['nombre'] }}" loading="lazy">
                        @else
                            <span class="fb-thumb-no-img">
                                <i class="fas fa-file-alt"></i>
                            </span>
                        @endif
                    </div>
                    <span class="vt-label">{{ $tp['nombre'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="fb-panel right" id="fbSearchPanel">
        <div class="fb-panel-head">
            <h3 class="fb-panel-title">Buscar en el periódico</h3>
            <button class="fb-panel-close" onclick="fbTogglePanel('fbSearchPanel')" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="fb-search-box">
            <div class="fb-search-field">
                <i class="fas fa-search"></i>
                <input type="search" id="fbSearchInput" placeholder="Mínimo 3 letras…"
                       autocomplete="off" oninput="fbSearch(this.value)">
                <button class="fb-panel-close" onclick="fbClearSearch()" title="Limpiar" aria-label="Limpiar">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
            <div class="fb-search-info" id="fbSearchInfo">Escribe al menos 3 caracteres.</div>
        </div>
        <div class="fb-panel-body" id="fbSearchResults"></div>
    </div>

        <div class="fb-wrapper active" id="flipbookSection">
          {{-- Escenario del libro (dos páginas: izquierda + derecha) --}}
          <div class="fb-stage">
            <div class="fb-book" id="fbBook">
              <div class="fb-page fb-page-left" id="fbPageLeft">
                <div class="fb-page-inner" id="fbInnerLeft"></div>
              </div>
              <div class="fb-spine"></div>
              <div class="fb-page fb-page-right" id="fbPageRight">
                <div class="fb-page-inner" id="fbInnerRight"></div>
                {{-- CurlingPageCorner: esquina para pasar la página --}}
                <div class="fb-curl" id="fbCurl" title="Arrastra para pasar la página"></div>
              </div>
            </div>
          </div>

          {{-- Toolbar inferior estilo FlipBuilder (toolbarColor #000) --}}
          <div class="fb-toolbar" id="fbToolbar">
              <button class="fb-tb-btn" onclick="fbTogglePanel('fbTocPanel')" title="Índice / Tabla de contenido">
                  <i class="fas fa-list-ul"></i>
              </button>
              <button class="fb-tb-btn" onclick="fbTogglePanel('fbThumbPanel')" title="Miniaturas">
                  <i class="fas fa-th"></i>
              </button>
              <button class="fb-tb-btn" onclick="fbTogglePanel('fbSearchPanel')" title="Buscar en el periódico (mín. 3 letras)">
                  <i class="fas fa-search"></i>
              </button>

              <span class="fb-tb-sep"></span>

              <button class="fb-tb-btn" id="fbBtnPrevTb" onclick="fbPrevSpread()" title="Anterior">
                  <i class="fas fa-chevron-left"></i>
              </button>
              <span class="fb-tb-counter" id="fbCounterTb">1 / {{ count($fbPages) }}</span>
              <button class="fb-tb-btn" id="fbBtnNextTb" onclick="fbNextSpread()" title="Siguiente">
                  <i class="fas fa-chevron-right"></i>
              </button>

              <span class="fb-tb-sep"></span>

              <button class="fb-tb-btn" onclick="fbZoomOut()" title="Alejar">
                  <i class="fas fa-search-minus"></i>
              </button>
              <span class="fb-tb-zoomval" id="fbZoomLabel">100%</span>
              <button class="fb-tb-btn" onclick="fbZoomIn()" title="Acercar">
                  <i class="fas fa-search-plus"></i>
              </button>

              <span class="fb-tb-sep"></span>

              <button class="fb-tb-btn" id="fbBtnSingle" onclick="fbToggleSingleMode()" title="Vista de página individual">
                  <i class="fas fa-file-alt"></i>
              </button>
              <button class="fb-tb-btn active" id="fbBtnDouble" onclick="fbToggleSingleMode()" title="Vista de doble página">
                  <i class="fas fa-book-open"></i>
              </button>
              <button class="fb-tb-btn" id="fbBtnSelect" onclick="fbToggleSelectMode()" title="Seleccionar texto">
                  <i class="fas fa-font"></i>
              </button>

              <span class="fb-tb-sep"></span>

              <a href="{{ route('periodico.public.pdf', $edicionActiva['id']) }}"
                 rel="noopener" class="fb-tb-btn danger" download
                 title="Descargar PDF">
                  <i class="fas fa-file-pdf"></i>
              </a>
              <button class="fb-tb-btn" onclick="readerToggleFullscreen()" title="Pantalla completa (F)">
                  <i class="fas fa-expand" id="fbFsIconTb"></i>
              </button>
          </div>

          <div class="fb-page-caption" id="fbPageCaption"></div>
        </div>

        {{-- El contenido de las páginas ya se renderiza en #fbPagesStore --}}

        {{-- LECTOR EDITORIAL: visor HTML de páginas.
             OCULTO PERMANENTEMENTE: el periódico público se muestra en una sola
             forma, el flipbook 3D. Este bloque se conserva inerte para no romper
             los scripts que lo referencian, pero nunca es visible ni accesible. --}}
        <div id="editorialSection" style="display:none !important;" aria-hidden="true">

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

{{-- SIN CDN: Flipbook CSS3 propio con JS vanilla --}}
<script>
// ============================================================
// MODO ÚNICO DE LECTURA: siempre flipbook 3D
// El periódico se muestra en una sola forma. Cualquier llamada
// heredada a switchReaderMode() se ignora y asegura el flipbook.
// ============================================================
function switchReaderMode(mode) {
    const flipbookSection = document.getElementById('flipbookSection');
    const editorialSection = document.getElementById('editorialSection');

    if (flipbookSection) {
        flipbookSection.classList.add('active');
        flipbookSection.style.setProperty('display', 'flex', 'important');
    }
    if (editorialSection) {
        editorialSection.style.setProperty('display', 'none', 'important');
    }
}

// ============================================================
// FLIPBOOK CSS3: MOTOR DE ANIMACIÓN
// ============================================================
(function() {
    // Hojas renderizadas en el servidor: el contenido real del periódico
    var fbStore = document.getElementById('fbPagesStore');
    var fbSources = fbStore ? Array.prototype.slice.call(fbStore.querySelectorAll('.fb-page-source')) : [];
    if (!fbSources.length) return;

    var fbCurrentSpread = 0; // índice de la página izquierda actual
    var fbAnimating = false;
    var fbTotal = fbSources.length;

    var elLeft    = document.getElementById('fbPageLeft');
    var elRight   = document.getElementById('fbPageRight');
    var elInnerL  = document.getElementById('fbInnerLeft');
    var elInnerR  = document.getElementById('fbInnerRight');
    var elCounter = document.getElementById('fbCounter');
    var elBtnPrev = document.getElementById('fbBtnPrev');
    var elBtnNext = document.getElementById('fbBtnNext');
    var elBtnFirst= document.getElementById('fbBtnFirst');
    var elBtnLast = document.getElementById('fbBtnLast');

    // Calcula el scale para que 720px de contenido quepan al 100% en el ancho del slot
    function getPageScale(slot) {
        if (!slot) return 1;
        var w = slot.clientWidth || (slot.getBoundingClientRect ? slot.getBoundingClientRect().width : 0);
        if (!w || w < 50) {
            var maxH = Math.max(300, (window.innerHeight || 800) - 170);
            var expectedW = Math.min((window.innerWidth || 1200) * 0.46, maxH * (720 / 1040));
            w = expectedW > 100 ? expectedW : 480;
        }
        return w / 720;
    }

    // Clona la página real y la monta con scale dentro del inner
    function mountPage(inner, slot, index) {
        if (!slot) return;
        if (!inner) {
            inner = slot.querySelector('.fb-page-inner');
            if (!inner) {
                inner = document.createElement('div');
                inner.className = 'fb-page-inner';
                slot.insertBefore(inner, slot.firstChild);
            }
        }
        inner.innerHTML = '';
        var src = (index !== null && index !== undefined) ? fbSources[index] : null;
        if (!src) {
            inner.innerHTML = '<div class="fb-page-placeholder">'
                + '<span class="fp-name">LATITUD 18</span>'
                + '<span class="fp-sub">Información Sin Ruido</span>'
                + '</div>';
            return;
        }
        var clone = src.cloneNode(true);
        clone.removeAttribute('data-fb-index');
        clone.classList.remove('fb-page-source');
        // Aplicar scale para que el lienzo de 720x1040 llene exactamente el slot
        var sc = getPageScale(slot);
        clone.style.width = '720px';
        clone.style.height = '1040px';
        clone.style.position = 'absolute';
        clone.style.top = '0';
        clone.style.left = '0';
        clone.style.transformOrigin = 'top left';
        clone.style.transform = 'scale(' + sc + ')';
        inner.appendChild(clone);
        // Forzar carga de imágenes lazy
        inner.querySelectorAll('img[loading="lazy"]').forEach(function(img) {
            img.loading = 'eager';
            if (img.dataset.src) { img.src = img.dataset.src; }
        });
    }

    // Llena las miniaturas con el contenido escalado (proporción 720px)
    function fillThumbnails() {
        fbSources.forEach(function(src, i) {
            var frame = document.getElementById('fb-thumb-frame-' + i) || document.querySelector('#fb-vthumb-' + i + ' .vt-frame');
            if (!frame || !src) return;
            var clone = src.cloneNode(true);
            clone.removeAttribute('data-fb-index');
            clone.classList.remove('fb-page-source');
            clone.style.width = '720px';
            clone.style.height = '1040px';
            clone.style.position = 'absolute';
            clone.style.top = '0';
            clone.style.left = '0';
            clone.style.transformOrigin = 'top left';
            var thumbW = frame.clientWidth || 54;
            clone.style.transform = 'scale(' + (thumbW / 720) + ')';
            clone.style.pointerEvents = 'none';
            frame.innerHTML = '';
            frame.appendChild(clone);
        });
    }

    // Reajusta scales al redimensionar ventana o al terminar el render
    function reapplyScales() {
        if (!elLeft || !elRight) return;
        var scL = getPageScale(elLeft);
        var scR = getPageScale(elRight);
        var inL = elInnerL || elLeft.querySelector('.fb-page-inner');
        var inR = elInnerR || elRight.querySelector('.fb-page-inner');
        [ { inner: inL, sc: scL }, { inner: inR, sc: scR } ].forEach(function(item) {
            if (!item.inner) return;
            var child = item.inner.firstElementChild;
            if (child && !child.classList.contains('fb-page-placeholder')) {
                child.style.width = '720px';
                child.style.height = '1040px';
                child.style.position = 'absolute';
                child.style.top = '0';
                child.style.left = '0';
                child.style.transformOrigin = 'top left';
                child.style.transform = 'scale(' + item.sc + ')';
            }
        });
    }
    window.addEventListener('resize', reapplyScales);
    window.addEventListener('load', reapplyScales);
    document.addEventListener('DOMContentLoaded', reapplyScales);
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(reapplyScales);
    }
    if (window.ResizeObserver) {
        var ro = new ResizeObserver(function() { reapplyScales(); });
        if (elLeft) ro.observe(elLeft);
        if (elRight) ro.observe(elRight);
    }
    setTimeout(reapplyScales, 50);
    setTimeout(reapplyScales, 150);
    setTimeout(reapplyScales, 350);
    setTimeout(reapplyScales, 700);

    /* Asegura que el canvas interno conserve su escala 1:1 dentro del contenedor clonado */
    function fitCanvases() {
        document.querySelectorAll('.fb-page-inner .np-canvas').forEach(function (canvas) {
            canvas.style.transform = 'none';
        });
    }
    window.fbFitCanvases = fitCanvases;

    function updateThumbs(idx) {
        document.querySelectorAll('.fb-thumb').forEach(function(th, i) {
            th.classList.toggle('active', i === idx || i === idx + 1);
        });
        // scroll miniatura activa
        var activeTh = document.getElementById('fb-thumb-' + idx);
        if (activeTh) activeTh.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    function renderSpread(idx, animate, direction) {
        // idx = índice de la página izquierda
        var hasLeft  = idx < fbTotal;
        var hasRight = (idx + 1) < fbTotal;

        if (!animate) {
            mountPage(elInnerL, elLeft, hasLeft  ? idx     : null);
            mountPage(elInnerR, elRight, hasRight ? idx + 1 : null);
            if (elLeft)  elLeft.style.display  = hasLeft  ? '' : 'none';
            if (elRight) elRight.style.display = hasRight ? '' : 'none';
            reapplyScales();
            if (window.fbFitCanvases) window.fbFitCanvases();
        } else {
            fbAnimating = true;
            if (direction === 'next') {
                if (elRight) { elRight.classList.add('flipping-out'); }
                if (elLeft)  { elLeft.classList.add('flipping-out');  }
            } else {
                if (elLeft)  { elLeft.classList.add('flipping-out-left');  }
                if (elRight) { elRight.classList.add('flipping-out-left'); }
            }

            setTimeout(function() {
                mountPage(elInnerL, elLeft,  hasLeft  ? idx     : null);
                mountPage(elInnerR, elRight, hasRight ? idx + 1 : null);
                reapplyScales();
                if (window.fbFitCanvases) window.fbFitCanvases();

                if (elLeft)  {
                    elLeft.className  = 'fb-page fb-page-left';
                    elLeft.style.display  = hasLeft ? '' : 'none';
                }
                if (elRight) {
                    elRight.className = 'fb-page fb-page-right';
                    elRight.style.display = hasRight ? '' : 'none';
                }

                requestAnimationFrame(function() {
                    if (direction === 'next') {
                        if (elLeft)  elLeft.classList.add('flipping-in');
                        if (elRight) elRight.classList.add('flipping-in');
                    } else {
                        if (elLeft)  elLeft.classList.add('flipping-in-left');
                        if (elRight) elRight.classList.add('flipping-in-left');
                    }
                    setTimeout(function() {
                        if (elLeft)  { elLeft.className  = 'fb-page fb-page-left'; }
                        if (elRight) { elRight.className = 'fb-page fb-page-right'; }
                        reapplyScales();
                        fbAnimating = false;
                    }, 400);
                });
            }, 200);
        }

        // Actualizar contador
        if (elCounter) {
            elCounter.textContent = hasRight
                ? (idx + 1) + '-' + (idx + 2) + ' / ' + fbTotal
                : (idx + 1) + ' / ' + fbTotal;
        }

        // Actualizar botones
        if (elBtnPrev)  elBtnPrev.disabled  = (idx <= 0);
        if (elBtnFirst) elBtnFirst.disabled = (idx <= 0);
        if (elBtnNext)  elBtnNext.disabled  = (idx + 2 >= fbTotal);
        if (elBtnLast)  elBtnLast.disabled  = (idx + 2 >= fbTotal);

        updateThumbs(idx);
        fbCurrentSpread = idx;

        // Sincronizar toolbar, caption, índice y miniaturas (FlipBuilder look)
        if (typeof fbSyncUi === 'function') fbSyncUi(idx);
    }

    window.fbGoToSpread = function(pageIdx) {
        if (fbAnimating) return;
        // Normalizar al spread (par)
        var spreadIdx = pageIdx % 2 === 0 ? pageIdx : pageIdx - 1;
        if (spreadIdx < 0) spreadIdx = 0;
        if (spreadIdx >= fbTotal) spreadIdx = fbTotal - (fbTotal % 2 === 0 ? 2 : 1);
        var dir = spreadIdx > fbCurrentSpread ? 'next' : 'prev';
        renderSpread(spreadIdx, spreadIdx !== fbCurrentSpread, dir);
    };

    window.fbNextSpread = function() {
        if (fbAnimating) return;
        var next = fbCurrentSpread + 2;
        if (next < fbTotal) renderSpread(next, true, 'next');
    };

    window.fbPrevSpread = function() {
        if (fbAnimating) return;
        var prev = fbCurrentSpread - 2;
        if (prev >= 0) renderSpread(prev, true, 'prev');
    };

    // Teclado: flechas para el flipbook
    document.addEventListener('keydown', function(e) {
        var flip = document.getElementById('flipbookSection');
        if (!flip || flip.style.display === 'none') return;
        // No interferir con la escritura dentro de la búsqueda o el modo selección
        var tag = (document.activeElement && document.activeElement.tagName) || '';
        if (tag === 'INPUT' || tag === 'TEXTAREA') return;
        if (document.getElementById('fbBook') &&
            document.getElementById('fbBook').classList.contains('fb-select-mode')) return;

        if (e.key === 'ArrowRight') { e.preventDefault(); fbNextSpread(); }
        if (e.key === 'ArrowLeft')  { e.preventDefault(); fbPrevSpread(); }
        if (e.key === 'Home')       { e.preventDefault(); fbGoToSpread(0); }
        if (e.key === 'End')        { e.preventDefault(); fbGoToSpread(fbTotal - 1); }
        if (e.key === '+' || e.key === '=') { e.preventDefault(); fbZoomIn(); }
        if (e.key === '-')         { e.preventDefault(); fbZoomOut(); }
        if (e.key === '0')         { e.preventDefault(); fbZoom = 1; fbApplyZoom(); }
        if (e.key === 'f' || e.key === 'F') { e.preventDefault(); readerToggleFullscreen(); }
        if (e.key === 't' || e.key === 'T') { e.preventDefault(); fbTogglePanel('fbThumbPanel'); }
        if (e.key === 'c' || e.key === 'C') { e.preventDefault(); fbTogglePanel('fbTocPanel'); }
        if (e.key === 's' || e.key === 'S') { e.preventDefault(); fbTogglePanel('fbSearchPanel'); }
        if (e.key === 'p' || e.key === 'P') { e.preventDefault(); fbToggleSingleMode(); }
        if (e.key === 'Escape') {
            document.querySelectorAll('.fb-panel.open').forEach(function (x) { x.classList.remove('open'); });
        }
    });

    // Touch/swipe para móvil
    var fbTouchStartX = 0;
    var fbEl = document.getElementById('flipbookSection');
    if (fbEl) {
        fbEl.addEventListener('touchstart', function(e) {
            fbTouchStartX = e.touches[0].clientX;
        }, { passive: true });
        fbEl.addEventListener('touchend', function(e) {
            var diff = fbTouchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) fbNextSpread(); else fbPrevSpread();
            }
        }, { passive: true });
    }

    /* =====================================================
       FLIPBUILDER LOOK — comportamiento del visor
       ===================================================== */

    // 1) Ocultar la pantalla de carga cuando el libro ya está montado
    function hideLoadingScreen() {
        var l = document.getElementById('fbLoadingScreen');
        if (l) l.classList.add('hide');
    }
    if (document.readyState === 'complete') {
        setTimeout(hideLoadingScreen, 350);
    } else {
        window.addEventListener('load', function () {
            setTimeout(hideLoadingScreen, 350);
        });
    }
    // Red de seguridad: si algo falla, la carga no deja el visor bloqueado
    setTimeout(function () {
        var l = document.getElementById('fbLoadingScreen');
        if (l) l.classList.add('hide');
    }, 4000);

    // 2) Toolbar auto-ocultable (toolbarAlwaysShow = "No")
    (function () {
        var tb = document.getElementById('fbToolbar');
        if (!tb) return;
        var hideTimer = null;
        function show() {
            tb.classList.remove('fb-tb-hidden');
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                // No ocultar si hay un panel abierto o el puntero está en la barra
                if (!document.querySelector('.fb-panel.open') &&
                    !tb.matches(':hover')) {
                    tb.classList.add('fb-tb-hidden');
                }
            }, 3200);
        }
        document.addEventListener('mousemove', function (e) {
            if (e.clientY > window.innerHeight - 130) { show(); }
        });
        tb.addEventListener('mouseenter', show);
        tb.addEventListener('mouseleave', function () {
            if (!document.querySelector('.fb-panel.open')) {
                tb.classList.add('fb-tb-hidden');
            }
        });
        document.addEventListener('touchstart', show, { passive: true });
        show();
    })();

    // 3) Paneles (índice / miniaturas / búsqueda)
    window.fbTogglePanel = function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        var wasOpen = el.classList.contains('open');
        document.querySelectorAll('.fb-panel.open').forEach(function (p) {
            p.classList.remove('open');
        });
        if (!wasOpen) {
            el.classList.add('open');
            if (id === 'fbSearchPanel') {
                var inp = document.getElementById('fbSearchInput');
                if (inp) setTimeout(function () { inp.focus(); }, 320);
            }
        }
        var tb = document.getElementById('fbToolbar');
        if (tb) tb.classList.remove('fb-tb-hidden');
    };

    // 4) Zoom (ZoomMapVisible=Hide, defaultZoomWidth 700 / maxZoomWidth 3563)
    var fbZoom = 1;
    function fbApplyZoom() {
        var book = document.getElementById('fbBook');
        var lbl = document.getElementById('fbZoomLabel');
        if (book) book.style.setProperty('--fbZoom', fbZoom);
        if (lbl) lbl.textContent = Math.round(fbZoom * 100) + '%';
        // Recalcular el escalado del contenido para que el texto no se deforme
        if (typeof reapplyScales === 'function') reapplyScales();
    }
    window.fbZoomIn = function () {
        fbZoom = Math.min(2.2, fbZoom + 0.15);
        fbApplyZoom();
    };
    window.fbZoomOut = function () {
        fbZoom = Math.max(0.6, fbZoom - 0.15);
        fbApplyZoom();
    };

    // 5) Modo página simple / doble (autoDoublePage = Yes)
    window.fbToggleSingleMode = function () {
        var wrap = document.querySelector('.fb-wrapper');
        if (!wrap) return;
        var single = wrap.classList.toggle('fb-single-mode');
        var bS = document.getElementById('fbBtnSingle');
        var bD = document.getElementById('fbBtnDouble');
        if (bS) bS.classList.toggle('active', single);
        if (bD) bD.classList.toggle('active', !single);
        // En página simple se muestra la hoja derecha (la del lector)
        if (single) {
            fbCurrentSpread = 1;
            renderSpread(1, false, 'next');
        } else {
            fbCurrentSpread = 0;
            renderSpread(0, false, 'prev');
        }
        if (typeof reapplyScales === 'function') reapplyScales();
    };

    // 6) Modo selección de texto
    window.fbToggleSelectMode = function () {
        var book = document.getElementById('fbBook');
        var btn = document.getElementById('fbBtnSelect');
        if (!book) return;
        var on = book.classList.toggle('fb-select-mode');
        if (btn) btn.classList.toggle('active', on);
        // En modo selección se desactiva el arrastre de página
        book.style.pointerEvents = on ? 'auto' : '';
    };

    // 7) Búsqueda en el texto renderizado (leastSearchChar = 3)
    window.fbClearSearch = function () {
        var inp = document.getElementById('fbSearchInput');
        if (inp) { inp.value = ''; inp.focus(); }
        document.querySelectorAll('.fb-search-hit').forEach(function (n) {
            n.replaceWith(document.createTextNode(n.textContent));
        });
        document.querySelectorAll('.fb-search-hit, mark').forEach(function (n) {
            if (n.tagName === 'MARK') n.replaceWith(document.createTextNode(n.textContent));
        });
        var res = document.getElementById('fbSearchResults');
        var inf = document.getElementById('fbSearchInfo');
        if (res) res.innerHTML = '';
        if (inf) inf.innerHTML = 'Escribe al menos 3 caracteres.';
    };

    window.fbSearch = function (term) {
        var info = document.getElementById('fbSearchInfo');
        var results = document.getElementById('fbSearchResults');
        term = (term || '').trim();

        // Quitar resaltado previo
        document.querySelectorAll('mark.fb-search-hit').forEach(function (n) {
            var parent = n.parentNode;
            parent.replaceChild(document.createTextNode(n.textContent), n);
            parent.normalize();
        });
        if (!results) return;

        if (term.length < 3) {
            results.innerHTML = '';
            if (info) info.innerHTML = 'Escribe al menos 3 caracteres.';
            return;
        }

        var store = document.getElementById('fbPagesStore');
        if (!store) return;
        var needle = term.toLowerCase();
        var pageHits = [];

        var sources = store.querySelectorAll('.fb-page-source');
        sources.forEach(function (src, pi) {
            var walker = document.createTreeWalker(src, NodeFilter.SHOW_TEXT, null);
            var targets = [];
            var node;
            while ((node = walker.nextNode())) {
                if (node.nodeValue && node.nodeValue.toLowerCase().indexOf(needle) !== -1) {
                    targets.push(node);
                }
            }
            if (!targets.length) return;
            pageHits.push({ index: pi, nodes: targets, count: targets.length });
            targets.forEach(function (t) {
                var frag = document.createDocumentFragment();
                var txt = t.nodeValue;
                var low = txt.toLowerCase();
                var pos = 0, idx;
                while ((idx = low.indexOf(needle, pos)) !== -1) {
                    if (idx > pos) frag.appendChild(document.createTextNode(txt.slice(pos, idx)));
                    var mk = document.createElement('mark');
                    mk.className = 'fb-search-hit';
                    mk.textContent = txt.substr(idx, term.length);
                    frag.appendChild(mk);
                    pos = idx + term.length;
                }
                if (pos < txt.length) frag.appendChild(document.createTextNode(txt.slice(pos)));
                t.parentNode.replaceChild(frag, t);
            });
        });

        if (info) {
            info.innerHTML = pageHits.length
                ? pageHits.reduce(function (a, p) { return a + p.count; }, 0) +
                  ' coincidencia(s) en ' + pageHits.length + ' página(s)'
                : '<span style="color:#FF8A8A;">Sin resultados para “' +
                  term.replace(/[<>&]/g, '') + '”</span>';
        }

        results.innerHTML = '';
        pageHits.forEach(function (p) {
            var row = document.createElement('div');
            row.className = 'fb-toc-item';
            row.innerHTML = '<span class="fb-toc-num">' + (p.index + 1) + '</span>' +
                            '<span>Página ' + (p.index + 1) + ' · ' + p.count + ' coincidencia(s)</span>';
            row.addEventListener('click', function () {
                window.fbGoToSpread(p.index);
                fbTogglePanel('fbSearchPanel');
            });
            results.appendChild(row);
        });

        // Si la página actual no tiene coincidencias, llevar a la primera que sí
        if (pageHits.length && !pageHits.some(function (p) { return p.index === fbCurrentSpread || p.index === fbCurrentSpread + 1; })) {
            window.fbGoToSpread(pageHits[0].index);
        }
        if (typeof reapplyScales === 'function') reapplyScales();
    };

    // 8) Page curl: arrastrar la esquina para pasar la página
    (function () {
        var curl = document.getElementById('fbCurl');
        var page = document.getElementById('fbPageRight');
        if (!curl || !page) return;
        var dragging = false, moved = false, startX = 0, startY = 0;

        function down(e) {
            if (document.getElementById('fbBook').classList.contains('fb-select-mode')) return;
            dragging = true; moved = false;
            startX = e.touches ? e.touches[0].clientX : e.clientX;
            startY = e.touches ? e.touches[0].clientY : e.clientY;
            page.classList.add('curl-dragging');
        }
        function move(e) {
            if (!dragging) return;
            var cx = e.touches ? e.touches[0].clientX : e.clientX;
            var cy = e.touches ? e.touches[0].clientY : e.clientY;
            var dx = startX - cx, dy = startY - cy;
            if (dx > 6 || dy > 6) moved = true;
            var p = Math.min(1, Math.max(0, (dx + dy) / 140));
            // Curva la esquina: el degrees grows con el arrastre
            page.style.clipPath = p > 0.02
                ? 'polygon(0 0, 100% 0, 100% ' + (100 - p * 26) + '%, ' +
                  (100 - p * 42) + '% ' + (100 - p * 4) + '%, 0 100%)'
                : '';
            if (p > 0.25) { page.style.transform = 'rotateY(-' + (p * 8).toFixed(1) + 'deg)'; }
        }
        function up() {
            if (!dragging) return;
            dragging = false;
            page.classList.remove('curl-dragging');
            var wasFar = parseFloat(page.style.clipPath ? '1' : '0');
            page.style.clipPath = '';
            page.style.transform = '';
            if (moved && wasFar) window.fbNextSpread();
        }
        curl.addEventListener('mousedown', down);
        curl.addEventListener('touchstart', down, { passive: true });
        document.addEventListener('mousemove', move);
        document.addEventListener('touchmove', move, { passive: true });
        document.addEventListener('mouseup', up);
        document.addEventListener('touchend', up);
    })();

    // Nombres de página para el caption y el índice
    var fbPageNames = {!! json_encode(array_values(array_map(function ($p) {
        return $p['nombre'] ?? ('Página ' . ($p['numero'] ?? ''));
    }, $fbPages))) !!};

    // Sincroniza toolbar, caption, índice y miniaturas con la página visible
    window.fbSyncUi = function (idx) {
        var c = document.getElementById('fbCounterTb');
        if (c) c.textContent = Math.min(idx + 1, fbTotal) + ' / ' + fbTotal;

        var cap = document.getElementById('fbPageCaption');
        if (cap) {
            var nm = fbPageNames[idx] || ('Página ' + Math.min(idx + 1, fbTotal));
            cap.textContent = 'Pág. ' + (idx + 1) + ' · ' + nm;
        }
        var bp = document.getElementById('fbBtnPrevTb');
        var bn = document.getElementById('fbBtnNextTb');
        if (bp) bp.disabled = idx <= 0;
        if (bn) bn.disabled = (idx + 2) >= fbTotal;

        document.querySelectorAll('.fb-toc-item, .fb-vthumb').forEach(function (n) {
            n.classList.remove('active');
        });
        [idx, idx + 1].forEach(function (i) {
            var a = document.getElementById('fb-toc-' + i);
            var b = document.getElementById('fb-vthumb-' + i);
            if (a) a.classList.add('active');
            if (b) b.classList.add('active');
        });
    };

    // Render inicial sin animación
    renderSpread(0, false, 'next');
    requestAnimationFrame(reapplyScales);

    // Llenar miniaturas con contenido real escalado (diferido para no bloquear el render)
    setTimeout(fillThumbnails, 200);

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

  // Sincroniza el icono de fullscreen en toolbar y en el visor legacy
  function updateReaderFsBtn() {
      const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement;
      const icon = document.getElementById('readerFsIcon');
      const btn = document.getElementById('btnReaderFullscreen');
      // Icono de la toolbar inferior (FlipBuilder look)
      const iconTb = document.getElementById('fbFsIconTb');
      const btnTb = document.querySelector('.fb-toolbar .fb-tb-btn[onclick*="readerToggleFullscreen"]');
      if (iconTb) iconTb.className = isFs ? 'fas fa-compress' : 'fas fa-expand';
      if (btnTb) btnTb.classList.toggle('active', !!isFs);
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
