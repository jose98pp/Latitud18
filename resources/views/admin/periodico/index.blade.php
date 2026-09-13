@extends('layouts.admin')
{{-- NOTE: Uses existing routes: admin.periodico.update, admin.periodico.publish, admin.periodico.uploadImage --}}

@section('title', 'Editor InDesign — ' . ($currentEdicion['titulo'] ?? 'Periódico'))

@section('page-title')
<div class="d-flex align-items-center gap-2" style="font-size:0.85rem;">
    <i class="fas fa-newspaper text-danger"></i>
    <span style="font-weight:800;letter-spacing:0.3px;">Editor de Maquetación</span>
    <span id="pageStatusBadge" class="badge {{ !empty($currentEdicion['publicada']) ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">
        {{ !empty($currentEdicion['publicada']) ? '✓ Publicada' : '⬤ Borrador' }}
    </span>
</div>
@endsection

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Cinzel:wght@700;900&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@500;700;800;900&family=Oswald:wght@500;700&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>

<style>
/* ══════════════════════════════════════════════════
   INDESIGN-STYLE EDITOR — ROOT VARIABLES
══════════════════════════════════════════════════ */
:root {
  --id-bg:         #2B2D31;
  --id-panel:      #1E2023;
  --id-toolbar:    #18191C;
  --id-border:     #3A3C40;
  --id-accent:     #C8472B;
  --id-accent2:    #4D7CCC;
  --id-text:       #E2E2E2;
  --id-text-muted: #888;
  --id-canvas-bg:  #404448;
  --id-ruler-bg:   #252729;
  --id-ruler-text: #777;
  --paper-w:       794px;
  --paper-h:       1123px;
}

/* ══════════════════════════════════════════ APP SHELL */
* { box-sizing: border-box; margin: 0; padding: 0; }

.id-app {
  display: grid;
  grid-template-rows: 40px 38px 1fr 120px;
  grid-template-columns: 52px 1fr 260px;
  height: calc(100vh - 60px);
  background: var(--id-bg);
  font-family: system-ui, -apple-system, sans-serif;
  color: var(--id-text);
  font-size: 0.78rem;
  overflow: hidden;
}

/* ══════════════════════════════════════════ TOP MENU BAR */
.id-menubar {
  grid-column: 1 / -1;
  grid-row: 1;
  background: var(--id-toolbar);
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 0 10px;
  border-bottom: 1px solid var(--id-border);
}

.id-menubar-title {
  font-weight: 800;
  font-size: 0.82rem;
  color: #fff;
  margin-right: 12px;
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
  max-width: 260px;
}

.id-edition-select:focus { outline: none; border-color: var(--id-accent2); }

.id-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
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
.id-btn-primary:hover { background: #3a64b0; }

.id-btn-danger {
  background: var(--id-accent);
  color: #fff;
  border-color: var(--id-accent);
}
.id-btn-danger:hover { background: #a8361f; }

.id-btn-success {
  background: #2d7a39;
  color: #fff;
  border-color: #2d7a39;
}
.id-btn-success:hover { background: #236130; }

.id-menubar-sep { width: 1px; height: 20px; background: var(--id-border); margin: 0 4px; }

.id-save-indicator {
  margin-left: auto;
  font-size: 0.7rem;
  color: #5da35d;
  display: flex;
  align-items: center;
  gap: 5px;
}

/* ══════════════════════════════════════════ CONTROL BAR (below menu) */
.id-controlbar {
  grid-column: 1 / -1;
  grid-row: 2;
  background: var(--id-panel);
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0 10px;
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
  max-width: 170px;
}

.id-font-size {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 2px 4px;
  border-radius: 3px;
  font-size: 0.72rem;
  width: 46px;
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
  gap: 2px;
  overflow: hidden;
}

.id-tool-btn {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  background: transparent;
  border: none;
  color: #888;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.15s;
  position: relative;
  flex-direction: column;
  gap: 2px;
}

.id-tool-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }
.id-tool-btn.active { background: var(--id-accent2); color: #fff; box-shadow: 0 2px 8px rgba(77,124,204,0.5); }

.id-tool-btn .tool-key {
  font-size: 0.55rem;
  font-weight: 700;
  color: inherit;
  opacity: 0.7;
}

.id-tool-sep { width: 32px; height: 1px; background: var(--id-border); margin: 4px 0; }

.id-zoom-label {
  font-size: 0.6rem;
  color: var(--id-text-muted);
  text-align: center;
  margin-top: 4px;
}

/* ══════════════════════════════════════════ CANVAS AREA */
.id-canvas-area {
  grid-column: 2;
  grid-row: 3;
  background: var(--id-canvas-bg);
  overflow: auto;
  position: relative;
  /* grid dot pattern */
  background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
  background-size: 20px 20px;
}

.id-canvas-area.ruler-on {
  padding-top: 24px;
  padding-left: 24px;
}

/* Rulers */
.ruler-h {
  position: sticky;
  top: 0;
  left: 24px;
  height: 24px;
  background: var(--id-ruler-bg);
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

/* Ruler Canvas elements */
.ruler-canvas {
  display: block;
}

/* Paper wrapper — centered in viewport */
.id-paper-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100%;
  padding: 40px;
}

/* The actual paper sheet */
.id-paper-sheet {
  width: var(--paper-w);
  min-height: var(--paper-h);
  background: #fff;
  position: relative;
  box-shadow: 0 8px 40px rgba(0,0,0,0.55);
  display: none;
  overflow: hidden;
  transform-origin: top center;
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
    linear-gradient(rgba(77,124,204,0.12) 1px, transparent 1px),
    linear-gradient(90deg, rgba(77,124,204,0.12) 1px, transparent 1px);
  background-size: 20px 20px;
}

.grid-visible .paper-grid-overlay { display: block; }

/* Margin guides */
.paper-margin-guide {
  position: absolute;
  top: 28px;
  left: 28px;
  right: 28px;
  bottom: 28px;
  border: 1px dashed rgba(215,25,32,0.25);
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

.id-frame:hover { border-color: rgba(77,124,204,0.5); }
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

/* TEXT FRAME */
.frame-text-inner {
  width: 100%;
  height: 100%;
  padding: 6px;
  outline: none;
  font-family: 'Source Sans 3', sans-serif;
  font-size: 14px;
  line-height: 1.5;
  color: #111;
  overflow: auto;
  word-wrap: break-word;
}

/* IMAGE FRAME */
.frame-img-inner {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  pointer-events: none;
}

.frame-img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #F3F4F6;
  color: #9CA3AF;
  gap: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
}

.frame-img-placeholder i { font-size: 2rem; opacity: 0.5; }

/* LINE FRAME */
.frame-line-inner {
  width: 100%;
  height: 2px;
  background: #000;
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
}

/* SHAPE FRAME */
.frame-shape-inner {
  width: 100%;
  height: 100%;
  background: #000;
}

/* Frame label (type indicator) */
.frame-label {
  position: absolute;
  top: -18px;
  left: 0;
  background: var(--id-accent2);
  color: #fff;
  font-size: 0.55rem;
  font-weight: 700;
  padding: 1px 5px;
  border-radius: 2px;
  text-transform: uppercase;
  display: none;
  white-space: nowrap;
}

.id-frame.selected .frame-label { display: block; }

/* ══════════════════════════════════════════ RIGHT INSPECTOR */
.id-inspector {
  grid-column: 3;
  grid-row: 3;
  background: var(--id-panel);
  border-left: 1px solid var(--id-border);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  overflow-x: hidden;
}

.inspector-section {
  border-bottom: 1px solid var(--id-border);
}

.inspector-section-header {
  padding: 7px 12px;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: var(--id-text-muted);
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  user-select: none;
}

.inspector-section-header:hover { color: #fff; }

.inspector-body {
  padding: 8px 12px 10px;
}

.inspector-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 7px;
}

.inspector-label {
  font-size: 0.65rem;
  color: var(--id-text-muted);
  min-width: 22px;
}

.inspector-input {
  flex: 1;
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 3px 6px;
  border-radius: 3px;
  font-size: 0.72rem;
}
.inspector-input:focus { outline: none; border-color: var(--id-accent2); }

.inspector-select {
  flex: 1;
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 3px 4px;
  border-radius: 3px;
  font-size: 0.72rem;
}

.insp-color-row {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  margin-top: 4px;
}

.insp-color-swatch {
  width: 20px;
  height: 20px;
  border-radius: 2px;
  cursor: pointer;
  border: 2px solid transparent;
  transition: border-color 0.15s;
}
.insp-color-swatch:hover { border-color: #fff; }
.insp-color-swatch.active { border-color: var(--id-accent2); }

.insp-color-custom {
  width: 20px;
  height: 20px;
  border-radius: 2px;
  border: 1px dashed #555;
  cursor: pointer;
  background: transparent;
  padding: 0;
}

.insp-full-btn {
  width: 100%;
  padding: 5px;
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--id-border);
  color: #ccc;
  border-radius: 3px;
  font-size: 0.7rem;
  cursor: pointer;
  text-align: center;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}
.insp-full-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
.insp-full-btn.danger:hover { background: rgba(215,25,32,0.2); border-color: var(--id-accent); color: #ef4444; }

/* Empty inspector state */
.inspector-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  text-align: center;
  color: var(--id-text-muted);
  gap: 10px;
}

.inspector-empty i { font-size: 2rem; opacity: 0.3; }
.inspector-empty p { font-size: 0.72rem; line-height: 1.4; }

/* ══════════════════════════════════════════ BOTTOM PAGES PANEL */
.id-pages-panel {
  grid-column: 1 / -1;
  grid-row: 4;
  background: var(--id-panel);
  border-top: 1px solid var(--id-border);
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 16px;
  overflow-x: auto;
}

.pages-panel-label {
  font-size: 0.65rem;
  color: var(--id-text-muted);
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
}

.page-thumb {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  flex-shrink: 0;
}

.page-thumb-preview {
  width: 60px;
  height: 85px;
  background: #fff;
  border: 2px solid transparent;
  border-radius: 3px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  transition: border-color 0.2s;
}

.page-thumb:hover .page-thumb-preview { border-color: var(--id-accent2); }
.page-thumb.active .page-thumb-preview { border-color: var(--id-accent2); box-shadow: 0 0 0 2px rgba(77,124,204,0.4); }

.page-thumb-label {
  font-size: 0.62rem;
  color: var(--id-text-muted);
  text-align: center;
  white-space: nowrap;
}

.page-thumb.active .page-thumb-label { color: var(--id-accent2); font-weight: 700; }

.page-add-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  flex-shrink: 0;
}

.page-add-preview {
  width: 60px;
  height: 85px;
  border: 2px dashed var(--id-border);
  border-radius: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--id-text-muted);
  font-size: 1.2rem;
  transition: all 0.15s;
}
.page-add-btn:hover .page-add-preview { border-color: var(--id-accent2); color: var(--id-accent2); }

/* ══════════════════════════════════════════ NEWS DRAWER */
.id-news-drawer {
  position: fixed;
  top: 0;
  right: -420px;
  width: 420px;
  height: 100vh;
  background: var(--id-panel);
  border-left: 3px solid var(--id-accent);
  z-index: 9000;
  display: flex;
  flex-direction: column;
  transition: right 0.3s cubic-bezier(0.16,1,0.3,1);
  box-shadow: -8px 0 30px rgba(0,0,0,0.5);
}

.id-news-drawer.open { right: 0; }

.drawer-header {
  background: var(--id-toolbar);
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--id-border);
  flex-shrink: 0;
}

