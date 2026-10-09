@extends('layouts.admin')
{{-- NOTE: Uses existing routes: admin.periodico.update, admin.periodico.publish, admin.periodico.uploadImage, admin.periodico.store --}}

@section('title', 'Editor InDesign — ' . ($currentEdicion['titulo'] ?? 'Periódico Digital'))

@php
    $curEstado = $currentEdicion['estado'] ?? (!empty($currentEdicion['publicada']) ? 'publicado' : 'borrador');
    $statusMap = [
        'borrador'   => ['class' => 'bg-secondary text-white', 'icon' => 'fas fa-circle', 'text' => 'Borrador'],
        'revision'   => ['class' => 'bg-warning text-dark', 'icon' => 'fas fa-eye', 'text' => 'En Revisión'],
        'aprobado'   => ['class' => 'bg-info text-white', 'icon' => 'fas fa-check', 'text' => 'Aprobado'],
        'programado' => ['class' => 'text-white', 'style' => 'background:#9333ea;', 'icon' => 'fas fa-clock', 'text' => 'Programado'],
        'publicado'  => ['class' => 'bg-success text-white', 'icon' => 'fas fa-check-double', 'text' => 'Publicada'],
    ];
    $badgeInfo = $statusMap[$curEstado] ?? $statusMap['borrador'];
@endphp

@section('page-title')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100" style="font-size:0.85rem;">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-newspaper text-danger"></i>
        <span style="font-weight:800;letter-spacing:0.3px;">Editor de Maquetación InDesign</span>
        <span id="pageStatusBadge" class="badge {{ $badgeInfo['class'] }}" style="font-size:0.72rem;padding:4px 8px;{{ $badgeInfo['style'] ?? '' }}">
            <i class="{{ $badgeInfo['icon'] }} me-1"></i> <span>{{ $badgeInfo['text'] }}</span>
        </span>
        @if(!empty($currentEdicion['fecha_programada']) && $curEstado === 'programado')
            <span class="badge bg-dark text-warning border border-warning" style="font-size:0.65rem;" title="Fecha programada">
                <i class="fas fa-calendar-alt me-1"></i> {{ date('d/m/Y H:i', strtotime($currentEdicion['fecha_programada'])) }}
            </span>
        @endif
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-success btn-save-periodico fw-bold d-inline-flex align-items-center gap-2 px-3 py-1 shadow-sm" onclick="saveEdicionLayout(true)" title="Guardar cambios de la edición (Ctrl+S)">
            <i class="fas fa-save"></i> <span>Guardar Periódico</span>
        </button>
        <a href="{{ route('periodico.public.show', $currentEdicion['id']) }}" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
            <i class="fas fa-external-link-alt"></i> Ver Lector
        </a>
    </div>
</div>
@endsection

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Cinzel:wght@700;900&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@500;700;800;900&family=Oswald:wght@500;700&family=Merriweather:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>

<style>
/* ══════════════════════════════════════════════════
   CORPORATE FONTS LOCAL DEFINITION (@font-face)
══════════════════════════════════════════════════ */
@font-face {
  font-family: 'Montserrat';
  src: url('/fonts/Montserrat-Regular.ttf') format('truetype');
  font-weight: 400;
  font-style: normal;
}
@font-face {
  font-family: 'Montserrat';
  src: url('/fonts/Montserrat-SemiBold.ttf') format('truetype');
  font-weight: 600;
  font-style: normal;
}
@font-face {
  font-family: 'Montserrat';
  src: url('/fonts/Montserrat-Bold.ttf') format('truetype');
  font-weight: 700;
  font-style: normal;
}
@font-face {
  font-family: 'Bebas Neue';
  src: url('/fonts/BebasNeue-Regular.ttf') format('truetype');
  font-weight: 400;
  font-style: normal;
}

.np-frame[data-font="Bebas Neue"], [data-font="Bebas Neue"] {
  font-family: 'Bebas Neue', Impact, 'Arial Narrow', sans-serif !important;
}

/* ══════════════════════════════════════════════════
   INDESIGN-STYLE EDITORIAL SUITE — ROOT VARIABLES
══════════════════════════════════════════════════ */
:root {
  --id-bg:         #1E2024;
  --id-panel:      #18191C;
  --id-toolbar:    #121315;
  --id-border:     #2D3035;
  --id-accent:     #D71920;
  --id-accent2:    #0284c7;
  --id-text:       #E5E7EB;
  --id-text-muted: #94A3B8;
  --id-canvas-bg:  #2B2D33;
  --id-ruler-bg:   #151618;
  --id-ruler-text: #64748B;
  --paper-w:       720px;
  --paper-h:       1040px;
}

/* ══════════════════════════════════════════ FULLSCREEN MODE */
.id-app.fullscreen-mode {
  position: fixed !important;
  inset: 0 !important;
  z-index: 99990 !important;
  height: 100vh !important;
  border-radius: 0 !important;
  grid-template-rows: 42px 40px 1fr 110px !important;
}

.id-app.fullscreen-mode ~ * { display: none !important; }

body.editor-fullscreen {
  overflow: hidden;
}

body.editor-fullscreen .content-wrapper,
body.editor-fullscreen .main-header,
body.editor-fullscreen .main-sidebar,
body.editor-fullscreen .main-footer {
  display: none !important;
}

body.editor-fullscreen .content-wrapper > .content-header { display: none !important; }

#fullscreen-overlay-indicator {
  display: none;
  position: fixed;
  top: 10px;
  right: 14px;
  z-index: 100000;
  background: rgba(0,0,0,0.72);
  border: 1px solid rgba(167,139,250,0.5);
  color: #a78bfa;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  gap: 6px;
  align-items: center;
  cursor: pointer;
  backdrop-filter: blur(4px);
  transition: opacity 0.3s;
}
#fullscreen-overlay-indicator.visible { display: flex; }
#fullscreen-overlay-indicator:hover { background: rgba(0,0,0,0.9); }

/* ══════════════════════════════════════════ APP SHELL */
* { box-sizing: border-box; margin: 0; padding: 0; }

.id-app {
  display: grid;
  grid-template-rows: 42px 40px 1fr 110px;
  grid-template-columns: 56px 1fr 280px;
  height: calc(100vh - 58px);
  background: var(--id-bg);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  color: var(--id-text);
  font-size: 0.78rem;
  overflow: hidden;
  border-radius: 4px;
}

/* ══════════════════════════════════════════ TOP MENU BAR */
.id-menubar {
  grid-column: 1 / -1;
  grid-row: 1;
  background: var(--id-toolbar);
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 12px;
  border-bottom: 1px solid var(--id-border);
  overflow-x: auto;
  scrollbar-width: thin;
}
.id-menubar::-webkit-scrollbar { height: 4px; }
.id-menubar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 2px; }

.id-menubar-title {
  font-weight: 800;
  font-size: 0.82rem;
  color: #fff;
  margin-right: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.id-edition-select {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  cursor: pointer;
  max-width: 220px;
}

.id-edition-select:focus { outline: none; border-color: var(--id-accent2); }

.id-format-select {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #38bdf8;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.72rem;
  cursor: pointer;
  font-weight: 700;
}

.id-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
  white-space: nowrap;
  text-decoration: none;
}

