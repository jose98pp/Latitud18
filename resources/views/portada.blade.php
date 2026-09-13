@extends('layouts.main')

@section('title', 'Latitud 18 | Noticias y Periódico Digital Semanal - Información Sin Ruido')

@section('content')
<style>
/* ==========================================================================
   LATITUD 18 - SISTEMA DE DISTRIBUCIÓN DE PORTADA
   Basado fielmente en latitud18.vercel.app
   ========================================================================== */

:root {
  --color-red: #D71920;
  --color-red-dark: #b51218;
  --color-red-light: #fef2f2;
  --color-navy: #0B1F3A;
  --color-navy-dark: #061120;
  --color-navy-light: #162c4e;
  --color-navy-subtle: #f0f4f9;
  --color-card-bg: #FFFFFF;
  --color-bg-page: #F8FAFC;
  --color-text-main: #111827;
  --color-text-secondary: #374151;
  --color-text-muted: #6B7280;
  --color-border: #E5E7EB;
  --font-title-anton: 'Anton', Impact, sans-serif;
  --font-title-bebas: 'Bebas Neue', Impact, sans-serif;
  --font-title-montserrat: 'Montserrat', sans-serif;
  --font-body: 'Source Sans 3', 'Montserrat', -apple-system, sans-serif;
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
}

[data-theme="dark"] {
  --color-bg-page: #080E18;
  --color-card-bg: #0D1726;
  --color-navy-subtle: #132238;
  --color-text-main: #F9FAFB;
  --color-text-secondary: #D1D5DB;
  --color-text-muted: #9CA3AF;
  --color-border: #1F2937;
}

/* ==========================================================================
   1. HERO 3 COLUMNAS (MAIN + 2 STACKED + LO MÁS LEÍDO)
   ========================================================================== */
.hero-news-section {
  padding: 14px 0 14px;
}

.hero-grid-3cols {
  display: grid;
  grid-template-columns: 4.5fr 2.2fr 2.2fr;
  gap: 14px;
  align-items: stretch;
}

/* Hero Carrusel Principal (Izquierda) */
.hero-carousel-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 400px;
  background: var(--color-navy);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hero-carousel-wrapper:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.hero-carousel-inner {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 400px;
}

.hero-carousel-slide {
  position: absolute;
  inset: 0;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
  transform: scale(1.02);
  z-index: 1;
}

.hero-carousel-slide.active {
  opacity: 1;
  visibility: visible;
  transform: scale(1);
  z-index: 2;
}

.hero-carousel-slide a {
  display: block;
  width: 100%;
  height: 100%;
  text-decoration: none;
  color: inherit;
  position: relative;
}

.hero-main-media {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 400px;
  overflow: hidden;
  background-color: var(--color-navy);
}

.hero-main-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  display: block;
}

.hero-carousel-wrapper:hover .hero-carousel-slide.active .hero-main-media img {
  transform: scale(1.03);
}

.hero-main-overlay-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.75) 35%, rgba(0,0,0,0.96) 100%);
  padding: 24px 20px 22px;
  color: #FFFFFF;
}

/* Flechas de Navegación del Carrusel */
.hero-car-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(11, 31, 58, 0.7);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255, 255, 255, 0.25);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.82rem;
  cursor: pointer;
  z-index: 10;
  transition: all 0.25s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.hero-car-arrow:hover {
  background: var(--color-red);
  border-color: var(--color-red);
  transform: translateY(-50%) scale(1.12);
  color: #FFFFFF;
}

.hero-car-arrow.prev {
  left: 12px;
}

.hero-car-arrow.next {
  right: 12px;
}

/* Indicadores de Páginas / Dots */
.hero-car-indicators {
  position: absolute;
  bottom: 14px;
  right: 18px;
  display: flex;
  align-items: center;
  gap: 5px;
  z-index: 10;
}

.hero-car-dot {
  width: 7px;
  height: 5px;
  border-radius: 3px;
  background: rgba(255, 255, 255, 0.4);
  border: none;
  padding: 0;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.hero-car-dot:hover {
  background: rgba(255, 255, 255, 0.75);
}

.hero-car-dot.active {
  width: 22px;
  background: var(--color-red);
  box-shadow: 0 0 8px rgba(215, 25, 32, 0.7);
}

.card-cat-tag {
  background-color: var(--color-red);
  color: #FFFFFF;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.68rem;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  padding: 2px 8px;
  border-radius: 2px;
  display: inline-block;
  margin-bottom: 8px;
}

.hero-main-headline {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 1.6rem;
  line-height: 1.2;
  color: #FFFFFF;
  margin-bottom: 8px;
  text-shadow: 0 2px 6px rgba(0,0,0,0.4);
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.hero-main-subhead {
  font-size: 0.85rem;
  color: #E2E8F0;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 8px;
}

.hero-main-meta {
  font-size: 0.7rem;
  color: #CBD5E1;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Columna Central (2 Tarjetas Apiladas) */
.hero-stacked-col {
  display: flex;
  flex-direction: column;
  gap: 14px;
  height: 100%;
}

.stacked-news-card {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  flex: 1;
  text-decoration: none;
  color: inherit;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stacked-news-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.stacked-media {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 188px;
  overflow: hidden;
  background-color: var(--color-navy);
}

.stacked-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
  display: block;
}

.stacked-news-card:hover .stacked-media img {
  transform: scale(1.04);
}

.stacked-overlay-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.85) 45%, #000000 100%);
  padding: 14px 14px 12px;
  color: #FFFFFF;
}

.stacked-headline {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.95rem;
  line-height: 1.25;
  color: #FFFFFF;
  margin-top: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Columna Derecha (LO MÁS LEÍDO) */
.hero-lo-mas-leido-col {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-sm);
  height: 100%;
}

.box-title-sm {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 0.92rem;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--color-navy);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 2px solid var(--color-navy);
  padding-bottom: 6px;
}