.drawer-filters {
  padding: 10px 14px;
  border-bottom: 1px solid var(--id-border);
  display: flex;
  flex-direction: column;
  gap: 7px;
  flex-shrink: 0;
}

.drawer-filter-select {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 5px 8px;
  border-radius: 4px;
  font-size: 0.74rem;
  width: 100%;
}

.drawer-search {
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 5px 8px;
  border-radius: 4px;
  font-size: 0.74rem;
  width: 100%;
}
.drawer-search:focus { outline: none; border-color: var(--id-accent2); }

.drawer-news-list {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
}

.drawer-news-card {
  background: #252729;
  border: 1px solid var(--id-border);
  border-radius: 6px;
  padding: 10px;
  margin-bottom: 8px;
  transition: border-color 0.15s;
}

.drawer-news-card:hover { border-color: var(--id-accent); }

.dnc-header {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  margin-bottom: 8px;
}

.dnc-thumb {
  width: 56px;
  height: 42px;
  object-fit: cover;
  border-radius: 3px;
  flex-shrink: 0;
}

.dnc-no-thumb {
  width: 56px;
  height: 42px;
  background: #374151;
  border-radius: 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
  flex-shrink: 0;
}

.dnc-info { flex: 1; min-width: 0; }
.dnc-cat {
  display: inline-block;
  background: var(--id-accent);
  color: #fff;
  font-size: 0.55rem;
  font-weight: 800;
  padding: 1px 5px;
  border-radius: 2px;
  text-transform: uppercase;
  margin-bottom: 3px;
}
.dnc-title {
  font-size: 0.74rem;
  font-weight: 600;
  color: #e2e2e2;
  line-height: 1.25;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.dnc-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 6px;
  padding-top: 6px;
  border-top: 1px solid rgba(255,255,255,0.06);
}

.dnc-btn {
  padding: 3px 8px;
  font-size: 0.62rem;
  font-weight: 700;
  border-radius: 3px;
  cursor: pointer;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.06);
  color: #ccc;
  transition: all 0.12s;
}
.dnc-btn:hover { background: var(--id-accent2); border-color: var(--id-accent2); color: #fff; }
.dnc-btn.primary { background: rgba(200,71,43,0.15); border-color: var(--id-accent); color: #f87;  }
.dnc-btn.primary:hover { background: var(--id-accent); color: #fff; }

/* ══════════════════════════════════════════ MODALS */
.id-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.75);
  z-index: 9500;
  display: none;
  align-items: center;
  justify-content: center;
}
.id-modal-overlay.open { display: flex; }

.id-modal {
  background: var(--id-panel);
  border: 1px solid var(--id-border);
  border-radius: 10px;
  width: 520px;
  max-width: 95vw;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0,0,0,0.6);
}