.id-btn-ghost {
  background: transparent;
  border-color: rgba(255,255,255,0.12);
  color: #ccc;
}
.id-btn-ghost:hover { background: rgba(255,255,255,0.1); color: #fff; }

.id-btn-primary {
  background: var(--id-accent2);
  color: #fff;
  border-color: var(--id-accent2);
}
.id-btn-primary:hover { background: #0369a1; }

.id-btn-danger {
  background: var(--id-accent);
  color: #fff;
  border-color: var(--id-accent);
}
.id-btn-danger:hover { background: #b51218; }

.id-btn-success {
  background: #16a34a;
  color: #fff;
  border-color: #16a34a;
}
.id-btn-success:hover { background: #15803d; }

.id-menubar-sep { width: 1px; height: 20px; background: var(--id-border); margin: 0 4px; }

.id-save-indicator {
  margin-left: auto;
  font-size: 0.7rem;
  color: #4ade80;
  display: flex;
  align-items: center;
  gap: 5px;
}

/* ══════════════════════════════════════════ CONTROL BAR */
.id-controlbar {
  grid-column: 1 / -1;
  grid-row: 2;
  background: var(--id-panel);
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 12px;
  border-bottom: 1px solid var(--id-border);
  overflow-x: auto;
}

.id-control-group {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 2px 6px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 4px;
}

.id-ctrl-label {
  font-size: 0.62rem;
  color: var(--id-text-muted);
  white-space: nowrap;
}

.id-ctrl-input {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 2px 5px;
  border-radius: 3px;
  font-size: 0.72rem;
  width: 58px;
  text-align: center;
}
.id-ctrl-input:focus { outline: none; border-color: var(--id-accent2); }

.id-ctrl-btn {
  background: transparent;
  border: none;
  color: #aaa;
  width: 26px;
  height: 26px;
  border-radius: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.12s;
}
.id-ctrl-btn:hover { background: rgba(255,255,255,0.12); color: #fff; }
.id-ctrl-btn.active { background: var(--id-accent2); color: #fff; }

.id-font-select {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 0.72rem;
  cursor: pointer;
  max-width: 150px;
}

.id-font-size {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 2px 4px;
  border-radius: 3px;
  font-size: 0.72rem;
  width: 44px;
  text-align: center;
}

/* ══════════════════════════════════════════ LEFT TOOLBOX */
.id-toolbox {
  grid-column: 1;
  grid-row: 3;
  background: var(--id-panel);
  border-right: 1px solid var(--id-border);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 0;
  gap: 3px;
  overflow: hidden;
}

.id-tool-btn {
  width: 42px;
  height: 42px;
  border-radius: 6px;
  background: transparent;
  border: none;
  color: #94A3B8;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.15s;
  position: relative;
}

.id-tool-btn span.hotkey {
  font-size: 0.55rem;
  position: absolute;
  bottom: 2px;
  right: 4px;
  color: #64748B;
  font-weight: 700;
}

.id-tool-btn:hover {
  background: rgba(255,255,255,0.08);
  color: #fff;
}

.id-tool-btn.active {
  background: var(--id-accent);
  color: #fff;
}
.id-tool-btn.active span.hotkey { color: rgba(255,255,255,0.8); }

.id-tool-sep {
  width: 32px;
  height: 1px;
  background: var(--id-border);
  margin: 4px 0;
}

/* ══════════════════════════════════════════ CENTER CANVAS STAGE */
.id-canvas-stage {
  grid-column: 2;
  grid-row: 3;
  background: var(--id-canvas-bg);
  position: relative;
  overflow: auto;
  display: flex;
  flex-direction: column;
}

/* Rulers */
.ruler-h {
  position: sticky;
  top: 0;
  left: 24px;
  height: 24px;
  background: var(--id-ruler-bg);
  border-bottom: 1px solid var(--id-border);
  z-index: 30;
  overflow: hidden;
  display: none;
}

.ruler-v {
  position: absolute;
  top: 24px;
  left: 0;
  width: 24px;
  background: var(--id-ruler-bg);
  z-index: 30;
  display: none;
}

.ruler-corner {
  position: sticky;
  top: 0;
  left: 0;
  width: 24px;
  height: 24px;
  background: var(--id-ruler-bg);
  border-right: 1px solid var(--id-border);
  border-bottom: 1px solid var(--id-border);
  z-index: 40;
  display: none;
}

.rulers-visible .ruler-h,
.rulers-visible .ruler-v,
.rulers-visible .ruler-corner { display: block; }

.ruler-canvas { display: block; }

/* Paper wrapper — centered */
.id-paper-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100%;
  padding: 30px;
}

/* The actual paper sheet */
.id-paper-sheet {
  width: var(--paper-w);
  min-height: var(--paper-h);
  background: #ffffff;
  position: relative;
  box-shadow: 0 10px 40px rgba(0,0,0,0.5);
  display: none;
  overflow: hidden;
  transform-origin: top center;
  color: #111827;
}

.id-paper-sheet.active { display: block; }

/* GRID OVERLAY */
.paper-grid-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1000;
  display: none;
  background-image:
    linear-gradient(rgba(2,132,199,0.12) 1px, transparent 1px),
    linear-gradient(90deg, rgba(2,132,199,0.12) 1px, transparent 1px);
  background-size: 20px 20px;
}

.grid-visible .paper-grid-overlay { display: block; }

/* Margin guides */
.paper-margin-guide {
  position: absolute;
  top: 20px;
  left: 20px;
  right: 20px;
  bottom: 20px;
  border: 1px dashed rgba(215,25,32,0.3);
  pointer-events: none;
  z-index: 1001;
}

/* ══════════════════════════════════════════ FRAME ELEMENTS */
.id-frame {
  position: absolute;
  border: 1px solid transparent;
  cursor: move;
  min-width: 40px;
  min-height: 20px;
  user-select: none;
  z-index: 10;
}

.id-frame:hover { border-color: rgba(2,132,199,0.5); }
.id-frame.selected {
  border-color: var(--id-accent2) !important;
  z-index: 100;
}

.id-frame.selected::before {
  content: '';
  position: absolute;
  inset: -1px;
  border: 1px solid var(--id-accent2);
  pointer-events: none;
}

/* Resize handles */
.id-frame .resize-handle {
  position: absolute;
  width: 8px;
  height: 8px;
  background: #fff;
  border: 1px solid var(--id-accent2);
  border-radius: 1px;
  z-index: 200;
  display: none;
}

.id-frame.selected .resize-handle { display: block; }

.resize-handle.nw { top: -4px;  left: -4px;  cursor: nw-resize; }
.resize-handle.n  { top: -4px;  left: calc(50% - 4px); cursor: n-resize; }
.resize-handle.ne { top: -4px;  right: -4px; cursor: ne-resize; }
.resize-handle.e  { top: calc(50% - 4px); right: -4px; cursor: e-resize; }
.resize-handle.se { bottom: -4px; right: -4px; cursor: se-resize; }
.resize-handle.s  { bottom: -4px; left: calc(50% - 4px); cursor: s-resize; }
.resize-handle.sw { bottom: -4px; left: -4px;  cursor: sw-resize; }
.resize-handle.w  { top: calc(50% - 4px); left: -4px; cursor: w-resize; }

/* ── ELEMENT INNER STYLES ── */
.frame-masthead-inner {
  width: 100%;
  height: 100%;
  background: #fff;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.frame-text-inner {
  width: 100%;
  height: 100%;
  padding: 4px;
  outline: none;
  font-family: 'Source Sans 3', sans-serif;
  font-size: 13px;
  line-height: 1.45;
  color: #111;
  overflow: auto;
  word-wrap: break-word;
}

.frame-img-inner {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.frame-img-placeholder {
  width: 100%;
  height: 100%;
  background: #f1f5f9;
  border: 1px dashed #cbd5e1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 0.75rem;
  cursor: pointer;
}

.frame-quote-inner {
  width: 100%;
  height: 100%;
  border-left: 4px solid var(--id-accent);
  padding: 8px 12px;
  background: #f8fafc;
  font-family: 'Playfair Display', serif;
  font-style: italic;
  font-size: 16px;
  line-height: 1.4;
  color: #0f172a;
  overflow: auto;
}

.frame-box-inner {
  width: 100%;
  height: 100%;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 2px;
  padding: 8px 10px;
  overflow: auto;
}

.frame-divider-inner {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
}

/* ══════════════════════════════════════════ RIGHT INSPECTOR */
.id-inspector {
  grid-column: 3;
  grid-row: 3;
  background: var(--id-panel);
  border-left: 1px solid var(--id-border);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.inspector-section {
  border-bottom: 1px solid var(--id-border);
}

.inspector-section-header {
  padding: 8px 12px;
  background: rgba(255,255,255,0.02);
  font-weight: 800;
  font-size: 0.7rem;
  color: #cbd5e1;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
}

.inspector-body {
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.inspector-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.inspector-label {
  font-size: 0.65rem;
  color: var(--id-text-muted);
  min-width: 32px;
}

.inspector-input {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 3px 6px;
  border-radius: 3px;
  font-size: 0.72rem;
  flex: 1;
}
.inspector-input:focus { outline: none; border-color: var(--id-accent2); }

.inspector-select {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 3px 6px;
  border-radius: 3px;
  font-size: 0.72rem;
  flex: 1;
  cursor: pointer;
}

.insp-full-btn {
  width: 100%;
  padding: 6px 10px;
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--id-border);
  border-radius: 4px;
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.15s;
}
.insp-full-btn:hover { background: rgba(255,255,255,0.12); }
.insp-full-btn.danger:hover { background: var(--id-accent); border-color: var(--id-accent); }

/* ══════════════════════════════════════════ BOTTOM PAGES PANEL */
.id-pages-panel {
  grid-column: 1 / -1;
  grid-row: 4;
  background: var(--id-toolbar);
  border-top: 1px solid var(--id-border);
  display: flex;
  align-items: center;
  padding: 0 16px;
  gap: 12px;
  overflow-x: auto;
}

.pages-panel-label {
  font-size: 0.72rem;
  font-weight: 800;
  color: #94A3B8;
  text-transform: uppercase;
  white-space: nowrap;
}

.page-thumb {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  border: 2px solid transparent;
  transition: all 0.15s;
}

.page-thumb:hover { background: rgba(255,255,255,0.05); }
.page-thumb.active {
  border-color: var(--id-accent);
  background: rgba(215,25,32,0.1);
}

.page-thumb-preview {
  width: 48px;
  height: 68px;
  background: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  position: relative;
  overflow: hidden;
}

.page-thumb-label {
  font-size: 0.65rem;
  color: #CBD5E1;
  font-weight: 700;
  white-space: nowrap;
}

.page-add-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  padding: 6px;
}
.page-add-preview {
  width: 48px;
  height: 68px;
  border: 1px dashed var(--id-border);
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748B;
  transition: all 0.15s;
}
.page-add-btn:hover .page-add-preview { border-color: #fff; color: #fff; }

/* ══════════════════════════════════════════ NEWS DRAWER */
.id-news-drawer {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: 380px;
  background: #111317;
  border-left: 2px solid var(--id-border);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  box-shadow: -8px 0 30px rgba(0,0,0,0.6);
  transform: translateX(100%);
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.id-news-drawer.open { transform: translateX(0); }

.drawer-header {
  padding: 12px 16px;
  background: #18191C;
  border-bottom: 1px solid var(--id-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.drawer-filters {
  padding: 10px 16px;
  display: flex;
  gap: 8px;
  background: #141518;
  border-bottom: 1px solid var(--id-border);
}

.drawer-filter-select, .drawer-search {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 0.75rem;
}
.drawer-search { flex: 1; }

.drawer-news-list {
  flex: 1;
  overflow-y: auto;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.drawer-news-card {
  background: #18191C;
  border: 1px solid var(--id-border);
  border-radius: 4px;
  padding: 10px;
  transition: all 0.15s;
}
.drawer-news-card:hover { border-color: var(--id-accent2); }

.dnc-header {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.dnc-thumb {
  width: 50px;
  height: 50px;
  border-radius: 2px;
  object-fit: cover;
}

.dnc-no-thumb {
  width: 50px;
  height: 50px;
  border-radius: 2px;
  background: #25272C;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
}

.dnc-info { flex: 1; min-width: 0; }
.dnc-cat {
  font-size: 0.6rem;
  color: var(--id-accent);
  font-weight: 800;
  text-transform: uppercase;
}
.dnc-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #E2E8F0;
  line-height: 1.25;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.dnc-actions {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  border-top: 1px solid rgba(255,255,255,0.06);
  padding-top: 8px;
}

.dnc-btn {
  padding: 3px 8px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  color: #CBD5E1;
  border-radius: 2px;
  font-size: 0.65rem;
  cursor: pointer;
  transition: all 0.12s;
}
.dnc-btn:hover { background: rgba(255,255,255,0.15); color: #fff; }
.dnc-btn.primary { background: var(--id-accent); color: #fff; border-color: var(--id-accent); }

/* ══════════════════════════════════════════ MODALS */
.id-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.75);
  backdrop-filter: blur(4px);
  z-index: 99999;
  display: none;
  align-items: center;
  justify-content: center;
}
.id-modal-overlay.open { display: flex; }

.id-modal {
  background: #18191C;
  border: 1px solid var(--id-border);
  border-radius: 6px;
  width: 90%;
  max-width: 760px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 50px rgba(0,0,0,0.8);
}

.id-modal-header {
  padding: 14px 18px;
  border-bottom: 1px solid var(--id-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.id-modal-title { font-weight: 800; font-size: 0.9rem; color: #fff; display: flex; align-items: center; gap: 8px; }
.id-modal-close { background: none; border: none; color: #888; font-size: 1.1rem; cursor: pointer; }
.id-modal-close:hover { color: #fff; }

.id-modal-body { padding: 18px; overflow-y: auto; flex: 1; }
.id-modal-footer { padding: 12px 18px; border-top: 1px solid var(--id-border); display: flex; justify-content: flex-end; gap: 8px; }

.template-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.template-card {
  background: #111317;
  border: 1px solid var(--id-border);
  border-radius: 4px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}
.template-card:hover {
  border-color: var(--id-accent2);
  transform: translateY(-2px);
  background: #15181E;
}
.template-card i { font-size: 1.8rem; margin-bottom: 8px; display: block; }
.template-card h6 { font-weight: 800; color: #fff; margin-bottom: 4px; font-size: 0.82rem; }
.template-card p { font-size: 0.68rem; color: #888; line-height: 1.35; margin: 0; }

.id-form-row { margin-bottom: 12px; }
.id-form-label { display: block; font-size: 0.7rem; font-weight: 700; color: #CBD5E1; margin-bottom: 4px; }
.id-form-input { width: 100%; background: #0E0F11; border: 1px solid var(--id-border); color: #fff; padding: 7px 10px; border-radius: 4px; font-size: 0.8rem; }
.id-form-input:focus { outline: none; border-color: var(--id-accent2); }

/* Toast */
.id-toast {
  position: fixed;
  bottom: 24px;
  left: 24px;
  background: #0f172a;
  border: 1px solid #334155;
  border-left: 4px solid var(--id-accent2);
  color: #fff;
  padding: 10px 16px;
  border-radius: 4px;
  font-size: 0.78rem;
  display: none;
  align-items: center;
  gap: 8px;
  z-index: 999999;
  box-shadow: 0 4px 20px rgba(0,0,0,0.4);
}
.id-toast.show { display: flex; animation: idFadeIn 0.2s; }
@keyframes idFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
/* ══════════════════════════════════════════ WORKFLOW & TEMPLATES SUITE */
.id-dropdown-menu {
  display: none;
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  background: #18191C;
  border: 1px solid var(--id-border);
  border-radius: 6px;
  min-width: 220px;
  box-shadow: 0 12px 36px rgba(0,0,0,0.65);
  z-index: 10000;
  padding: 6px;
}
.id-dropdown-menu.show { display: block; animation: idFadeIn 0.15s ease-out; }
.id-dropdown-header {
  font-size: 0.65rem;
  font-weight: 800;
  color: var(--id-text-muted);
  text-transform: uppercase;
  padding: 4px 8px;
  letter-spacing: 0.5px;
}
.id-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  background: transparent;
  border: none;
  color: #E2E8F0;
  padding: 7px 10px;
  font-size: 0.74rem;
  font-weight: 600;
  border-radius: 4px;
  cursor: pointer;
  text-align: left;
  transition: all 0.15s;
}
.id-dropdown-item:hover {
  background: #2B2D33;
  color: #38bdf8;
}

/* Locked frames */
.id-frame.locked {
  outline: 1px dashed rgba(239, 68, 68, 0.7) !important;
  cursor: not-allowed !important;
}
.id-frame.locked .resize-handle { display: none !important; }
.frame-lock-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(239, 68, 68, 0.9);
  color: #fff;
  font-size: 0.55rem;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 3px;
  z-index: 50;
  pointer-events: none;
  box-shadow: 0 1px 3px rgba(0,0,0,0.5);
}

/* Template Catalog Tabs & Cards */
.tpl-tabs {
  display: flex;
  gap: 6px;
  border-bottom: 1px solid var(--id-border);
  padding-bottom: 8px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}
.tpl-tab-btn {
  background: #18191C;
  border: 1px solid var(--id-border);
  color: #94A3B8;
  padding: 5px 12px;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.tpl-tab-btn:hover { color: #fff; border-color: #475569; }
.tpl-tab-btn.active {
  background: var(--id-accent2);
  border-color: var(--id-accent2);
  color: #fff;
}
.tpl-card-badge {
  display: inline-block;
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  padding: 2px 6px;
  border-radius: 3px;
  margin-bottom: 6px;
  letter-spacing: 0.5px;
}
.tpl-card-actions {
  display: flex;
  gap: 6px;
  margin-top: 10px;
}
.tpl-btn-apply {
  flex: 1;
  background: #0284c7;
  color: #fff;
  border: none;
  padding: 6px 10px;
  font-size: 0.72rem;
  font-weight: 700;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.15s;
}
.tpl-btn-apply:hover { background: #0369a1; }
.tpl-btn-action {
  background: #1E2024;
  border: 1px solid var(--id-border);
  color: #CBD5E1;
  padding: 6px 8px;
  font-size: 0.72rem;
  border-radius: 4px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.tpl-btn-action:hover { background: #2B2D33; color: #fff; }
.tpl-btn-action.danger:hover { background: #991b1b; color: #fff; border-color: #ef4444; }

/* Control bar active state */
.id-ctrl-btn.active {
  background: var(--id-accent2);
  color: #fff;
}

/* ══════════════════════════════════════════════════
   DIGITAL TEMPLATE & UI EXTENSIONS
══════════════════════════════════════════════════ */
.frame-linked-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #0284c7;
  color: #fff;
  width: 18px;
  height: 18px;
  border-radius: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
  z-index: 25;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  pointer-events: none;
}
.frame-category-bar {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  z-index: 20;
  pointer-events: none;
}
.ad-format-badge {
  position: absolute;
  top: 4px;
  left: 4px;
  background: #1e40af;
  color: #fff;
  font-size: 8px;
  font-weight: 800;
  padding: 1px 5px;
  border-radius: 2px;
  font-family: 'Montserrat', sans-serif;
  z-index: 25;
  letter-spacing: 0.5px;
}
.ad-disponible-box {
  width: 100%;
  height: 100%;
  background: #E8F0FE;
  border: 1px dashed #3b82f6;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}
.ad-watermark-text {
  font-family: 'Montserrat', sans-serif;
  font-size: 13px;
  font-weight: 800;
  color: #1e40af;
  letter-spacing: 2px;
}
.corp-palette-btn {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 1px solid #475569;
  cursor: pointer;
  display: inline-block;
  transition: transform 0.1s, border-color 0.1s;
}
.corp-palette-btn:hover {
  transform: scale(1.15);
  border-color: #fff;
}
.field-error-msg {
  color: #f87171;
  font-size: 0.65rem;
  margin-top: 2px;
  display: none;
}
.field-error-msg.visible {
  display: block;
}
.slot-card {
  background: #1e2024;
  border: 1px solid #334155;
  border-radius: 5px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.slot-badge {
  font-size: 0.62rem;
  font-weight: 800;
  color: #38bdf8;
  text-transform: uppercase;
}
</style>

<div class="id-app" id="idApp">

  {{-- ── 1. MENUBAR (TOP) ── --}}
  <div class="id-menubar">
    <div class="id-menubar-title">
      <span style="font-family:'Anton',sans-serif;letter-spacing:1px;font-size:1.1rem;color:var(--id-accent);">ID</span>
      <span>EDITORIAL SUITE</span>
    </div>

    {{-- Selector de Edición --}}
    <select class="id-edition-select" id="editionSelect" onchange="switchEdition(this.value)">
      @foreach($ediciones as $ed)
        <option value="{{ $ed['id'] }}" {{ $ed['id'] === $currentEdicion['id'] ? 'selected' : '' }}>
          {{ $ed['numero_edicion'] }} ({{ $ed['fecha'] }}) {{ !empty($ed['publicada']) ? '✓' : '•' }}
        </option>
      @endforeach
    </select>

    {{-- BOTÓN PRINCIPAL DE GUARDAR DESTACADO (Siempre visible y accesible) --}}
    <button class="id-btn id-btn-success" id="mainSaveBtn" onclick="saveEdicionLayout(true)" style="background:#16a34a;border-color:#15803d;color:#fff;font-weight:900;padding:5px 14px;box-shadow:0 0 10px rgba(22,163,74,0.35);" title="Guardar cambios de la edición (Ctrl+S)">
      <i class="fas fa-save"></i> <span>GUARDAR</span>
    </button>

    {{-- Indicador de guardado --}}
    <div class="id-save-indicator" id="saveIndicator" style="margin-left:4px;margin-right:6px;">
      <i class="fas fa-check-circle"></i> <span>Al día</span>
    </div>

    <div class="id-menubar-sep"></div>

    {{-- Selector de Formato de Papel --}}
    <select class="id-format-select" id="formatSelect" onchange="changePaperFormat(this.value)" title="Formato de página">
      <option value="tabloid" selected>Tabloide 28 × 43 cm (720 × 1106)</option>
      <option value="broadsheet">Broadsheet Clásico (820 × 1160)</option>
      <option value="compact">Compacto A4 (680 × 960)</option>
    </select>

    <button class="id-btn id-btn-ghost" onclick="openModal('newEdicionModal')">
      <i class="fas fa-plus"></i> Nueva Edición
    </button>

    <div class="id-menubar-sep"></div>

    {{-- Pantalla Completa --}}
    <button class="id-btn id-btn-ghost" id="fullscreenBtn" onclick="toggleFullscreenEditor()" title="Expandir editor a pantalla completa (F11)" style="color:#a78bfa;border:1px solid rgba(167,139,250,0.3);">
      <i class="fas fa-expand" id="fullscreenIcon"></i> <span id="fullscreenLabel">Pantalla Completa</span>
    </button>

    <div class="id-menubar-sep"></div>

    {{-- Plantillas --}}
    <button class="id-btn id-btn-ghost" onclick="openModal('templateModal')" style="color:#38bdf8;">
      <i class="fas fa-th-large"></i> Plantillas
    </button>

    {{-- Noticias Web --}}
    <button class="id-btn id-btn-ghost" onclick="toggleNewsDrawer()">
      <i class="fas fa-newspaper" style="color:var(--id-accent);"></i> Noticias ({{ count($noticiasPublicadas) }})
    </button>

    {{-- Publicidad Cross-page --}}
    <button class="id-btn id-btn-ghost" onclick="openPublicidadModal()" style="color:#f59e0b;" title="Inventario publicitario en todas las páginas">
      <i class="fas fa-bullhorn"></i> Publicidad
    </button>

    {{-- Plantilla 12 Páginas --}}
    <button class="id-btn id-btn-ghost" onclick="openPlantillaEdicionModal()" style="color:#c084fc;" title="Configurar plantilla completa de 12 slots">
      <i class="fas fa-book"></i> Plantilla 12 Págs
    </button>

    {{-- Previsualización Fullscreen --}}
    <button class="id-btn id-btn-ghost" onclick="openPreviewModal()" style="color:#10b981;" title="Previsualizar edición completa sin interactores (720x1040)">
      <i class="fas fa-desktop"></i> Previsualizar
    </button>

    {{-- Exportar PDF --}}
    <button class="id-btn id-btn-ghost" onclick="openPdfExportModal()">
      <i class="fas fa-file-pdf text-danger"></i> PDF
    </button>

    {{-- Vista Previa en Lector Público --}}
    <a href="{{ route('periodico.public.show', $currentEdicion['id']) }}" target="_blank" class="id-btn id-btn-ghost">
      <i class="fas fa-eye"></i> Lector
    </a>

    <div class="id-menubar-sep"></div>

    {{-- Flujo Editorial Contextual (Máquina de Estados) --}}
    <div class="id-dropdown" style="position:relative;display:inline-block;" id="estadoToolbarContainer">
      <button class="id-btn id-btn-ghost" id="editorialDropdownBtn" onclick="toggleStateDropdown(event)" style="border:1px solid rgba(255,255,255,0.2);padding:4px 10px;" title="Cambiar estado editorial de la edición">
        <span id="topBarStateBadge" class="badge {{ $badgeInfo['class'] }}" style="font-size:0.65rem;padding:3px 6px;{{ $badgeInfo['style'] ?? '' }}">
          <i class="{{ $badgeInfo['icon'] }} me-1" id="topBarStateIcon"></i> <span id="topBarStateText">{{ $badgeInfo['text'] }}</span>
        </span>
        <span style="margin-left:4px;font-weight:700;">Estado</span>
        <i class="fas fa-chevron-down" style="font-size:0.55rem;margin-left:4px;"></i>
      </button>
      <div class="id-dropdown-menu" id="editorialDropdownMenu" style="min-width:200px;">
        <div class="id-dropdown-header">Transiciones Permitidas</div>
        <div id="allowedTransitionsContainer">
          {{-- Rendered dynamically according to ESTADO_TRANSICIONES --}}
        </div>
      </div>
    </div>

    {{-- Publicar / Despublicar directo (para aprobados / programados) --}}
    <button class="id-btn {{ !empty($currentEdicion['publicada']) ? 'id-btn-success' : 'id-btn-danger' }}" id="publishBtn" onclick="togglePublishEdition()">
      <i class="fas {{ !empty($currentEdicion['publicada']) ? 'fa-check-circle' : 'fa-globe' }}"></i>
      <span id="publishBtnLabel">{{ !empty($currentEdicion['publicada']) ? 'Publicado' : 'Publicar Edición' }}</span>
    </button>
  </div>

  {{-- ── 2. CONTROL BAR (FORMATTING & GEOMETRY) ── --}}
  <div class="id-controlbar">
    {{-- Undo / Redo / Guardar Rápido --}}
    <div class="id-control-group">
      <button class="id-ctrl-btn" onclick="undoAction()" title="Deshacer (Ctrl+Z)"><i class="fas fa-undo"></i></button>
      <button class="id-ctrl-btn" onclick="redoAction()" title="Rehacer (Ctrl+Y)"><i class="fas fa-redo"></i></button>
      <button class="id-ctrl-btn" onclick="saveEdicionLayout(true)" title="Guardar cambios (Ctrl+S)" style="color:#4ade80;"><i class="fas fa-save"></i></button>
    </div>

    {{-- Zoom Controls --}}
    <div class="id-control-group">
      <button class="id-ctrl-btn" onclick="changeZoom(-0.1)" title="Reducir zoom"><i class="fas fa-search-minus"></i></button>
      <span class="id-ctrl-label" id="zoomDisplay" style="min-width:36px;text-align:center;font-weight:700;">100%</span>
      <button class="id-ctrl-btn" onclick="changeZoom(0.1)" title="Aumentar zoom"><i class="fas fa-search-plus"></i></button>
      <button class="id-ctrl-btn" onclick="resetZoom()" title="Ajustar 100%"><i class="fas fa-compress-arrows-alt"></i></button>
    </div>

    {{-- Rulers & Grid & Snap --}}
    <div class="id-control-group">
      <button class="id-ctrl-btn" id="rulerToggleBtn" onclick="toggleRulers()" title="Mostrar Reglas"><i class="fas fa-ruler-combined"></i></button>
      <button class="id-ctrl-btn" id="gridToggleBtn" onclick="toggleGrid()" title="Mostrar Cuadrícula"><i class="fas fa-border-all"></i></button>
      <button class="id-ctrl-btn" id="snapToggleBtn" onclick="toggleSnapGrid()" title="Ajuste Magnético a Cuadrícula (Snap 10px)"><i class="fas fa-magnet"></i></button>
      <button class="id-ctrl-btn active" id="marginToggleBtn" onclick="toggleMargins()" title="Guías de Sangría"><i class="fas fa-vector-square"></i></button>
    </div>

    {{-- Typography Quick Bar --}}
    <div class="id-control-group">
      <select class="id-font-select" id="cbFontFamily" onchange="applyStyleToSelection()">
        <option value="'Source Sans 3',sans-serif">Source Sans 3</option>
        <option value="'Playfair Display',serif">Playfair Display</option>
        <option value="'Anton',sans-serif">Anton</option>
        <option value="'Oswald',sans-serif">Oswald</option>
        <option value="'Bebas Neue',sans-serif">Bebas Neue</option>
        <option value="'Montserrat',sans-serif">Montserrat</option>
        <option value="'Merriweather',serif">Merriweather</option>
      </select>
      <input type="number" class="id-font-size" id="cbFontSize" value="14" min="8" max="120" onchange="applyStyleToSelection()" title="Tamaño de fuente">
      <button class="id-ctrl-btn" onclick="toggleBold()" title="Negrita (Ctrl+B)"><i class="fas fa-bold"></i></button>
      <button class="id-ctrl-btn" onclick="toggleItalic()" title="Cursiva (Ctrl+I)"><i class="fas fa-italic"></i></button>
      <button class="id-ctrl-btn" onclick="toggleUnderline()" title="Subrayado"><i class="fas fa-underline"></i></button>
      <button class="id-ctrl-btn" onclick="toggleUppercase()" title="Mayúsculas / Minúsculas"><i class="fas fa-font"></i></button>
    </div>

    {{-- Alignment & Columns --}}
    <div class="id-control-group">
      <button class="id-ctrl-btn" onclick="applyAlign('left')" title="Alinear a la izquierda"><i class="fas fa-align-left"></i></button>
      <button class="id-ctrl-btn" onclick="applyAlign('center')" title="Centrar"><i class="fas fa-align-center"></i></button>
      <button class="id-ctrl-btn" onclick="applyAlign('right')" title="Alinear a la derecha"><i class="fas fa-align-right"></i></button>
      <button class="id-ctrl-btn" onclick="applyAlign('justify')" title="Justificar periodístico"><i class="fas fa-align-justify"></i></button>
      <div style="width:1px;height:16px;background:var(--id-border);margin:0 2px;"></div>
      <span class="id-ctrl-label">Cols:</span>
      <button class="id-ctrl-btn" onclick="setColumns(1)" title="1 Columna">1</button>
      <button class="id-ctrl-btn" onclick="setColumns(2)" title="2 Columnas">2</button>
      <button class="id-ctrl-btn" onclick="setColumns(3)" title="3 Columnas">3</button>
      <button class="id-ctrl-btn" onclick="setColumns(4)" title="4 Columnas">4</button>
    </div>
  </div>

  {{-- ── 3. LEFT TOOLBOX (INDESIGN PALETTE) ── --}}
  <div class="id-toolbox">
    {{-- Select / Move --}}
    <button class="id-tool-btn active" id="tool-select" onclick="selectTool('select')" title="Herramienta Selección (V)">
      <i class="fas fa-mouse-pointer"></i>
      <span class="hotkey">V</span>
    </button>

    <div class="id-tool-sep"></div>

    {{-- Masthead / Cabecera --}}
    <button class="id-tool-btn" id="tool-masthead" onclick="createSpecialElement('masthead')" title="Cabecera Oficial de Periódico (M)">
      <i class="fas fa-heading text-danger"></i>
      <span class="hotkey">M</span>
    </button>

    {{-- Titular / Headline --}}
    <button class="id-tool-btn" id="tool-headline" onclick="createSpecialElement('headline')" title="Titular con Kicker / Antetítulo (H)">
      <i class="fas fa-font" style="color:#38bdf8;"></i>
      <span class="hotkey">H</span>
    </button>

    {{-- Text / Article --}}
    <button class="id-tool-btn" id="tool-text" onclick="createSpecialElement('article')" title="Artículo Multi-columna (A)">
      <i class="fas fa-align-left" style="color:#4ade80;"></i>
      <span class="hotkey">A</span>
    </button>

    {{-- Image / Photo --}}
    <button class="id-tool-btn" id="tool-image" onclick="createSpecialElement('image')" title="Fotonoticia con Pie (I)">
      <i class="fas fa-image" style="color:#f59e0b;"></i>
      <span class="hotkey">I</span>
    </button>

    {{-- Quote / Cita destacada --}}
    <button class="id-tool-btn" id="tool-quote" onclick="createSpecialElement('quote')" title="Cita Destacada / Pull Quote (Q)">
      <i class="fas fa-quote-left" style="color:#a855f7;"></i>
      <span class="hotkey">Q</span>
    </button>

    {{-- Box / Módulo --}}
    <button class="id-tool-btn" id="tool-box" onclick="createSpecialElement('box')" title="Caja de Datos / Módulo (B)">
      <i class="fas fa-vector-square" style="color:#ec4899;"></i>
      <span class="hotkey">B</span>
    </button>

    {{-- Divider / Pleca --}}
    <button class="id-tool-btn" id="tool-line" onclick="createSpecialElement('divider')" title="Pleca / Filete Divisor (D)">
      <i class="fas fa-minus" style="color:#94a3b8;"></i>
      <span class="hotkey">D</span>
    </button>

    {{-- QR Code Digital --}}
    <button class="id-tool-btn" id="tool-qr" onclick="createSpecialElement('qr')" title="Código QR Digital (K)">
      <i class="fas fa-qrcode" style="color:#10b981;"></i>
      <span class="hotkey">K</span>
    </button>

    {{-- Ad / Módulo Publicitario --}}
    <button class="id-tool-btn" id="tool-ad" onclick="createSpecialElement('ad')" title="Módulo Publicitario / Anuncio (P)">
      <i class="fas fa-ad" style="color:#f59e0b;"></i>
      <span class="hotkey">P</span>
    </button>

    <div class="id-tool-sep"></div>

    {{-- Base de Datos Noticias --}}
    <button class="id-tool-btn" onclick="toggleNewsDrawer()" title="Buscar e Insertar Noticias de la Web (N)">
      <i class="fas fa-database" style="color:#38bdf8;"></i>
      <span class="hotkey">N</span>
    </button>
  </div>

  {{-- ── 4. CENTER CANVAS STAGE ── --}}
  <div class="id-canvas-stage" id="canvasStage">
    {{-- Rulers --}}
    <div class="ruler-corner" id="rulerCorner"></div>
    <div class="ruler-h" id="rulerH"><canvas id="canvasRulerH" class="ruler-canvas" height="24"></canvas></div>
    <div class="ruler-v" id="rulerV"><canvas id="canvasRulerV" class="ruler-canvas" width="24"></canvas></div>

    <div class="id-paper-wrapper" id="paperWrapper">
      <div id="sheetsContainer" style="display:contents;"></div>
    </div>
  </div>

  {{-- ── 5. RIGHT INSPECTOR (PROPERTIES) ── --}}
  <div class="id-inspector" id="inspector">
    {{-- Geometry / Dimensions --}}
    <div class="inspector-section" id="inspGeometry">
      <div class="inspector-section-header">
        <span><i class="fas fa-ruler" style="margin-right:5px;"></i> Geometría & Capas</span>
        <i class="fas fa-chevron-down" style="font-size:0.6rem;"></i>
      </div>
      <div class="inspector-body">
        <div class="inspector-row">
          <span class="inspector-label">X</span>
          <input type="number" class="inspector-input" id="inspX" onchange="applyGeometry()" title="Posición X">
          <span class="inspector-label">Y</span>
          <input type="number" class="inspector-input" id="inspY" onchange="applyGeometry()" title="Posición Y">
        </div>
        <div class="inspector-row">
          <span class="inspector-label">Ancho</span>
          <input type="number" class="inspector-input" id="inspW" onchange="applyGeometry()" title="Ancho">
          <span class="inspector-label">Alto</span>
          <input type="number" class="inspector-input" id="inspH" onchange="applyGeometry()" title="Alto">
        </div>
        <div class="inspector-row">
          <span class="inspector-label">Capa Z</span>
          <input type="number" class="inspector-input" id="inspZ" min="1" max="999" onchange="applyZIndex()" title="Orden de Capa">
          <span class="inspector-label" style="min-width:60px;">Opacidad</span>
          <input type="range" class="inspector-input" id="inspOpacity" min="0" max="100" value="100" oninput="applyOpacity()" style="padding:0;height:18px;">
          <span class="inspector-label" id="inspOpacityVal">100%</span>
        </div>
      </div>
    </div>

    {{-- Masthead / Header Inspector --}}
    <div class="inspector-section" id="inspMasthead" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-heading" style="color:var(--id-accent);margin-right:5px;"></i> Cabecera del Periódico</span>
      </div>
      <div class="inspector-body">
        <div>
          <label class="id-form-label">Nombre del Diario / Sección (máx 100)</label>
          <input type="text" class="inspector-input" id="inspMastheadName" maxlength="100" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div style="margin-top:4px;">
          <label class="id-form-label">Número de Edición (1–99999)</label>
          <input type="number" class="inspector-input" id="inspMastheadNum" min="1" max="99999" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div style="margin-top:4px;">
          <label class="id-form-label">Fecha de Emisión</label>
          <input type="text" class="inspector-input" id="inspMastheadDate" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div style="margin-top:4px;">
          <label class="id-form-label">Ciudad / Lugar (máx 100)</label>
          <input type="text" class="inspector-input" id="inspMastheadCity" maxlength="100" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div class="inspector-row" style="margin-top:4px;">
          <div style="flex:1;">
            <label class="id-form-label">Oreja Izquierda</label>
            <input type="text" class="inspector-input" id="inspMastheadLeftEar" onchange="applyMastheadProps()" style="width:100%;">
          </div>
          <div style="flex:1;">
            <label class="id-form-label">Oreja Derecha</label>
            <input type="text" class="inspector-input" id="inspMastheadRightEar" onchange="applyMastheadProps()" style="width:100%;">
          </div>
        </div>
      </div>
    </div>

    {{-- Article Dedicated Inspector --}}
    <div class="inspector-section" id="inspArticle" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-newspaper" style="color:#4ade80;margin-right:5px;"></i> Contenido de Artículo</span>
      </div>
      <div class="inspector-body">
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <label class="id-form-label">Titular Principal *</label>
            <span id="titularCharCount" style="font-size:0.65rem;color:#94a3b8;">0/200</span>
          </div>
          <input type="text" class="inspector-input" id="inspArticleTitular" maxlength="250" oninput="handleArticleTitularInput(this)" style="width:100%;font-weight:700;">
          <div id="inspArticleTitularError" class="field-error-msg">El titular no puede estar vacío ni superar los 200 caracteres.</div>
        </div>
        <div style="margin-top:6px;">
          <label class="id-form-label">Subtítulo / Bajada (máx 200)</label>
          <input type="text" class="inspector-input" id="inspArticleSubtitulo" maxlength="200" oninput="applyArticleProps()" style="width:100%;">
        </div>
        <div style="margin-top:6px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <label class="id-form-label">Cuerpo de Texto</label>
            <span id="cuerpoCharCount" style="font-size:0.65rem;color:#94a3b8;">0/1000</span>
          </div>
          <textarea class="inspector-input" id="inspArticleCuerpo" maxlength="1000" rows="4" oninput="applyArticleProps()" style="width:100%;font-size:0.75rem;resize:vertical;"></textarea>
        </div>
        <div class="inspector-row" style="margin-top:6px;">
          <div style="flex:1;">
            <label class="id-form-label">Autor (máx 100)</label>
            <input type="text" class="inspector-input" id="inspArticleAutor" maxlength="100" oninput="applyArticleProps()" style="width:100%;">
          </div>
          <div style="flex:1;">
            <label class="id-form-label">Sección (máx 100)</label>
            <input type="text" class="inspector-input" id="inspArticleCatLabel" maxlength="100" oninput="applyArticleProps()" style="width:100%;">
          </div>
        </div>
        <div class="inspector-row" style="margin-top:6px;align-items:center;">
          <span class="inspector-label" style="min-width:70px;">Color Sección</span>
          <input type="color" class="inspector-input" id="inspArticleCatColor" value="#D71920" onchange="applyArticleProps()" style="width:36px;padding:1px 2px;cursor:pointer;">
          <div style="display:flex;align-items:center;gap:4px;margin-left:auto;">
            <span class="corp-palette-btn" style="background:#0B1F3A;" onclick="setArticleColor('#0B1F3A')" title="Azul Corporativo"></span>
            <span class="corp-palette-btn" style="background:#D71920;" onclick="setArticleColor('#D71920')" title="Rojo Corporativo"></span>
            <span class="corp-palette-btn" style="background:#000000;" onclick="setArticleColor('#000000')" title="Negro"></span>
            <span class="corp-palette-btn" style="background:#FFFFFF;" onclick="setArticleColor('#FFFFFF')" title="Blanco"></span>
          </div>
        </div>

        {{-- Linked News Status Box --}}
        <div id="inspArticleLinkedBox" style="display:none;margin-top:10px;padding:8px;background:rgba(2,132,199,0.12);border:1px solid #0284c7;border-radius:4px;">
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:0.72rem;color:#38bdf8;font-weight:700;"><i class="fas fa-link me-1"></i> Noticia Vinculada</span>
            <button type="button" class="id-btn id-btn-ghost" onclick="unlinkArticleNews()" style="font-size:0.65rem;color:#f87171;padding:2px 6px;border:1px solid rgba(248,113,113,0.3);">
              Desvincular
            </button>
          </div>
          <div id="inspArticleLinkedTitle" style="font-size:0.68rem;color:#cbd5e1;margin-top:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></div>
        </div>
      </div>
    </div>

    {{-- Text & Headline Inspector --}}
    <div class="inspector-section" id="inspText" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-font" style="margin-right:5px;"></i> Tipografía & Formato</span>
      </div>
      <div class="inspector-body">
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Fuente</span>
          <select class="inspector-select" id="inspFontFamily" onchange="applyTextProps()">
            <option value="'Montserrat',sans-serif">Montserrat (Corporativa)</option>
            <option value="'Bebas Neue',sans-serif">Bebas Neue (Titulares)</option>
            <option value="'Source Sans 3',sans-serif">Source Sans 3</option>
            <option value="'Playfair Display',serif">Playfair Display</option>
            <option value="'Anton',sans-serif">Anton</option>
            <option value="'Oswald',sans-serif">Oswald</option>
            <option value="'Merriweather',serif">Merriweather</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Tamaño</span>
          <input type="number" class="inspector-input" id="inspFontSize" value="14" min="6" max="300" onchange="applyTextProps()">
          <span class="inspector-label">px</span>
          <span class="inspector-label" style="min-width:40px;">Línea</span>
          <input type="number" class="inspector-input" id="inspLineH" value="1.4" min="0.8" max="4" step="0.05" onchange="applyTextProps()">
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Columnas</span>
          <input type="number" class="inspector-input" id="inspTextCols" value="1" min="1" max="6" onchange="applyTextProps()">
          <span class="inspector-label" style="min-width:48px;">Alinear</span>
          <select class="inspector-select" id="inspTextAlign" onchange="applyTextProps()">
            <option value="left">Izquierda</option>
            <option value="center">Centro</option>
            <option value="right">Derecha</option>
            <option value="justify">Justificado</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Color</span>
          <input type="color" class="inspector-input" id="inspTextColor" value="#111111" onchange="applyTextProps()" style="width:36px;padding:1px 2px;cursor:pointer;">
          <span class="inspector-label" style="min-width:48px;">Fondo</span>
          <input type="color" class="inspector-input" id="inspTextBg" value="#ffffff" onchange="applyTextProps()" style="width:36px;padding:1px 2px;cursor:pointer;">
          <button class="id-ctrl-btn" onclick="clearTextBg()" title="Sin fondo" style="font-size:0.65rem;">Transp.</button>
        </div>
        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
          <span style="font-size:0.65rem;color:#94a3b8;">Colores:</span>
          <span class="corp-palette-btn" style="background:#0B1F3A;" onclick="applyQuickTextColor('#0B1F3A')" title="Azul Corporativo"></span>
          <span class="corp-palette-btn" style="background:#D71920;" onclick="applyQuickTextColor('#D71920')" title="Rojo Corporativo"></span>
          <span class="corp-palette-btn" style="background:#000000;" onclick="applyQuickTextColor('#000000')" title="Negro"></span>
          <span class="corp-palette-btn" style="background:#FFFFFF;" onclick="applyQuickTextColor('#FFFFFF')" title="Blanco"></span>
        </div>
      </div>
    </div>

    {{-- Image Inspector --}}
    <div class="inspector-section" id="inspImage" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-image" style="margin-right:5px;"></i> Imagen & Fotonoticia</span>
      </div>
      <div class="inspector-body">
        <button class="insp-full-btn" id="btnUploadImageInsp" onclick="openImagePicker()">
          <i class="fas fa-cloud-upload-alt"></i> Subir / Cambiar Imagen...
        </button>
        <div style="margin-top:4px;">
          <label class="id-form-label">URL de la Imagen</label>
          <input type="url" class="inspector-input" id="inspImgUrl" placeholder="https://..." onchange="applyImageUrl()" style="width:100%;">
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:50px;">Ajuste</span>
          <select class="inspector-select" id="inspImgFit" onchange="applyImageFit()">
            <option value="cover">Cover (Recortar)</option>
            <option value="contain">Contain (Completa)</option>
            <option value="fill">Fill (Estirar)</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:50px;">Borde</span>
          <input type="color" class="inspector-input" id="inspImgBorder" value="#000000" onchange="applyImageBorderColor()" style="width:30px;padding:1px 2px;">
          <input type="number" class="inspector-input" id="inspImgBorderW" value="0" min="0" max="20" onchange="applyImageBorder()" style="width:50px;" title="Grosor">
          <span class="inspector-label">px</span>
        </div>
      </div>
    </div>

    {{-- QR Inspector --}}
    <div class="inspector-section" id="inspQr" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-qrcode" style="color:#10b981;margin-right:5px;"></i> Código QR Digital</span>
      </div>
      <div class="inspector-body">
        <div>
          <label class="id-form-label">Enlace / URL de Destino</label>
          <input type="url" class="inspector-input" id="inspQrUrl" placeholder="https://latitud18.com/..." onchange="applyQrProps()" style="width:100%;">
        </div>
        <div style="margin-top:6px;">
          <label class="id-form-label">Texto / Etiqueta inferior</label>
          <input type="text" class="inspector-input" id="inspQrLabel" placeholder="Escanea para ver..." onchange="applyQrProps()" style="width:100%;">
        </div>
      </div>
    </div>

    {{-- Ad Module Inspector --}}
    <div class="inspector-section" id="inspAd" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-ad" style="color:#f59e0b;margin-right:5px;"></i> Módulo Publicitario</span>
      </div>
      <div class="inspector-body">
        <div>
          <label class="id-form-label">Formato Oficial (14 Formatos)</label>
          <select class="inspector-select" id="inspAdFormat" onchange="changeSelectedAdFormat(this.value)" style="width:100%;">
            <option value="A1">A1 — Banner Superior (932x227 px / 24.7x6.0 cm)</option>
            <option value="A2">A2 — Banner Interior (932x227 px / 24.7x6.0 cm)</option>
            <option value="B1">B1 — Media Página Vertical (499x983 px / 13.2x26.0 cm)</option>
            <option value="B2">B2 — Media Página Vertical (499x983 px / 13.2x26.0 cm)</option>
            <option value="C1">C1 — Media Página Horizontal (1006x484 px / 26.6x12.8 cm)</option>
            <option value="C2">C2 — Media Página Horizontal (1006x484 px / 26.6x12.8 cm)</option>
            <option value="D1">D1 — 1/4 Página Vertical (242x492 px / 6.4x13.0 cm)</option>
            <option value="D2">D2 — 1/4 Página Vertical (242x492 px / 6.4x13.0 cm)</option>
            <option value="E1">E1 — 1/4 Página Horizontal (499x235 px / 13.2x6.2 cm)</option>
            <option value="E2">E2 — 1/4 Página Horizontal (499x235 px / 13.2x6.2 cm)</option>
            <option value="E3">E3 — 1/4 Página Horizontal (499x235 px / 13.2x6.2 cm)</option>
            <option value="E4">E4 — 1/4 Página Horizontal (499x235 px / 13.2x6.2 cm)</option>
            <option value="F1">F1 — Pie de Página (1006x182 px / 26.6x4.8 cm)</option>
            <option value="F2">F2 — Pie de Página (1006x182 px / 26.6x4.8 cm)</option>
          </select>
        </div>
        <div style="margin-top:6px;">
          <label class="id-form-label">Estado del Espacio</label>
          <div style="display:flex;gap:6px;">
            <button type="button" class="id-btn" id="btnAdStatusDisponible" onclick="setAdStatus('disponible')" style="flex:1;background:#1e40af;color:#fff;font-size:0.7rem;padding:4px;">
              Disponible
            </button>
            <button type="button" class="id-btn id-btn-ghost" id="btnAdStatusOcupado" onclick="setAdStatus('ocupado')" style="flex:1;font-size:0.7rem;padding:4px;">
              Ocupado
            </button>
          </div>
        </div>
        <div style="margin-top:6px;">
          <label class="id-form-label">Nombre del Anunciante</label>
          <input type="text" class="inspector-input" id="inspAdAdvertiserName" placeholder="Ej: Banco Mercantil Santa Cruz" oninput="applyAdAdvertiserName(this.value)" style="width:100%;">
        </div>
        <div style="margin-top:6px;">
          <label class="id-form-label">Imagen Publicitaria (JPG, PNG ≤ 10MB)</label>
          <input type="file" id="adImageFileInput" accept="image/jpeg,image/png,image/jpg" style="display:none;" onchange="uploadAdImageFile(this)">
          <button type="button" class="insp-full-btn" id="btnUploadAdImage" onclick="document.getElementById('adImageFileInput').click()">
            <i class="fas fa-upload"></i> <span id="adImageUploadBtnLabel">Subir Imagen Anunciante</span>
          </button>
          <div id="adImageThumbPreview" style="margin-top:6px;display:none;text-align:center;">
            <img id="adImageThumbImg" src="" style="max-width:100%;max-height:80px;border-radius:3px;border:1px solid #475569;">
          </div>
        </div>
      </div>
    </div>

    {{-- Actions & Ordering --}}
    <div class="inspector-section" id="inspActions" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-layer-group" style="margin-right:5px;"></i> Capas y Acciones</span>
      </div>
      <div class="inspector-body" style="display:flex;flex-direction:column;gap:6px;">
        <div class="inspector-row">
          <button class="insp-full-btn" onclick="bringToFront()" style="flex:1;" title="Traer al frente absoluto">
            <i class="fas fa-angle-double-up"></i> Al Frente
          </button>
          <button class="insp-full-btn" onclick="sendToBack()" style="flex:1;" title="Enviar al fondo absoluto">
            <i class="fas fa-angle-double-down"></i> Al Fondo
          </button>
        </div>
        <div class="inspector-row">
          <button class="insp-full-btn" onclick="bringForward()" style="flex:1;" title="Subir una capa (+1)">
            <i class="fas fa-arrow-up"></i> Avanzar (+1)
          </button>
          <button class="insp-full-btn" onclick="sendBackward()" style="flex:1;" title="Bajar una capa (-1)">
            <i class="fas fa-arrow-down"></i> Retroceder (-1)
          </button>
        </div>
        <button class="insp-full-btn" id="inspLockBtn" onclick="toggleLockSelected()" title="Bloquear / Desbloquear elemento para evitar modificaciones accidentales">
          <i class="fas fa-lock"></i> <span id="inspLockBtnText">Bloquear Elemento</span>
        </button>
        <button class="insp-full-btn" onclick="duplicateSelected()">
          <i class="fas fa-clone"></i> Duplicar Elemento
        </button>
        <button class="insp-full-btn danger" onclick="deleteSelected()">
          <i class="fas fa-trash-alt"></i> Eliminar Elemento
        </button>
      </div>
    </div>
  </div>

  {{-- ── 6. BOTTOM PAGES PANEL ── --}}
  <div class="id-pages-panel" id="pagesPanel">
    <span class="pages-panel-label"><i class="fas fa-book-open" style="margin-right:4px;"></i> Páginas</span>
    <div style="width:1px;height:60px;background:var(--id-border);margin:0 4px;"></div>
    <div id="pageThumbs" style="display:flex;gap:8px;align-items:center;"></div>
    <div class="page-add-btn" onclick="addNewPage()" title="Agregar página nueva">
      <div class="page-add-preview"><i class="fas fa-plus"></i></div>
      <span class="page-thumb-label">Nueva</span>
    </div>
  </div>

</div>

{{-- ═══════════════════════════════════════════════════════
     NEWS DRAWER (DATABASE INTEGRATION)
═══════════════════════════════════════════════════════ --}}
<div class="id-news-drawer" id="newsDrawer">
  <div class="drawer-header">
    <div style="display:flex;align-items:center;gap:8px;">
      <i class="fas fa-newspaper" style="color:var(--id-accent);font-size:1.1rem;"></i>
      <div>
        <div style="font-weight:800;font-size:0.82rem;color:#fff;">Noticias Publicadas</div>
        <div style="font-size:0.65rem;color:var(--id-text-muted);">Inserta contenido real con un solo clic</div>
      </div>
    </div>
    <button style="background:transparent;border:none;color:#888;cursor:pointer;font-size:1rem;" onclick="toggleNewsDrawer()">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div class="drawer-filters">
    <select class="drawer-filter-select" id="drawerCatFilter" onchange="filterDrawerNews()">
      <option value="all">Todas las Secciones</option>
      @foreach($categorias as $cat)
        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
      @endforeach
    </select>
    <input class="drawer-search" type="text" id="drawerSearch" placeholder="Buscar noticia..." oninput="handleNewsSearchInput(this.value)">
  </div>
  <div class="drawer-news-list" id="drawerNewsList">
    @foreach($noticiasPublicadas as $noticia)
      @php
        $newsData = [
          "id" => $noticia->id,
          "titulo" => $noticia->titulo,
          "bajada" => \Illuminate\Support\Str::limit(strip_tags($noticia->contenido), 200),
          "contenido" => strip_tags($noticia->contenido),
          "categoria" => $noticia->category->name ?? "General",
          "categoria_color" => $noticia->category->color ?? "#D71920",
          "imagen" => $noticia->getImageUrl(),
          "autor" => $noticia->autor ?? "REDACCIÓN"
        ];
      @endphp
      <div class="drawer-news-card" data-category="{{ $noticia->category_id }}" data-title="{{ strtolower($noticia->titulo) }}" onclick='onDrawerNewsCardClick(@json($newsData))' style="cursor:pointer;" title="Haz clic para asignar directamente al marco seleccionado o usa los botones inferiores">
        <div class="dnc-header">
          @if($noticia->imagen)
            <img class="dnc-thumb" src="{{ $newsData['imagen'] }}" alt="" onerror="this.onerror=null;this.src='{{ asset('images/default-news.svg') }}'">
          @else
            <div class="dnc-no-thumb"><i class="fas fa-image"></i></div>
          @endif
          <div class="dnc-info">
            <span class="dnc-cat">{{ $newsData['categoria'] }}</span>
            <div class="dnc-title">{{ $noticia->titulo }}</div>
          </div>
        </div>
        <div class="dnc-actions" onclick="event.stopPropagation()">
          <span style="font-size:0.58rem;color:var(--id-text-muted);margin-right:2px;">Insertar:</span>
          <button class="dnc-btn primary" onclick='insertNewsBlock(@json($newsData), "article")'>
            <i class="fas fa-newspaper"></i> Artículo Completo
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock(@json($newsData), "headline")'>
            <i class="fas fa-heading"></i> Solo Titular
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock(@json($newsData), "photo_caption")'>
            <i class="fas fa-camera"></i> Fotonoticia
          </button>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: BIBLIOTECA DE MAQUETAS Y PLANTILLAS INDESIGN
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="templateModal">
  <div class="id-modal" style="max-width:980px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-th-large" style="color:var(--id-accent);"></i> Biblioteca de Maquetas y Plantillas InDesign</div>
      <button class="id-modal-close" onclick="closeModal('templateModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
        <p style="font-size:0.75rem;color:#94a3b8;margin:0;max-width:550px;">
          Selecciona una maqueta prediseñada para aplicar a la página actual con 1 solo clic. También puedes guardar tu propio diseño o exportar/importar archivos <code>.latitud-template</code>.
        </p>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <button class="id-btn id-btn-ghost" onclick="openSaveTemplateModal()" style="border:1px solid rgba(255,255,255,0.15);color:#4ade80;">
            <i class="fas fa-save"></i> Guardar como Plantilla
          </button>
          <button class="id-btn id-btn-ghost" onclick="openImportTemplateModal()" style="border:1px solid rgba(255,255,255,0.15);color:#38bdf8;">
            <i class="fas fa-file-import"></i> Importar Archivo
          </button>
          <button class="id-btn id-btn-ghost" onclick="exportCurrentAsTemplateFile()" style="border:1px solid rgba(255,255,255,0.15);color:#f59e0b;" title="Descargar diseño actual en archivo .latitud-template">
            <i class="fas fa-file-export"></i> Exportar Página
          </button>
        </div>
      </div>

      {{-- Search & Tabs --}}
      <div style="margin-bottom:10px;">
        <input type="text" id="tplSearchInput" class="id-form-input" placeholder="Buscar plantilla por nombre o sección..." oninput="searchTemplates(this.value)" style="margin-bottom:10px;padding:6px 12px;">
      </div>

      <div class="tpl-tabs" id="tplTabs">
        <button class="tpl-tab-btn active" onclick="filterTemplateCards('all', this)"><i class="fas fa-border-all me-1"></i> Todas</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('portada', this)"><i class="fas fa-newspaper me-1"></i> Portadas</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('editorial', this)"><i class="fas fa-feather-alt me-1"></i> Editorial</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('politica', this)"><i class="fas fa-landmark me-1"></i> Política</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('santacruz', this)"><i class="fas fa-map-marker-alt me-1"></i> Santa Cruz</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('pais', this)"><i class="fas fa-flag me-1"></i> País</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('economia', this)"><i class="fas fa-chart-line me-1"></i> Economía</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('seguridad', this)"><i class="fas fa-shield-alt me-1"></i> Seguridad</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('mundo', this)"><i class="fas fa-globe me-1"></i> Mundo</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('deportes', this)"><i class="fas fa-futbol me-1"></i> Deportes</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('publicidad', this)"><i class="fas fa-bullhorn me-1"></i> Publicidad</button>
        <button class="tpl-tab-btn" onclick="filterTemplateCards('custom', this)" style="border-color:rgba(74,222,128,0.4);"><i class="fas fa-star me-1 text-warning"></i> Mis Plantillas</button>
      </div>

      {{-- Grid de Plantillas --}}
      <div class="template-grid" id="templatesGridContainer">
        {{-- Se renderiza dinámicamente con renderTemplateCards() --}}
      </div>
    </div>
    <div class="id-modal-footer">
      <button class="id-btn id-btn-ghost" onclick="closeModal('templateModal')">Cerrar Biblioteca</button>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: GUARDAR PÁGINA ACTUAL COMO PLANTILLA
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="saveTemplateModal">
  <div class="id-modal" style="max-width:500px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-bookmark text-success"></i> Guardar como Plantilla InDesign</div>
      <button class="id-modal-close" onclick="closeModal('saveTemplateModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <p style="font-size:0.75rem;color:#cbd5e1;margin-bottom:14px;">
        La distribución, titulares, tipografías y marcos de la <strong>página actual</strong> se guardarán como una plantilla reutilizable en tu biblioteca.
      </p>
      <div class="id-form-row">
        <label class="id-form-label">Nombre de la Plantilla *</label>
        <input type="text" id="tplSaveName" class="id-form-input" placeholder="Ej: Portada Domingo Especial" required>
      </div>
      <div class="id-form-row">
        <label class="id-form-label">Categoría *</label>
        <select id="tplSaveCategory" class="id-form-input">
          <option value="portadas">Portadas</option>
          <option value="interior" selected>Páginas Interiores / Reportaje</option>
          <option value="opinion">Opinión & Editorial</option>
          <option value="deportes">Contra Ataque / Deportes</option>
          <option value="contraportada">Contraportada</option>
          <option value="especial">Edición Especial</option>
        </select>
      </div>
      <div class="id-form-row">
        <label class="id-form-label">Descripción Breve</label>
        <textarea id="tplSaveDescription" class="id-form-input" rows="2" placeholder="Resumen del estilo y estructura editorial..."></textarea>
      </div>
      <div class="id-form-row">
        <label class="id-form-label">Color de Distintivo</label>
        <input type="color" id="tplSaveColor" value="#0284c7" style="height:32px;width:60px;padding:2px;cursor:pointer;background:#18191c;border:1px solid #334155;border-radius:4px;">
      </div>
    </div>
    <div class="id-modal-footer">
      <button type="button" class="id-btn id-btn-ghost" onclick="closeModal('saveTemplateModal')">Cancelar</button>
      <button type="button" class="id-btn id-btn-success" onclick="submitSaveTemplate()"><i class="fas fa-check"></i> Guardar en Biblioteca</button>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: IMPORTAR PLANTILLA (.latitud-template)
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="importTemplateModal">
  <div class="id-modal" style="max-width:520px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-file-import text-primary"></i> Importar Archivo de Plantilla</div>
      <button class="id-modal-close" onclick="closeModal('importTemplateModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <div style="border:2px dashed var(--id-border);border-radius:6px;padding:26px;text-align:center;cursor:pointer;background:rgba(255,255,255,0.02);" onclick="document.getElementById('templateFileInput').click()">
        <i class="fas fa-file-code" style="font-size:2.2rem;color:var(--id-accent2);margin-bottom:8px;"></i>
        <p style="font-size:0.8rem;font-weight:700;margin-bottom:4px;color:#fff;">Selecciona un archivo .latitud-template o .json</p>
        <p style="font-size:0.68rem;color:#888;">Permite compartir maquetaciones entre redactores o equipos de diseño</p>
        <input type="file" id="templateFileInput" style="display:none;" accept=".latitud-template,.json" onchange="onTemplateFileSelected(this)">
      </div>
      <div id="selectedTemplateFileInfo" style="margin-top:10px;font-size:0.75rem;color:#4ade80;display:none;">
        <i class="fas fa-check-circle"></i> Archivo seleccionado: <strong id="selectedTemplateFileName"></strong>
      </div>
    </div>
    <div class="id-modal-footer">
      <button type="button" class="id-btn id-btn-ghost" onclick="closeModal('importTemplateModal')">Cancelar</button>
      <button type="button" class="id-btn id-btn-primary" id="btnSubmitImportTpl" onclick="submitImportTemplate()" disabled><i class="fas fa-upload"></i> Importar Plantilla</button>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: PROGRAMAR PUBLICACIÓN AUTOMÁTICA
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="scheduleModal">
  <div class="id-modal" style="max-width:480px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-clock" style="color:#a855f7;"></i> Programar Publicación Automática</div>
      <button class="id-modal-close" onclick="closeModal('scheduleModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <p style="font-size:0.75rem;color:#cbd5e1;margin-bottom:14px;">
        Configura la fecha y hora exacta en que la edición <strong>{{ $currentEdicion['numero_edicion'] }}</strong> pasará automáticamente a estado <strong>Publicada</strong> y se activará en el portal web.
      </p>
      <div class="id-form-row">
        <label class="id-form-label">Fecha y Hora de Publicación *</label>
        <input type="datetime-local" id="schedDateTimeInput" class="id-form-input" required>
      </div>
      <div style="display:flex;gap:6px;margin-top:8px;">
        <button type="button" class="id-btn id-btn-ghost" onclick="setQuickSchedule(1)" style="font-size:0.7rem;padding:3px 8px;border:1px solid #334155;">
          Mañana 06:00 AM
        </button>
        <button type="button" class="id-btn id-btn-ghost" onclick="setQuickSchedule(7)" style="font-size:0.7rem;padding:3px 8px;border:1px solid #334155;">
          Próximo Domingo 07:00 AM
        </button>
      </div>
    </div>
    <div class="id-modal-footer">
      <button type="button" class="id-btn id-btn-ghost" onclick="closeModal('scheduleModal')">Cancelar</button>
      <button type="button" class="id-btn" style="background:#9333ea;color:#fff;border:none;" onclick="submitSchedulePublication()">
        <i class="fas fa-calendar-check"></i> Confirmar Programación
      </button>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: NUEVA EDICIÓN
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="newEdicionModal">
  <div class="id-modal" style="max-width:500px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-plus-circle text-danger"></i> Nueva Edición Semanal</div>
      <button class="id-modal-close" onclick="closeModal('newEdicionModal')"><i class="fas fa-times"></i></button>
    </div>
    <form action="{{ route('admin.periodico.store') }}" method="POST">
      @csrf
      <div class="id-modal-body">
        <div class="id-form-row">
          <label class="id-form-label">Número de Edición</label>
          <input type="text" name="numero_edicion" class="id-form-input" value="N° {{ rand(11000, 19999) }}" required>
        </div>
        <div class="id-form-row">
          <label class="id-form-label">Fecha de Emisión</label>
          <input type="text" name="fecha" class="id-form-input" value="{{ date('d \d\e F \d\e Y') }}" required>
        </div>
        <div class="id-form-row">
          <label class="id-form-label">Precio</label>
          <input type="text" name="precio" class="id-form-input" value="Bs 7,00">
        </div>
        <div class="id-form-row">
          <label class="id-form-label"><i class="fas fa-layer-group text-danger me-1"></i> Seleccionar Plantilla Base:</label>
          <select name="plantilla_id" class="id-form-input" style="font-weight:600;">
            @if(isset($plantillasModels) && $plantillasModels->count() > 0)
              @foreach($plantillasModels as $pTpl)
                <option value="{{ $pTpl->id }}" {{ $pTpl->id === 'tpl_portada_clasica' ? 'selected' : '' }}>
                  {{ $pTpl->nombre }} ({{ ucfirst($pTpl->categoria) }})
                </option>
              @endforeach
            @else
              <option value="tpl_portada_clasica">Portada clásica (Portadas)</option>
              <option value="tpl_portada_deportiva">Portada deportiva (Portadas)</option>
              <option value="tpl_politica">Política (Nacional)</option>
              <option value="tpl_economia">Economía (Finanzas)</option>
              <option value="tpl_entrevista">Página de entrevista (Diálogos)</option>
              <option value="tpl_fotografica">Página fotográfica (Fotorreportaje)</option>
              <option value="tpl_contraataque">Contra Ataque (Deportes)</option>
              <option value="tpl_publicidad">Publicidad (Comercial)</option>
              <option value="tpl_contraportada">Contraportada (Cultura)</option>
            @endif
          </select>
          <small style="color:#94a3b8;font-size:0.68rem;display:block;margin-top:4px;">
            <i class="fas fa-shield-alt text-success"></i> La plantilla original no se alterará; se creará una copia editable e independiente.
          </small>
        </div>
        <div class="id-form-row">
          <label class="id-form-label">O clonar desde edición existente:</label>
          <select name="clonar_de" class="id-form-input">
            <option value="">-- No clonar (Usar la plantilla base elegida) --</option>
            @foreach($ediciones as $ed)
              <option value="{{ $ed['id'] }}">{{ $ed['numero_edicion'] }} ({{ $ed['fecha'] }})</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="id-modal-footer">
        <button type="button" class="id-btn id-btn-ghost" onclick="closeModal('newEdicionModal')">Cancelar</button>
        <button type="submit" class="id-btn id-btn-danger"><i class="fas fa-check"></i> Crear Edición</button>
      </div>
    </form>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: IMAGE PICKER & UPLOAD
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="imagePickerModal">
  <div class="id-modal" style="max-width:540px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-image" style="color:var(--id-accent2);"></i> Insertar Imagen en el Marco</div>
      <button class="id-modal-close" onclick="closeModal('imagePickerModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <div style="border:2px dashed var(--id-border);border-radius:6px;padding:28px;text-align:center;cursor:pointer;background:rgba(255,255,255,0.02);" onclick="document.getElementById('imageFileInput').click()">
        <i class="fas fa-cloud-upload-alt" style="font-size:2.2rem;color:var(--id-accent);margin-bottom:8px;"></i>
        <p style="font-size:0.8rem;font-weight:700;margin-bottom:4px;color:#fff;">Haz clic para subir una foto desde tu equipo</p>
        <p style="font-size:0.68rem;color:#888;">Formatos: JPG, PNG, WEBP (Hasta 10MB)</p>
        <input type="file" id="imageFileInput" style="display:none;" accept="image/*" onchange="uploadImageForFrame(this)">
      </div>
      <div style="margin-top:16px;">
        <label class="id-form-label">O pega un enlace web (URL):</label>
        <div style="display:flex;gap:8px;">
          <input type="url" class="id-form-input" id="imageUrlInput" placeholder="https://ejemplo.com/foto.jpg" style="flex:1;">
          <button class="id-btn id-btn-primary" onclick="applyImageUrlToFrame()"><i class="fas fa-check"></i> Aplicar</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: EXPORTAR A PDF
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="pdfExportModal">
  <div class="id-modal" style="max-width:500px;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-file-pdf text-danger"></i> Exportar Edición a PDF</div>
      <button class="id-modal-close" onclick="closeModal('pdfExportModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <p style="font-size:0.78rem;color:#cbd5e1;margin-bottom:14px;">
        El PDF de <strong>{{ $currentEdicion['numero_edicion'] }}</strong> se genera en el servidor
        con todas las páginas compuestas. Es el mismo archivo que descarga el público.
      </p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <a class="insp-full-btn" href="{{ route('admin.periodico.pdf', $currentEdicion['id']) }}"
           style="padding:12px;font-size:0.85rem;text-align:center;text-decoration:none;">
          <i class="fas fa-file-pdf text-danger"></i> Descargar PDF real ({{ count($currentEdicion['paginas'] ?? []) }} páginas)
        </a>
      </div>
      @if (!empty($currentEdicion['pdf_url']))
        <p style="font-size:0.7rem;color:#94a3b8;margin-top:12px;">
          <i class="fas fa-info-circle"></i>
          Existe un PDF subido para esta edición, se entregará ese archivo en lugar del generado.
        </p>
      @endif
    </div>
    <div class="id-modal-footer">
      <button class="id-btn id-btn-ghost" onclick="closeModal('pdfExportModal')">Cerrar</button>
    </div>
  </div>
</div>

{{-- Toast notification --}}
<div class="id-toast" id="idToast">
  <i class="fas fa-check-circle" id="idToastIcon"></i>
  <span id="idToastMsg">Guardado</span>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: PREVISUALIZACIÓN FULL-SCREEN (TASK 15)
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="previewModal" style="padding:0;background:rgba(10,12,16,0.96);z-index:99999;">
  <div style="width:100vw;height:100vh;display:flex;flex-direction:column;background:#121315;color:#fff;">
    {{-- Header --}}
    <div style="height:52px;background:#18191c;border-bottom:1px solid #2d3035;display:flex;align-items:center;justify-content:space-between;padding:0 20px;">
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-weight:900;color:#D71920;letter-spacing:1px;font-family:'Montserrat',sans-serif;font-size:1.1rem;">LATITUD 18</span>
        <span style="color:#94a3b8;font-size:0.8rem;">Previsualización de Edición — {{ $currentEdicion['numero_edicion'] ?? '' }}</span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <button type="button" class="id-btn id-btn-ghost" onclick="previewPrevPage()" style="padding:4px 10px;"><i class="fas fa-chevron-left me-1"></i> Anterior</button>
        <span style="font-size:0.8rem;color:#cbd5e1;">Página</span>
        <input type="number" id="previewPageInput" min="1" max="12" value="1" onchange="previewGoToPage(this.value)" style="width:50px;background:#2b2d33;border:1px solid #475569;color:#fff;text-align:center;border-radius:4px;padding:3px;">
        <span style="font-size:0.8rem;color:#94a3b8;" id="previewTotalPages">de 12</span>
        <button type="button" class="id-btn id-btn-ghost" onclick="previewNextPage()" style="padding:4px 10px;">Siguiente <i class="fas fa-chevron-right ms-1"></i></button>
        <div style="width:1px;height:24px;background:#2d3035;margin:0 6px;"></div>
        <button type="button" class="id-btn" onclick="previewEditCurrentPage()" style="background:#0284c7;color:#fff;border:none;padding:5px 12px;font-size:0.75rem;">
          <i class="fas fa-edit me-1"></i> Editar esta Página
        </button>
        <button type="button" class="id-modal-close" onclick="closeModal('previewModal')" style="margin-left:8px;font-size:1.2rem;"><i class="fas fa-times"></i></button>
      </div>
    </div>
    {{-- Canvas Stage --}}
    <div style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;padding:24px;background:#0b0f19;" id="previewCanvasContainer">
      <div id="previewSheetWrapper" style="width:720px;height:1040px;background:#fff;box-shadow:0 15px 45px rgba(0,0,0,0.6);position:relative;overflow:hidden;transform-origin:center top;">
        {{-- Dynamically rendered non-interactive page elements --}}
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: INVENTARIO DE PUBLICIDAD CROSS-PAGE (TASK 12)
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="publicidadModal">
  <div class="id-modal" style="max-width:960px;max-height:85vh;display:flex;flex-direction:column;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-bullhorn text-warning"></i> Inventario de Publicidad (Cross-Page)</div>
      <button class="id-modal-close" onclick="closeModal('publicidadModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body" style="overflow-y:auto;flex:1;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <p style="font-size:0.75rem;color:#94a3b8;margin:0;">
          Inventario consolidado de los 14 formatos de módulos publicitarios en todas las páginas de la edición.
        </p>
        <button type="button" class="id-btn id-btn-ghost" onclick="renderPublicidadInventory()" style="font-size:0.7rem;padding:3px 8px;">
          <i class="fas fa-sync-alt me-1"></i> Actualizar
        </button>
      </div>
      <div class="table-responsive">
        <table class="table table-dark table-hover" style="font-size:0.75rem;margin:0;vertical-align:middle;">
          <thead>
            <tr style="border-bottom:1px solid #334155;color:#cbd5e1;">
              <th>Formato</th>
              <th>Página</th>
              <th>Dimensiones</th>
              <th>Estado</th>
              <th>Anunciante</th>
              <th>Imagen</th>
              <th style="text-align:right;">Acciones</th>
            </tr>
          </thead>
          <tbody id="publicidadTableBody">
            {{-- Dynamic rows --}}
          </tbody>
        </table>
      </div>
    </div>
    <div class="id-modal-footer">
      <button class="id-btn id-btn-ghost" onclick="closeModal('publicidadModal')">Cerrar</button>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: PLANTILLAS DE EDICIÓN (12 PÁGINAS) (TASK 13)
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="plantillaEdicionModal">
  <div class="id-modal" style="max-width:1050px;max-height:88vh;display:flex;flex-direction:column;">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-book text-info"></i> Plantilla de Edición Completa (12 Slots)</div>
      <button class="id-modal-close" onclick="closeModal('plantillaEdicionModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body" style="overflow-y:auto;flex:1;">
      <div style="display:flex;gap:20px;flex-wrap:wrap;">
        <div style="flex:1;min-width:320px;">
          <h6 style="color:#fff;font-size:0.85rem;margin-bottom:4px;"><i class="fas fa-th-list me-1 text-danger"></i> Asignación de Secciones por Slot</h6>
          <p style="font-size:0.72rem;color:#94a3b8;margin-bottom:12px;">Cada slot representa una página física del periódico semanal con su sección correspondiente.</p>
          <div id="editionSlotsGrid" style="display:grid;grid-template-columns:repeat(2, 1fr);gap:8px;">
            {{-- 12 slots dynamically injected --}}
          </div>
          <div style="margin-top:14px;background:#1e2024;padding:12px;border-radius:6px;border:1px solid #334155;">
            <div class="id-form-row">
              <label class="id-form-label">Nombre de esta Plantilla de Edición *</label>
              <input type="text" id="editionTemplateName" class="id-form-input" placeholder="Ej: Esquema Semanal Estándar" required>
            </div>
            <div class="id-form-row" style="margin-bottom:8px;">
              <label class="id-form-label">Descripción</label>
              <input type="text" id="editionTemplateDesc" class="id-form-input" placeholder="Ej: Distribución clásica de 12 páginas con 2 páginas de Política y Economía">
            </div>
            <button type="button" class="id-btn id-btn-primary" onclick="submitSaveEditionTemplate()" style="width:100%;">
              <i class="fas fa-save me-1"></i> Guardar Plantilla de Edición
            </button>
          </div>
        </div>
        <div style="width:360px;border-left:1px solid #2d3035;padding-left:16px;">
          <h6 style="color:#fff;font-size:0.85rem;margin-bottom:4px;"><i class="fas fa-layer-group me-1 text-warning"></i> Plantillas Guardadas</h6>
          <p style="font-size:0.72rem;color:#94a3b8;margin-bottom:10px;">Crea una edición completa de 12 páginas de forma transaccional.</p>
          <div id="savedEditionTemplatesList" style="display:flex;flex-direction:column;gap:8px;max-height:480px;overflow-y:auto;">
            {{-- Dynamic list of saved edition templates --}}
          </div>
        </div>
      </div>
    </div>
    <div class="id-modal-footer">
      <button class="id-btn id-btn-ghost" onclick="closeModal('plantillaEdicionModal')">Cerrar</button>
    </div>
  </div>
</div>

@php
    $loadedTemplatesForEditor = isset($plantillasModels)
        ? $plantillasModels->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->nombre,
                'category' => $t->categoria,
                'description' => $t->descripcion ?? '',
                'preview_color' => $t->preview_color ?? '#1e293b',
                'is_custom' => (bool) $t->is_custom,
                'frames' => is_array($t->frames) ? $t->frames : (json_decode($t->frames, true) ?: []),
                'configuracion' => $t->configuracion,
            ];
        })->values()->all()
        : [];
@endphp

<script>
// ═══════════════════════════════════════════════════════
//   INDESIGN-STYLE NEWSPAPER EDITOR — ADVANCED ENGINE
// ═══════════════════════════════════════════════════════

let currentEdicion    = @json($currentEdicion);
let activePageIndex   = 0;
let selectedFrame     = null;
let currentTool       = 'select';
let zoomLevel         = 1.0;
let hasUnsavedChanges = false;
let frameIdCounter    = 1000;
let gridVisible       = false;
let marginsVisible    = true;
let rulersVisible     = false;
let snapGridActive    = true;
let loadedTemplates   = @json($loadedTemplatesForEditor);
let currentFilterCat  = 'all';

// Undo / Redo history
let undoHistory = [];
let redoHistory = [];

const PAPER_FORMATS = {
  tabloid:   { w: 720, h: 1106, name: 'Tabloide Moderno' },
  broadsheet:{ w: 820, h: 1160, name: 'Broadsheet Clásico' },
  compact:   { w: 680, h: 960,  name: 'Compacto A4' }
};

// Catálogo oficial de formatos publicitarios LATITUD 18 (Req 3.1)
const AD_FORMATS = {
  'A1': { label: 'Banner Superior', w: 635, h: 154, w_cm: 24.7, h_cm: 6 },
  'A2': { label: 'Banner Interior', w: 635, h: 154, w_cm: 24.7, h_cm: 6 },
  'B1': { label: 'Media Página Vertical', w: 324, h: 669, w_cm: 12.6, h_cm: 26 },
  'B2': { label: 'Media Página Vertical', w: 324, h: 669, w_cm: 12.6, h_cm: 26 },
  'C1': { label: 'Media Página Horizontal', w: 658, h: 329, w_cm: 25.6, h_cm: 12.8 },
  'C2': { label: 'Media Página Horizontal', w: 658, h: 329, w_cm: 25.6, h_cm: 12.8 },
  'D1': { label: '1/4 Página Vertical', w: 123, h: 334, w_cm: 4.8, h_cm: 13 },
  'D2': { label: '1/4 Página Vertical', w: 123, h: 334, w_cm: 4.8, h_cm: 13 },
  'E1': { label: '1/4 Página Horizontal', w: 324, h: 159, w_cm: 12.6, h_cm: 6.2 },
  'E2': { label: '1/4 Página Horizontal', w: 324, h: 159, w_cm: 12.6, h_cm: 6.2 },
  'E3': { label: '1/4 Página Horizontal', w: 324, h: 159, w_cm: 12.6, h_cm: 6.2 },
  'E4': { label: '1/4 Página Horizontal', w: 324, h: 159, w_cm: 12.6, h_cm: 6.2 },
  'F1': { label: 'Pie de Página', w: 658, h: 123, w_cm: 25.6, h_cm: 4.8 },
  'F2': { label: 'Pie de Página', w: 658, h: 123, w_cm: 25.6, h_cm: 4.8 }
};

// Grafo inmutable de transiciones de estado editorial (Req 8.1)
const ESTADO_TRANSICIONES = {
  'borrador':   ['revision'],
  'revision':   ['aprobado', 'borrador'],
  'aprobado':   ['programado', 'revision'],
  'programado': ['publicado', 'aprobado'],
  'publicado':  []
};

// Mapa oficial de 12 slots para plantillas de edición completa (Req 5.1)
const EDITION_SLOTS = [
  { slot: 1, seccion: 'Portada' },
  { slot: 2, seccion: 'Editorial/Opinión' },
  { slot: 3, seccion: 'Política (A)' },
  { slot: 4, seccion: 'Política (B)' },
  { slot: 5, seccion: 'Santa Cruz (A)' },
  { slot: 6, seccion: 'Santa Cruz (B)' },
  { slot: 7, seccion: 'País (A)' },
  { slot: 8, seccion: 'País (B)' },
  { slot: 9, seccion: 'Economía (A)' },
  { slot: 10, seccion: 'Economía (B)' },
  { slot: 11, seccion: 'Seguridad/Judicial' },
  { slot: 12, seccion: 'Mundo/Deportes/Cultura' }
];

let previewActivePage = 0;
let newsSearchDebounceTimer = null;
let savedEditionTemplates = [];

// Initialize pages frames
(currentEdicion.paginas || []).forEach((p, i) => {
    if (!p.frames) p.frames = [];
    if (!p.id) p.id = 'pg-' + i;
});

document.addEventListener('DOMContentLoaded', () => {
    renderAllPages();
    switchPage(0);
    initInteract();
    setupKeyboard();
    updateZoomDisplay();
    renderTemplateCards(loadedTemplates);
    loadTemplatesCatalog();
    updateTopBarBadges(currentEdicion.estado || (currentEdicion.publicada ? 'publicado' : 'borrador'), currentEdicion.fecha_programada);
    renderAllowedTransitions();
    checkFontsLoaded();
});

function checkFontsLoaded() {
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            ['Montserrat', 'Bebas Neue'].forEach(f => {
                if (!document.fonts.check(`16px "${f}"`)) {
                    console.warn(`[Tipografía] Advertencia no bloqueante: La fuente corporativa "${f}" no cargó en el navegador.`);
                }
            });
        }).catch(err => console.warn('Error verificando fuentes:', err));
    }
}

// ── FORMAT SWITCHER ─────────────────────────────────
function changePaperFormat(formatKey) {
    const f = PAPER_FORMATS[formatKey] || PAPER_FORMATS.tabloid;
    document.documentElement.style.setProperty('--paper-w', f.w + 'px');
    document.documentElement.style.setProperty('--paper-h', f.h + 'px');
    
    (currentEdicion.paginas || []).forEach(page => {
        page.ancho = f.w; page.alto = f.h;
        if (formatKey === 'tabloid') page.configuracion = { ...(page.configuracion || {}), page_width_px:f.w, page_height_px:f.h, page_width_mm:280, page_height_mm:430, margin_px:31 };
        else if (page.configuracion) { delete page.configuracion.page_width_mm; delete page.configuracion.page_height_mm; }
    });
    markUnsaved();
    document.querySelectorAll('.id-paper-sheet').forEach(sheet => {
        sheet.style.width = f.w + 'px';
        sheet.style.minHeight = f.h + 'px';
        sheet.style.height = f.h + 'px';
    });

    showToast(`Formato cambiado a ${f.name} (${f.w}x${f.h}px)`, 'success');
}

// ── RENDER PAGES ───────────────────────────────────
function renderAllPages() {
    const container = document.getElementById('sheetsContainer');
    container.innerHTML = '';

    (currentEdicion.paginas || []).forEach((page, pIdx) => {
        const sheet = createSheetDOM(page, pIdx);
        container.appendChild(sheet);
    });

    renderPageThumbs();
}

function createSheetDOM(page, pIdx) {
    const sheet = document.createElement('div');
    sheet.className = `id-paper-sheet ${pIdx === activePageIndex ? 'active' : ''}`;
    sheet.id = `sheet-${pIdx}`;
    sheet.dataset.page = pIdx;
    sheet.style.width = (page.ancho || 720) + 'px';
    sheet.style.height = (page.alto || 1106) + 'px';

    // Grid overlay
    const gridOverlay = document.createElement('div');
    gridOverlay.className = 'paper-grid-overlay';
    sheet.appendChild(gridOverlay);

    // Margin guide
    const marginGuide = document.createElement('div');
    marginGuide.className = 'paper-margin-guide';
    const margin = page.configuracion?.margin_px ?? 31;
    marginGuide.style.inset = margin + 'px';
    sheet.appendChild(marginGuide);

    // Render existing frames or default layout
    if (page.frames && page.frames.length > 0) {
        page.frames.forEach(fData => {
            const frameEl = buildFrameElement(fData, pIdx);
            sheet.appendChild(frameEl);
        });
    } else {
        // If empty page, auto apply initial default template
        setTimeout(() => {
            if (pIdx === 0 && (!page.frames || page.frames.length === 0)) {
                applyTemplate('la_estrella_portada');
            }
        }, 100);
    }

    // Click on canvas background deselects
    sheet.addEventListener('click', (e) => {
        if (e.target === sheet || e.target === gridOverlay || e.target === marginGuide) {
            deselectAll();
        }
    });

    return sheet;
}

function switchPage(idx) {
    if (idx < 0 || idx >= (currentEdicion.paginas || []).length) return;
    activePageIndex = idx;
    deselectAll();

    document.querySelectorAll('.id-paper-sheet').forEach((s, i) => {
        s.classList.toggle('active', i === idx);
    });

    document.querySelectorAll('.page-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === idx);
    });
}

function renderPageThumbs() {
    const container = document.getElementById('pageThumbs');
    container.innerHTML = '';
    (currentEdicion.paginas || []).forEach((page, idx) => {
        const thumb = document.createElement('div');
        thumb.className = `page-thumb ${idx === activePageIndex ? 'active' : ''}`;
        thumb.onclick = () => switchPage(idx);

        const preview = document.createElement('div');
        preview.className = 'page-thumb-preview';
        preview.innerHTML = `<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#cbd5e1;font-weight:900;">${idx+1}</div>`;

        const label = document.createElement('span');
        label.className = 'page-thumb-label';
        label.textContent = page.nombre || `Pág. ${idx + 1}`;

        thumb.appendChild(preview);
        thumb.appendChild(label);
        container.appendChild(thumb);
    });
}

function addNewPage() {
    currentEdicion.paginas.push({
        id: 'pg-' + Date.now(),
        numero: currentEdicion.paginas.length + 1,
        nombre: 'Página ' + (currentEdicion.paginas.length + 1),
        tipo: 'blank',
        frames: []
    });
    renderAllPages();
    switchPage(currentEdicion.paginas.length - 1);
    markUnsaved();
    showToast('Nueva página agregada', 'success');
}

// ── FRAME CREATION & BUILDER ───────────────────────
function createSpecialElement(type) {
    recordHistory();
    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    if (!sheet) return;

    let fData = {
        id: 'f-' + (++frameIdCounter),
        type: type,
        x: 30,
        y: 80,
        w: 660,
        h: 120,
        z: 10,
        opacity: 1
    };

    if (type === 'masthead') {
        fData.w = 680;
        fData.h = 110;
        fData.newspaperName = 'LA ESTRELLA';
        fData.subBadge = 'del Oriente';
        fData.motto = 'EL PRIMER PERIÓDICO DE SANTA CRUZ • FUNDADO EN 1864';
        fData.editionDate = 'Santa Cruz de la Sierra • ' + (currentEdicion.fecha || 'Hoy');
        fData.editionNumber = currentEdicion.numero_edicion || 'N° 11.986';
        fData.price = currentEdicion.precio || 'Bs 7,00';
        fData.leftEar = 'CRE 100% Tarifa Equitativa';
        fData.rightEar = 'DÓLAR: Bs 12,58';
    } else if (type === 'headline') {
        fData.w = 680;
        fData.h = 90;
        fData.kicker = 'SEGURIDAD NACIONAL';
        fData.content = '<p style="font-family:\'Oswald\',sans-serif;font-size:32px;font-weight:700;line-height:1.1;color:#0f172a;margin:0;">Titular de impacto periodístico para la edición</p>';
    } else if (type === 'article') {
        fData.w = 680;
        fData.h = 220;
        fData.columns = 3;
        fData.font_family = 'Montserrat';
        fData.titular = 'Titular de Artículo Periodístico';
        fData.subtitulo = 'Subtítulo descriptivo o bajada de la noticia en la edición.';
        fData.cuerpo = 'Texto del artículo periodístico. Haz doble clic para editar o asigna una noticia real desde el panel lateral. El texto fluye armónicamente con tipografía corporativa Montserrat y justificación editorial.';
        fData.autor = 'Redacción Central';
        fData.categoria_label = 'General';
        fData.categoria_color = '#D71920';
    } else if (type === 'image') {
        fData.w = 400;
        fData.h = 240;
        fData.src = 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=80';
        fData.caption = 'CELEBRACIÓN. Retreta cultural y homenaje en Santa Cruz de la Sierra.';
    } else if (type === 'quote') {
        fData.w = 320;
        fData.h = 100;
        fData.content = '<p style="margin:0;font-size:15px;line-height:1.35;font-style:italic;">"La libertad de expresión y el periodismo independiente son la base del desarrollo de los pueblos."</p><small style="display:block;margin-top:6px;font-weight:700;color:#64748b;">— Redacción Central</small>';
    } else if (type === 'box') {
        fData.w = 280;
        fData.h = 120;
        fData.content = '<h6 style="font-size:12px;font-weight:800;color:#0b1f3a;margin-bottom:4px;text-transform:uppercase;">DATO CLAVE</h6><p style="font-size:11px;color:#334155;line-height:1.4;margin:0;">Resumen o indicador económico relevante para los lectores.</p>';
    } else if (type === 'divider') {
        fData.w = 680;
        fData.h = 6;
        fData.color = '#cbd5e1';
    } else if (type === 'qr') {
        fData.w = 200;
        fData.h = 210;
        fData.url = 'https://latitud18.com/periodico';
        fData.label = 'Escanea para edición digital';
    } else if (type === 'ad') {
        const defaultFormat = 'A1';
        const fmt = AD_FORMATS[defaultFormat];
        fData.format_code = defaultFormat;
        fData.w = fmt ? fmt.w : 400;
        fData.h = fmt ? fmt.h : 160;
        fData.status = 'disponible';
        fData.lockResize = true;
    }

    if (!currentEdicion.paginas[activePageIndex].frames) {
        currentEdicion.paginas[activePageIndex].frames = [];
    }
    const pg = currentEdicion.paginas[activePageIndex];
    const m = pg.configuracion?.margin_px ?? 31;
    fData.w = Math.min(fData.w, (pg.ancho || 720) - 2*m);
    fData.h = Math.min(fData.h, (pg.alto || 1106) - 2*m);
    fData.x = Math.max(m, Math.min(fData.x, (pg.ancho || 720)-m-fData.w));
    fData.y = Math.max(m, Math.min(fData.y, (pg.alto || 1106)-m-fData.h));
    currentEdicion.paginas[activePageIndex].frames.push(fData);

    const el = buildFrameElement(fData, activePageIndex);
    sheet.appendChild(el);
    selectFrame(el);
    markUnsaved();
    showToast('Elemento agregado', 'success');
}

function buildFrameElement(fData, pIdx) {
    const el = document.createElement('div');
    el.className = 'id-frame';
    el.id = `frame-${fData.id}`;
    el.dataset.frameId = fData.id;
    el.dataset.type = fData.type;

    if (fData.isLocked) {
        el.classList.add('locked');
        el.dataset.locked = 'true';
    }
    if (fData.lockResize || fData.type === 'ad') {
        el.dataset.lockResize = 'true';
    }

    el.style.left = (fData.x || 20) + 'px';
    el.style.top = (fData.y || 20) + 'px';
    el.style.width = (fData.w || 200) + 'px';
    el.style.height = (fData.h || 100) + 'px';
    el.style.zIndex = fData.z || 10;
    if (fData.opacity !== undefined) el.style.opacity = fData.opacity;

    // Render inner content by type
    if (fData.type === 'masthead') {
        el.dataset.font = 'Bebas Neue';
        el.innerHTML = `
            <div class="frame-masthead-inner" style="border-bottom:3px solid #0284c7;padding-bottom:4px;font-family:'Bebas Neue',sans-serif;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <div style="background:#fef9c3;color:#854d0e;padding:4px 8px;border-radius:2px;font-size:9px;font-weight:700;width:140px;line-height:1.2;font-family:'Montserrat',sans-serif;">
                        ${fData.leftEar || 'CRE 100%'}
                    </div>
                    <div style="text-align:center;flex:1;">
                        <span style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:#0284c7;line-height:1;letter-spacing:1.5px;">${fData.nombre_seccion || fData.newspaperName || 'LATITUD 18'}</span>
                        <span style="background:#D71920;color:#fff;font-family:'Bebas Neue',sans-serif;font-size:20px;padding:2px 8px;border-radius:2px;margin-left:4px;vertical-align:middle;">${fData.subBadge || 'DIARIO DIGITAL'}</span>
                        <div style="font-size:9px;font-weight:800;letter-spacing:1.5px;color:#64748b;text-transform:uppercase;margin-top:2px;font-family:'Montserrat',sans-serif;">${fData.motto || 'INFORMACIÓN SIN RUIDO • SANTA CRUZ DE LA SIERRA'}</div>
                    </div>
                    <div style="background:#0284c7;color:#fff;padding:4px 8px;border-radius:2px;font-size:9px;font-weight:800;width:130px;text-align:right;line-height:1.2;font-family:'Montserrat',sans-serif;">
                        ${fData.rightEar || 'DÓLAR: Bs 12,58'}
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid #e2e8f0;padding-top:3px;margin-top:4px;font-size:9px;color:#64748b;font-weight:600;font-family:'Montserrat',sans-serif;">
                    <span>${fData.fecha || fData.editionDate || (currentEdicion.fecha || 'Santa Cruz de la Sierra')}</span>
                    <span><strong>${fData.numero_edicion ? 'Edición ' + fData.numero_edicion : (currentEdicion.numero_edicion || '')}</strong></span>
                    <span>${fData.ciudad || (currentEdicion.ciudad || 'Santa Cruz')}</span>
                </div>
            </div>
        `;
    } else if (fData.type === 'headline') {
        el.innerHTML = `
            <div style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:center;">
                ${fData.kicker ? `<span style="font-size:11px;font-weight:800;color:#D71920;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;font-family:'Montserrat',sans-serif;">${fData.kicker}</span>` : ''}
                <div class="frame-text-inner" contenteditable="true" style="padding:0;font-family:'Bebas Neue',Impact,sans-serif;font-size:32px;letter-spacing:0.5px;line-height:1.05;">
                    ${fData.content || 'TITULAR DE IMPACTO PERIODÍSTICO'}
                </div>
            </div>
        `;
    } else if (fData.type === 'article') {
        const isLinked = !!(fData.noticia_id || fData.propiedades?.noticia_id);
        const catColor = fData.categoria_color || fData.propiedades?.categoria_color || '#D71920';
        const cols = fData.columns || 3;
        const titular = fData.titular || fData.content_title || 'Titular del Artículo';
        const subtitulo = fData.subtitulo || '';
        const cuerpo = fData.cuerpo || fData.content || 'Texto del artículo periodístico...';
        const autor = fData.autor || '';

        el.dataset.font = 'Montserrat';
        el.innerHTML = `
            <div style="width:100%;height:100%;position:relative;display:flex;flex-direction:column;box-sizing:border-box;font-family:'Montserrat',sans-serif;${isLinked ? 'padding-top:8px;' : ''}">
                ${isLinked ? `<div class="frame-category-bar" style="background:${catColor};"></div><div class="frame-linked-badge" title="Vinculado a noticia"><i class="fas fa-link"></i></div>` : ''}
                <div class="article-headline-block" style="margin-bottom:4px;">
                    <div class="frame-text-inner" contenteditable="true" style="font-family:'Montserrat',sans-serif;font-size:17px;font-weight:800;line-height:1.15;color:#0f172a;margin:0;">
                        ${titular}
                    </div>
                    ${subtitulo ? `<div style="font-size:10.5px;font-weight:600;color:#64748b;font-style:italic;margin-top:2px;">${subtitulo}</div>` : ''}
                </div>
                <div class="frame-text-inner" contenteditable="true" style="flex:1;font-family:'Montserrat',sans-serif;font-size:11px;line-height:1.4;color:#1e293b;column-count:${cols > 1 ? cols : ''};column-gap:12px;text-align:justify;overflow:hidden;">
                    ${cuerpo}
                </div>
                ${autor ? `<div style="font-size:9px;font-weight:700;color:${catColor};text-transform:uppercase;margin-top:4px;">Por: ${autor}</div>` : ''}
            </div>
        `;
    } else if (fData.type === 'image') {
        el.innerHTML = `
            <div style="width:100%;height:100%;display:flex;flex-direction:column;position:relative;">
                <div style="flex:1;position:relative;overflow:hidden;" class="image-stage-wrap">
                    <img src="${fData.src || 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=80'}" class="frame-img-inner" style="object-fit:${fData.image_fit === 'contain' ? 'contain' : 'cover'};">
                </div>
                ${fData.caption ? `<div style="font-size:9.5px;color:#475569;line-height:1.3;padding-top:4px;font-style:italic;font-family:'Montserrat',sans-serif;">${fData.caption}</div>` : ''}
            </div>
        `;
    } else if (fData.type === 'quote') {
        el.innerHTML = `<div class="frame-quote-inner" contenteditable="true" style="font-family:'Montserrat',sans-serif;">${fData.content || '"Cita destacada periodística..."'}</div>`;
    } else if (fData.type === 'box') {
        el.innerHTML = `<div class="frame-box-inner" contenteditable="true" style="font-family:'Montserrat',sans-serif;">${fData.content || 'Caja de contenido'}</div>`;
    } else if (fData.type === 'divider') {
        el.innerHTML = `<div class="frame-divider-inner"><div style="width:100%;height:2px;background:${fData.color || '#cbd5e1'};"></div></div>`;
    } else if (fData.type === 'qr') {
        const qrUrl = encodeURIComponent(fData.url || 'https://latitud18.com/periodico');
        el.innerHTML = `
            <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#fff;border:1px solid #e2e8f0;padding:6px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.08);border-radius:3px;box-sizing:border-box;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${qrUrl}" alt="QR" style="width:calc(100% - 8px);max-height:calc(100% - 22px);object-fit:contain;">
                <div style="font-size:9px;font-weight:700;color:#334155;margin-top:2px;text-transform:uppercase;letter-spacing:0.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;width:100%;font-family:'Montserrat',sans-serif;">
                    ${fData.label || 'Escanea para leer online'}
                </div>
            </div>
        `;
    } else if (fData.type === 'ad') {
        const formatCode = fData.format_code || fData.propiedades?.format_code || 'A1';
        const status = fData.status || fData.propiedades?.status || 'disponible';
        const adImg = fData.advertiser_image_url || fData.propiedades?.advertiser_image_url || null;
        const adName = fData.advertiser_name || fData.propiedades?.advertiser_name || '';

        el.dataset.formatCode = formatCode;
        el.dataset.adStatus = status;

        if (status === 'ocupado' && adImg) {
            el.innerHTML = `
                <div style="width:100%;height:100%;position:relative;overflow:hidden;">
                    <span class="ad-format-badge">${formatCode}</span>
                    <img src="${adImg}" alt="${adName}" style="width:100%;height:100%;object-fit:contain;display:block;">
                </div>
            `;
        } else {
            el.innerHTML = `
                <div class="ad-disponible-box" style="position:relative;">
                    <span class="ad-format-badge">${formatCode}</span>
                    <span class="ad-watermark-text">DISPONIBLE</span>
                    <div style="font-size:9px;color:#3b82f6;margin-top:2px;font-family:'Montserrat',sans-serif;">${AD_FORMATS[formatCode]?.label || 'Espacio Publicitario'}</div>
                </div>
            `;
        }
    } else {
        // Default text frame
        const cols = fData.columns || 1;
        el.innerHTML = `<div class="frame-text-inner" contenteditable="true" style="column-count:${cols > 1 ? cols : ''};column-gap:14px;font-family:'Montserrat',sans-serif;">${fData.content || 'Texto periodístico...'}</div>`;
    }

    // Handles or Lock badge
    if (fData.isLocked) {
        const lockIcon = document.createElement('div');
        lockIcon.className = 'frame-lock-badge';
        lockIcon.innerHTML = '<i class="fas fa-lock"></i>';
        el.appendChild(lockIcon);
    } else {
        ['nw','n','ne','e','se','s','sw','w'].forEach(pos => {
            const handle = document.createElement('div');
            handle.className = `resize-handle ${pos}`;
            el.appendChild(handle);
        });
    }

    el.addEventListener('mousedown', (e) => {
        e.stopPropagation();
        selectFrame(el);
    });

    return el;
}

// ── INTERACT.JS DRAG & RESIZE ──────────────────────
function initInteract() {
    interact('.id-frame')
        .draggable({
            listeners: {
                start(event) {
                    if (event.target.dataset.locked === 'true') return;
                    recordHistory();
                },
                move(event) {
                    const target = event.target;
                    if (target.dataset.locked === 'true') return;

                    let x = (parseFloat(target.style.left) || 0) + event.dx;
                    let y = (parseFloat(target.style.top)  || 0) + event.dy;

                    if (snapGridActive) {
                        x = Math.round(x / snapGridSize) * snapGridSize;
                        y = Math.round(y / snapGridSize) * snapGridSize;
                    }

                    target.style.left = Math.max(0, x) + 'px';
                    target.style.top  = Math.max(0, y) + 'px';
                    updateInspectorGeometry(target);
                    saveFrameGeoToModel(target);
                    markUnsaved();
                }
            }
        })
        .resizable({
            edges: { left: '.w, .nw, .sw', right: '.e, .ne, .se', bottom: '.s, .se, .sw', top: '.n, .nw, .ne' },
            listeners: {
                start(event) {
                    if (event.target.dataset.locked === 'true' || event.target.dataset.lockResize === 'true' || event.target.dataset.type === 'ad') return;
                    recordHistory();
                },
                move(event) {
                    const target = event.target;
                    if (target.dataset.locked === 'true' || target.dataset.lockResize === 'true' || target.dataset.type === 'ad') return;

                    let w = Math.max(40, event.rect.width);
                    let h = Math.max(20, event.rect.height);
                    let x = parseFloat(target.style.left) || 0;
                    let y = parseFloat(target.style.top)  || 0;

                    x += event.deltaRect.left;
                    y += event.deltaRect.top;

                    if (snapGridActive) {
                        w = Math.round(w / snapGridSize) * snapGridSize;
                        h = Math.round(h / snapGridSize) * snapGridSize;
                        x = Math.round(x / snapGridSize) * snapGridSize;
                        y = Math.round(y / snapGridSize) * snapGridSize;
                    }

                    target.style.width  = w + 'px';
                    target.style.height = h + 'px';
                    target.style.left   = Math.max(0, x) + 'px';
                    target.style.top    = Math.max(0, y) + 'px';

                    updateInspectorGeometry(target);
                    saveFrameGeoToModel(target);
                    markUnsaved();
                }
            }
        });
}

function selectFrame(el) {
    if (selectedFrame === el) return;
    deselectAll();
    selectedFrame = el;
    el.classList.add('selected');
    populateInspector(el);
}

function deselectAll() {
    if (selectedFrame) {
        selectedFrame.classList.remove('selected');
        selectedFrame = null;
    }
    document.querySelectorAll('.inspector-section').forEach(s => {
        if (s.id !== 'inspGeometry' && s.id !== 'inspActions') s.style.display = 'none';
    });
}

function populateInspector(el) {
    const type = el.dataset.type;
    const fId = el.dataset.frameId;
    const page = currentEdicion.paginas[activePageIndex];
    const fData = page?.frames?.find(f => f.id == fId) || {};

    document.getElementById('inspX').value = Math.round(parseFloat(el.style.left) || 0);
    document.getElementById('inspY').value = Math.round(parseFloat(el.style.top) || 0);
    document.getElementById('inspW').value = Math.round(parseFloat(el.style.width) || 0);
    document.getElementById('inspH').value = Math.round(parseFloat(el.style.height) || 0);
    document.getElementById('inspZ').value = parseInt(el.style.zIndex) || 10;

    const inspMasthead = document.getElementById('inspMasthead');
    const inspArticle  = document.getElementById('inspArticle');
    const inspText     = document.getElementById('inspText');
    const inspImage    = document.getElementById('inspImage');
    const inspQr       = document.getElementById('inspQr');
    const inspAd       = document.getElementById('inspAd');

    if (inspMasthead) inspMasthead.style.display = type === 'masthead' ? '' : 'none';
    if (inspArticle)  inspArticle.style.display  = type === 'article' ? '' : 'none';
    if (inspText)     inspText.style.display     = (type === 'article' || type === 'headline' || type === 'text') ? '' : 'none';
    if (inspImage)    inspImage.style.display    = type === 'image' ? '' : 'none';
    if (type === 'image') document.getElementById('inspImgFit').value = fData.image_fit || 'cover';
    if (inspQr)       inspQr.style.display       = type === 'qr' ? '' : 'none';
    if (inspAd)       inspAd.style.display       = type === 'ad' ? '' : 'none';
    document.getElementById('inspActions').style.display = '';

    // Lock button status
    const lockBtnText = document.getElementById('inspLockBtnText');
    const isLocked = fData.isLocked || el.dataset.locked === 'true';
    if (lockBtnText) {
        lockBtnText.textContent = isLocked ? 'Desbloquear Elemento' : 'Bloquear Elemento';
    }

    if (type === 'masthead') {
        document.getElementById('inspMastheadName').value = fData.newspaperName || 'LA ESTRELLA';
        document.getElementById('inspMastheadBadge').value = fData.subBadge || 'del Oriente';
        document.getElementById('inspMastheadMotto').value = fData.motto || '';
        document.getElementById('inspMastheadLeftEar').value = fData.leftEar || '';
        document.getElementById('inspMastheadRightEar').value = fData.rightEar || '';
    } else if (type === 'article') {
        const titularInput = document.getElementById('inspArticleTitular');
        const titularVal = fData.titular || fData.content_title || '';
        if (titularInput) {
            titularInput.value = titularVal;
            const countEl = document.getElementById('titularCharCount');
            if (countEl) countEl.textContent = `${titularVal.length}/200`;
            const errEl = document.getElementById('inspArticleTitularError');
            const isValid = titularVal.trim().length > 0 && titularVal.length <= 200;
            if (errEl) errEl.style.display = isValid ? 'none' : 'block';
            titularInput.classList.toggle('is-invalid', !isValid);
        }

        const subtituloInput = document.getElementById('inspArticleSubtitulo');
        if (subtituloInput) subtituloInput.value = fData.subtitulo || '';

        const cuerpoInput = document.getElementById('inspArticleCuerpo');
        const cuerpoVal = fData.cuerpo || fData.content || '';
        if (cuerpoInput) {
            cuerpoInput.value = cuerpoVal;
            const cuerpoCount = document.getElementById('cuerpoCharCount');
            if (cuerpoCount) cuerpoCount.textContent = `${cuerpoVal.length}/1000`;
        }

        const autorInput = document.getElementById('inspArticleAutor');
        if (autorInput) autorInput.value = fData.autor || '';

        const catLabelInput = document.getElementById('inspArticleCatLabel');
        if (catLabelInput) catLabelInput.value = fData.categoria_label || '';

        const catColorInput = document.getElementById('inspArticleCatColor');
        if (catColorInput) catColorInput.value = fData.categoria_color || '#D71920';

        const isLinked = !!(fData.noticia_id || fData.propiedades?.noticia_id);
        const linkedBox = document.getElementById('inspArticleLinkedBox');
        const linkedTitle = document.getElementById('inspArticleLinkedTitle');
        if (linkedBox) {
            linkedBox.style.display = isLinked ? 'block' : 'none';
            if (linkedTitle && isLinked) {
                linkedTitle.textContent = titularVal || `Noticia #${fData.noticia_id || fData.propiedades?.noticia_id}`;
            }
        }
    } else if (type === 'qr') {
        document.getElementById('inspQrUrl').value = fData.url || 'https://latitud18.com/periodico';
        document.getElementById('inspQrLabel').value = fData.label || 'Escanea para edición digital';
    } else if (type === 'ad') {
        const fmtCode = fData.format_code || fData.propiedades?.format_code || 'A1';
        const status = fData.status || fData.propiedades?.status || 'disponible';
        const advName = fData.advertiser_name || fData.propiedades?.advertiser_name || '';
        const advImg = fData.advertiser_image_url || fData.propiedades?.advertiser_image_url || null;

        const selFormat = document.getElementById('inspAdFormat');
        if (selFormat) selFormat.value = fmtCode;

        const btnDisp = document.getElementById('btnAdStatusDisponible');
        const btnOcup = document.getElementById('btnAdStatusOcupado');
        if (btnDisp && btnOcup) {
            btnDisp.className = status === 'disponible' ? 'id-btn id-btn-primary' : 'id-btn id-btn-ghost';
            btnOcup.className = status === 'ocupado' ? 'id-btn id-btn-success' : 'id-btn id-btn-ghost';
        }

        const advNameInput = document.getElementById('inspAdAdvertiserName');
        if (advNameInput) advNameInput.value = advName;

        const thumbPreview = document.getElementById('adImageThumbPreview');
        const thumbImg = document.getElementById('adImageThumbImg');
        if (thumbPreview && thumbImg) {
            if (advImg) {
                thumbPreview.style.display = 'block';
                thumbImg.src = advImg;
            } else {
                thumbPreview.style.display = 'none';
                thumbImg.src = '';
            }
        }
    }
}

function updateInspectorGeometry(el) {
    document.getElementById('inspX').value = Math.round(parseFloat(el.style.left) || 0);
    document.getElementById('inspY').value = Math.round(parseFloat(el.style.top) || 0);
    document.getElementById('inspW').value = Math.round(parseFloat(el.style.width) || 0);
    document.getElementById('inspH').value = Math.round(parseFloat(el.style.height) || 0);
}

function applyGeometry() {
    if (!selectedFrame) return;
    selectedFrame.style.left   = document.getElementById('inspX').value + 'px';
    selectedFrame.style.top    = document.getElementById('inspY').value + 'px';
    selectedFrame.style.width  = document.getElementById('inspW').value + 'px';
    selectedFrame.style.height = document.getElementById('inspH').value + 'px';
    saveFrameGeoToModel(selectedFrame);
    markUnsaved();
}

function applyZIndex() {
    if (!selectedFrame) return;
    selectedFrame.style.zIndex = document.getElementById('inspZ').value;
    saveFrameZIndex(selectedFrame);
    markUnsaved();
}

function applyOpacity() {
    if (!selectedFrame) return;
    const val = parseInt(document.getElementById('inspOpacity').value) / 100;
    selectedFrame.style.opacity = val;
    document.getElementById('inspOpacityVal').textContent = Math.round(val * 100) + '%';
    markUnsaved();
}

function constrainFrameToPage(el, page) {
    if (page?.configuracion?.page_width_mm !== 280) return;
    const margin = page.configuracion.margin_px || 31;
    const width = page.ancho || 720, height = page.alto || 1106;
    const w = Math.max(1, Math.min(parseFloat(el.style.width) || 1, width - 2*margin));
    const h = Math.max(1, Math.min(parseFloat(el.style.height) || 1, height - 2*margin));
    el.style.width = w + 'px'; el.style.height = h + 'px';
    el.style.left = Math.max(margin, Math.min(parseFloat(el.style.left) || 0, width-margin-w)) + 'px';
    el.style.top = Math.max(margin, Math.min(parseFloat(el.style.top) || 0, height-margin-h)) + 'px';
}

function saveFrameGeoToModel(el) {
    const page = currentEdicion.paginas[activePageIndex];
    constrainFrameToPage(el, page);
    updateInspectorGeometry(el);
    const f = page?.frames?.find(f => f.id == el.dataset.frameId);
    if (f) {
        f.x = Math.round(parseFloat(el.style.left));
        f.y = Math.round(parseFloat(el.style.top));
        f.w = Math.round(parseFloat(el.style.width));
        f.h = Math.round(parseFloat(el.style.height));
    }
}

function saveFrameZIndex(el) {
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(f => f.id == el.dataset.frameId);
    if (f) f.z = parseInt(el.style.zIndex);
}

// ── MASTHEAD PROPS ─────────────────────────────────
function applyMastheadProps() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'masthead') return;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    f.newspaperName = document.getElementById('inspMastheadName').value;
    f.subBadge = document.getElementById('inspMastheadBadge').value;
    f.motto = document.getElementById('inspMastheadMotto').value;
    f.leftEar = document.getElementById('inspMastheadLeftEar').value;
    f.rightEar = document.getElementById('inspMastheadRightEar').value;

    // Re-render masthead inner
    selectedFrame.innerHTML = '';
    const newEl = buildFrameElement(f, activePageIndex);
    selectedFrame.innerHTML = newEl.innerHTML;
    markUnsaved();
}

// ── TEXT PROPS ─────────────────────────────────────
function applyTextProps() {
    if (!selectedFrame) return;
    const textDiv = selectedFrame.querySelector('.frame-text-inner');
    if (!textDiv) return;

    const ff  = document.getElementById('inspFontFamily').value;
    const fs  = document.getElementById('inspFontSize').value;
    const lh  = document.getElementById('inspLineH').value;
    const cols= document.getElementById('inspTextCols').value;
    const col = document.getElementById('inspTextColor').value;
    const align= document.getElementById('inspTextAlign').value;
    const bg  = document.getElementById('inspTextBg').value;

    Object.assign(textDiv.style, {
        fontFamily: ff,
        fontSize: fs + 'px',
        lineHeight: lh,
        color: col,
        textAlign: align,
        backgroundColor: bg,
        columnCount: parseInt(cols) > 1 ? cols : '',
    });
    markUnsaved();
}

function clearTextBg() {
    if (!selectedFrame) return;
    const textDiv = selectedFrame.querySelector('.frame-text-inner');
    if (textDiv) textDiv.style.backgroundColor = 'transparent';
    markUnsaved();
}

function applyStyleToSelection() {
    const ff = document.getElementById('cbFontFamily').value;
    const fs = document.getElementById('cbFontSize').value;
    document.execCommand('fontName', false, ff);
    document.execCommand('fontSize', false, 4);
    markUnsaved();
}

function toggleBold()      { document.execCommand('bold');      markUnsaved(); }
function toggleItalic()    { document.execCommand('italic');    markUnsaved(); }
function toggleUnderline() { document.execCommand('underline'); markUnsaved(); }
function toggleUppercase() {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (frame) {
        frame.style.textTransform = frame.style.textTransform === 'uppercase' ? '' : 'uppercase';
        markUnsaved();
    }
}

function applyAlign(align) {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (frame) {
        frame.style.textAlign = align;
        markUnsaved();
    }
}

function setColumns(n) {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (frame) {
        frame.style.columnCount = n > 1 ? n : '';
        frame.style.columnGap = n > 1 ? '14px' : '';
        markUnsaved();
    }
}

// ── IMAGE HANDLING ─────────────────────────────────
function openImagePicker() { openModal('imagePickerModal'); }

function applyImageUrl() {
    applyImageUrlToFrame();
}

function applyImageUrlToFrame() {
    const url = document.getElementById('imageUrlInput').value;
    if (url && selectedFrame) {
        applyImageSrc(url);
        closeModal('imagePickerModal');
    }
}

function uploadImageForFrame(input) {
    const file = input.files[0];
    if (!file) return;

    // Validación Req 2.2, 2.3 (JPEG/PNG/WEBP <= 10MB)
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (!validTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
        showToast('Error: El archivo debe ser imagen JPEG, PNG o WEBP y no superar los 10 MB.', 'error');
        input.value = '';
        return;
    }

    const uploadBtn = document.getElementById('btnUploadImageInsp');
    if (uploadBtn) uploadBtn.disabled = true;

    // Spinner en el marco mientras carga (Req 2.9)
    if (selectedFrame) {
        const stageWrap = selectedFrame.querySelector('.image-stage-wrap');
        if (stageWrap) {
            const spin = document.createElement('div');
            spin.className = 'upload-spinner-overlay';
            spin.style.cssText = 'position:absolute;inset:0;background:rgba(0,0,0,0.55);display:flex;align-items:center;justify-content:center;color:#38bdf8;z-index:20;';
            spin.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i>';
            stageWrap.appendChild(spin);
        }
    }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("admin.periodico.uploadImage") }}', { method:'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (uploadBtn) uploadBtn.disabled = false;
            if (selectedFrame) {
                selectedFrame.querySelector('.upload-spinner-overlay')?.remove();
            }
            if (d.url) { 
                applyImageSrc(d.url); 
                closeModal('imagePickerModal'); 
                showToast('Imagen cargada con éxito', 'success');
            } else {
                showToast(d.message || 'Error al subir imagen', 'error');
            }
        })
        .catch(err => {
            if (uploadBtn) uploadBtn.disabled = false;
            if (selectedFrame) {
                selectedFrame.querySelector('.upload-spinner-overlay')?.remove();
            }
            console.error(err);
            showToast('Error de red al subir la imagen', 'error');
        });
}

function applyImageSrc(src) {
    if (!selectedFrame) return;
    let img = selectedFrame.querySelector('.frame-img-inner');
    if (img) {
        img.src = src;
    }
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) f.src = src;
    markUnsaved();
}

function applyImageFit() {
    if (!selectedFrame) return;
    const fit = document.getElementById('inspImgFit').value;
    const img = selectedFrame.querySelector('.frame-img-inner');
    if (img) img.style.objectFit = fit;
    const f = currentEdicion.paginas[activePageIndex]?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) f.image_fit = fit;
    markUnsaved();
}

function applyImageBorder() {
    if (!selectedFrame) return;
    const w = document.getElementById('inspImgBorderW').value;
    const img = selectedFrame.querySelector('.frame-img-inner');
    if (img) img.style.borderWidth = w + 'px';
    markUnsaved();
}

function applyImageBorderColor() {
    if (!selectedFrame) return;
    const col = document.getElementById('inspImgBorder').value;
    const img = selectedFrame.querySelector('.frame-img-inner');
    if (img) {
        img.style.borderColor = col;
        img.style.borderStyle = 'solid';
    }
    markUnsaved();
}

// ── LAYER ACTIONS & PROPS ──────────────────────────
function bringToFront() {
    if (!selectedFrame) return;
    recordHistory();
    let maxZ = 10;
    document.querySelectorAll('.id-frame').forEach(f => {
        maxZ = Math.max(maxZ, parseInt(f.style.zIndex) || 10);
    });
    selectedFrame.style.zIndex = maxZ + 1;
    saveFrameZIndex(selectedFrame);
    document.getElementById('inspZ').value = maxZ + 1;
    markUnsaved();
    showToast('Elemento traído al frente', 'success');
}

function sendToBack() {
    if (!selectedFrame) return;
    recordHistory();
    selectedFrame.style.zIndex = 2;
    saveFrameZIndex(selectedFrame);
    document.getElementById('inspZ').value = 2;
    markUnsaved();
    showToast('Elemento enviado al fondo', 'success');
}

function bringForward() {
    if (!selectedFrame) return;
    recordHistory();
    let currentZ = parseInt(selectedFrame.style.zIndex) || 10;
    selectedFrame.style.zIndex = currentZ + 1;
    saveFrameZIndex(selectedFrame);
    document.getElementById('inspZ').value = currentZ + 1;
    markUnsaved();
    showToast('Capa avanzada (+1)', 'success');
}

function sendBackward() {
    if (!selectedFrame) return;
    recordHistory();
    let currentZ = parseInt(selectedFrame.style.zIndex) || 10;
    selectedFrame.style.zIndex = Math.max(1, currentZ - 1);
    saveFrameZIndex(selectedFrame);
    document.getElementById('inspZ').value = Math.max(1, currentZ - 1);
    markUnsaved();
    showToast('Capa retrocedida (-1)', 'success');
}

function toggleLockSelected() {
    if (!selectedFrame) return;
    recordHistory();
    const fId = selectedFrame.dataset.frameId;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == fId);
    if (!f) return;

    const isLocked = !f.isLocked;
    f.isLocked = isLocked;

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);

    document.getElementById('inspLockBtnText').textContent = isLocked ? 'Desbloquear Elemento' : 'Bloquear Elemento';
    markUnsaved();
    showToast(isLocked ? 'Elemento bloqueado (protegido contra edición)' : 'Elemento desbloqueado', 'success');
}

function applyQrProps() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'qr') return;
    recordHistory();
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    f.url = document.getElementById('inspQrUrl').value;
    f.label = document.getElementById('inspQrLabel').value;

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);
    markUnsaved();
}

// ── ARTICLE INSPECTOR ACTIONS (TASK 10.2, 16.1) ────────
function handleArticleTitularInput(input) {
    const val = input.value;
    const countEl = document.getElementById('titularCharCount');
    if (countEl) countEl.textContent = `${val.length}/200`;

    const errEl = document.getElementById('inspArticleTitularError');
    const isValid = val.trim().length > 0 && val.length <= 200;
    if (errEl) errEl.style.display = isValid ? 'none' : 'block';
    input.classList.toggle('is-invalid', !isValid);

    // Bloquear/desbloquear botón Guardar (Req 2.7, 2.8)
    document.querySelectorAll('#mainSaveBtn, .btn-save-periodico').forEach(b => {
        b.disabled = !isValid;
        if (!isValid) b.title = 'Corrige el titular del artículo (1-200 caracteres) para poder guardar';
        else b.title = 'Guardar cambios de la edición (Ctrl+S)';
    });

    applyArticleProps();
}

function validateArticleTitular() {
    const input = document.getElementById('inspArticleTitular');
    if (!input) return true;
    const val = input.value.trim();
    return val.length > 0 && val.length <= 200;
}

function applyArticleProps() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'article') return;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    const titularVal   = document.getElementById('inspArticleTitular')?.value || '';
    const subtituloVal = document.getElementById('inspArticleSubtitulo')?.value || '';
    const cuerpoVal    = document.getElementById('inspArticleCuerpo')?.value || '';
    const autorVal     = document.getElementById('inspArticleAutor')?.value || '';
    const catLabelVal  = document.getElementById('inspArticleCatLabel')?.value || '';
    const catColorVal  = document.getElementById('inspArticleCatColor')?.value || '#D71920';

    f.titular          = titularVal;
    f.subtitulo        = subtituloVal;
    f.cuerpo           = cuerpoVal;
    f.autor            = autorVal;
    f.categoria_label  = catLabelVal;
    f.categoria_color  = catColorVal;

    if (!f.propiedades) f.propiedades = {};
    f.propiedades.categoria_color = catColorVal;
    f.propiedades.categoria_label = catLabelVal;

    const cuerpoCount = document.getElementById('cuerpoCharCount');
    if (cuerpoCount) cuerpoCount.textContent = `${cuerpoVal.length}/1000`;

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);
    markUnsaved();
}

function setArticleColor(color) {
    const input = document.getElementById('inspArticleCatColor');
    if (input) input.value = color;
    applyArticleProps();
}

function unlinkArticleNews() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'article') return;
    recordHistory();
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    // Desvincula reteniendo titular, subtítulo, cuerpo, autor, categoría (Req 4.6)
    delete f.noticia_id;
    if (f.propiedades) delete f.propiedades.noticia_id;

    const linkedBox = document.getElementById('inspArticleLinkedBox');
    if (linkedBox) linkedBox.style.display = 'none';

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);
    markUnsaved();
    showToast('Noticia desvinculada (contenido retenido)', 'success');
}