[data-theme="dark"] .box-title-sm {
  color: #FFFFFF;
  border-bottom-color: var(--color-red);
}

.lo-mas-leido-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
  justify-content: space-between;
}

.leido-item-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  text-decoration: none;
  padding-bottom: 10px;
  border-bottom: 1px dashed var(--color-border);
  transition: all 0.15s ease;
}

.leido-item-row:last-child {
  padding-bottom: 0;
  border-bottom: none;
}

.leido-num {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 1.35rem;
  line-height: 1;
  color: var(--color-red);
  min-width: 26px;
  flex-shrink: 0;
}

.leido-text {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.82rem;
  line-height: 1.35;
  color: var(--color-text-main);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s ease;
}

.leido-item-row:hover .leido-text {
  color: var(--color-red);
}

/* ==========================================================================
   2. TICKER DE ÚLTIMA HORA (SEGUNDA FRANJA)
   ========================================================================== */
.ticker-strip-bar {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  margin: 8px 0 24px;
  display: flex;
  align-items: center;
  overflow: hidden;
  height: 40px;
  box-shadow: var(--shadow-sm);
}

.ticker-label-badge {
  background-color: var(--color-red);
  color: #FFFFFF;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  padding: 0 14px;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  flex-shrink: 0;
}

.ticker-label-badge i {
  font-size: 0.45rem;
  animation: pulse 1.2s infinite;
}

.ticker-news-items-row {
  flex: 1;
  display: flex;
  align-items: center;
  overflow-x: auto;
  scrollbar-width: none;
  padding: 0 16px;
  gap: 0;
  white-space: nowrap;
}

.ticker-news-items-row::-webkit-scrollbar {
  display: none;
}

.ticker-snippet {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: color 0.15s;
  padding: 0 14px;
  position: relative;
}
.ticker-snippet + .ticker-snippet::before {
  content: '•';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-border);
  font-size: 0.7rem;
}

.ticker-snippet:hover {
  color: var(--color-red);
}

.ticker-snippet .time {
  color: var(--color-red);
  font-weight: 800;
  margin-right: 5px;
}

.ticker-ver-mas {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.72rem;
  color: var(--color-red);
  padding: 0 14px;
  white-space: nowrap;
  text-decoration: none;
  cursor: pointer;
}

.ticker-ver-mas:hover {
  color: var(--color-red-dark);
}

/* ==========================================================================
   3. FILA 1 DE CATEGORÍAS (3 COLUMNAS / SPLIT LAYOUT)
   ========================================================================== */
.cat-row-3cols {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.cat-block-box {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-sm);
}

.cat-block-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid var(--color-navy);
  padding-bottom: 4px;
  margin-bottom: 12px;
  position: relative;
}

[data-theme="dark"] .cat-block-head {
  border-bottom-color: #334155;
}

.cat-block-head::before {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 36px;
  height: 2px;
  background-color: var(--color-red);
}

.cat-block-title {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 1.05rem;
  color: var(--color-navy);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
}

[data-theme="dark"] .cat-block-title {
  color: #FFFFFF;
}

.cat-ver-mas-link {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.72rem;
  color: var(--color-red);
  text-decoration: none;
  letter-spacing: 0.3px;
  transition: color 0.15s;
}

.cat-ver-mas-link:hover {
  color: var(--color-red-dark);
}

.block-layout-split {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 8px;
  margin-bottom: 10px;
}

.split-left-main {
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
}

.split-left-main img {
  width: 100%;
  height: 130px;
  object-fit: cover;
  border-radius: 2px;
  margin-bottom: 6px;
  transition: transform 0.3s;
}

.split-left-main:hover img {
  transform: scale(1.02);
}

.split-headline {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.85rem;
  line-height: 1.28;
  color: var(--color-navy);
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}

[data-theme="dark"] .split-headline {
  color: #FFFFFF;
}

.split-left-main:hover .split-headline {
  color: var(--color-red);
}

.split-right-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.mini-thumb-item {
  display: flex;
  flex-direction: column;
  gap: 3px;
  text-decoration: none;
  color: inherit;
  transition: opacity 0.15s;
}
.mini-thumb-item:hover { opacity: 0.85; }

.mini-thumb-item img {
  width: 100%;
  height: 62px;
  object-fit: cover;
  border-radius: 2px;
}