.id-modal-header {
  padding: 14px 18px;
  border-bottom: 1px solid var(--id-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.id-modal-title {
  font-size: 0.85rem;
  font-weight: 800;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 8px;
}

.id-modal-close {
  background: transparent;
  border: none;
  color: #888;
  cursor: pointer;
  font-size: 1rem;
  padding: 2px;
  transition: color 0.15s;
}
.id-modal-close:hover { color: #fff; }

.id-modal-body { padding: 18px; }
.id-modal-footer { padding: 12px 18px; border-top: 1px solid var(--id-border); display: flex; justify-content: flex-end; gap: 8px; }

/* ══════════════════════════════════════════ TEMPLATE GRID */
.template-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.template-card {
  border: 2px solid var(--id-border);
  border-radius: 6px;
  padding: 14px 12px;
  cursor: pointer;
  text-align: center;
  transition: all 0.15s;
  background: rgba(255,255,255,0.02);
}

.template-card:hover { border-color: var(--id-accent2); background: rgba(77,124,204,0.08); }
.template-card.selected { border-color: var(--id-accent2); background: rgba(77,124,204,0.15); }

.template-card i { font-size: 1.8rem; margin-bottom: 6px; }
.template-card h6 { font-size: 0.78rem; font-weight: 800; color: #e2e2e2; margin-bottom: 4px; }
.template-card p { font-size: 0.65rem; color: var(--id-text-muted); line-height: 1.35; margin: 0; }

/* MISC */
.id-form-row { margin-bottom: 12px; }
.id-form-label { font-size: 0.7rem; color: var(--id-text-muted); display: block; margin-bottom: 4px; font-weight: 600; }
.id-form-input {
  width: 100%;
  background: #0E0F11;
  border: 1px solid var(--id-border);
  color: #fff;
  padding: 6px 8px;
  border-radius: 4px;
  font-size: 0.78rem;
}
.id-form-input:focus { outline: none; border-color: var(--id-accent2); }

/* Zoom indicator in toolbar */
.zoom-display {
  font-size: 0.7rem;
  color: var(--id-text-muted);
  min-width: 36px;
  text-align: center;
}

/* Crosshair cursor for drawing tools */
.id-canvas-area.draw-mode { cursor: crosshair; }

/* Progress toast */
.id-toast {
  position: fixed;
  bottom: 140px;
  left: 50%;
  transform: translateX(-50%) translateY(20px);
  background: #1a1c20;
  color: #fff;
  padding: 8px 18px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 700;
  border: 1px solid var(--id-border);
  box-shadow: 0 4px 20px rgba(0,0,0,0.4);
  z-index: 9999;
  opacity: 0;
  transition: all 0.3s;
  pointer-events: none;
  display: flex;
  align-items: center;
  gap: 8px;
}
.id-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

/* Newspaper typography within frames */
.id-paper-sheet [contenteditable]:focus {
  outline: 2px solid rgba(77,124,204,0.6);
  outline-offset: 2px;
  border-radius: 2px;
}
</style>

{{-- ═══════════════════════════════════════════════════════
     APP SHELL
═══════════════════════════════════════════════════════ --}}
<div class="id-app">

  {{-- ── TOP MENU BAR ── --}}
  <div class="id-menubar">
    <div class="id-menubar-title">
      <i class="fas fa-newspaper" style="color:var(--id-accent);"></i>
      Editor InDesign
    </div>

    <select class="id-edition-select" id="edicionSelector" onchange="changeEdicion(this.value)">
      @foreach($ediciones as $ed)
        <option value="{{ $ed['id'] }}" {{ $ed['id'] === $currentEdicion['id'] ? 'selected' : '' }}>
          {{ $ed['numero_edicion'] }} — {{ $ed['fecha'] }} {{ !empty($ed['publicada']) ? '⭐' : '' }}
        </option>
      @endforeach
    </select>

    <button class="id-btn id-btn-ghost" onclick="openModal('newEdicionModal')">
      <i class="fas fa-plus"></i> Nueva
    </button>

    <div class="id-menubar-sep"></div>

    <button class="id-btn id-btn-ghost" onclick="toggleNewsDrawer()" id="drawerToggleBtn">
      <i class="fas fa-newspaper"></i> Noticias
    </button>
    <button class="id-btn id-btn-ghost" onclick="openModal('templateModal')">
      <i class="fas fa-th-large"></i> Plantilla
    </button>
    <a href="{{ route('admin.periodico.pdf', $currentEdicion['id']) }}" target="_blank" class="id-btn id-btn-ghost">
      <i class="fas fa-print"></i> PDF
    </a>
    <a href="{{ route('periodico.public.show', $currentEdicion['id']) }}" target="_blank" class="id-btn id-btn-ghost">
      <i class="fas fa-eye"></i> Ver
    </a>

    <div class="id-menubar-sep"></div>

    <button class="id-btn id-btn-primary" onclick="saveEdicion()" id="btnSave">
      <i class="fas fa-save"></i> Guardar
    </button>
    <button class="id-btn id-btn-success" onclick="publishEdicion()">
      <i class="fas fa-paper-plane"></i> Publicar
    </button>

    <div class="id-save-indicator" id="saveIndicator">
      <i class="fas fa-check-circle"></i> <span>Sincronizado</span>
    </div>
  </div>

  {{-- ── CONTROL BAR (text/object properties) ── --}}
  <div class="id-controlbar" id="controlBar">
    {{-- Typography group --}}
    <div class="id-control-group" id="cbTypography">
      <span class="id-ctrl-label">Fuente</span>
      <select class="id-font-select" id="cbFontFamily" onchange="applyStyleToSelection()">
        <option value="'Source Sans 3',sans-serif" selected>Source Sans 3</option>
        <option value="'Playfair Display',serif">Playfair Display</option>
        <option value="'Anton',Impact,sans-serif">Anton</option>
        <option value="'Bebas Neue',Impact,sans-serif">Bebas Neue</option>
        <option value="'Montserrat',sans-serif">Montserrat</option>
        <option value="'Merriweather',serif">Merriweather</option>
        <option value="'Oswald',sans-serif">Oswald</option>
        <option value="Georgia,serif">Georgia</option>
        <option value="Arial,sans-serif">Arial</option>
      </select>
      <input type="number" class="id-font-size" id="cbFontSize" value="14" min="6" max="200" onchange="applyStyleToSelection()" title="Tamaño">
      <span class="id-ctrl-label">px</span>
    </div>

    {{-- Style group --}}
    <div class="id-control-group" id="cbStyle">
      <button class="id-ctrl-btn" id="cbBold"   onclick="toggleBold()"      title="Negrita (Ctrl+B)"><b>B</b></button>
      <button class="id-ctrl-btn" id="cbItalic" onclick="toggleItalic()"    title="Cursiva (Ctrl+I)"><i>I</i></button>
      <button class="id-ctrl-btn" id="cbUnder"  onclick="toggleUnderline()" title="Subrayado"><u>U</u></button>
      <button class="id-ctrl-btn" id="cbUpper"  onclick="toggleUppercase()" title="MAYÚSCULAS">aA</button>
    </div>

    {{-- Alignment --}}
    <div class="id-control-group" id="cbAlign">
      <button class="id-ctrl-btn" onclick="applyAlign('left')"    title="Izquierda"><i class="fas fa-align-left"></i></button>
      <button class="id-ctrl-btn" onclick="applyAlign('center')"  title="Centro"><i class="fas fa-align-center"></i></button>
      <button class="id-ctrl-btn" onclick="applyAlign('right')"   title="Derecha"><i class="fas fa-align-right"></i></button>
      <button class="id-ctrl-btn" onclick="applyAlign('justify')" title="Justificar"><i class="fas fa-align-justify"></i></button>
    </div>

    {{-- Color --}}
    <div class="id-control-group" id="cbColor">
      <span class="id-ctrl-label">Color</span>
      <input type="color" class="id-ctrl-input" id="cbTextColor" value="#111111" onchange="applyTextColor(this.value)" style="width:28px;padding:1px 2px;cursor:pointer;" title="Color de texto">
    </div>

    {{-- Columns --}}
    <div class="id-control-group" id="cbColumns">
      <span class="id-ctrl-label">Cols</span>
      <button class="id-ctrl-btn" onclick="setColumns(1)" title="1 columna">1</button>
      <button class="id-ctrl-btn" onclick="setColumns(2)" title="2 columnas">2</button>
      <button class="id-ctrl-btn" onclick="setColumns(3)" title="3 columnas">3</button>
      <button class="id-ctrl-btn" onclick="setColumns(4)" title="4 columnas">4</button>
    </div>

    {{-- Line height --}}
    <div class="id-control-group" id="cbLineH">
      <span class="id-ctrl-label">Línea</span>
      <input type="number" class="id-ctrl-input" id="cbLineHeight" value="1.5" min="0.8" max="4" step="0.1" onchange="applyStyleToSelection()" style="width:48px;" title="Interlineado">
    </div>

    {{-- Zoom --}}
    <div class="id-control-group" style="margin-left:auto;">
      <button class="id-ctrl-btn" onclick="changeZoom(-0.1)" title="Alejar"><i class="fas fa-search-minus"></i></button>
      <span class="zoom-display" id="zoomDisplay">100%</span>
      <button class="id-ctrl-btn" onclick="changeZoom(0.1)" title="Acercar"><i class="fas fa-search-plus"></i></button>
      <button class="id-ctrl-btn" onclick="resetZoom()" title="Ajustar"><i class="fas fa-compress-arrows-alt"></i></button>
    </div>

    {{-- View toggles --}}
    <div class="id-control-group">
      <button class="id-ctrl-btn" id="gridToggleBtn" onclick="toggleGrid()" title="Mostrar/ocultar cuadrícula">
        <i class="fas fa-border-all"></i>
      </button>
      <button class="id-ctrl-btn" id="marginToggleBtn" onclick="toggleMargins()" title="Mostrar márgenes">
        <i class="fas fa-border-outer"></i>
      </button>
    </div>
  </div>

  {{-- ── LEFT TOOLBOX ── --}}
  <div class="id-toolbox">
    <button class="id-tool-btn active" id="tool-select" onclick="setTool('select')" title="Selección (V)">
      <i class="fas fa-mouse-pointer"></i>
      <span class="tool-key">V</span>
    </button>
    <div class="id-tool-sep"></div>
    <button class="id-tool-btn" id="tool-text" onclick="setTool('text')" title="Marco de Texto (T)">
      <i class="fas fa-font"></i>
      <span class="tool-key">T</span>
    </button>
    <button class="id-tool-btn" id="tool-image" onclick="setTool('image')" title="Marco de Imagen (F)">
      <i class="fas fa-image"></i>
      <span class="tool-key">F</span>
    </button>
    <button class="id-tool-btn" id="tool-rect" onclick="setTool('rect')" title="Rectángulo (R)">
      <i class="fas fa-square"></i>
      <span class="tool-key">R</span>
    </button>
    <button class="id-tool-btn" id="tool-line" onclick="setTool('line')" title="Línea (L)">
      <i class="fas fa-minus"></i>
      <span class="tool-key">L</span>
    </button>
    <div class="id-tool-sep"></div>
    <button class="id-tool-btn" onclick="changeZoom(0.15)" title="Acercar (+)">
      <i class="fas fa-search-plus"></i>
      <span class="tool-key">+</span>
    </button>
    <button class="id-tool-btn" onclick="changeZoom(-0.15)" title="Alejar (-)">
      <i class="fas fa-search-minus"></i>
      <span class="tool-key">−</span>
    </button>
    <div class="id-tool-sep"></div>
    <button class="id-tool-btn" onclick="deleteSelected()" title="Eliminar seleccionado (Del)" style="color:#ef4444;">
      <i class="fas fa-trash"></i>
    </button>
    <button class="id-tool-btn" onclick="bringToFront()" title="Traer al frente">
      <i class="fas fa-arrow-up"></i>
    </button>
    <button class="id-tool-btn" onclick="sendToBack()" title="Enviar atrás">
      <i class="fas fa-arrow-down"></i>
    </button>
    <div class="id-tool-sep"></div>
    <button class="id-tool-btn" onclick="duplicateSelected()" title="Duplicar (Ctrl+D)">
      <i class="fas fa-copy"></i>
    </button>
  </div>

  {{-- ── CANVAS AREA ── --}}
  <div class="id-canvas-area" id="canvasArea" onclick="handleCanvasClick(event)">
    <div class="id-paper-wrapper" id="paperWrapper">
      <div id="sheetsContainer"></div>
    </div>
  </div>

  {{-- ── RIGHT INSPECTOR ── --}}
  <div class="id-inspector" id="inspector">
    <div class="inspector-empty" id="inspectorEmpty">
      <i class="fas fa-mouse-pointer"></i>
      <p>Selecciona un elemento para editar sus propiedades</p>
    </div>

    {{-- Object geometry section --}}
    <div class="inspector-section" id="inspGeometry" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-ruler-combined" style="margin-right:5px;"></i> Posición y Tamaño</span>
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
          <span class="inspector-label">W</span>
          <input type="number" class="inspector-input" id="inspW" onchange="applyGeometry()" title="Ancho">
          <span class="inspector-label">H</span>
          <input type="number" class="inspector-input" id="inspH" onchange="applyGeometry()" title="Alto">
        </div>
        <div class="inspector-row">
          <span class="inspector-label">Z</span>
          <input type="number" class="inspector-input" id="inspZ" min="1" max="999" onchange="applyZIndex()" title="Orden Z">
          <span class="inspector-label" style="opacity:0.5;">idx</span>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Opacidad</span>
          <input type="range" class="inspector-input" id="inspOpacity" min="0" max="100" value="100" oninput="applyOpacity()" style="padding:0;height:20px;">
          <span class="inspector-label" id="inspOpacityVal">100%</span>
        </div>
      </div>
    </div>

    {{-- Text properties --}}
    <div class="inspector-section" id="inspText" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-font" style="margin-right:5px;"></i> Texto</span>
        <i class="fas fa-chevron-down" style="font-size:0.6rem;"></i>
      </div>
      <div class="inspector-body">
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Fuente</span>
          <select class="inspector-select" id="inspFontFamily" onchange="applyTextProps()">
            <option value="'Source Sans 3',sans-serif">Source Sans 3</option>
            <option value="'Playfair Display',serif">Playfair Display</option>
            <option value="'Anton',sans-serif">Anton</option>
            <option value="'Bebas Neue',sans-serif">Bebas Neue</option>
            <option value="'Montserrat',sans-serif">Montserrat</option>
            <option value="'Merriweather',serif">Merriweather</option>
            <option value="'Oswald',sans-serif">Oswald</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Tamaño</span>
          <input type="number" class="inspector-input" id="inspFontSize" value="14" min="6" max="300" onchange="applyTextProps()">
          <span class="inspector-label">px</span>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Peso</span>
          <select class="inspector-select" id="inspFontWeight" onchange="applyTextProps()">
            <option value="400">Normal (400)</option>
            <option value="600">Semibold (600)</option>
            <option value="700">Bold (700)</option>
            <option value="800">Extrabold (800)</option>
            <option value="900">Black (900)</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Línea</span>
          <input type="number" class="inspector-input" id="inspLineH" value="1.5" min="0.8" max="4" step="0.05" onchange="applyTextProps()">
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Columnas</span>
          <input type="number" class="inspector-input" id="inspTextCols" value="1" min="1" max="6" onchange="applyTextProps()">
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Color</span>
          <input type="color" class="inspector-input" id="inspTextColor" value="#111111" onchange="applyTextProps()" style="width:40px;padding:1px 2px;cursor:pointer;">
          <select class="inspector-select" id="inspTextAlign" onchange="applyTextProps()">
            <option value="left">← Izquierda</option>
            <option value="center">↔ Centro</option>
            <option value="right">→ Derecha</option>
            <option value="justify">≡ Justificar</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">BG</span>
          <input type="color" class="inspector-input" id="inspTextBg" value="#ffffff" onchange="applyTextProps()" style="width:40px;padding:1px 2px;cursor:pointer;">
          <button class="id-ctrl-btn" onclick="clearTextBg()" title="Sin fondo" style="font-size:0.65rem;">Ninguno</button>
        </div>
      </div>
    </div>

    {{-- Image properties --}}
    <div class="inspector-section" id="inspImage" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-image" style="margin-right:5px;"></i> Imagen</span>
      </div>
      <div class="inspector-body">
        <button class="insp-full-btn" onclick="openImagePicker()">
          <i class="fas fa-cloud-upload-alt"></i> Cambiar imagen...
        </button>
        <div style="margin-top:8px;">
          <label class="id-form-label">URL de imagen</label>
          <input type="url" class="inspector-input" id="inspImgUrl" placeholder="https://..." onchange="applyImageUrl()" style="width:100%;">
        </div>
        <div class="inspector-row" style="margin-top:8px;">
          <span class="inspector-label" style="min-width:60px;">Ajuste</span>
          <select class="inspector-select" id="inspImgFit" onchange="applyImageFit()">
            <option value="cover">Cover (recortar)</option>
            <option value="contain">Contain (completo)</option>
            <option value="fill">Fill (estirar)</option>
          </select>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Borde</span>
          <input type="color" class="inspector-input" id="inspImgBorder" value="#000000" onchange="applyImageBorderColor()" style="width:30px;padding:1px 2px;">
          <input type="number" class="inspector-input" id="inspImgBorderW" value="0" min="0" max="20" onchange="applyImageBorder()" style="width:50px;" title="Grosor borde">
          <span class="inspector-label">px</span>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Radio</span>
          <input type="number" class="inspector-input" id="inspImgRadius" value="0" min="0" max="100" onchange="applyImageRadius()">
          <span class="inspector-label">px</span>
        </div>
      </div>
    </div>

    {{-- Shape / rect properties --}}
    <div class="inspector-section" id="inspShape" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-shapes" style="margin-right:5px;"></i> Forma</span>
      </div>
      <div class="inspector-body">
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Relleno</span>
          <input type="color" class="inspector-input" id="inspShapeFill" value="#000000" onchange="applyShapeProps()" style="width:40px;padding:1px 2px;cursor:pointer;">
          <button class="id-ctrl-btn" onclick="clearShapeFill()" style="font-size:0.65rem;">Ninguno</button>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Borde</span>
          <input type="color" class="inspector-input" id="inspShapeBorder" value="#000000" onchange="applyShapeProps()" style="width:30px;padding:1px 2px;">
          <input type="number" class="inspector-input" id="inspShapeBorderW" value="0" min="0" max="20" onchange="applyShapeProps()" style="width:50px;" title="Grosor">
          <span class="inspector-label">px</span>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Radio</span>
          <input type="number" class="inspector-input" id="inspShapeRadius" value="0" min="0" max="200" onchange="applyShapeProps()">
          <span class="inspector-label">px</span>
        </div>
      </div>
    </div>

    {{-- Line properties --}}
    <div class="inspector-section" id="inspLine" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-minus" style="margin-right:5px;"></i> Línea</span>
      </div>
      <div class="inspector-body">
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Color</span>
          <input type="color" class="inspector-input" id="inspLineColor" value="#000000" onchange="applyLineProps()" style="width:40px;padding:1px 2px;">
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Grosor</span>
          <input type="number" class="inspector-input" id="inspLineW" value="2" min="1" max="20" onchange="applyLineProps()">
          <span class="inspector-label">px</span>
        </div>
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:60px;">Estilo</span>
          <select class="inspector-select" id="inspLineStyle" onchange="applyLineProps()">
            <option value="solid">Sólida</option>
            <option value="dashed">Punteada</option>
            <option value="dotted">Puntos</option>
          </select>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="inspector-section" id="inspActions" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-cog" style="margin-right:5px;"></i> Acciones</span>
      </div>
      <div class="inspector-body" style="display:flex;flex-direction:column;gap:6px;">
        <button class="insp-full-btn" onclick="duplicateSelected()">
          <i class="fas fa-copy"></i> Duplicar elemento
        </button>
        <button class="insp-full-btn" onclick="bringToFront()">
          <i class="fas fa-arrow-up"></i> Traer al frente
        </button>
        <button class="insp-full-btn" onclick="sendToBack()">
          <i class="fas fa-arrow-down"></i> Enviar atrás
        </button>
        <button class="insp-full-btn danger" onclick="deleteSelected()">
          <i class="fas fa-trash"></i> Eliminar elemento
        </button>
      </div>
    </div>
  </div>

  {{-- ── BOTTOM PAGES PANEL ── --}}
  <div class="id-pages-panel" id="pagesPanel">
    <span class="pages-panel-label"><i class="fas fa-th-large" style="margin-right:4px;"></i> Páginas</span>
    <div style="width:1px;height:80px;background:var(--id-border);margin:0 6px;"></div>
    <div id="pageThumbs" style="display:flex;gap:10px;align-items:center;"></div>
    <div class="page-add-btn" onclick="addNewPage()" title="Agregar página">
      <div class="page-add-preview"><i class="fas fa-plus"></i></div>
      <span class="page-thumb-label">Agregar</span>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     NEWS DRAWER
═══════════════════════════════════════════════════════ --}}
<div class="id-news-drawer" id="newsDrawer">
  <div class="drawer-header">
    <div style="display:flex;align-items:center;gap:8px;">
      <i class="fas fa-newspaper" style="color:var(--id-accent);font-size:1.1rem;"></i>
      <div>
        <div style="font-weight:800;font-size:0.82rem;">Insertar Noticias</div>
        <div style="font-size:0.65rem;color:var(--id-text-muted);">Haz clic en una acción para insertar en la página</div>
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
    <input class="drawer-search" type="text" id="drawerSearch" placeholder="Buscar noticia..." oninput="filterDrawerNews()">
  </div>
  <div class="drawer-news-list" id="drawerNewsList">
    @foreach($noticiasPublicadas as $noticia)
      <div class="drawer-news-card" data-category="{{ $noticia->category_id }}" data-title="{{ strtolower($noticia->titulo) }}">
        <div class="dnc-header">
          @if($noticia->imagen)
            <img class="dnc-thumb" src="{{ $noticia->imagenUrl }}" alt="">
          @else
            <div class="dnc-no-thumb"><i class="fas fa-image"></i></div>
          @endif
          <div class="dnc-info">
            <span class="dnc-cat">{{ $noticia->category->name ?? 'General' }}</span>
            <div class="dnc-title">{{ $noticia->titulo }}</div>
          </div>
        </div>
        <div class="dnc-actions">
          <span style="font-size:0.58rem;color:var(--id-text-muted);margin-right:2px;">Insertar:</span>
          <button class="dnc-btn primary" onclick='insertNewsBlock({{ json_encode(["id"=>$noticia->id,"titulo"=>$noticia->titulo,"bajada"=>\Illuminate\Support\Str::limit(strip_tags($noticia->contenido),200),"contenido"=>strip_tags($noticia->contenido),"categoria"=>$noticia->category->name??"General","imagen"=>$noticia->imagenUrl,"autor"=>$noticia->autor??"REDACCIÓN"]) }}, "article")'>
            <i class="fas fa-newspaper"></i> Artículo
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock({{ json_encode(["id"=>$noticia->id,"titulo"=>$noticia->titulo,"bajada"=>\Illuminate\Support\Str::limit(strip_tags($noticia->contenido),200),"contenido"=>strip_tags($noticia->contenido),"categoria"=>$noticia->category->name??"General","imagen"=>$noticia->imagenUrl,"autor"=>$noticia->autor??"REDACCIÓN"]) }}, "headline")'>
            <i class="fas fa-heading"></i> Titular
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock({{ json_encode(["id"=>$noticia->id,"titulo"=>$noticia->titulo,"bajada"=>\Illuminate\Support\Str::limit(strip_tags($noticia->contenido),200),"contenido"=>strip_tags($noticia->contenido),"categoria"=>$noticia->category->name??"General","imagen"=>$noticia->imagenUrl,"autor"=>$noticia->autor??"REDACCIÓN"]) }}, "photo_caption")'>
            <i class="fas fa-camera"></i> Foto
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock({{ json_encode(["id"=>$noticia->id,"titulo"=>$noticia->titulo,"bajada"=>\Illuminate\Support\Str::limit(strip_tags($noticia->contenido),200),"contenido"=>strip_tags($noticia->contenido),"categoria"=>$noticia->category->name??"General","imagen"=>$noticia->imagenUrl,"autor"=>$noticia->autor??"REDACCIÓN"]) }}, "text_only")'>
            <i class="fas fa-align-left"></i> Solo texto
          </button>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════ --}}