// ── AD INSPECTOR ACTIONS (TASK 11.1, 16.2) ──────────
function changeSelectedAdFormat(formatCode) {
    if (!selectedFrame || selectedFrame.dataset.type !== 'ad') return;
    recordHistory();
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    const fmt = AD_FORMATS[formatCode] || AD_FORMATS['A1'];
    f.format_code = formatCode;
    f.w = fmt.w;
    f.h = fmt.h;
    const pg = currentEdicion.paginas[activePageIndex];
    const m = pg.configuracion?.margin_px ?? 31;
    f.x = Math.max(m, Math.min(f.x, (pg.ancho || 720) - m - f.w));
    f.y = Math.max(m, Math.min(f.y, (pg.alto || 1106) - m - f.h));
    f.lockResize = true;
    if (!f.propiedades) f.propiedades = {};
    f.propiedades.format_code = formatCode;

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);
    updateInspectorGeometry(newEl);
    markUnsaved();
    showToast(`Formato ${formatCode} aplicado (${fmt.w}x${fmt.h}px)`, 'success');
}

function setAdStatus(status) {
    if (!selectedFrame || selectedFrame.dataset.type !== 'ad') return;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    if (status === 'ocupado') {
        const img = f.advertiser_image_url || f.propiedades?.advertiser_image_url;
        if (!img) {
            showToast('Para marcar como ocupado se requiere subir una imagen publicitaria primero.', 'error');
            return;
        }
    }

    recordHistory();
    f.status = status;
    if (!f.propiedades) f.propiedades = {};
    f.propiedades.status = status;

    const btnDisp = document.getElementById('btnAdStatusDisponible');
    const btnOcup = document.getElementById('btnAdStatusOcupado');
    if (btnDisp && btnOcup) {
        btnDisp.className = status === 'disponible' ? 'id-btn id-btn-primary' : 'id-btn id-btn-ghost';
        btnOcup.className = status === 'ocupado' ? 'id-btn id-btn-success' : 'id-btn id-btn-ghost';
    }

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);
    markUnsaved();
    showToast(`Módulo publicitario marcado como ${status}`, 'success');
}