.mini-thumb-item span {
  font-size: 0.72rem;
  font-weight: 700;
  line-height: 1.25;
  color: var(--color-text-main);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}

.mini-thumb-item:hover span {
  color: var(--color-red);
}

.block-bullet-points {
  border-top: 1px dashed var(--color-border);
  padding-top: 8px;
  margin-top: auto;
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  display: flex;
  flex-direction: column;
  gap: 5px;
  list-style: none;
  padding-left: 0;
  margin-bottom: 0;
}

.block-bullet-points li a {
  color: inherit;
  text-decoration: none;
  display: block;
  white-space: normal;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.15s;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.block-bullet-points li a:hover {
  color: var(--color-red);
}

/* ==========================================================================
   4. FILA 2 DE CATEGORÍAS (4 COLUMNAS: 3 CATS + LATITUD 18 INVESTIGA)
   ========================================================================== */
.cat-row-4cols {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}

.card-cat-sm-box {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-sm);
}

.card-cat-sm-img {
  width: 100%;
  height: 130px;
  object-fit: cover;
  border-radius: 2px;
  margin-bottom: 8px;
  transition: transform 0.3s;
  cursor: pointer;
}
.card-cat-sm-img:hover { transform: scale(1.02); }

.card-cat-sm-box a:hover .card-cat-sm-img {
  transform: scale(1.02);
}

.card-cat-sm-title {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.88rem;
  line-height: 1.3;
  color: var(--color-navy);
  margin-bottom: 8px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}

[data-theme="dark"] .card-cat-sm-title {
  color: #FFFFFF;
}

.card-cat-sm-box a:hover .card-cat-sm-title {
  color: var(--color-red);
}

/* Tarjeta Especial: LATITUD 18 INVESTIGA */
.investiga-special-card {
  background: #0B1F3A;
  color: #FFFFFF;
  border: 1px solid #1E293B;
  border-radius: 2px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 4px 16px rgba(11,31,58,0.25);
}

.investiga-badge-title {
  font-family: var(--font-title-montserrat);
  font-weight: 900;
  font-size: 0.85rem;
  letter-spacing: 0.6px;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 8px;
}

.investiga-badge-title .red {
  color: var(--color-red);
}

.investiga-img {
  width: 100%;
  height: 110px;
  object-fit: cover;
  border-radius: 2px;
  margin-bottom: 8px;
}

.investiga-headline {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.88rem;
  line-height: 1.32;
  color: #FFFFFF;
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-investiga-action {
  background-color: var(--color-red);
  color: #FFFFFF !important;
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 8px;
  text-align: center;
  border-radius: 2px;
  display: block;
  text-decoration: none;
  transition: background 0.2s;
}

.btn-investiga-action:hover {
  background-color: var(--color-red-dark);
}

/* ==========================================================================
   5. FILA 3: OPINIÓN & LATITUD 18 TV (SPLIT 1.3fr : 1fr)
   ========================================================================== */
.row-opinion-tv-split {
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 20px;
  margin-bottom: 28px;
}

/* Bloque Opinión */
.opinion-block-container {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  padding: 16px;
  box-shadow: var(--shadow-sm);
}

.columnists-grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.columnist-mini-card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
}

.columnist-mini-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 6px;
  border: 2px solid var(--color-navy);
}

.columnist-tag {
  font-family: var(--font-title-montserrat);
  font-weight: 800;
  font-size: 0.62rem;
  text-transform: uppercase;
  color: var(--color-red);
  margin-bottom: 3px;
}

.columnist-article-title {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.78rem;
  line-height: 1.25;
  color: var(--color-text-main);
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}

.columnist-mini-card:hover .columnist-article-title {
  color: var(--color-red);
}

.columnist-name-author {
  font-size: 0.68rem;
  color: var(--color-text-muted);
  font-style: italic;
}

/* Bloque Latitud 18 TV */
.tv-block-container {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: 2px;
  padding: 16px;
  box-shadow: var(--shadow-sm);
}

.tv-cards-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.tv-video-thumb-card {
  display: flex;
  flex-direction: column;
  cursor: pointer;
  text-decoration: none;
  color: inherit;
}

.tv-thumb-wrap {
  position: relative;
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 6px;
  aspect-ratio: 16 / 10;
  background-color: #000;
}

.tv-thumb-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.85;
  transition: transform 0.3s, opacity 0.3s;
}

.tv-video-thumb-card:hover .tv-thumb-wrap img {
  transform: scale(1.05);
  opacity: 1;
}

.tv-play-overlay-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 32px;
  height: 32px;
  background: rgba(215, 25, 32, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.75rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  transition: transform 0.2s;
}

.tv-video-thumb-card:hover .tv-play-overlay-icon {
  transform: translate(-50%, -50%) scale(1.15);
}

.tv-duration-badge {
  position: absolute;
  bottom: 4px;
  right: 4px;
  background: rgba(0,0,0,0.75);
  color: #fff;
  font-size: 0.6rem;
  font-weight: 700;
  padding: 1px 4px;
  border-radius: 2px;
}

.tv-video-title {
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.75rem;
  line-height: 1.25;
  color: var(--color-text-main);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color 0.15s;
}