{{-- Template Modal --}}
<div class="id-modal-overlay" id="templateModal">
  <div class="id-modal">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-th-large" style="color:var(--id-accent);"></i> Aplicar Plantilla a la Página</div>
      <button class="id-modal-close" onclick="closeModal('templateModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <p style="font-size:0.72rem;color:#888;margin-bottom:14px;">Elige una plantilla prediseñada para la página actual. Esto añadirá un conjunto de marcos de texto e imagen listos para editar.</p>
      <div class="template-grid">
        <div class="template-card" onclick="applyTemplate('portada')">
          <i class="fas fa-newspaper text-danger"></i>
          <h6>Portada Clásica</h6>
          <p>Cabecera, foto principal grande, titulares laterales y cintillo de noticias</p>
        </div>
        <div class="template-card" onclick="applyTemplate('two_col')">
          <i class="fas fa-columns" style="color:#4D7CCC;"></i>
          <h6>Dos Columnas</h6>
          <p>Dos artículos grandes en paralelo con imagen y cuerpo de texto</p>
        </div>
        <div class="template-card" onclick="applyTemplate('three_col')">
          <i class="fas fa-th-large" style="color:#2d7a39;"></i>
          <h6>Tres Columnas</h6>
          <p>Tres bloques de artículos con imagen pequeña y texto multi-columna</p>
        </div>
        <div class="template-card" onclick="applyTemplate('editorial')">
          <i class="fas fa-feather-alt" style="color:#b07d2e;"></i>
          <h6>Editorial / Opinión</h6>
          <p>Artículo de opinión con drop cap, cita destacada y cuadro de servicios</p>
        </div>
        <div class="template-card" onclick="applyTemplate('photo_spread')">
          <i class="fas fa-images" style="color:#8b3fd8;"></i>
          <h6>Foto Reportaje</h6>
          <p>Una imagen grande dominante con texto corto al pie</p>
        </div>
        <div class="template-card" onclick="applyTemplate('blank')">
          <i class="fas fa-square" style="color:#555;"></i>
          <h6>Página en Blanco</h6>
          <p>Empezar desde cero con un lienzo vacío</p>
        </div>
      </div>
    </div>
    <div class="id-modal-footer">
      <button class="id-btn id-btn-ghost" onclick="closeModal('templateModal')">Cancelar</button>
    </div>
  </div>
</div>

{{-- New Edition Modal --}}
<div class="id-modal-overlay" id="newEdicionModal">
  <div class="id-modal">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-plus-circle" style="color:var(--id-accent);"></i> Nueva Edición Semanal</div>
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
          <label class="id-form-label">Clonar desde:</label>
          <select name="clonar_de" class="id-form-input">
            <option value="">Plantilla estándar de 4 páginas</option>
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

{{-- Image Picker Modal --}}
<div class="id-modal-overlay" id="imagePickerModal">
  <div class="id-modal">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-image" style="color:var(--id-accent2);"></i> Insertar Imagen</div>
      <button class="id-modal-close" onclick="closeModal('imagePickerModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <div style="border:2px dashed var(--id-border);border-radius:6px;padding:28px;text-align:center;cursor:pointer;background:rgba(255,255,255,0.02);" onclick="document.getElementById('imageFileInput').click()">
        <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--id-accent);margin-bottom:8px;"></i>
        <p style="font-size:0.78rem;font-weight:700;margin-bottom:4px;">Haz clic para seleccionar una imagen</p>
        <p style="font-size:0.65rem;color:#888;">JPG, PNG, WEBP hasta 10MB</p>
        <input type="file" id="imageFileInput" style="display:none;" accept="image/*" onchange="uploadImageForFrame(this)">
      </div>
      <div style="margin-top:16px;">
        <label class="id-form-label">O pega la URL de la imagen</label>
        <div style="display:flex;gap:8px;">
          <input type="url" class="id-form-input" id="imageUrlInput" placeholder="https://ejemplo.com/foto.jpg" style="flex:1;">
          <button class="id-btn id-btn-primary" onclick="applyImageUrlToFrame()"><i class="fas fa-check"></i></button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Toast notification --}}
<div class="id-toast" id="idToast">
  <i class="fas fa-check-circle" id="idToastIcon"></i>
  <span id="idToastMsg">Guardado</span>
</div>

<script>
// ═══════════════════════════════════════════════════════
//   INDESIGN-STYLE NEWSPAPER EDITOR — CORE ENGINE
// ═══════════════════════════════════════════════════════

// ── STATE ──────────────────────────────────────────
let currentEdicion  = @json($currentEdicion);
let activePageIndex = 0;
let selectedFrame   = null;
let currentTool     = 'select';
let zoomLevel       = 1.0;
let isDrawing       = false;
let drawStart       = null;
let drawEl          = null;
let hasUnsavedChanges = false;
let frameIdCounter  = 100;
let gridVisible     = false;
let marginsVisible  = true;