function applyAdAdvertiserName(name) {
    if (!selectedFrame || selectedFrame.dataset.type !== 'ad') return;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    f.advertiser_name = name;
    if (!f.propiedades) f.propiedades = {};
    f.propiedades.advertiser_name = name;
    markUnsaved();
}

function uploadAdImageFile(input) {
    const file = input.files[0];
    if (!file) return;

    // Validación Req 3.3 (JPEG/PNG/WEBP <= 10MB)
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (!validTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
        showToast('Error: El archivo debe ser imagen JPEG, PNG o WEBP y no superar los 10 MB.', 'error');
        input.value = '';
        return;
    }

    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame?.dataset.frameId);
    if (!f) return;

    const uploadBtn = document.getElementById('btnUploadAdImage');
    const btnLabel = document.getElementById('adImageUploadBtnLabel');
    if (uploadBtn) uploadBtn.disabled = true;
    if (btnLabel) btnLabel.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';

    // Spinner en el marco
    if (selectedFrame) {
        selectedFrame.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#1e293b;color:#38bdf8;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("admin.periodico.uploadImage") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (uploadBtn) uploadBtn.disabled = false;
            if (btnLabel) btnLabel.textContent = 'Subir Imagen Anunciante';
            if (d.url) {
                f.advertiser_image_url = d.url;
                f.status = 'ocupado';
                if (!f.propiedades) f.propiedades = {};
                f.propiedades.advertiser_image_url = d.url;
                f.propiedades.status = 'ocupado';

                const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
                const newEl = buildFrameElement(f, activePageIndex);
                sheet.replaceChild(newEl, selectedFrame);
                selectFrame(newEl);
                populateInspector(newEl);
                markUnsaved();
                showToast('Imagen publicitaria subida con éxito', 'success');
            } else {
                showToast(d.message || 'Error al subir imagen publicitaria', 'error');
                if (selectedFrame) {
                    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
                    const newEl = buildFrameElement(f, activePageIndex);
                    sheet.replaceChild(newEl, selectedFrame);
                    selectFrame(newEl);
                }
            }
        })
        .catch(err => {
            if (uploadBtn) uploadBtn.disabled = false;
            if (btnLabel) btnLabel.textContent = 'Subir Imagen Anunciante';
            console.error(err);
            showToast('Error al procesar subida de imagen publicitaria', 'error');
        });
}