.tv-video-thumb-card:hover .tv-video-title {
  color: var(--color-red);
}

/* ==========================================================================
   6. ESPACIO PUBLICITARIO
   ========================================================================== */
.ad-banner-strip-block {
  background: var(--color-navy-subtle);
  border: 1px dashed var(--color-border);
  color: var(--color-text-muted);
  text-align: center;
  padding: 16px;
  font-family: var(--font-title-montserrat);
  font-weight: 700;
  font-size: 0.72rem;
  letter-spacing: 2px;
  margin: 10px 0 28px;
  border-radius: 2px;
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */
@media (max-width: 1100px) {
  .hero-grid-3cols {
    grid-template-columns: 1.3fr 1fr;
  }
  .hero-lo-mas-leido-col {
    grid-column: span 2;
  }
  .cat-row-3cols {
    grid-template-columns: repeat(2, 1fr);
  }
  .cat-row-4cols {
    grid-template-columns: repeat(2, 1fr);
  }
  .row-opinion-tv-split {
    grid-template-columns: 1fr;
  }
  .columnists-grid-4 {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-grid-3cols {
    grid-template-columns: 1fr;
  }
  .hero-lo-mas-leido-col {
    grid-column: span 1;
  }
  .hero-stacked-col {
    display: none;
  }
  .cat-row-3cols {
    grid-template-columns: repeat(2, 1fr);
  }
  .cat-row-4cols {
    grid-template-columns: repeat(2, 1fr);
  }
  .columnists-grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  .block-layout-split {
    grid-template-columns: 1fr;
  }
  .row-opinion-tv-split {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .cat-row-3cols {
    grid-template-columns: 1fr;
  }
  .cat-row-4cols {
    grid-template-columns: 1fr;
  }
  .columnists-grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  .ticker-snippet {
    font-size: 0.72rem;
    padding: 0 10px;
  }
}
</style>

<div class="container">

  {{-- =========================================================================
       1. HERO 3 COLUMNAS: PRINCIPAL + 2 STACKED + LO MÁS LEÍDO
       ========================================================================= --}}
  <section class="hero-news-section">
    <div class="hero-grid-3cols">

      {{-- Carrusel Principal (Columna Izquierda) con estilo Latitud 18 --}}
      <article class="hero-carousel-wrapper" id="heroNewsCarousel">
        <div class="hero-carousel-inner">
          @foreach($noticias->take(5) as $index => $noticiaItem)
            <div class="hero-carousel-slide @if($index === 0) active @endif" data-slide-index="{{ $index }}">
              <a href="{{ $noticiaItem->url }}">
                <div class="hero-main-media">
                  <img src="{{ $noticiaItem->imagenUrl ?? asset('images/default-news.svg') }}"
                       alt="{{ $noticiaItem->titulo }}"
                       onerror="handleImageError(this)">
                  <div class="hero-main-overlay-info">
                    <span class="card-cat-tag">{{ $noticiaItem->category->name ?? 'Destacado' }}</span>
                    <h2 class="hero-main-headline">{{ $noticiaItem->titulo }}</h2>
                    <p class="hero-main-subhead">{{ $noticiaItem->excerptLimpio ?? Str::limit(strip_tags($noticiaItem->contenido), 150) }}</p>
                    <div class="hero-main-meta">
                      <i class="far fa-clock"></i>
                      <span>{{ \Carbon\Carbon::parse($noticiaItem->created_at)->locale('es')->diffForHumans() }}</span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>

        {{-- Flechas de Navegación --}}
        <button type="button" class="hero-car-arrow prev" id="heroPrevBtn" aria-label="Noticia anterior">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" class="hero-car-arrow next" id="heroNextBtn" aria-label="Noticia siguiente">
          <i class="fas fa-chevron-right"></i>
        </button>

        {{-- Indicadores Dots --}}
        <div class="hero-car-indicators">
          @foreach($noticias->take(5) as $index => $noticiaItem)
            <button type="button" class="hero-car-dot @if($index === 0) active @endif"
                    data-index="{{ $index }}"
                    aria-label="Ir a diapositiva {{ $index + 1 }}"></button>
          @endforeach
        </div>
      </article>

      {{-- Columna Central (2 Tarjetas Apiladas) --}}
      <div class="hero-stacked-col">
        @foreach($noticias->slice(1, 2) as $stackedItem)
          <article style="display:flex; flex:1;">
            <a href="{{ $stackedItem->url }}" class="stacked-news-card">
              <div class="stacked-media">
                <img src="{{ $stackedItem->imagenUrl ?? asset('images/default-news.svg') }}"
                     alt="{{ $stackedItem->titulo }}"
                     onerror="handleImageError(this)">
                <div class="stacked-overlay-info">
                  <span class="card-cat-tag">{{ $stackedItem->category->name ?? 'Actualidad' }}</span>
                  <h3 class="stacked-headline">{{ $stackedItem->titulo }}</h3>
                </div>
              </div>
            </a>
          </article>
        @endforeach
        @php $secondStacked = $noticias->slice(3, 1)->first(); @endphp
        @if($secondStacked)
          <article style="display:flex; flex:1;">
            <a href="{{ $secondStacked->url }}" class="stacked-news-card">
              <div class="stacked-media">
                <img src="{{ $secondStacked->imagenUrl ?? asset('images/default-news.svg') }}"
                     alt="{{ $secondStacked->titulo }}"
                     onerror="handleImageError(this)">
                <div class="stacked-overlay-info">
                  <span class="card-cat-tag">{{ $secondStacked->category->name ?? 'Actualidad' }}</span>
                  <h3 class="stacked-headline">{{ $secondStacked->titulo }}</h3>
                </div>
              </div>
            </a>
          </article>
        @endif
      </div>

      {{-- Columna Derecha (LO MÁS LEÍDO 01 - 05) --}}
      <aside class="hero-lo-mas-leido-col">
        <h3 class="box-title-sm">
          <i class="fa-solid fa-chart-line" style="color: var(--color-red);"></i> LO MÁS LEÍDO
        </h3>
        <div class="lo-mas-leido-list">
          @foreach($masLeidas->take(5) as $index => $leido)
            <a href="{{ $leido->url }}" class="leido-item-row">
              <div class="leido-num">0{{ $index + 1 }}</div>
              <div class="leido-text">{{ $leido->titulo }}</div>
            </a>
          @endforeach
        </div>
      </aside>

    </div>
  </section>

  {{-- =========================================================================
       2. TICKER DE ÚLTIMAS NOTICIAS (SEGUNDA FRANJA - DINÁMICO Y CLIQUEABLE)
       ========================================================================= --}}
  <div class="ticker-strip-bar">
    <div class="ticker-label-badge">
      <i class="fa-solid fa-circle"></i> ÚLTIMAS NOTICIAS
    </div>
    <div class="ticker-news-items-row" id="tickerRow">
      @foreach($ultimasNoticias->take(10) as $ultima)
        <a href="{{ $ultima->url }}" class="ticker-snippet">
          {{ $ultima->titulo }}
        </a>
      @endforeach
    </div>
    <a href="{{ route('categoria.noticias', $categorias->first()->id ?? 1) }}" class="ticker-ver-mas">
      VER MÁS &gt;
    </a>
  </div>

  {{-- =========================================================================
       3. FILA 1 DE CATEGORÍAS (3 COLUMNAS: POLÍTICA | NACIONAL | PAÍS)
       ========================================================================= --}}
  <section class="cat-row-3cols">
    @foreach($categorias->take(3) as $categoria)
      @php
        $catNews = $noticiasPorCategoria[$categoria->id] ?? collect();
        $firstNews = $catNews->first();
        $subNews = $catNews->slice(1, 2);
        $bulletNews = $catNews->slice(3, 2);
      @endphp
      <div class="cat-block-box">
        <div class="cat-block-head">
          <h3 class="cat-block-title">{{ strtoupper($categoria->name) }}</h3>
          <a href="{{ route('categoria.noticias', $categoria->id) }}" class="cat-ver-mas-link">
            VER MÁS &gt;
          </a>
        </div>

        @if($firstNews)
          <div class="block-layout-split">
            {{-- Noticia Principal de la Sección (Izquierda) --}}
            <a href="{{ $firstNews->url }}" class="split-left-main">
              <img src="{{ $firstNews->imagenUrl ?? asset('images/default-news.svg') }}"
                   alt="{{ $firstNews->titulo }}"
                   onerror="handleImageError(this)">
              <h4 class="split-headline">{{ $firstNews->titulo }}</h4>
            </a>

            {{-- Miniaturas de Noticias Secundarias (Derecha) --}}
            <div class="split-right-list">
              @foreach($subNews as $sub)
                <a href="{{ $sub->url }}" class="mini-thumb-item">
                  <img src="{{ $sub->imagenUrl ?? asset('images/default-news.svg') }}"
                       alt="{{ $sub->titulo }}"
                       onerror="handleImageError(this)">
                  <span>{{ $sub->titulo }}</span>
                </a>
              @endforeach
            </div>
          </div>
        @endif

        {{-- Viñetas de Noticias Inferiores --}}
        @if($bulletNews->count() > 0)
          <ul class="block-bullet-points">
            @foreach($bulletNews as $bullet)
              <li>
                <a href="{{ $bullet->url }}">
                  • {{ $bullet->titulo }}
                </a>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    @endforeach
  </section>

  {{-- =========================================================================
       4. FILA 2 DE CATEGORÍAS (4 COLUMNAS: 3 SECCIONES + LATITUD 18 INVESTIGA)
       ========================================================================= --}}
  <section class="cat-row-4cols">
    @foreach($categorias->slice(3, 3) as $categoria)
      @php
        $catNews = $noticiasPorCategoria[$categoria->id] ?? collect();
        $catMain = $catNews->first();
        $catBullets = $catNews->slice(1, 2);
      @endphp
      <div class="card-cat-sm-box">
        <div class="cat-block-head">
          <h3 class="cat-block-title" style="font-size: 0.95rem;">{{ strtoupper($categoria->name) }}</h3>
          <a href="{{ route('categoria.noticias', $categoria->id) }}" class="cat-ver-mas-link">
            VER MÁS &gt;
          </a>
        </div>

        @if($catMain)
          <a href="{{ $catMain->url }}" style="text-decoration:none;">
            <img src="{{ $catMain->imagenUrl ?? asset('images/default-news.svg') }}"
                 alt="{{ $catMain->titulo }}"
                 class="card-cat-sm-img"
                 onerror="handleImageError(this)">
            <h4 class="card-cat-sm-title">{{ $catMain->titulo }}</h4>
          </a>
        @endif

        @if($catBullets->count() > 0)
          <ul class="block-bullet-points">
            @foreach($catBullets as $bullet)
              <li>
                <a href="{{ $bullet->url }}">
                  • {{ $bullet->titulo }}
                </a>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    @endforeach

    {{-- Columna 4: Tarjeta Especial LATITUD 18 INVESTIGA / EDICIÓN ESPECIAL --}}
    @php
      // Noticia marcada con es_investigacion o fallback armónico
      $investigaNoticia = $noticiaInvestigacion ?? $noticias->slice(3, 1)->first() ?? $noticias->first();
    @endphp
    <div class="investiga-special-card">
      <div>
        <div class="investiga-badge-title">
          <i class="fa-solid fa-magnifying-glass red"></i> LATITUD<span class="red">18</span> INVESTIGA
        </div>
        @if($investigaNoticia)
          <a href="{{ $investigaNoticia->url }}" style="text-decoration:none; color:inherit;">
            <img src="{{ $investigaNoticia->imagenUrl ?? asset('images/default-news.svg') }}"
                 alt="{{ $investigaNoticia->titulo }}"
                 class="investiga-img"
                 onerror="handleImageError(this)">
            <h4 class="investiga-headline">{{ $investigaNoticia->titulo }}</h4>
          </a>
        @endif
      </div>
      @if($investigaNoticia)
        <a href="{{ $investigaNoticia->url }}" class="btn-investiga-action">
          VER INVESTIGACIÓN &gt;
        </a>
      @else
        <a href="{{ route('periodico.public.index') }}" class="btn-investiga-action">
          VER EDICIÓN DIGITAL &gt;
        </a>
      @endif
    </div>
  </section>

  {{-- =========================================================================
       5. FILA 3: OPINIÓN & LATITUD 18 TV (SPLIT 1.3fr : 1fr)
       ========================================================================= --}}
  <section class="row-opinion-tv-split">

    {{-- Bloque OPINIÓN (4 Columnistas Dinámicos) --}}
    <div class="opinion-block-container">
      <div class="cat-block-head">
        <h3 class="cat-block-title">OPINIÓN</h3>
        <a href="{{ route('opinion.index') }}" class="cat-ver-mas-link">
          VER MÁS &gt;
        </a>
      </div>

      <div class="columnists-grid-4">
        @if(isset($articulosOpinion) && $articulosOpinion->isNotEmpty())
          @foreach($articulosOpinion as $art)
            <a href="{{ route('opinion.articulo', $art->id) }}" class="columnist-mini-card" style="text-decoration:none; color:inherit;">
              <img src="{{ $art->columnista->avatar_url }}" alt="{{ $art->columnista->nombre }}" class="columnist-mini-avatar" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($art->columnista->nombre) }}&background=0B1F3A&color=fff'">
              <span class="columnist-tag">{{ $art->tipo ?? 'COLUMNA' }}</span>
              <h4 class="columnist-article-title" title="{{ $art->titulo }}">{{ $art->titulo }}</h4>
              <span class="columnist-name-author">Por {{ $art->columnista->nombre }}</span>
            </a>
          @endforeach
        @else
          {{-- Fallback si aún no hay artículos de opinión creados en el panel --}}
          @php
            $sampleColumnistas = [
              ['tag' => 'EDITORIAL', 'autor' => 'Redacción Latitud 18', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80'],
              ['tag' => 'COLUMNA', 'autor' => 'Lic. Roberto Vaca D.', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80'],
              ['tag' => 'ANÁLISIS', 'autor' => 'Dra. Verónica Zapana', 'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80'],
              ['tag' => 'COLUMNA', 'autor' => 'Jorge Richter R.', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80'],
            ];
            $opinionNoticias = $noticias->slice(4, 4);
          @endphp
          @foreach($sampleColumnistas as $idx => $col)
            @php $opItem = $opinionNoticias->values()->get($idx) ?? $noticias->first(); @endphp
            @if($opItem)
              <a href="{{ $opItem->url }}" class="columnist-mini-card">
                <img src="{{ $col['avatar'] }}" alt="{{ $col['autor'] }}" class="columnist-mini-avatar">
                <span class="columnist-tag">{{ $col['tag'] }}</span>
                <h4 class="columnist-article-title">{{ $opItem->titulo }}</h4>
                <span class="columnist-name-author">Por {{ $col['autor'] }}</span>
              </a>
            @endif
          @endforeach
        @endif
      </div>
    </div>

    {{-- Bloque LATITUD 18 TV (3 Video Cards Dinámicas) --}}
    <div class="tv-block-container">
      <div class="cat-block-head">
        <h3 class="cat-block-title">LATITUD 18 TV</h3>
        <button onclick="openLiveModal()" class="cat-ver-mas-link" style="background:none;border:none;cursor:pointer;">
          EN VIVO &gt;
        </button>
      </div>

      <div class="tv-cards-grid-3">
        @if(isset($noticiasConVideo) && $noticiasConVideo->isNotEmpty())
          @foreach($noticiasConVideo->take(3) as $videoNews)
            @php
              $thumb = $videoNews->youtube_thumbnail ?: ($videoNews->imagenUrl ?: asset('images/default-news.svg'));
            @endphp
            <a href="{{ $videoNews->url }}" class="tv-video-thumb-card" style="text-decoration:none; color:inherit;">
              <div class="tv-thumb-wrap">
                <img src="{{ $thumb }}" alt="{{ $videoNews->titulo }}" onerror="handleImageError(this)">
                <div class="tv-play-overlay-icon"><i class="fa-solid fa-play"></i></div>
                <div class="tv-duration-badge"><i class="fab fa-youtube"></i> VIDEO</div>
              </div>
              <h4 class="tv-video-title">{{ $videoNews->titulo }}</h4>
            </a>
          @endforeach
        @else
          {{-- Fallback si aún no hay noticias con enlace de YouTube guardado --}}
          @php
            $tvItems = [
              ['titulo' => 'Reportaje Especial: Balance económico y reactivación productiva', 'dur' => '12:45', 'img' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80'],
              ['titulo' => 'Entrevista exclusiva con analistas institucionales de Santa Cruz', 'dur' => '08:37', 'img' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=300&q=80'],
              ['titulo' => 'Edición central de noticias: Las claves de la jornada en vivo', 'dur' => '15:20', 'img' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80'],
            ];
          @endphp
          @foreach($tvItems as $tv)
            <div class="tv-video-thumb-card" onclick="openLiveModal()">
              <div class="tv-thumb-wrap">
                <img src="{{ $tv['img'] }}" alt="{{ $tv['titulo'] }}">
                <div class="tv-play-overlay-icon"><i class="fa-solid fa-play"></i></div>
                <div class="tv-duration-badge">{{ $tv['dur'] }}</div>
              </div>
              <h4 class="tv-video-title">{{ $tv['titulo'] }}</h4>
            </div>
          @endforeach
        @endif
      </div>
    </div>

  </section>

  {{-- =========================================================================
       6. ESPACIO PUBLICITARIO DINÁMICO & EXPLORADOR DE SECCIONES
       ========================================================================= --}}
  @php
    $bannerMiddle = null;
    if (isset($banners)) {
      $bannerMiddle = ($banners['portada_middle'] ?? collect())->first() 
                   ?? ($banners['footer'] ?? collect())->first()
                   ?? ($banners['home_middle'] ?? collect())->first();
    }
  @endphp
  <div class="ad-banner-strip-block">
    @if($bannerMiddle)
      ESPACIO PUBLICITARIO • {{ strtoupper($bannerMiddle->title) }}
      <div style="margin-top:8px;">
        <a href="{{ $bannerMiddle->link ?: '#' }}" @if($bannerMiddle->link) target="_blank" @endif style="display:inline-block; max-width:970px; width:100%;">
          <img src="{{ asset($bannerMiddle->image_path) }}" alt="{{ $bannerMiddle->title }}"
               style="width:100%; max-height:180px; object-fit:contain; border-radius:2px; box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
        </a>
      </div>
    @else
      ESPACIO PUBLICITARIO • 970 x 90
      <div style="margin-top:8px;">
        <a href="https://radiobetania.com/" target="_blank" style="display:inline-block; max-width:800px; width:100%;">
          <img src="{{ asset('images/betania.jpg') }}" alt="Publicidad Betania"
               style="width:100%; border-radius:2px; box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
        </a>
      </div>
    @endif
  </div>

  {{-- =========================================================================
       7. NEWSLETTER HIGHLIGHT BANNER (PORTADA)
       ========================================================================= --}}
  <div style="background:linear-gradient(135deg, var(--color-navy) 0%, var(--color-navy-dark) 100%); border-radius:4px; border-left:5px solid var(--color-red); padding:32px 28px; margin-bottom:32px; box-shadow:var(--shadow-md); color:#fff;">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:24px;">
      <div style="flex:1; min-width:280px;">
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(215,25,32,0.2); border:1px solid rgba(215,25,32,0.4); padding:4px 10px; border-radius:2px; font-family:var(--font-title-montserrat); font-weight:800; font-size:0.68rem; color:#fff; text-transform:uppercase; margin-bottom:10px;">
          <i class="fas fa-paper-plane text-danger" style="color:var(--color-red);"></i> Boletín Exclusivo
        </div>
        <h3 style="font-family:var(--font-title-bebas); font-size:2.2rem; color:#fff; line-height:1; margin-bottom:6px;">
          SUSCRÍBETE A LAS NOTICIAS SIN RUIDO
        </h3>
        <p style="font-size:0.85rem; color:#94A3B8; margin:0; max-width:600px; line-height:1.5;">
          Recibe cada mañana nuestro resumen informativo de Santa Cruz, Bolivia y el mundo seleccionado por periodistas independientes.
        </p>
      </div>

      <div style="flex:1; min-width:300px; max-width:480px;">
        <form onsubmit="handleNewsletterSubmit(event, 'Portada')" data-msg-target="portada-newsletter-msg" style="display:flex; gap:8px; flex-wrap:nowrap;">
          <input type="email" required placeholder="Ingresa tu correo electrónico..." style="flex:1; padding:12px 14px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.25); color:#fff; border-radius:2px; font-size:0.88rem; outline:none;">
          <button type="submit" style="background:var(--color-red); color:#fff; font-family:var(--font-title-montserrat); font-weight:800; font-size:0.75rem; text-transform:uppercase; padding:12px 20px; border:none; border-radius:2px; cursor:pointer; white-space:nowrap; transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
            Suscribirme
          </button>
        </form>
        <div id="portada-newsletter-msg" style="display:none; margin-top:10px; font-size:0.82rem; padding:8px 12px; border-radius:2px;"></div>
      </div>
    </div>
  </div>

  {{-- Explorador de todas las categorías en píldoras --}}
  <div style="text-align:center; padding: 20px 0 36px; border-top: 1px solid var(--color-border); margin-bottom: 20px;">
    <h3 style="font-family:var(--font-title-montserrat); font-weight:900; font-size:0.82rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--color-navy); margin-bottom:14px;">
      Explora Todas las Secciones
    </h3>
    <div style="display:flex; flex-wrap:wrap; justify-content:center; gap:8px;">
      @foreach($categorias as $cat)
        <a href="{{ route('categoria.noticias', $cat->id) }}"
           style="font-family:var(--font-title-montserrat); font-weight:700; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px; color:var(--color-navy); background:var(--color-navy-subtle); padding:6px 16px; border-radius:2px; text-decoration:none; border:1px solid var(--color-border); transition:all 0.2s;"
           onmouseover="this.style.background='var(--color-red)';this.style.color='#fff';this.style.borderColor='var(--color-red)'"
           onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-navy)';this.style.borderColor='var(--color-border)'">
          {{ $cat->name }}
        </a>
      @endforeach
    </div>
  </div>

</div>

{{-- Script Controlador del Carrusel Principal (Latitud 18) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.getElementById('heroNewsCarousel');
  if (!carousel) return;

  const slides = carousel.querySelectorAll('.hero-carousel-slide');
  const dots = carousel.querySelectorAll('.hero-car-dot');
  const prevBtn = document.getElementById('heroPrevBtn');
  const nextBtn = document.getElementById('heroNextBtn');
  const total = slides.length;
  if (total <= 1) return;

  let current = 0;
  let autoplayTimer = null;

  function goToSlide(index) {
    if (index < 0) index = total - 1;
    if (index >= total) index = 0;
    current = index;

    slides.forEach(function(slide, i) {
      slide.classList.toggle('active', i === current);
    });

    dots.forEach(function(dot, i) {
      dot.classList.toggle('active', i === current);
    });
  }

  function nextSlide() {
    goToSlide(current + 1);
  }

  function prevSlide() {
    goToSlide(current - 1);
  }

  function startAutoplay() {
    stopAutoplay();
    autoplayTimer = setInterval(nextSlide, 5000);
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      nextSlide();
      startAutoplay();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      prevSlide();
      startAutoplay();
    });
  }

  dots.forEach(function(dot) {
    dot.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const idx = parseInt(this.getAttribute('data-index'), 10);
      goToSlide(idx);
      startAutoplay();
    });
  });

  carousel.addEventListener('mouseenter', stopAutoplay);
  carousel.addEventListener('mouseleave', startAutoplay);

  // Soporte Touch Swipe en pantallas móviles / táctiles
  let touchStartX = 0;
  let touchEndX = 0;

  carousel.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  carousel.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 45) {
      nextSlide();
      startAutoplay();
    } else if (touchEndX - touchStartX > 45) {
      prevSlide();
      startAutoplay();
    }
  }, { passive: true });

  startAutoplay();
});

// -------------------------------------------------------
// TICKER AUTO-SCROLL: desplaza horizontalmente el ticker
// -------------------------------------------------------
(function() {
  const row = document.getElementById('tickerRow');
  if (!row) return;
  let pos = 0;
  const speed = 0.6; // px/frame
  let paused = false;

  function scroll() {
    if (!paused) {
      pos += speed;
      if (pos >= row.scrollWidth / 2) pos = 0;
      row.scrollLeft = pos;
    }
    requestAnimationFrame(scroll);
  }

  row.addEventListener('mouseenter', function() { paused = true; });
  row.addEventListener('mouseleave', function() { paused = false; });

  // Duplicate items so scrolling loops
  const items = row.innerHTML;
  row.innerHTML = items + items;

  requestAnimationFrame(scroll);
})();
</script>

{{-- Widget de Elfsight si está configurado --}}
<script async src="https://static.elfsight.com/platform/platform.js"></script>
<div class="elfsight-app-fbb50d0e-c779-44ab-bf7f-b16fd3542ccc" data-elfsight-app-lazy></div>
@endsection