// Ensure each page has a frames array
(currentEdicion.paginas || []).forEach((p, i) => {
    if (!p.frames) p.frames = [];
    if (!p.id) p.id = 'pg-' + i;
});

// ── INIT ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderAllPages();
    switchPage(0);
    initInteract();
    setupKeyboard();
    updateZoomDisplay();
});

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
    sheet.style.width   = 'var(--paper-w)';
    sheet.style.minHeight = 'var(--paper-h)';

    // Grid overlay
    const gridOverlay = document.createElement('div');
    gridOverlay.className = 'paper-grid-overlay';
    sheet.appendChild(gridOverlay);

    // Margin guide
    const marginGuide = document.createElement('div');
    marginGuide.className = 'paper-margin-guide';
    sheet.appendChild(marginGuide);

    // Render frames
    (page.frames || []).forEach(frameData => {
        const el = createFrameElement(frameData, pIdx);
        if (el) sheet.appendChild(el);
    });

    return sheet;
}

function createFrameElement(data, pIdx) {
    const el = document.createElement('div');
    el.className = 'id-frame';
    el.id = `frame-${data.id}`;
    el.dataset.frameId = data.id;
    el.dataset.type    = data.type;
    el.dataset.page    = pIdx;

    // Geometry
    el.style.left    = (data.x  || 30) + 'px';
    el.style.top     = (data.y  || 30) + 'px';
    el.style.width   = (data.w  || 200) + 'px';
    el.style.height  = (data.h  || 100) + 'px';
    el.style.zIndex  = data.z  || 10;
    el.style.opacity = data.opacity !== undefined ? data.opacity : 1;

    // Frame label
    const label = document.createElement('div');
    label.className = 'frame-label';
    label.textContent = data.type;
    el.appendChild(label);

    // Resize handles
    ['nw','n','ne','e','se','s','sw','w'].forEach(pos => {
        const h = document.createElement('div');
        h.className = `resize-handle ${pos}`;
        el.appendChild(h);
    });

    // Inner content
    const inner = buildFrameInner(data);
    if (inner) el.appendChild(inner);

    // Click to select
    el.addEventListener('mousedown', (e) => {
        if (currentTool === 'select') {
            e.stopPropagation();
            selectFrame(el);
        }
    });

    // Double-click to edit text
    if (data.type === 'text') {
        el.addEventListener('dblclick', () => {
            const textDiv = el.querySelector('.frame-text-inner');
            if (textDiv) {
                textDiv.contentEditable = 'true';
                textDiv.focus();
                markUnsaved();
            }
        });
    }

    // Image frame click to replace
    if (data.type === 'image') {
        el.addEventListener('dblclick', () => openImagePickerForFrame(el));
    }

    return el;
}

function buildFrameInner(data) {
    switch (data.type) {
        case 'text': {
            const d = document.createElement('div');
            d.className = 'frame-text-inner';
            d.contentEditable = 'false';
            d.innerHTML = data.content || '<p>Haz doble clic para editar el texto...</p>';
            // Apply styles
            if (data.styles) {
                Object.assign(d.style, {
                    fontFamily:    data.styles.fontFamily    || "'Source Sans 3',sans-serif",
                    fontSize:      (data.styles.fontSize     || 14) + 'px',
                    fontWeight:    data.styles.fontWeight    || '400',
                    lineHeight:    data.styles.lineHeight     || '1.5',
                    color:         data.styles.color          || '#111111',
                    textAlign:     data.styles.textAlign      || 'left',
                    backgroundColor: data.styles.backgroundColor || 'transparent',
                    columnCount:   data.styles.columns        || 1,
                });
            }
            d.addEventListener('blur', () => {
                d.contentEditable = 'false';
                saveFrameContent(data.id, d.innerHTML);
            });
            return d;
        }
        case 'image': {
            if (data.src) {
                const img = document.createElement('img');
                img.className = 'frame-img-inner';
                img.src = data.src;
                img.style.objectFit = data.imgFit || 'cover';
                if (data.borderWidth) {
                    img.style.border = `${data.borderWidth}px solid ${data.borderColor || '#000'}`;
                }
                img.style.borderRadius = (data.borderRadius || 0) + 'px';
                return img;
            } else {
                const ph = document.createElement('div');
                ph.className = 'frame-img-placeholder';
                ph.innerHTML = '<i class="fas fa-image"></i><span>Doble clic para añadir imagen</span>';
                return ph;
            }
        }
        case 'line': {
            const d = document.createElement('div');
            d.className = 'frame-line-inner';
            d.style.background   = data.lineColor || '#000000';
            d.style.height       = (data.lineWidth || 2) + 'px';
            d.style.borderStyle  = data.lineStyle || 'solid';
            return d;
        }
        case 'rect':
        case 'shape': {
            const d = document.createElement('div');
            d.className = 'frame-shape-inner';
            d.style.background    = data.fillColor || 'transparent';
            d.style.border        = `${data.borderWidth||0}px ${data.borderStyle||'solid'} ${data.borderColor||'#000'}`;
            d.style.borderRadius  = (data.borderRadius || 0) + 'px';
            return d;
        }
    }
    return null;
}

// ── INTERACT.JS — DRAG & RESIZE ─────────────────────
function initInteract() {
    interact('.id-frame').draggable({
        listeners: {
            start(e) {
                if (currentTool !== 'select') return;
                selectFrame(e.target);
            },
            move(e) {
                if (currentTool !== 'select') return;
                const el = e.target;
                const x = (parseFloat(el.style.left) || 0) + e.dx;
                const y = (parseFloat(el.style.top)  || 0) + e.dy;
                el.style.left = x + 'px';
                el.style.top  = y + 'px';
                updateInspectorGeometry(el);
                markUnsaved();
            }
        },
        modifiers: [
            interact.modifiers.restrictRect({ restriction: 'parent', endOnly: false })
        ]
    }).resizable({
        edges: { left: true, right: true, bottom: true, top: true,
                 topLeft: true, topRight: true, bottomLeft: true, bottomRight: true },
        listeners: {
            move(e) {
                const el = e.target;
                el.style.width  = e.rect.width  + 'px';
                el.style.height = e.rect.height + 'px';
                el.style.left   = e.rect.left - e.target.closest('.id-paper-sheet').getBoundingClientRect().left + 'px';
                el.style.top    = e.rect.top  - e.target.closest('.id-paper-sheet').getBoundingClientRect().top  + 'px';
                updateInspectorGeometry(el);
                markUnsaved();
            }
        },
        modifiers: [
            interact.modifiers.restrictSize({ min: { width: 20, height: 10 } })
        ]
    });
}

// ── TOOL SYSTEM ────────────────────────────────────
function setTool(tool) {
    currentTool = tool;
    document.querySelectorAll('.id-tool-btn[id^="tool-"]').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('tool-' + tool);
    if (btn) btn.classList.add('active');

    const canvasArea = document.getElementById('canvasArea');
    canvasArea.classList.toggle('draw-mode', tool !== 'select');
}

// Draw on canvas click (for non-select tools)
function handleCanvasClick(e) {
    if (currentTool === 'select') return;
    if (e.target.classList.contains('id-frame') || e.target.closest('.id-frame')) return;

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    if (!sheet) return;

    const rect = sheet.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoomLevel;
    const y = (e.clientY - rect.top)  / zoomLevel;

    const typeMap = { text: 'text', image: 'image', rect: 'rect', line: 'line' };
    const type = typeMap[currentTool] || 'text';

    const defaults = {
        text:  { w: 220, h: 120, content: '<p style="font-size:14px;">Haz doble clic para editar...</p>' },
        image: { w: 240, h: 180 },
        rect:  { w: 160, h: 80,  fillColor: '#000000' },
        line:  { w: 200, h: 4,   lineColor: '#000000', lineWidth: 2 },
    };

    createFrame({ type, x: Math.round(x), y: Math.round(y), ...defaults[type] });
    setTool('select'); // Switch back to selection after drawing
    showToast('Marco creado. Haz doble clic para editar.', 'success');
}

// ── FRAME MANAGEMENT ───────────────────────────────
function createFrame(data) {
    const id = ++frameIdCounter;
    const frameData = {
        id,
        type:    data.type     || 'text',
        x:       data.x        || 50,
        y:       data.y        || 50,
        w:       data.w        || 200,
        h:       data.h        || 100,
        z:       data.z        || (currentEdicion.paginas[activePageIndex]?.frames?.length || 0) + 10,
        opacity: 1,
        content: data.content  || '',
        src:     data.src      || null,
        styles:  data.styles   || {},
        fillColor:   data.fillColor   || 'transparent',
        borderColor: data.borderColor || '#000000',
        borderWidth: data.borderWidth || 0,
        borderRadius:data.borderRadius|| 0,
        lineColor:   data.lineColor   || '#000000',
        lineWidth:   data.lineWidth   || 2,
        lineStyle:   data.lineStyle   || 'solid',
        imgFit:      data.imgFit      || 'cover',
        ...data
    };

    // Save to model
    if (!currentEdicion.paginas[activePageIndex].frames) {
        currentEdicion.paginas[activePageIndex].frames = [];
    }
    currentEdicion.paginas[activePageIndex].frames.push(frameData);

    // Render
    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const el = createFrameElement(frameData, activePageIndex);
    sheet.appendChild(el);
    initInteract(); // Reinit to pick up new element
    selectFrame(el);
    markUnsaved();
    return el;
}

function selectFrame(el) {
    // Deselect previous
    document.querySelectorAll('.id-frame.selected').forEach(f => f.classList.remove('selected'));

    if (!el) {
        selectedFrame = null;
        showInspectorEmpty();
        return;
    }

    el.classList.add('selected');
    selectedFrame = el;
    updateInspectorForFrame(el);
}

function saveFrameContent(frameId, content) {
    const page = currentEdicion.paginas[activePageIndex];
    if (!page || !page.frames) return;
    const f = page.frames.find(f => f.id == frameId);
    if (f) f.content = content;
    markUnsaved();
}

function deleteSelected() {
    if (!selectedFrame) return;
    const frameId = selectedFrame.dataset.frameId;
    const page = currentEdicion.paginas[activePageIndex];
    if (page && page.frames) {
        page.frames = page.frames.filter(f => f.id != frameId);
    }
    selectedFrame.remove();
    selectedFrame = null;
    showInspectorEmpty();
    markUnsaved();
    showToast('Elemento eliminado', 'info');
}

function duplicateSelected() {
    if (!selectedFrame) return;
    const frameId = selectedFrame.dataset.frameId;
    const page = currentEdicion.paginas[activePageIndex];
    const original = page?.frames?.find(f => f.id == frameId);
    if (!original) return;
    const copy = JSON.parse(JSON.stringify(original));
    copy.id = ++frameIdCounter;
    copy.x  = (original.x || 0) + 20;
    copy.y  = (original.y || 0) + 20;
    page.frames.push(copy);
    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const el = createFrameElement(copy, activePageIndex);
    sheet.appendChild(el);
    initInteract();
    selectFrame(el);
    markUnsaved();
}