function applyAdProps() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'ad') return;
    recordHistory();
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (!f) return;

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(f, activePageIndex);
    sheet.replaceChild(newEl, selectedFrame);
    selectFrame(newEl);
    markUnsaved();
}

function duplicateSelected() {
    if (!selectedFrame) return;
    recordHistory();
    const page = currentEdicion.paginas[activePageIndex];
    const fData = page?.frames?.find(f => f.id == selectedFrame.dataset.frameId);
    if (!fData) return;

    const cloneData = JSON.parse(JSON.stringify(fData));
    cloneData.id = 'f-' + (++frameIdCounter);
    cloneData.x = (cloneData.x || 0) + 20;
    cloneData.y = (cloneData.y || 0) + 20;

    page.frames.push(cloneData);
    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const newEl = buildFrameElement(cloneData, activePageIndex);
    sheet.appendChild(newEl);
    selectFrame(newEl);
    markUnsaved();
    showToast('Elemento duplicado', 'success');
}

function deleteSelected() {
    if (!selectedFrame) return;
    if (selectedFrame.dataset.locked === 'true') {
        showToast('El elemento está bloqueado. Desbloquéalo primero para eliminarlo.', 'error');
        return;
    }
    recordHistory();
    const fId = selectedFrame.dataset.frameId;
    const page = currentEdicion.paginas[activePageIndex];
    if (page?.frames) {
        page.frames = page.frames.filter(f => f.id != fId);
    }
    selectedFrame.remove();
    selectedFrame = null;
    deselectAll();
    markUnsaved();
    showToast('Elemento eliminado', 'success');
}