function bringToFront() {
    if (!selectedFrame) return;
    const maxZ = Math.max(...Array.from(document.querySelectorAll('.id-frame')).map(f => parseInt(f.style.zIndex||0)));
    selectedFrame.style.zIndex = maxZ + 1;
    saveFrameZIndex(selectedFrame);
    markUnsaved();
}

function sendToBack() {
    if (!selectedFrame) return;
    selectedFrame.style.zIndex = 1;
    saveFrameZIndex(selectedFrame);
    markUnsaved();
}

function saveFrameZIndex(el) {
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(f => f.id == el.dataset.frameId);
    if (f) f.z = parseInt(el.style.zIndex);
    document.getElementById('inspZ').value = el.style.zIndex;
}

// ── INSPECTOR ──────────────────────────────────────
function showInspectorEmpty() {
    document.getElementById('inspectorEmpty').style.display = '';
    ['inspGeometry','inspText','inspImage','inspShape','inspLine','inspActions']
        .forEach(id => { const el = document.getElementById(id); if(el) el.style.display = 'none'; });
}

function updateInspectorForFrame(el) {
    document.getElementById('inspectorEmpty').style.display = 'none';

    const type = el.dataset.type;
    const frameId = el.dataset.frameId;
    const page = currentEdicion.paginas[activePageIndex];
    const data = page?.frames?.find(f => f.id == frameId) || {};

    // Always show geometry + actions
    showSection('inspGeometry');
    showSection('inspActions');

    document.getElementById('inspX').value = Math.round(parseFloat(el.style.left));
    document.getElementById('inspY').value = Math.round(parseFloat(el.style.top));
    document.getElementById('inspW').value = Math.round(parseFloat(el.style.width));
    document.getElementById('inspH').value = Math.round(parseFloat(el.style.height));
    document.getElementById('inspZ').value = parseInt(el.style.zIndex) || 10;
    const opacity = parseFloat(el.style.opacity);
    document.getElementById('inspOpacity').value = isNaN(opacity) ? 100 : Math.round(opacity * 100);
    document.getElementById('inspOpacityVal').textContent = (isNaN(opacity) ? 100 : Math.round(opacity * 100)) + '%';

    // Hide type-specific sections first
    ['inspText','inspImage','inspShape','inspLine'].forEach(id => { document.getElementById(id).style.display = 'none'; });

    if (type === 'text') {
        showSection('inspText');
        const textDiv = el.querySelector('.frame-text-inner');
        if (textDiv) {
            const cs = window.getComputedStyle(textDiv);
            document.getElementById('inspFontFamily').value = data.styles?.fontFamily || "'Source Sans 3',sans-serif";
            document.getElementById('inspFontSize').value   = parseInt(cs.fontSize) || 14;
            document.getElementById('inspFontWeight').value = cs.fontWeight || '400';
            document.getElementById('inspLineH').value      = cs.lineHeight !== 'normal' ? (parseFloat(cs.lineHeight)/parseFloat(cs.fontSize)).toFixed(2) : '1.5';
            document.getElementById('inspTextCols').value   = parseInt(textDiv.style.columnCount) || 1;
            document.getElementById('inspTextAlign').value  = textDiv.style.textAlign || 'left';
            document.getElementById('inspTextBg').value     = rgbToHex(textDiv.style.backgroundColor) || '#ffffff';
            document.getElementById('inspTextColor').value  = rgbToHex(cs.color) || '#111111';
        }
    } else if (type === 'image') {
        showSection('inspImage');
        document.getElementById('inspImgUrl').value      = data.src || '';
        document.getElementById('inspImgFit').value      = data.imgFit || 'cover';
        document.getElementById('inspImgBorderW').value  = data.borderWidth || 0;
        document.getElementById('inspImgBorder').value   = data.borderColor || '#000000';
        document.getElementById('inspImgRadius').value   = data.borderRadius || 0;
    } else if (type === 'rect' || type === 'shape') {
        showSection('inspShape');
        document.getElementById('inspShapeFill').value   = data.fillColor || '#000000';
        document.getElementById('inspShapeBorder').value = data.borderColor || '#000000';
        document.getElementById('inspShapeBorderW').value= data.borderWidth || 0;
        document.getElementById('inspShapeRadius').value = data.borderRadius || 0;
    } else if (type === 'line') {
        showSection('inspLine');
        document.getElementById('inspLineColor').value = data.lineColor || '#000000';
        document.getElementById('inspLineW').value     = data.lineWidth || 2;
        document.getElementById('inspLineStyle').value = data.lineStyle || 'solid';
    }
}

function showSection(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = '';
}

function updateInspectorGeometry(el) {
    document.getElementById('inspX').value = Math.round(parseFloat(el.style.left));
    document.getElementById('inspY').value = Math.round(parseFloat(el.style.top));
    document.getElementById('inspW').value = Math.round(parseFloat(el.style.width));
    document.getElementById('inspH').value = Math.round(parseFloat(el.style.height));
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
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(f => f.id == selectedFrame.dataset.frameId);
    if (f) f.opacity = val;
    markUnsaved();
}

function saveFrameGeoToModel(el) {
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(f => f.id == el.dataset.frameId);
    if (f) {
        f.x = Math.round(parseFloat(el.style.left));
        f.y = Math.round(parseFloat(el.style.top));
        f.w = Math.round(parseFloat(el.style.width));
        f.h = Math.round(parseFloat(el.style.height));
    }
}

// ── TEXT PROPERTIES ────────────────────────────────
function applyTextProps() {
    if (!selectedFrame || selectedFrame.dataset.type !== 'text') return;
    const textDiv = selectedFrame.querySelector('.frame-text-inner');
    if (!textDiv) return;

    const ff  = document.getElementById('inspFontFamily').value;
    const fs  = document.getElementById('inspFontSize').value;
    const fw  = document.getElementById('inspFontWeight').value;
    const lh  = document.getElementById('inspLineH').value;
    const cols= document.getElementById('inspTextCols').value;
    const col = document.getElementById('inspTextColor').value;
    const align= document.getElementById('inspTextAlign').value;
    const bg  = document.getElementById('inspTextBg').value;

    Object.assign(textDiv.style, {
        fontFamily:    ff,
        fontSize:      fs + 'px',
        fontWeight:    fw,
        lineHeight:    lh,
        color:         col,
        textAlign:     align,
        backgroundColor: bg,
        columnCount:   parseInt(cols) > 1 ? cols : '',
    });

    // Save to model
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) {
        f.styles = { fontFamily:ff, fontSize:parseInt(fs), fontWeight:fw, lineHeight:lh,
                     columns:parseInt(cols), color:col, textAlign:align, backgroundColor:bg };
    }
    markUnsaved();
}

function clearTextBg() {
    if (!selectedFrame) return;
    const textDiv = selectedFrame.querySelector('.frame-text-inner');
    if (textDiv) { textDiv.style.backgroundColor = 'transparent'; }
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f && f.styles) f.styles.backgroundColor = 'transparent';
    markUnsaved();
}

// ── CONTROL BAR — text formatting ──────────────────
function applyStyleToSelection() {
    const ff = document.getElementById('cbFontFamily').value;
    const fs = document.getElementById('cbFontSize').value;
    const lh = document.getElementById('cbLineHeight').value;
    document.execCommand('fontName', false, ff);
    document.execCommand('fontSize', false, 4);
    // Update selected text spans
    document.querySelectorAll('font[size="4"]').forEach(el => {
        el.removeAttribute('size');
        el.style.fontSize = fs + 'px';
        el.style.fontFamily = ff;
        el.style.lineHeight = lh;
    });
    markUnsaved();
}

function toggleBold()      { document.execCommand('bold');      markUnsaved(); }
function toggleItalic()    { document.execCommand('italic');    markUnsaved(); }
function toggleUnderline() { document.execCommand('underline'); markUnsaved(); }

function toggleUppercase() {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (!frame) return;
    frame.style.textTransform = frame.style.textTransform === 'uppercase' ? '' : 'uppercase';
    markUnsaved();
}

function applyAlign(align) {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (frame) {
        frame.style.textAlign = align;
        const page = currentEdicion.paginas[activePageIndex];
        const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
        if (f && f.styles) f.styles.textAlign = align;
    }
    markUnsaved();
}

function applyTextColor(color) {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (frame) {
        frame.style.color = color;
        const page = currentEdicion.paginas[activePageIndex];
        const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
        if (f && f.styles) f.styles.color = color;
    }
    markUnsaved();
}

function setColumns(n) {
    const frame = selectedFrame?.querySelector('.frame-text-inner');
    if (!frame) return;
    frame.style.columnCount = n > 1 ? n : '';
    frame.style.columnGap = n > 1 ? '14px' : '';
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f && f.styles) f.styles.columns = n;
    markUnsaved();
}

// ── IMAGE PROPERTIES ───────────────────────────────
function openImagePickerForFrame(el) {
    selectedFrame = el;
    openModal('imagePickerModal');
}

function openImagePicker() {
    if (selectedFrame && selectedFrame.dataset.type === 'image') {
        openModal('imagePickerModal');
    }
}

function applyImageUrlToFrame() {
    const url = document.getElementById('imageUrlInput').value;
    if (!url || !selectedFrame) return;
    applyImageSrc(url);
    closeModal('imagePickerModal');
}

function uploadImageForFrame(input) {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', '{{ csrf_token() }}');
    fetch('{{ route("admin.periodico.uploadImage") }}', { method:'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.url) { applyImageSrc(d.url); closeModal('imagePickerModal'); }
        });
}

function applyImageSrc(src) {
    if (!selectedFrame || selectedFrame.dataset.type !== 'image') return;
    let img = selectedFrame.querySelector('.frame-img-inner');
    if (!img) {
        selectedFrame.querySelector('.frame-img-placeholder')?.remove();
        img = document.createElement('img');
        img.className = 'frame-img-inner';
        selectedFrame.appendChild(img);
        initInteract();
    }
    img.src = src;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) f.src = src;
    document.getElementById('inspImgUrl').value = src;
    markUnsaved();
}

function applyImageUrl() { applyImageSrc(document.getElementById('inspImgUrl').value); }

function applyImageFit() {
    if (!selectedFrame) return;
    const fit = document.getElementById('inspImgFit').value;
    const img = selectedFrame.querySelector('.frame-img-inner');
    if (img) img.style.objectFit = fit;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) f.imgFit = fit;
    markUnsaved();
}

function applyImageBorder() {
    if (!selectedFrame) return;
    const w = document.getElementById('inspImgBorderW').value;
    const c = document.getElementById('inspImgBorder').value;
    const img = selectedFrame.querySelector('.frame-img-inner');
    if (img) img.style.border = `${w}px solid ${c}`;
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) { f.borderWidth = parseInt(w); f.borderColor = c; }
    markUnsaved();
}

function applyImageBorderColor() { applyImageBorder(); }
function applyImageRadius() {
    if (!selectedFrame) return;
    const r = document.getElementById('inspImgRadius').value;
    const img = selectedFrame.querySelector('.frame-img-inner');
    if (img) img.style.borderRadius = r + 'px';
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) f.borderRadius = parseInt(r);
    markUnsaved();
}

// ── SHAPE PROPERTIES ───────────────────────────────
function applyShapeProps() {
    if (!selectedFrame) return;
    const shape = selectedFrame.querySelector('.frame-shape-inner');
    if (!shape) return;
    const fill    = document.getElementById('inspShapeFill').value;
    const bColor  = document.getElementById('inspShapeBorder').value;
    const bWidth  = document.getElementById('inspShapeBorderW').value;
    const radius  = document.getElementById('inspShapeRadius').value;
    shape.style.background    = fill;
    shape.style.border        = `${bWidth}px solid ${bColor}`;
    shape.style.borderRadius  = radius + 'px';
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) { f.fillColor = fill; f.borderColor = bColor; f.borderWidth = parseInt(bWidth); f.borderRadius = parseInt(radius); }
    markUnsaved();
}

function clearShapeFill() {
    const shape = selectedFrame?.querySelector('.frame-shape-inner');
    if (shape) shape.style.background = 'transparent';
    markUnsaved();
}

// ── LINE PROPERTIES ────────────────────────────────
function applyLineProps() {
    if (!selectedFrame) return;
    const line  = selectedFrame.querySelector('.frame-line-inner');
    if (!line) return;
    const color = document.getElementById('inspLineColor').value;
    const w     = document.getElementById('inspLineW').value;
    const style = document.getElementById('inspLineStyle').value;
    line.style.background   = color;
    line.style.height       = w + 'px';
    // dashed/dotted via border
    const page = currentEdicion.paginas[activePageIndex];
    const f = page?.frames?.find(fr => fr.id == selectedFrame.dataset.frameId);
    if (f) { f.lineColor = color; f.lineWidth = parseInt(w); f.lineStyle = style; }
    markUnsaved();
}

// ── NEWS INSERTION ─────────────────────────────────
function insertNewsBlock(news, mode) {
    const offset = { x: 50 + Math.random() * 40, y: 50 + Math.random() * 40 };

    if (mode === 'article') {
        // Image frame
        createFrame({ type:'image', x: offset.x, y: offset.y, w: 350, h: 220, src: news.imagen, imgFit:'cover' });
        // Category badge (rect)
        createFrame({ type:'rect', x: offset.x, y: offset.y + 220, w: 80, h: 20, fillColor:'#C8472B', z:20 });
        // Category text
        createFrame({ type:'text', x: offset.x, y: offset.y + 220, w: 80, h: 20,
            content: `<p style="font-size:10px;font-weight:800;text-transform:uppercase;color:#fff;padding:2px 5px;">${news.categoria}</p>`,
            styles:{ fontFamily:"'Montserrat',sans-serif", fontSize:10, fontWeight:'800', color:'#ffffff' }, z:21 });
        // Headline
        createFrame({ type:'text', x: offset.x, y: offset.y + 246, w: 350, h: 70,
            content: `<p style="font-family:'Playfair Display',serif;font-size:22px;font-weight:900;line-height:1.1;color:#000;">${news.titulo}</p>`,
            styles:{ fontFamily:"'Playfair Display',serif", fontSize:22, fontWeight:'900', lineHeight:'1.1', color:'#000000' } });
        // Excerpt
        createFrame({ type:'text', x: offset.x, y: offset.y + 320, w: 350, h: 100,
            content: `<p style="font-family:'Source Sans 3',sans-serif;font-size:13px;line-height:1.55;color:#333;">${news.bajada}</p>`,
            styles:{ fontFamily:"'Source Sans 3',sans-serif", fontSize:13, lineHeight:'1.55', color:'#333333' } });
        // Author
        createFrame({ type:'text', x: offset.x, y: offset.y + 424, w: 350, h: 22,
            content: `<p style="font-size:10px;font-weight:700;color:#888;text-transform:uppercase;">Por ${news.autor || 'REDACCIÓN'}</p>`,
            styles:{ fontSize:10, fontWeight:'700', color:'#888888' } });
    } else if (mode === 'headline') {
        createFrame({ type:'text', x: offset.x, y: offset.y, w: 380, h: 80,
            content: `<p style="font-family:'Playfair Display',serif;font-size:26px;font-weight:900;line-height:1.05;color:#000;">${news.titulo}</p>`,
            styles:{ fontFamily:"'Playfair Display',serif", fontSize:26, fontWeight:'900' } });
    } else if (mode === 'photo_caption') {
        createFrame({ type:'image', x: offset.x, y: offset.y, w: 300, h: 200, src: news.imagen, imgFit:'cover' });
        createFrame({ type:'text', x: offset.x, y: offset.y + 205, w: 300, h: 40,
            content: `<p style="font-size:11px;color:#555;font-style:italic;">${news.titulo}</p>`,
            styles:{ fontSize:11, color:'#555555' } });
    } else if (mode === 'text_only') {
        createFrame({ type:'text', x: offset.x, y: offset.y, w: 330, h: 160,
            content: `<p style="font-family:'Source Sans 3',sans-serif;font-size:13px;line-height:1.6;color:#111;"><strong>${news.titulo}</strong><br><br>${news.bajada}</p>`,
            styles:{ fontFamily:"'Source Sans 3',sans-serif", fontSize:13 } });
    }

    showToast('Noticia insertada en la página', 'success');
    toggleNewsDrawer();
}

// ── PAGE MANAGEMENT ────────────────────────────────
function switchPage(index) {
    // Save current frame geometry first
    if (selectedFrame) saveFrameGeoToModel(selectedFrame);
    selectFrame(null);

    activePageIndex = index;
    document.querySelectorAll('.id-paper-sheet').forEach((s, i) => {
        s.classList.toggle('active', i === index);
    });
    renderPageThumbs();
    initInteract();
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
    showToast('Nueva página creada', 'success');
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
        preview.style.background = '#fff';
        // Mini representation
        preview.innerHTML = `<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#ddd;font-weight:900;">${idx+1}</div>`;

        const label = document.createElement('span');
        label.className = 'page-thumb-label';
        label.textContent = page.nombre || `Pág. ${idx + 1}`;

        thumb.appendChild(preview);
        thumb.appendChild(label);
        container.appendChild(thumb);

        // Right-click to delete
        thumb.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            if (currentEdicion.paginas.length > 1 && confirm('¿Eliminar esta página?')) {
                currentEdicion.paginas.splice(idx, 1);
                renderAllPages();
                switchPage(Math.min(activePageIndex, currentEdicion.paginas.length - 1));
                markUnsaved();
            }
        });
    });
}