// ── TEMPLATES LIBRARY & IMPORT/EXPORT SUITE ──────────
function loadTemplatesCatalog() {
    fetch('{{ route("admin.periodico.templates.list") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.templates && Array.isArray(data.templates)) {
            loadedTemplates = data.templates;
            renderTemplateCards(loadedTemplates);
            renderEditionSlotsGrid();
        }
    })
    .catch(err => {
        console.warn('No se pudo cargar el catálogo de plantillas remoto, usando plantillas locales.', err);
        renderTemplateCards(loadedTemplates);
    });
}

function renderTemplateCards(templates) {
    const container = document.getElementById('templatesGridContainer');
    if (!container) return;

    const query = (document.getElementById('tplSearchInput')?.value || '').toLowerCase().trim();
    let filtered = templates;

    if (currentFilterCat === 'custom') {
        filtered = filtered.filter(t => t.is_custom);
    } else if (currentFilterCat !== 'all') {
        filtered = filtered.filter(t => {
            const cat = (t.category || '').toLowerCase();
            const filter = currentFilterCat.toLowerCase();
            return cat === filter || 
                   (filter === 'portada' && (cat === 'portadas' || cat === 'portada')) ||
                   (filter === 'editorial' && (cat === 'editorial' || cat === 'opinion')) ||
                   (filter === 'deportes' && (cat === 'deportes' || cat === 'contraataque'));
        });
    }

    if (query) {
        filtered = filtered.filter(t => 
            (t.name && t.name.toLowerCase().includes(query)) ||
            (t.description && t.description.toLowerCase().includes(query)) ||
            (t.category && t.category.toLowerCase().includes(query))
        );
    }

    if (filtered.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 36px 12px; color: #94a3b8;">
                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 8px; opacity: 0.5;"></i>
                <h6 style="color: #cbd5e1; font-weight: 700;">No se encontraron plantillas</h6>
                <p style="font-size: 0.72rem; margin: 0;">Prueba seleccionando otra categoría o guarda la página actual como una nueva plantilla personalizada.</p>
            </div>
        `;
        return;
    }

    const categoryIcons = {
        portadas: 'fas fa-newspaper text-danger',
        portada: 'fas fa-newspaper text-danger',
        editorial: 'fas fa-feather-alt text-warning',
        opinion: 'fas fa-feather-alt text-warning',
        politica: 'fas fa-landmark text-primary',
        santacruz: 'fas fa-map-marker-alt text-success',
        pais: 'fas fa-flag text-danger',
        economia: 'fas fa-chart-line text-info',
        seguridad: 'fas fa-shield-alt text-secondary',
        mundo: 'fas fa-globe text-primary',
        entrevistas: 'fas fa-microphone-alt text-info',
        fotografia: 'fas fa-camera text-info',
        deportes: 'fas fa-futbol text-danger',
        publicidad: 'fas fa-bullhorn text-warning',
        contraportada: 'fas fa-book-open text-secondary',
        interior: 'fas fa-columns text-info',
        especial: 'fas fa-star text-warning',
        general: 'fas fa-th-large text-secondary'
    };

    container.innerHTML = filtered.map(t => {
        const iconClass = categoryIcons[t.category] || 'fas fa-th-large text-secondary';
        const isCustom = !!t.is_custom;
        const colorPill = t.preview_color || '#0284c7';
        const framesCount = (t.frames || []).length;

        return `
            <div class="template-card" style="border-top: 3px solid ${colorPill};">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span class="tpl-card-badge" style="background: rgba(255,255,255,0.08); color: ${colorPill};">
                        ${t.category}
                    </span>
                    ${isCustom ? '<span class="badge bg-success" style="font-size:0.55rem;">Personalizada</span>' : ''}
                </div>
                <i class="${iconClass}"></i>
                <h6>${t.name}</h6>
                <p>${t.description || 'Maqueta periodística estructurada para InDesign.'}</p>
                <div style="font-size:0.62rem;color:#64748b;margin-top:4px;">${framesCount} bloques incluidos</div>
                <div class="tpl-card-actions" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
                    <button class="tpl-btn-apply" onclick="applyTemplateFromCatalog('${t.id}')" title="Aplica esta maqueta a la página actual abierta" style="flex:1;">
                        <i class="fas fa-paint-brush me-1"></i> Aplicar
                    </button>
                    <button class="tpl-btn-apply" onclick="createEditionFromTemplateCard('${t.id}', '${t.name}')" title="Crea un ejemplar nuevo basado en esta plantilla sin alterarla" style="background:#D71920;border-color:#D71920;color:#fff;flex:1.2;">
                        <i class="fas fa-plus-circle me-1"></i> Crear Edición
                    </button>
                    <button class="tpl-btn-action" onclick="duplicateTemplateAction('${t.id}')" title="Duplicar plantilla (crea una copia independiente)">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="tpl-btn-action" onclick="exportTemplateFile('${t.id}')" title="Exportar como .latitud-template">
                        <i class="fas fa-download"></i>
                    </button>
                    ${isCustom ? `
                        <button class="tpl-btn-action danger" onclick="deleteCustomTemplate('${t.id}')" title="Eliminar plantilla">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}

function filterTemplateCards(cat, btn) {
    currentFilterCat = cat;
    document.querySelectorAll('.tpl-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderTemplateCards(loadedTemplates);
}

function searchTemplates(query) {
    renderTemplateCards(loadedTemplates);
}

function duplicateTemplateAction(tplId) {
    showToast('Duplicando plantilla...', 'info');
    fetch(`/admin/periodico/templates/${tplId}/duplicate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Plantilla duplicada exitosamente', 'success');
            loadTemplatesCatalog();
        } else {
            showToast(data.message || 'Error al duplicar plantilla', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error de conexión al duplicar plantilla', 'error');
    });
}

function createEditionFromTemplateCard(tplId, tplName) {
    const defaultNum = `Edición ${Math.floor(Math.random() * 800) + 120}`;
    const num = prompt(`Crear nueva edición a partir de "${tplName}":\n\nIngresa el número o título de la edición:`, defaultNum);
    if (!num) return;

    closeModal('templateModal');
    showToast('Creando nueva edición a partir de plantilla...', 'info');

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch(`/admin/periodico/templates/${tplId}/create-edition`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
            numero_edicion: num,
            fecha: new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' })
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.redirect_url) {
            showToast(res.message || 'Edición creada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = res.redirect_url;
            }, 600);
        } else {
            showToast(res.message || 'Error al crear edición', 'error');
        }
    })
    .catch(err => {
        console.error('Error al crear edición:', err);
        showToast('Error de conexión con el servidor', 'error');
    });
}

function applyTemplateFromCatalog(tplId) {
    let tpl = loadedTemplates.find(t => t.id === tplId);
    if (!tpl) {
        // Fallback for special blank template
        if (tplId === 'blank') {
            closeModal('templateModal');
            recordHistory();
            const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
            sheet.querySelectorAll('.id-frame').forEach(el => el.remove());
            currentEdicion.paginas[activePageIndex].frames = [];
            markUnsaved();
            showToast('Lienzo en blanco aplicado', 'success');
            return;
        }
        showToast('Plantilla no encontrada', 'error');
        return;
    }

    // Confirmación nativa si la página ya tiene frames (Req 1.4)
    const existingFrames = currentEdicion.paginas[activePageIndex]?.frames || [];
    if (existingFrames.length > 0) {
        if (!confirm(`La página ${activePageIndex + 1} ya contiene ${existingFrames.length} elementos. ¿Estás seguro de que deseas reemplazarlos por la plantilla "${tpl.name}"?`)) {
            return;
        }
    }

    closeModal('templateModal');
    recordHistory();

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    sheet.querySelectorAll('.id-frame').forEach(el => el.remove());
    currentEdicion.paginas[activePageIndex].frames = [];

    const page = currentEdicion.paginas[activePageIndex];
    if (tpl.configuracion) {
        page.configuracion = JSON.parse(JSON.stringify(tpl.configuracion));
        page.ancho = page.configuracion.page_width_px || page.ancho || 720;
        page.alto = page.configuracion.page_height_px || page.alto || 1040;
    }
    page.plantilla_id = tplId;
    sheet.style.width = (page.ancho || 720) + 'px';
    sheet.style.height = (page.alto || 1040) + 'px';
    const guide = sheet.querySelector('.paper-margin-guide');
    if (guide) guide.style.inset = (page.configuracion?.margin_px ?? 12) + 'px';

    // Clona los marcos asignando nuevos IDs únicos
    const clonedFrames = JSON.parse(JSON.stringify(tpl.frames || []));
    clonedFrames.forEach((f, idx) => {
        f.id = 'f-' + Date.now() + '-' + (++frameIdCounter);
        currentEdicion.paginas[activePageIndex].frames.push(f);
        const el = buildFrameElement(f, activePageIndex);
        sheet.appendChild(el);
    });

    deselectAll();
    markUnsaved();
    showToast(`Plantilla "${tpl.name}" aplicada exitosamente a la página ${activePageIndex + 1}`, 'success');
}

// Compatibilidad con llamados directos anteriores
function applyTemplate(type) {
    const aliasMap = {
        'la_estrella_portada': 'tpl_portada_tradicional',
        'latitud18_broadsheet': 'tpl_portada_tabloide',
        'editorial_opinion': 'tpl_opinion_editorial',
        'comunidad_cultura': 'tpl_reportaje_4col',
        'negocios_economia': 'tpl_reportaje_4col',
        'blank': 'blank'
    };
    const targetId = aliasMap[type] || type;
    applyTemplateFromCatalog(targetId);
}