// ── TEMPLATES ──────────────────────────────────────
function applyTemplate(type) {
    closeModal('templateModal');

    // Clear current page frames
    currentEdicion.paginas[activePageIndex].frames = [];
    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    sheet.querySelectorAll('.id-frame').forEach(el => el.remove());

    const templates = {
        portada: () => {
            // Header bar
            createFrame({ type:'rect', x:0, y:0, w:794, h:80, fillColor:'#0B1F3A', z:2 });
            createFrame({ type:'text', x:10, y:8, w:550, h:65, z:3,
                content: '<p style="font-family:\'Anton\',sans-serif;font-size:48px;letter-spacing:2px;color:#fff;text-transform:uppercase;">LA ESTRELLA<span style="background:#C8472B;color:#fff;font-size:20px;padding:2px 8px;margin-left:8px;vertical-align:middle;">del Oriente</span></p>',
                styles:{fontFamily:"'Anton',sans-serif",fontSize:48,color:'#ffffff'}});
            createFrame({ type:'text', x:560, y:8, w:230, h:65, z:3,
                content: '<p style="font-size:11px;color:#fff;text-align:right;">Santa Cruz de la Sierra • Bolivia<br><strong>N° 11.986</strong> • Bs 7,00</p>',
                styles:{fontSize:11, color:'#ffffff', textAlign:'right'}});
            // Separator
            createFrame({ type:'line', x:0, y:82, w:794, h:3, lineColor:'#C8472B', lineWidth:3, z:4 });
            // Main headline
            createFrame({ type:'text', x:20, y:95, w:520, h:90, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:32px;font-weight:900;line-height:1.05;color:#000;">Titular principal de la edición de hoy</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:32,fontWeight:'900'}});
            // Subheadline
            createFrame({ type:'text', x:20, y:190, w:520, h:55, z:5,
                content: '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:14px;line-height:1.5;color:#333;">Texto introductorio o subtítulo de la noticia principal que aparece en portada...</p>',
                styles:{fontFamily:"'Source Sans 3',sans-serif",fontSize:14}});
            // Main image
            createFrame({ type:'image', x:20, y:250, w:520, h:340, z:5 });
            // Sidebar noticias
            createFrame({ type:'rect', x:558, y:95, w:215, h:3, fillColor:'#C8472B', z:4 });
            createFrame({ type:'text', x:558, y:100, w:215, h:50, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:14px;font-weight:900;line-height:1.2;color:#000;">Segunda noticia importante del día</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:14,fontWeight:'900'}});
            createFrame({ type:'image', x:558, y:155, w:215, h:130, z:5 });
            createFrame({ type:'line', x:558, y:292, w:215, h:1, lineColor:'#ccc', z:4 });
            createFrame({ type:'text', x:558, y:296, w:215, h:50, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:14px;font-weight:900;line-height:1.2;color:#000;">Tercera noticia destacada de la jornada</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:14,fontWeight:'900'}});
            createFrame({ type:'image', x:558, y:350, w:215, h:130, z:5 });
            // Bottom bar
            createFrame({ type:'rect', x:0, y:600, w:794, h:30, fillColor:'#C8472B', z:8 });
            createFrame({ type:'text', x:8, y:604, w:780, h:22, z:9,
                content: '<p style="font-size:11px;font-weight:800;color:#fff;text-transform:uppercase;">Noticia de última hora</p>',
                styles:{fontSize:11, fontWeight:'800', color:'#ffffff'}});
        },
        two_col: () => {
            createFrame({ type:'line', x:0, y:0, w:794, h:3, lineColor:'#C8472B', lineWidth:3, z:2 });
            createFrame({ type:'image', x:20, y:10, w:370, h:240, z:5 });
            createFrame({ type:'text', x:20, y:255, w:370, h:70, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:20px;font-weight:900;line-height:1.1;color:#000;">Titular de la columna izquierda</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:20,fontWeight:'900'}});
            createFrame({ type:'text', x:20, y:330, w:370, h:180, z:5,
                content: '<p style="font-size:13px;line-height:1.6;color:#333;column-count:2;column-gap:14px;">Cuerpo del artículo izquierdo. Haz doble clic para editar. Aquí va el texto de la noticia con varias columnas de texto...</p>',
                styles:{fontSize:13, columns:2}});
            createFrame({ type:'line', x:397, y:10, w:1, h:500, lineColor:'#ccc', z:3 });
            createFrame({ type:'image', x:404, y:10, w:370, h:240, z:5 });
            createFrame({ type:'text', x:404, y:255, w:370, h:70, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:20px;font-weight:900;line-height:1.1;color:#000;">Titular de la columna derecha</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:20,fontWeight:'900'}});
            createFrame({ type:'text', x:404, y:330, w:370, h:180, z:5,
                content: '<p style="font-size:13px;line-height:1.6;color:#333;column-count:2;column-gap:14px;">Cuerpo del artículo derecho. Haz doble clic para editar...</p>',
                styles:{fontSize:13, columns:2}});
        },
        three_col: () => {
            createFrame({ type:'line', x:0, y:0, w:794, h:3, lineColor:'#0B1F3A', lineWidth:3, z:2 });
            [0,1,2].forEach(i => {
                const x = 20 + i * 258;
                createFrame({ type:'image', x, y:10, w:240, h:160, z:5 });
                createFrame({ type:'text', x, y:175, w:240, h:55, z:5,
                    content: `<p style="font-family:'Playfair Display',serif;font-size:16px;font-weight:900;color:#000;">Titular columna ${i+1}</p>`,
                    styles:{fontFamily:"'Playfair Display',serif",fontSize:16,fontWeight:'900'}});
                createFrame({ type:'text', x, y:235, w:240, h:200, z:5,
                    content: '<p style="font-size:12px;line-height:1.55;color:#333;">Cuerpo de la noticia para esta columna...</p>',
                    styles:{fontSize:12}});
                if (i < 2) createFrame({ type:'line', x:258+(i*258)+12, y:10, w:1, h:430, lineColor:'#ddd', z:3 });
            });
        },
        editorial: () => {
            createFrame({ type:'rect', x:0, y:0, w:794, h:36, fillColor:'#0B1F3A', z:2 });
            createFrame({ type:'text', x:8, y:4, w:300, h:28, z:3,
                content: '<p style="font-family:\'Anton\',sans-serif;font-size:22px;letter-spacing:2px;color:#fff;">EDITORIAL</p>',
                styles:{fontFamily:"'Anton',sans-serif",fontSize:22,color:'#ffffff'}});
            createFrame({ type:'text', x:20, y:50, w:460, h:60, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:26px;font-weight:900;line-height:1.1;font-style:italic;color:#0B1F3A;">Título del editorial o columna de opinión</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:26,fontWeight:'900'}});
            createFrame({ type:'text', x:20, y:120, w:460, h:400, z:5,
                content: '<p style="font-size:13px;line-height:1.7;text-align:justify;color:#222;column-count:2;column-gap:18px;">Texto del editorial. Haz doble clic para editar este contenido. El texto puede fluir en múltiples columnas como en un periódico impreso real...</p>',
                styles:{fontSize:13, columns:2, textAlign:'justify'}});
            createFrame({ type:'rect', x:20, y:130, w:2, h:390, fillColor:'#C8472B', z:6 });
            createFrame({ type:'text', x:500, y:50, w:270, h:60, z:5,
                content: '<p style="font-size:11px;font-weight:700;color:#666;text-align:right;font-style:italic;">Por el Equipo Editorial<br>La Estrella del Oriente</p>',
                styles:{fontSize:11,textAlign:'right'}});
            createFrame({ type:'rect', x:500, y:120, w:270, h:2, fillColor:'#C8472B', z:4 });
            createFrame({ type:'text', x:500, y:130, w:270, h:220, z:5,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:18px;font-weight:700;font-style:italic;color:#0B1F3A;border-top:2px solid #C8472B;border-bottom:2px solid #C8472B;padding:10px 0;">"Cita destacada del editorial o frase principal que resume la posición del periódico."</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:18}});
        },
        photo_spread: () => {
            createFrame({ type:'image', x:0, y:0, w:794, h:700, z:5 });
            createFrame({ type:'rect', x:0, y:620, w:794, h:80, fillColor:'rgba(0,0,0,0.75)', z:8 });
            createFrame({ type:'text', x:20, y:628, w:754, h:60, z:9,
                content: '<p style="font-family:\'Playfair Display\',serif;font-size:22px;font-weight:900;color:#fff;line-height:1.1;">Pie de foto o titular del foto-reportaje</p>',
                styles:{fontFamily:"'Playfair Display',serif",fontSize:22,fontWeight:'900',color:'#ffffff'}});
        },
        blank: () => {
            // Empty page with margin indicators only
        }
    };

    if (templates[type]) templates[type]();
    markUnsaved();
    showToast('Plantilla aplicada', 'success');
}

// ── ZOOM ───────────────────────────────────────────
function changeZoom(delta) {
    zoomLevel = Math.max(0.2, Math.min(3, zoomLevel + delta));
    applyZoomTransform();
}

function resetZoom() {
    zoomLevel = 1.0;
    applyZoomTransform();
}

function applyZoomTransform() {
    document.querySelectorAll('.id-paper-sheet').forEach(sheet => {
        sheet.style.transform = `scale(${zoomLevel})`;
        sheet.style.transformOrigin = 'top center';
        sheet.style.marginBottom = `${(zoomLevel - 1) * parseFloat(getComputedStyle(sheet).minHeight)}px`;
    });
    document.getElementById('zoomDisplay').textContent = Math.round(zoomLevel * 100) + '%';
}

function updateZoomDisplay() {
    document.getElementById('zoomDisplay').textContent = Math.round(zoomLevel * 100) + '%';
}

// ── GRID & GUIDES ──────────────────────────────────
function toggleGrid() {
    gridVisible = !gridVisible;
    document.querySelectorAll('.id-paper-sheet').forEach(s => s.classList.toggle('grid-visible', gridVisible));
    document.getElementById('gridToggleBtn').classList.toggle('active', gridVisible);
}

function toggleMargins() {
    marginsVisible = !marginsVisible;
    document.querySelectorAll('.paper-margin-guide').forEach(g => g.style.display = marginsVisible ? '' : 'none');
    document.getElementById('marginToggleBtn').classList.toggle('active', marginsVisible);
}

// ── MODALS ─────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close modal on overlay click
document.querySelectorAll('.id-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('open');
    });
});

// ── NEWS DRAWER ────────────────────────────────────
function toggleNewsDrawer() {
    document.getElementById('newsDrawer').classList.toggle('open');
}

function filterDrawerNews() {
    const cat  = document.getElementById('drawerCatFilter').value;
    const term = document.getElementById('drawerSearch').value.toLowerCase();
    document.querySelectorAll('.drawer-news-card').forEach(card => {
        const matchCat  = cat === 'all' || card.dataset.category == cat;
        const matchTerm = !term || card.dataset.title.includes(term);
        card.style.display = (matchCat && matchTerm) ? '' : 'none';
    });
}

// ── SAVE & PUBLISH ─────────────────────────────────
function markUnsaved() {
    hasUnsavedChanges = true;
    const ind = document.getElementById('saveIndicator');
    ind.innerHTML = '<i class="fas fa-circle" style="color:#f59e0b;font-size:0.5rem;"></i> <span>Cambios sin guardar</span>';
    ind.style.color = '#f59e0b';
}

function syncFrameDataFromDOM() {
    (currentEdicion.paginas || []).forEach((page, pIdx) => {
        const sheet = document.getElementById(`sheet-${pIdx}`);
        if (!sheet) return;
        (page.frames || []).forEach(f => {
            const el = document.getElementById(`frame-${f.id}`);
            if (!el) return;
            f.x = Math.round(parseFloat(el.style.left)  || 0);
            f.y = Math.round(parseFloat(el.style.top)   || 0);
            f.w = Math.round(parseFloat(el.style.width) || 100);
            f.h = Math.round(parseFloat(el.style.height)|| 80);
            f.z = parseInt(el.style.zIndex) || 10;
            f.opacity = parseFloat(el.style.opacity) || 1;
            if (f.type === 'text') {
                const inner = el.querySelector('.frame-text-inner');
                if (inner) f.content = inner.innerHTML;
            }
        });
    });
}

function saveEdicion() {
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    syncFrameDataFromDOM();

    fetch(`/admin/periodico/${currentEdicion.id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-HTTP-Method-Override': 'PUT' },
        body: JSON.stringify({ ...currentEdicion, paginas: currentEdicion.paginas })
    })
    .then(r => r.json())
    .then(d => {
        hasUnsavedChanges = false;
        const ind = document.getElementById('saveIndicator');
        ind.innerHTML = '<i class="fas fa-check-circle"></i> <span>Sincronizado</span>';
        ind.style.color = '#5da35d';
        showToast('✓ Edición guardada correctamente', 'success');
    })
    .catch(() => showToast('Error al guardar. Inténtalo de nuevo.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar';
    });
}

function publishEdicion() {
    if (!confirm('¿Publicar esta edición en la web para que sea visible por los lectores?')) return;
    currentEdicion.publicada = true;
    saveEdicion();
    document.getElementById('pageStatusBadge').textContent = '✓ Publicada';
    document.getElementById('pageStatusBadge').className = 'badge bg-success';
}

function changeEdicion(id) {
    if (hasUnsavedChanges && !confirm('Hay cambios sin guardar. ¿Continuar sin guardar?')) return;
    window.location.href = '{{ route("admin.periodico.index") }}?edicion_id=' + id;
}

// ── KEYBOARD SHORTCUTS ─────────────────────────────
function setupKeyboard() {
    document.addEventListener('keydown', (e) => {
        const focused = document.activeElement;
        const isTyping = focused.isContentEditable || ['INPUT','TEXTAREA','SELECT'].includes(focused.tagName);

        if (!isTyping) {
            if (e.key === 'v' || e.key === 'V') setTool('select');
            if (e.key === 't' || e.key === 'T') setTool('text');
            if (e.key === 'f' || e.key === 'F') setTool('image');
            if (e.key === 'r' || e.key === 'R') setTool('rect');
            if (e.key === 'l' || e.key === 'L') setTool('line');
            if (e.key === 'Delete' || e.key === 'Backspace') deleteSelected();
            if (e.key === '=' || e.key === '+') changeZoom(0.1);
            if (e.key === '-' || e.key === '_') changeZoom(-0.1);
            if (e.key === 'Escape') { selectFrame(null); setTool('select'); }
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveEdicion(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') { e.preventDefault(); duplicateSelected(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'z') { /* TODO: undo */ }
    });
}

// ── TOAST NOTIFICATION ─────────────────────────────
function showToast(msg, type = 'success') {
    const toast = document.getElementById('idToast');
    const icon  = document.getElementById('idToastIcon');
    const msgEl = document.getElementById('idToastMsg');
    icon.className  = type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
    icon.style.color= type === 'error' ? '#ef4444' : '#4ade80';
    msgEl.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2800);
}

// ── HELPERS ────────────────────────────────────────
function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent') return '#ffffff';
    const m = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!m) return rgb;
    return '#' + [m[1],m[2],m[3]].map(x => parseInt(x).toString(16).padStart(2,'0')).join('');
}
</script>
@endsection