// Guardar plantilla personalizada
function openSaveTemplateModal() {
    closeModal('templateModal');
    document.getElementById('tplSaveName').value = `Plantilla Pág. ${activePageIndex + 1} - ${currentEdicion.titulo || 'Edición'}`;
    openModal('saveTemplateModal');
}

function submitSaveTemplate() {
    const name = document.getElementById('tplSaveName').value.trim();
    const category = document.getElementById('tplSaveCategory').value;
    const description = document.getElementById('tplSaveDescription').value.trim();
    const previewColor = document.getElementById('tplSaveColor').value;

    if (!name) {
        alert('Por favor introduce un nombre para la plantilla.');
        return;
    }

    const currentFrames = currentEdicion.paginas[activePageIndex]?.frames || [];
    if (currentFrames.length === 0) {
        if (!confirm('La página actual está vacía. ¿Deseas guardar una plantilla sin elementos?')) {
            return;
        }
    }

    fetch('{{ route("admin.periodico.templates.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            category: category,
            description: description,
            preview_color: previewColor,
            frames: currentFrames,
            configuracion: currentEdicion.paginas[activePageIndex]?.configuracion || null
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('saveTemplateModal');
            showToast('¡Plantilla guardada exitosamente en la biblioteca!', 'success');
            loadTemplatesCatalog();
            openModal('templateModal');
        } else {
            showToast(data.message || 'Error al guardar plantilla', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error de conexión al guardar plantilla', 'error');
    });
}

// Exportar plantilla actual como archivo descargable .latitud-template
function exportCurrentAsTemplateFile() {
    const currentFrames = currentEdicion.paginas[activePageIndex]?.frames || [];
    const templateData = {
        file_type: 'latitud-template',
        version: '1.0',
        exported_at: new Date().toISOString(),
        template: {
            name: `${currentEdicion.titulo || 'Periódico'} - Pág. ${activePageIndex + 1}`,
            category: 'especial',
            description: `Exportado desde la edición ${currentEdicion.numero_edicion}`,
            preview_color: '#0284c7',
            frames: currentFrames,
            configuracion: currentEdicion.paginas[activePageIndex]?.configuracion || null
        }
    };

    downloadJsonAsFile(templateData, `plantilla_pag_${activePageIndex + 1}.latitud-template`);
    showToast('Archivo .latitud-template descargado con éxito', 'success');
}

function exportTemplateFile(tplId) {
    const tpl = loadedTemplates.find(t => t.id === tplId);
    if (!tpl) return;

    const templateData = {
        file_type: 'latitud-template',
        version: '1.0',
        exported_at: new Date().toISOString(),
        template: tpl
    };

    downloadJsonAsFile(templateData, `${tpl.name.toLowerCase().replace(/[^a-z0-9]/g, '_')}.latitud-template`);
    showToast(`Plantilla "${tpl.name}" exportada`, 'success');
}

function downloadJsonAsFile(obj, filename) {
    const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(obj, null, 2));
    const a = document.createElement('a');
    a.setAttribute('href', dataStr);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    a.remove();
}

// Importar plantilla desde archivo
function openImportTemplateModal() {
    closeModal('templateModal');
    document.getElementById('templateFileInput').value = '';
    document.getElementById('selectedTemplateFileInfo').style.display = 'none';
    document.getElementById('btnSubmitImportTpl').disabled = true;
    openModal('importTemplateModal');
}

function onTemplateFileSelected(input) {
    const file = input.files[0];
    if (file) {
        document.getElementById('selectedTemplateFileName').textContent = file.name;
        document.getElementById('selectedTemplateFileInfo').style.display = 'block';
        document.getElementById('btnSubmitImportTpl').disabled = false;
    }
}

function submitImportTemplate() {
    const input = document.getElementById('templateFileInput');
    const file = input.files[0];
    if (!file) return;

    const fd = new FormData();
    fd.append('template_file', file);
    fd.append('_token', '{{ csrf_token() }}');

    const btn = document.getElementById('btnSubmitImportTpl');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importando...';

    fetch('{{ route("admin.periodico.templates.import") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Importar Plantilla';

        if (data.success) {
            closeModal('importTemplateModal');
            showToast(data.message || '¡Plantilla importada con éxito!', 'success');
            loadTemplatesCatalog();
            openModal('templateModal');
        } else {
            showToast(data.message || 'Error al importar archivo', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Importar Plantilla';
        showToast('Error al procesar el archivo de plantilla', 'error');
    });
}

function deleteCustomTemplate(tplId) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta plantilla de la biblioteca?')) return;

    fetch(`{{ url('/admin/periodico/templates') }}/${tplId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        showToast('Plantilla eliminada de la biblioteca', 'success');
        loadTemplatesCatalog();
    })
    .catch(err => {
        console.error(err);
        showToast('Error al eliminar plantilla', 'error');
    });
}

function createCustomFrame(fData) {
    fData.id = 'f-' + (++frameIdCounter);
    if (!currentEdicion.paginas[activePageIndex].frames) {
        currentEdicion.paginas[activePageIndex].frames = [];
    }
    const pg = currentEdicion.paginas[activePageIndex];
    const m = pg.configuracion?.margin_px ?? 31;
    fData.w = Math.min(fData.w, (pg.ancho || 720) - 2*m);
    fData.h = Math.min(fData.h, (pg.alto || 1106) - 2*m);
    fData.x = Math.max(m, Math.min(fData.x, (pg.ancho || 720)-m-fData.w));
    fData.y = Math.max(m, Math.min(fData.y, (pg.alto || 1106)-m-fData.h));
    currentEdicion.paginas[activePageIndex].frames.push(fData);

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const el = buildFrameElement(fData, activePageIndex);
    sheet.appendChild(el);
}

// ── INSERT NEWS FROM DATABASE ──────────────────────
// ── REMOTE NEWS SEARCH & ASSIGNMENT (TASK 10.1, 10.2) ──
function handleNewsSearchInput(query) {
    clearTimeout(newsSearchDebounceTimer);
    newsSearchDebounceTimer = setTimeout(() => {
        performRemoteNewsSearch(query);
    }, 300);
}

function performRemoteNewsSearch(query) {
    const cat = document.getElementById('drawerCatFilter')?.value;
    const term = (query || '').trim();

    if (!term) {
        filterDrawerNews();
        return;
    }

    const catParam = (cat && cat !== 'all') ? `&category_id=${cat}` : '';
    fetch(`/admin/periodico/noticias/search?q=${encodeURIComponent(term)}${catParam}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('drawerNewsList');
        if (!list) return;

        const noticias = data.noticias?.data || data.noticias || [];
        if (noticias.length === 0) {
            list.innerHTML = `
                <div style="text-align:center;padding:24px 12px;color:#94a3b8;">
                    <i class="fas fa-search" style="font-size:1.8rem;margin-bottom:8px;opacity:0.4;"></i>
                    <h6 style="color:#cbd5e1;font-size:0.8rem;font-weight:700;">No se encontraron noticias</h6>
                    <p style="font-size:0.68rem;margin:0;">No hay resultados publicados para "${term}".</p>
                </div>
            `;
            return;
        }

        list.innerHTML = noticias.map(n => {
            const imgUrl = n.image_url || n.imagen || null;
            const catName = n.category?.name || 'General';
            const catColor = n.category?.color || '#D71920';
            const serialized = JSON.stringify({
                id: n.id,
                titulo: n.titulo,
                bajada: n.bajada || '',
                contenido: n.contenido ? n.contenido.replace(/<[^>]*>/g, '') : '',
                categoria: catName,
                categoria_color: catColor,
                imagen: imgUrl,
                autor: n.autor || 'REDACCIÓN'
            }).replace(/'/g, "&#39;");

            return `
                <div class="drawer-news-card" data-category="${n.category_id || ''}" data-title="${(n.titulo || '').toLowerCase()}" onclick='onDrawerNewsCardClick(${serialized})' style="cursor:pointer;">
                    <div class="dnc-header">
                        ${imgUrl ? `<img class="dnc-thumb" src="${imgUrl}" alt="">` : '<div class="dnc-no-thumb"><i class="fas fa-image"></i></div>'}
                        <div class="dnc-info">
                            <span class="dnc-cat" style="color:${catColor};">${catName}</span>
                            <div class="dnc-title">${n.titulo}</div>
                        </div>
                    </div>
                    <div class="dnc-actions" onclick="event.stopPropagation()">
                        <span style="font-size:0.58rem;color:var(--id-text-muted);margin-right:2px;">Insertar:</span>
                        <button class="dnc-btn primary" onclick='insertNewsBlock(${serialized}, "article")'>
                            <i class="fas fa-newspaper"></i> Artículo
                        </button>
                        <button class="dnc-btn" onclick='insertNewsBlock(${serialized}, "headline")'>
                            <i class="fas fa-heading"></i> Titular
                        </button>
                        <button class="dnc-btn" onclick='insertNewsBlock(${serialized}, "photo_caption")'>
                            <i class="fas fa-camera"></i> Foto
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    })
    .catch(err => {
        console.warn('Error en búsqueda remota de noticias, usando filtro local', err);
        filterDrawerNews();
    });
}

function onDrawerNewsCardClick(news) {
    if (selectedFrame && selectedFrame.dataset.type === 'article') {
        const page = currentEdicion.paginas[activePageIndex];
        const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
        if (f) {
            recordHistory();
            f.noticia_id = news.id;
            if (!f.propiedades) f.propiedades = {};
            f.propiedades.noticia_id = news.id;
            f.titular = news.titulo;
            f.subtitulo = news.bajada || '';
            f.cuerpo = news.contenido || news.bajada || '';
            f.autor = news.autor || 'Redacción';
            f.categoria_label = news.categoria || 'General';
            f.categoria_color = news.categoria_color || '#D71920';

            // Auto-poblar marco de imagen adyacente con confirmación (Req 4.4)
            if (news.imagen) {
                const imgFrame = (page.frames || []).find(fr => fr.type === 'image');
                if (imgFrame) {
                    if (confirm('La noticia incluye fotografía. ¿Deseas asignarla también al marco de imagen de esta página?')) {
                        imgFrame.src = news.imagen;
                        imgFrame.caption = news.titulo;
                        const imgEl = document.getElementById(`frame-${imgFrame.id}`);
                        if (imgEl) {
                            const newImgEl = buildFrameElement(imgFrame, activePageIndex);
                            imgEl.parentNode.replaceChild(newImgEl, imgEl);
                        }
                    }
                }
            }

            const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
            const newEl = buildFrameElement(f, activePageIndex);
            sheet.replaceChild(newEl, selectedFrame);
            selectFrame(newEl);
            populateInspector(newEl);
            markUnsaved();
            showToast('Noticia vinculada al artículo con éxito (≤300ms)', 'success');
            toggleNewsDrawer();
            return;
        }
    }

    insertNewsBlock(news, 'article');
}

function insertNewsBlock(news, mode) {
    toggleNewsDrawer();
    recordHistory();

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    if (!sheet) return;

    if (mode === 'article') {
        createCustomFrame({ type:'headline', x:20, y:100, w:680, h:80, z:8,
            kicker: news.categoria || 'NOTICIA',
            content: `<p style="font-family:'Oswald',sans-serif;font-size:26px;font-weight:700;line-height:1.15;color:#0f172a;margin:0;">${news.titulo}</p>`
        });
        if (news.imagen) {
            createCustomFrame({ type:'image', x:20, y:185, w:300, h:180, z:7, src: news.imagen, caption: news.titulo });
            createCustomFrame({ type:'article', x:330, y:185, w:370, h:240, z:7, columns:2,
                titular: news.titulo, subtitulo: news.bajada || '', cuerpo: news.contenido || news.bajada || '',
                autor: news.autor || 'Redacción', categoria_label: news.categoria || 'General', categoria_color: news.categoria_color || '#D71920',
                noticia_id: news.id
            });
        } else {
            createCustomFrame({ type:'article', x:20, y:185, w:680, h:200, z:7, columns:3,
                titular: news.titulo, subtitulo: news.bajada || '', cuerpo: news.contenido || news.bajada || '',
                autor: news.autor || 'Redacción', categoria_label: news.categoria || 'General', categoria_color: news.categoria_color || '#D71920',
                noticia_id: news.id
            });
        }
    } else if (mode === 'headline') {
        createCustomFrame({ type:'headline', x:20, y:100, w:680, h:70, z:8,
            kicker: news.categoria || 'NOTICIA',
            content: `<p style="font-family:'Oswald',sans-serif;font-size:26px;font-weight:700;color:#0f172a;margin:0;">${news.titulo}</p>`
        });
    } else if (mode === 'photo_caption') {
        createCustomFrame({ type:'image', x:20, y:100, w:420, h:260, z:7, src: news.imagen, caption: news.titulo });
    }

    markUnsaved();
    showToast('Noticia insertada en la maqueta', 'success');
}

// ── EDITORIAL WORKFLOW & PUBLICATION SUITE (TASK 14) ─────────
function toggleStateDropdown(e) {
    e.stopPropagation();
    renderAllowedTransitions();
    document.getElementById('editorialDropdownMenu')?.classList.toggle('show');
}

document.addEventListener('click', () => {
    document.getElementById('editorialDropdownMenu')?.classList.remove('show');
});

function renderAllowedTransitions() {
    const container = document.getElementById('allowedTransitionsContainer');
    if (!container) return;

    const curState = currentEdicion.estado || (currentEdicion.publicada ? 'publicado' : 'borrador');
    const allowed = ESTADO_TRANSICIONES[curState] || [];

    if (allowed.length === 0) {
        container.innerHTML = `
            <div style="padding:8px 12px;font-size:0.72rem;color:#94a3b8;font-style:italic;">
                No hay transiciones posibles desde "${curState}".
            </div>
        `;
        return;
    }

    const stateMeta = {
        'borrador':   { label: 'Pasar a Borrador', icon: 'fas fa-circle text-secondary' },
        'revision':   { label: 'Enviar a Revisión', icon: 'fas fa-eye text-warning' },
        'aprobado':   { label: 'Aprobar Edición', icon: 'fas fa-check text-info' },
        'programado': { label: 'Programar Publicación...', icon: 'fas fa-clock', style: 'color:#c084fc;' },
        'publicado':  { label: 'Publicar Ahora', icon: 'fas fa-check-double text-success' }
    };

    container.innerHTML = allowed.map(st => {
        const meta = stateMeta[st] || { label: st, icon: 'fas fa-arrow-right text-white' };
        return `
            <button type="button" class="id-dropdown-item" onclick="onTransitionClick('${st}')" style="display:flex;align-items:center;gap:8px;width:100%;text-align:left;background:transparent;border:none;padding:7px 12px;font-size:0.74rem;cursor:pointer;color:#e2e8f0;">
                <i class="${meta.icon}" style="${meta.style || ''}"></i>
                <span>${meta.label}</span>
            </button>
        `;
    }).join('');
}

function onTransitionClick(nuevoEstado) {
    document.getElementById('editorialDropdownMenu')?.classList.remove('show');
    if (nuevoEstado === 'programado') {
        setQuickSchedule(1);
        openModal('scheduleModal');
        return;
    }
    updateEditorialState(nuevoEstado);
}

function updateEditorialState(estado, fechaProgramada = null) {
    document.getElementById('editorialDropdownMenu')?.classList.remove('show');

    fetch(`{{ url('/admin/periodico') }}/${currentEdicion.id}/estado`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            estado: estado,
            fecha_programada: fechaProgramada
        })
    })
    .then(async r => {
        const data = await r.json();
        if (r.status === 422 || !r.ok) {
            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Transición editorial no permitida'));
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            currentEdicion.estado = estado;
            if (fechaProgramada) currentEdicion.fecha_programada = fechaProgramada;
            if (estado === 'publicado') currentEdicion.publicada = true;
            else if (estado === 'programado') currentEdicion.publicada = false;

            updateTopBarBadges(estado, fechaProgramada);
            renderAllowedTransitions();
            showToast(data.message || `Estado editorial actualizado a: ${estado}`, 'success');
        } else {
            showToast(data.message || 'Error al actualizar estado editorial', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast(err.message || 'Error al actualizar estado editorial', 'error');
    });
}

function updateTopBarBadges(estado, fechaProgramada = null) {
    const states = {
        borrador:   { class: 'bg-secondary text-white', icon: 'fas fa-circle', text: 'Borrador' },
        revision:   { class: 'bg-warning text-dark',    icon: 'fas fa-eye',    text: 'En Revisión' },
        aprobado:   { class: 'bg-info text-white',      icon: 'fas fa-check',  text: 'Aprobado' },
        programado: { class: 'text-white', style: 'background:#9333ea;', icon: 'fas fa-clock', text: 'Programado' },
        publicado:  { class: 'bg-success text-white',   icon: 'fas fa-check-double', text: 'Publicada' }
    };
    const s = states[estado] || states.borrador;

    // Menubar badge
    const mbBadge = document.getElementById('topBarStateBadge');
    const mbText  = document.getElementById('topBarStateText');
    if (mbBadge) {
        mbBadge.className = `badge ${s.class}`;
        mbBadge.setAttribute('style', `font-size:0.65rem;padding:3px 6px;${s.style || ''}`);
        if (mbText) mbText.textContent = s.text;
    }

    // Page title badge
    const ptBadge = document.getElementById('pageStatusBadge');
    if (ptBadge) {
        ptBadge.className = `badge ${s.class}`;
        ptBadge.setAttribute('style', `font-size:0.72rem;padding:4px 8px;${s.style || ''}`);
        ptBadge.innerHTML = `<i class="${s.icon} me-1"></i> <span>${s.text}</span>`;
    }

    // Quick publish button
    const pubBtn = document.getElementById('publishBtn');
    const pubLbl = document.getElementById('publishBtnLabel');
    if (pubBtn && pubLbl) {
        if (estado === 'publicado') {
            pubBtn.className = 'id-btn id-btn-success';
            pubLbl.textContent = 'Publicado';
        } else {
            pubBtn.className = 'id-btn id-btn-danger';
            pubLbl.textContent = 'Publicar Edición';
        }
    }
}

function setQuickSchedule(daysAhead) {
    const now = new Date();
    now.setDate(now.getDate() + daysAhead);
    now.setHours(6, 0, 0, 0);
    const isoLocal = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('schedDateTimeInput').value = isoLocal;
}

function submitSchedulePublication() {
    const dtVal = document.getElementById('schedDateTimeInput').value;
    if (!dtVal) {
        alert('Por favor selecciona una fecha y hora válida.');
        return;
    }
    closeModal('scheduleModal');
    updateEditorialState('programado', dtVal);
}

// ── LATITUD 18 ADVANCED MODALS ENGINES (TASKS 12, 13, 15) ────

// 1. PREVISUALIZACIÓN FULL-SCREEN (TASK 15)
function openPreviewModal() {
    previewActivePage = activePageIndex;
    const totalP = (currentEdicion.paginas || []).length;
    const totalEl = document.getElementById('previewTotalPages');
    if (totalEl) totalEl.textContent = `de ${totalP}`;
    const pageInp = document.getElementById('previewPageInput');
    if (pageInp) {
        pageInp.max = totalP;
        pageInp.value = previewActivePage + 1;
    }
    previewRenderPage(previewActivePage);
    openModal('previewModal');
}

function previewRenderPage(idx) {
    const totalP = (currentEdicion.paginas || []).length;
    if (idx < 0 || idx >= totalP) return;
    previewActivePage = idx;

    const pageInp = document.getElementById('previewPageInput');
    if (pageInp) pageInp.value = idx + 1;

    const container = document.getElementById('previewSheetWrapper');
    if (!container) return;
    container.innerHTML = '';

    const page = currentEdicion.paginas[idx];
    if (!page) return;
    container.style.width = (page.ancho || 720) + 'px';
    container.style.height = (page.alto || 1040) + 'px';

    (page.frames || []).forEach(fData => {
        const el = document.createElement('div');
        el.className = 'id-frame preview-frame';
        el.style.position = 'absolute';
        el.style.left = (fData.x || 20) + 'px';
        el.style.top = (fData.y || 20) + 'px';
        el.style.width = (fData.w || 200) + 'px';
        el.style.height = (fData.h || 100) + 'px';
        el.style.zIndex = fData.z || 10;
        if (fData.opacity !== undefined) el.style.opacity = fData.opacity;
        el.style.pointerEvents = 'none'; // Desactiva todas las interacciones (Req 6.3)

        if (fData.type === 'masthead') {
            el.innerHTML = `
                <div class="frame-masthead-inner" style="border-bottom:3px solid #0284c7;padding-bottom:4px;font-family:'Bebas Neue',sans-serif;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <div style="background:#fef9c3;color:#854d0e;padding:4px 8px;font-size:9px;font-weight:700;width:140px;line-height:1.2;font-family:'Montserrat',sans-serif;">
                            ${fData.leftEar || 'CRE 100%'}
                        </div>
                        <div style="text-align:center;flex:1;">
                            <span style="font-family:'Bebas Neue',sans-serif;font-size:52px;color:#0284c7;line-height:1;letter-spacing:1.5px;">${fData.nombre_seccion || fData.newspaperName || 'LATITUD 18'}</span>
                            <span style="background:#D71920;color:#fff;font-family:'Bebas Neue',sans-serif;font-size:20px;padding:2px 8px;margin-left:4px;vertical-align:middle;">${fData.subBadge || 'DIARIO DIGITAL'}</span>
                            <div style="font-size:9px;font-weight:800;letter-spacing:1.5px;color:#64748b;text-transform:uppercase;margin-top:2px;font-family:'Montserrat',sans-serif;">${fData.motto || 'INFORMACIÓN SIN RUIDO • SANTA CRUZ DE LA SIERRA'}</div>
                        </div>
                        <div style="background:#0284c7;color:#fff;padding:4px 8px;font-size:9px;font-weight:800;width:130px;text-align:right;line-height:1.2;font-family:'Montserrat',sans-serif;">
                            ${fData.rightEar || 'DÓLAR: Bs 12,58'}
                        </div>
                    </div>
                </div>
            `;
        } else if (fData.type === 'headline') {
            el.innerHTML = `
                <div style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:center;">
                    ${fData.kicker ? `<span style="font-size:11px;font-weight:800;color:#D71920;text-transform:uppercase;margin-bottom:2px;font-family:'Montserrat',sans-serif;">${fData.kicker}</span>` : ''}
                    <div style="font-family:'Bebas Neue',Impact,sans-serif;font-size:32px;letter-spacing:0.5px;line-height:1.05;">
                        ${fData.content || 'TITULAR DE IMPACTO PERIODÍSTICO'}
                    </div>
                </div>
            `;
        } else if (fData.type === 'article') {
            const isLinked = !!(fData.noticia_id || fData.propiedades?.noticia_id);
            const catColor = fData.categoria_color || fData.propiedades?.categoria_color || '#D71920';
            const cols = fData.columns || 3;
            const titular = fData.titular || fData.content_title || 'Titular del Artículo';
            const subtitulo = fData.subtitulo || '';
            const cuerpo = fData.cuerpo || fData.content || 'Texto del artículo...';
            const autor = fData.autor || '';

            el.innerHTML = `
                <div style="width:100%;height:100%;position:relative;display:flex;flex-direction:column;box-sizing:border-box;font-family:'Montserrat',sans-serif;${isLinked ? 'padding-top:8px;' : ''}">
                    ${isLinked ? `<div class="frame-category-bar" style="background:${catColor};height:6px;position:absolute;top:0;left:0;right:0;"></div>` : ''}
                    <div style="margin-bottom:4px;">
                        <div style="font-family:'Montserrat',sans-serif;font-size:17px;font-weight:800;line-height:1.15;color:#0f172a;margin:0;">${titular}</div>
                        ${subtitulo ? `<div style="font-size:10.5px;font-weight:600;color:#64748b;font-style:italic;margin-top:2px;">${subtitulo}</div>` : ''}
                    </div>
                    <div style="flex:1;font-family:'Montserrat',sans-serif;font-size:11px;line-height:1.4;color:#1e293b;column-count:${cols > 1 ? cols : ''};column-gap:12px;text-align:justify;overflow:hidden;">
                        ${cuerpo}
                    </div>
                    ${autor ? `<div style="font-size:9px;font-weight:700;color:${catColor};text-transform:uppercase;margin-top:4px;">Por: ${autor}</div>` : ''}
                </div>
            `;
        } else if (fData.type === 'image') {
            el.innerHTML = `
                <div style="width:100%;height:100%;display:flex;flex-direction:column;position:relative;">
                    <div style="flex:1;position:relative;overflow:hidden;">
                        <img src="${fData.src || ''}" style="width:100%;height:100%;object-fit:${fData.image_fit === 'contain' ? 'contain' : 'cover'};display:block;">
                    </div>
                    ${fData.caption ? `<div style="font-size:9.5px;color:#475569;line-height:1.3;padding-top:4px;font-style:italic;font-family:'Montserrat',sans-serif;">${fData.caption}</div>` : ''}
                </div>
            `;
        } else if (fData.type === 'ad') {
            const formatCode = fData.format_code || fData.propiedades?.format_code || 'A1';
            const status = fData.status || fData.propiedades?.status || 'disponible';
            const adImg = fData.advertiser_image_url || fData.propiedades?.advertiser_image_url || null;
            const adName = fData.advertiser_name || fData.propiedades?.advertiser_name || '';

            if (status === 'ocupado' && adImg) {
                el.innerHTML = `
                    <div style="width:100%;height:100%;position:relative;overflow:hidden;">
                        <span class="ad-format-badge">${formatCode}</span>
                        <img src="${adImg}" alt="${adName}" style="width:100%;height:100%;object-fit:contain;display:block;">
                    </div>
                `;
            } else {
                el.innerHTML = `
                    <div class="ad-disponible-box" style="position:relative;width:100%;height:100%;background:#E8F0FE;border:1px dashed #3b82f6;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <span class="ad-format-badge">${formatCode}</span>
                        <span class="ad-watermark-text" style="font-size:16px;font-weight:900;color:rgba(59,130,246,0.35);letter-spacing:2px;font-family:'Montserrat',sans-serif;">DISPONIBLE</span>
                    </div>
                `;
            }
        } else if (fData.type === 'quote') {
            el.innerHTML = `<div style="font-family:'Montserrat',sans-serif;padding:6px;border-left:3px solid #D71920;background:#f8fafc;font-size:13px;line-height:1.35;font-style:italic;">${fData.content || ''}</div>`;
        } else if (fData.type === 'box') {
            el.innerHTML = `<div style="font-family:'Montserrat',sans-serif;padding:8px;border:1px solid #cbd5e1;background:#f8fafc;">${fData.content || ''}</div>`;
        } else if (fData.type === 'divider') {
            el.innerHTML = `<div style="width:100%;height:2px;background:${fData.color || '#cbd5e1'};"></div>`;
        } else if (fData.type === 'qr') {
            const qrUrl = encodeURIComponent(fData.url || 'https://latitud18.com/periodico');
            el.innerHTML = `
                <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#fff;border:1px solid #e2e8f0;padding:6px;text-align:center;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${qrUrl}" alt="QR" style="width:calc(100% - 8px);max-height:calc(100% - 22px);object-fit:contain;">
                    <div style="font-size:9px;font-weight:700;color:#334155;margin-top:2px;text-transform:uppercase;font-family:'Montserrat',sans-serif;">${fData.label || 'Escanea para leer online'}</div>
                </div>
            `;
        } else {
            el.innerHTML = `<div style="font-family:'Montserrat',sans-serif;">${fData.content || ''}</div>`;
        }

        container.appendChild(el);
    });
}

function previewPrevPage() {
    if (previewActivePage > 0) previewRenderPage(previewActivePage - 1);
}

function previewNextPage() {
    const totalP = (currentEdicion.paginas || []).length;
    if (previewActivePage < totalP - 1) previewRenderPage(previewActivePage + 1);
}

function previewGoToPage(val) {
    const num = parseInt(val) - 1;
    const totalP = (currentEdicion.paginas || []).length;
    if (!isNaN(num) && num >= 0 && num < totalP) {
        previewRenderPage(num);
    }
}

function previewEditCurrentPage() {
    closeModal('previewModal');
    switchPage(previewActivePage);
}

// 2. INVENTARIO DE PUBLICIDAD CROSS-PAGE (TASK 12)
function openPublicidadModal() {
    renderPublicidadInventory();
    openModal('publicidadModal');
}

function renderPublicidadInventory() {
    const tbody = document.getElementById('publicidadTableBody');
    if (!tbody) return;

    let items = [];
    (currentEdicion.paginas || []).forEach((page, pIdx) => {
        (page.frames || []).forEach(f => {
            if (f.type === 'ad') {
                items.push({ pageIdx: pIdx, pageNum: page.numero || (pIdx + 1), frame: f });
            }
        });
    });

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;">No hay espacios publicitarios insertados en ninguna página de esta edición.</td></tr>`;
        return;
    }

    tbody.innerHTML = items.map(it => {
        const f = it.frame;
        const fmtCode = f.format_code || f.propiedades?.format_code || 'A1';
        const fmt = AD_FORMATS[fmtCode] || {};
        const status = f.status || f.propiedades?.status || 'disponible';
        const advName = f.advertiser_name || f.propiedades?.advertiser_name || '—';
        const advImg = f.advertiser_image_url || f.propiedades?.advertiser_image_url || null;

        return `
            <tr style="border-bottom:1px solid #1e293b;">
                <td><span class="badge bg-primary" style="font-size:0.7rem;">${fmtCode}</span> <span style="font-size:0.7rem;color:#cbd5e1;margin-left:4px;">${fmt.label || ''}</span></td>
                <td>Pág. ${it.pageNum}</td>
                <td style="color:#94a3b8;font-size:0.68rem;">${fmt.w || f.w}x${fmt.h || f.h} px (${fmt.w_cm || ''}x${fmt.h_cm || ''} cm)</td>
                <td>
                    <span class="badge ${status === 'ocupado' ? 'bg-success' : 'bg-warning text-dark'}" style="font-size:0.68rem;">
                        ${status.toUpperCase()}
                    </span>
                </td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${advName}</td>
                <td>
                    ${advImg ? `<img src="${advImg}" style="width:36px;height:24px;object-fit:cover;border-radius:2px;border:1px solid #475569;">` : '<span style="color:#64748b;font-size:0.65rem;">Sin imagen</span>'}
                </td>
                <td style="text-align:right;">
                    ${status === 'disponible' ? `
                        <button type="button" class="id-btn id-btn-success" onclick="toggleAdInventoryStatus(${it.pageIdx}, '${f.id}', 'ocupado')" style="font-size:0.65rem;padding:2px 8px;">
                            Marcar Ocupado
                        </button>
                    ` : `
                        <button type="button" class="id-btn id-btn-ghost" onclick="toggleAdInventoryStatus(${it.pageIdx}, '${f.id}', 'disponible')" style="font-size:0.65rem;padding:2px 8px;border:1px solid #475569;">
                            Marcar Disponible
                        </button>
                    `}
                </td>
            </tr>
        `;
    }).join('');
}

function toggleAdInventoryStatus(pageIdx, frameId, newStatus) {
    const page = currentEdicion.paginas[pageIdx];
    const f = page?.frames?.find(fr => fr.id == frameId);
    if (!f) return;

    if (newStatus === 'ocupado') {
        const img = f.advertiser_image_url || f.propiedades?.advertiser_image_url;
        if (!img) {
            showToast('Para marcar como ocupado se requiere subir una imagen publicitaria primero en este marco.', 'error');
            return;
        }
    }

    f.status = newStatus;
    if (!f.propiedades) f.propiedades = {};
    f.propiedades.status = newStatus;

    if (pageIdx === activePageIndex) {
        const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
        const el = document.getElementById(`frame-${f.id}`);
        if (sheet && el) {
            const newEl = buildFrameElement(f, activePageIndex);
            sheet.replaceChild(newEl, el);
            if (selectedFrame === el) selectFrame(newEl);
        }
    }

    renderPublicidadInventory();
    markUnsaved();
    showToast(`Estado publicitario cambiado a ${newStatus}`, 'success');
}

// 3. PLANTILLA DE EDICIÓN COMPLETA (12 SLOTS) (TASK 13)
function openPlantillaEdicionModal() {
    renderEditionSlotsGrid();
    loadSavedEditionTemplates();
    openModal('plantillaEdicionModal');
}

const SLOT_DEFAULT_TEMPLATES = {
    1: 'tpl-portada-default',
    2: 'tpl-editorial-default',
    3: 'tpl-politica-a',
    4: 'tpl-politica-b',
    5: 'tpl-santacruz-a',
    6: 'tpl-santacruz-b',
    7: 'tpl-pais-a',
    8: 'tpl-pais-b',
    9: 'tpl-economia-a',
    10: 'tpl-economia-b',
    11: 'tpl-seguridad-default',
    12: 'tpl-mundo-default',
};

function renderEditionSlotsGrid() {
    const grid = document.getElementById('editionSlotsGrid');
    if (!grid) return;

    grid.innerHTML = EDITION_SLOTS.map(s => {
        const defaultTplId = SLOT_DEFAULT_TEMPLATES[s.slot] || '';
        const tplOptions = (loadedTemplates || []).map(t => {
            const isSelected = (t.id === defaultTplId) ? 'selected' : '';
            return `<option value="${t.id}" ${isSelected}>${t.name} (${t.category})</option>`;
        }).join('');

        return `
            <div style="background:#18191c;padding:8px;border:1px solid #2d3035;border-radius:4px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                    <span style="font-weight:800;color:#38bdf8;font-size:0.75rem;">Slot ${s.slot}</span>
                    <span style="color:#94a3b8;font-size:0.65rem;text-transform:uppercase;">${s.seccion}</span>
                </div>
                <select class="id-form-input edition-slot-select" data-slot="${s.slot}" style="font-size:0.72rem;padding:4px;width:100%;">
                    <option value="">(Página en blanco / Sin plantilla)</option>
                    ${tplOptions}
                </select>
            </div>
        `;
    }).join('');
}

function loadSavedEditionTemplates() {
    const list = document.getElementById('savedEditionTemplatesList');
    if (!list) return;

    list.innerHTML = '<div style="color:#94a3b8;font-size:0.72rem;text-align:center;padding:12px;"><i class="fas fa-spinner fa-spin me-1"></i> Cargando plantillas...</div>';

    fetch('/admin/periodico/plantilla-edicion', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const payload = data && (data.plantillas_edicion || data.plantillas || data);
        savedEditionTemplates = Array.isArray(payload) ? payload : [];
        if (savedEditionTemplates.length === 0) {
            list.innerHTML = '<div style="color:#64748b;font-size:0.72rem;text-align:center;padding:16px;">Aún no hay plantillas de edición completa guardadas.</div>';
            return;
        }

        list.innerHTML = savedEditionTemplates.map(tpl => {
            return `
                <div style="background:#18191c;border:1px solid #2d3035;border-radius:4px;padding:8px;">
                    <div style="font-weight:700;color:#f8fafc;font-size:0.78rem;">${tpl.nombre}</div>
                    <div style="font-size:0.68rem;color:#94a3b8;margin-top:2px;">${tpl.descripcion || 'Sin descripción'}</div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                        <span style="font-size:0.62rem;color:#64748b;">12 Páginas</span>
                        <button type="button" class="id-btn id-btn-primary" onclick="createEditionFromSavedTemplate(${tpl.id}, '${tpl.nombre}')" style="font-size:0.68rem;padding:3px 8px;">
                            <i class="fas fa-magic me-1"></i> Crear Edición
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    })
    .catch(err => {
        console.error(err);
        list.innerHTML = '<div style="color:#ef4444;font-size:0.72rem;text-align:center;padding:12px;">Error al cargar plantillas de edición</div>';
    });
}

function submitSaveEditionTemplate() {
    const name = document.getElementById('editionTemplateName')?.value.trim();
    const desc = document.getElementById('editionTemplateDesc')?.value.trim();

    if (!name) {
        alert('Por favor introduce un nombre para la plantilla de edición.');
        return;
    }

    const mapping = [];
    document.querySelectorAll('.edition-slot-select').forEach(sel => {
        const slotNum = parseInt(sel.dataset.slot);
        const tplId = sel.value || null;
        mapping.push({ slot: slotNum, plantilla_id: tplId });
    });

    if (mapping.length !== 12) {
        alert('La plantilla de edición debe contener exactamente 12 slots.');
        return;
    }

    fetch('/admin/periodico/plantilla-edicion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            nombre: name,
            descripcion: desc,
            mapping: mapping
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editionTemplateName').value = '';
            document.getElementById('editionTemplateDesc').value = '';
            showToast('¡Plantilla de edición de 12 páginas guardada exitosamente!', 'success');
            loadSavedEditionTemplates();
        } else {
            showToast(data.message || 'Error al guardar plantilla de edición', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error de conexión al guardar plantilla', 'error');
    });
}

function createEditionFromSavedTemplate(tplId, tplName) {
    const defaultNum = `Edición ${Math.floor(Math.random() * 800) + 120}`;
    const num = prompt(`Crear nueva edición completa desde "${tplName}":\n\nNúmero de la edición:`, defaultNum);
    if (!num) return;

    const fecha = prompt(`Fecha de la edición:`, new Date().toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' }));
    if (!fecha) return;

    closeModal('plantillaEdicionModal');
    showToast('Generando edición completa de 12 páginas...', 'info');

    fetch(`/admin/periodico/plantilla-edicion/${tplId}/crear-edicion`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            numero_edicion: num,
            fecha: fecha,
            titulo: `Periódico Digital - ${num}`
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.redirect_url) {
            showToast(res.message || '¡Edición de 12 páginas creada con éxito!', 'success');
            setTimeout(() => {
                window.location.href = res.redirect_url;
            }, 600);
        } else {
            const slotMsg = res.failed_slot ? ` (Falló en Slot ${res.failed_slot})` : '';
            showToast((res.message || 'Error al crear edición') + slotMsg, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Error de conexión al crear edición completa', 'error');
    });
}

function toggleSnapGrid() {
    snapGridActive = !snapGridActive;
    const btn = document.getElementById('snapToggleBtn');
    if (btn) btn.classList.toggle('active', snapGridActive);
    showToast(snapGridActive ? 'Ajuste magnético a cuadrícula (10px) ACTIVADO' : 'Ajuste magnético DESACTIVADO', 'success');
}

// ── FULLSCREEN EDITOR MODE ─────────────────────────
let isFullscreen = false;

function toggleFullscreenEditor() {
    const app = document.getElementById('idApp');
    const indicator = document.getElementById('fullscreen-overlay-indicator');
    const icon = document.getElementById('fullscreenIcon');
    const label = document.getElementById('fullscreenLabel');

    isFullscreen = !isFullscreen;

    if (isFullscreen) {
        app.classList.add('fullscreen-mode');
        document.body.classList.add('editor-fullscreen');
        // Hide Laravel admin layout elements
        document.querySelectorAll('.main-header, .main-sidebar, .main-footer, .content-header').forEach(el => {
            if (el) el.style.display = 'none';
        });
        // Make the content wrapper and its parent full-screen
        const contentWrapper = document.querySelector('.content-wrapper');
        const appWrapper = document.querySelector('.wrapper');
        if (contentWrapper) {
            contentWrapper.style.marginLeft = '0';
            contentWrapper.style.padding = '0';
            contentWrapper.style.minHeight = '100vh';
        }
        if (appWrapper) {
            appWrapper.style.overflow = 'hidden';
        }
        window.scrollTo(0, 0);
        if (indicator) indicator.classList.add('visible');
        if (icon) { icon.className = 'fas fa-compress'; }
        if (label) label.textContent = 'Salir Pantalla Completa';
        // Try native fullscreen API
        try {
            if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen();
        } catch(e) {}
        showToast('Modo Pantalla Completa activado — Presiona Esc o haz clic en el indicador para salir', 'success');
    } else {
        exitFullscreen();
    }
}

function exitFullscreen() {
    isFullscreen = false;
    const app = document.getElementById('idApp');
    const indicator = document.getElementById('fullscreen-overlay-indicator');
    const icon = document.getElementById('fullscreenIcon');
    const label = document.getElementById('fullscreenLabel');

    app.classList.remove('fullscreen-mode');
    document.body.classList.remove('editor-fullscreen');

    // Restore admin layout
    document.querySelectorAll('.main-header, .main-sidebar, .main-footer, .content-header').forEach(el => {
        if (el) el.style.display = '';
    });
    const contentWrapper = document.querySelector('.content-wrapper');
    const appWrapper = document.querySelector('.wrapper');
    if (contentWrapper) {
        contentWrapper.style.marginLeft = '';
        contentWrapper.style.padding = '';
        contentWrapper.style.minHeight = '';
    }
    if (appWrapper) appWrapper.style.overflow = '';

    if (indicator) indicator.classList.remove('visible');
    if (icon) icon.className = 'fas fa-expand';
    if (label) label.textContent = 'Pantalla Completa';

    try {
        if (document.exitFullscreen && document.fullscreenElement) document.exitFullscreen();
    } catch(e) {}

    showToast('Modo Pantalla Completa desactivado', 'success');
}

// Exit fullscreen on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isFullscreen) {
        exitFullscreen();
    }
    // F11 to toggle
    if (e.key === 'F11' && document.getElementById('idApp')) {
        e.preventDefault();
        toggleFullscreenEditor();
    }
});

// Sync with native fullscreen API exit (user presses Esc in browser)
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && isFullscreen) {
        exitFullscreen();
    }
});

// ── UNDO / REDO ────────────────────────────────────
function recordHistory() {
    undoHistory.push(JSON.parse(JSON.stringify(currentEdicion)));
    if (undoHistory.length > 40) undoHistory.shift();
    redoHistory = [];
}

function undoAction() {
    if (undoHistory.length === 0) {
        showToast('No hay más acciones para deshacer', 'info');
        return;
    }
    redoHistory.push(JSON.parse(JSON.stringify(currentEdicion)));
    currentEdicion = undoHistory.pop();
    renderAllPages();
    switchPage(activePageIndex);
    markUnsaved();
    showToast('Acción deshecha (Ctrl+Z)', 'success');
}

function redoAction() {
    if (redoHistory.length === 0) {
        showToast('No hay más acciones para rehacer', 'info');
        return;
    }
    undoHistory.push(JSON.parse(JSON.stringify(currentEdicion)));
    currentEdicion = redoHistory.pop();
    renderAllPages();
    switchPage(activePageIndex);
    markUnsaved();
    showToast('Acción rehecha (Ctrl+Y)', 'success');
}

function setupKeyboard() {
    document.addEventListener('keydown', (e) => {
        // Ctrl+S o Cmd+S para Guardar en cualquier momento
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
            saveEdicionLayout(true);
            return;
        }

        if (e.target.isContentEditable || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        if (e.ctrlKey && (e.key === 'z' || e.key === 'Z')) { e.preventDefault(); undoAction(); }
        if (e.ctrlKey && (e.key === 'y' || e.key === 'Y')) { e.preventDefault(); redoAction(); }
        if (e.ctrlKey && (e.key === 'l' || e.key === 'L')) { e.preventDefault(); toggleLockSelected(); }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (selectedFrame) { e.preventDefault(); deleteSelected(); }
        }
        if (e.key === 'v' || e.key === 'V') selectTool('select');
        if (e.key === 'm' || e.key === 'M') createSpecialElement('masthead');
        if (e.key === 'h' || e.key === 'H') createSpecialElement('headline');
        if (e.key === 'a' || e.key === 'A') createSpecialElement('article');
        if (e.key === 'i' || e.key === 'I') createSpecialElement('image');
        if (e.key === 'q' || e.key === 'Q') createSpecialElement('quote');
        if (e.key === 'b' || e.key === 'B') createSpecialElement('box');
        if (e.key === 'd' || e.key === 'D') createSpecialElement('divider');
        if (e.key === 'k' || e.key === 'K') createSpecialElement('qr');
        if (e.key === 'p' || e.key === 'P') createSpecialElement('ad');
        if (e.key === 'n' || e.key === 'N') toggleNewsDrawer();
    });
}

function selectTool(tool) {
    currentTool = tool;
    document.querySelectorAll('.id-tool-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`tool-${tool}`)?.classList.add('active');
}

// ── MODALS & DRAWER ────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.id-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});

function toggleNewsDrawer() { document.getElementById('newsDrawer').classList.toggle('open'); }

function filterDrawerNews() {
    const cat  = document.getElementById('drawerCatFilter').value;
    const term = document.getElementById('drawerSearch').value.toLowerCase();
    document.querySelectorAll('.drawer-news-card').forEach(card => {
        const matchCat  = cat === 'all' || card.dataset.category == cat;
        const matchTerm = !term || card.dataset.title.includes(term);
        card.style.display = (matchCat && matchTerm) ? '' : 'none';
    });
}

function openPdfExportModal() { openModal('pdfExportModal'); }

// La descarga del PDF se hace contra la ruta real (server-side DomPDF).
// No se usa window.print() para evitar una segunda vía de exportación.

// ── SAVE & PUBLISH BACKEND ─────────────────────────
function markUnsaved() {
    hasUnsavedChanges = true;
    const ind = document.getElementById('saveIndicator');
    ind.innerHTML = '<i class="fas fa-circle" style="color:#f59e0b;font-size:0.5rem;"></i> <span>Cambios sin guardar</span>';
    ind.style.color = '#f59e0b';
}

function switchEdition(id) {
    if (hasUnsavedChanges && !confirm('Tienes cambios sin guardar. ¿Deseas continuar y cambiar de edición?')) {
        document.getElementById('editionSelect').value = currentEdicion.id;
        return;
    }
    window.location.href = `{{ route('admin.periodico.index') }}?edicion_id=${id}`;
}

function saveEdicionLayout(showSuccessToast = true) {
    // Sync active frames text
    (currentEdicion.paginas || []).forEach((page, pIdx) => {
        (page.frames || []).forEach(f => {
            const el = document.getElementById(`frame-${f.id}`);
            if (el) {
                const textInner = el.querySelector('.frame-text-inner') || el.querySelector('.frame-quote-inner') || el.querySelector('.frame-box-inner');
                if (textInner) f.content = textInner.innerHTML;
            }
        });
    });

    const ind = document.getElementById('saveIndicator');
    if (ind) {
        ind.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Guardando...</span>';
        ind.style.color = '#38bdf8';
    }

    const saveBtns = document.querySelectorAll('#mainSaveBtn, .btn-save-periodico');
    saveBtns.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Guardando...</span>';
    });

    fetch(`{{ url('/admin/periodico') }}/${currentEdicion.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(currentEdicion)
    })
    .then(r => r.json())
    .then(data => {
        hasUnsavedChanges = false;
        if (ind) {
            ind.innerHTML = '<i class="fas fa-check-circle" style="color:#4ade80;"></i> <span>Al día</span>';
            ind.style.color = '#4ade80';
        }
        saveBtns.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> <span>Guardar Periódico</span>';
        });
        const mainBtn = document.getElementById('mainSaveBtn');
        if (mainBtn) mainBtn.innerHTML = '<i class="fas fa-save"></i> <span>GUARDAR</span>';

        if (showSuccessToast) showToast('¡Edición guardada exitosamente!', 'success');
    })
    .catch(err => {
        console.error(err);
        if (ind) {
            ind.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i> <span>Error</span>';
            ind.style.color = '#ef4444';
        }
        saveBtns.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> <span>Guardar Periódico</span>';
        });
        const mainBtn = document.getElementById('mainSaveBtn');
        if (mainBtn) mainBtn.innerHTML = '<i class="fas fa-save"></i> <span>GUARDAR</span>';

        showToast('Error al guardar la edición. Verifica tu conexión.', 'error');
    });
}

function togglePublishEdition() {
    const isPub = !currentEdicion.publicada;
    currentEdicion.publicada = isPub;

    fetch(`{{ url('/admin/periodico') }}/${currentEdicion.id}/publish`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ publicada: isPub })
    })
    .then(r => r.json())
    .then(data => {
        const btn = document.getElementById('publishBtn');
        const lbl = document.getElementById('publishBtnLabel');
        const badge = document.getElementById('pageStatusBadge');

        if (isPub) {
            btn.className = 'id-btn id-btn-success';
            lbl.textContent = 'Publicado';
            if (badge) { badge.className = 'badge bg-success'; badge.textContent = '✓ Publicada'; }
            showToast('La edición ahora es visible al público', 'success');
        } else {
            btn.className = 'id-btn id-btn-danger';
            lbl.textContent = 'Publicar Edición';
            if (badge) { badge.className = 'badge bg-secondary'; badge.textContent = '⬤ Borrador'; }
            showToast('La edición pasó a modo borrador', 'success');
        }
    });
}

function showToast(msg, type = 'success') {
    const toast = document.getElementById('idToast');
    const msgEl = document.getElementById('idToastMsg');
    const icon  = document.getElementById('idToastIcon');
    if (!toast || !msgEl) return;

    msgEl.textContent = msg;
    icon.className = type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-danger';
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

// Auto-guardado en segundo plano cada 60 segundos si hay cambios pendientes
setInterval(() => {
    if (hasUnsavedChanges) {
        saveEdicionLayout(false);
    }
}, 60000);
</script>
@endsection