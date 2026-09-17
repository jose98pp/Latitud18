@extends('layouts.admin')
{{-- NOTE: Uses existing routes: admin.periodico.update, admin.periodico.publish, admin.periodico.uploadImage, admin.periodico.store --}}

@section('title', 'Editor InDesign — ' . ($currentEdicion['titulo'] ?? 'Periódico Digital'))

@section('page-title')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100" style="font-size:0.85rem;">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-newspaper text-danger"></i>
        <span style="font-weight:800;letter-spacing:0.3px;">Editor de Maquetación InDesign</span>
        <span id="pageStatusBadge" class="badge {{ !empty($currentEdicion['publicada']) ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.7rem;">
            {{ !empty($currentEdicion['publicada']) ? '✓ Publicada' : '⬤ Borrador' }}
        </span>
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
      <option value="tabloid" selected>Tabloide Moderno (720 × 1040)</option>
      <option value="broadsheet">Broadsheet Clásico (820 × 1160)</option>
      <option value="compact">Compacto A4 (680 × 960)</option>
    </select>

    <button class="id-btn id-btn-ghost" onclick="openModal('newEdicionModal')">
      <i class="fas fa-plus"></i> Nueva Edición
    </button>

    <div class="id-menubar-sep"></div>

    {{-- Plantillas --}}
    <button class="id-btn id-btn-ghost" onclick="openModal('templateModal')" style="color:#38bdf8;">
      <i class="fas fa-th-large"></i> Plantillas InDesign
    </button>

    {{-- Noticias Web --}}
    <button class="id-btn id-btn-ghost" onclick="toggleNewsDrawer()">
      <i class="fas fa-newspaper" style="color:var(--id-accent);"></i> Insertar Noticias ({{ count($noticiasPublicadas) }})
    </button>

    {{-- Exportar PDF --}}
    <button class="id-btn id-btn-ghost" onclick="openPdfExportModal()">
      <i class="fas fa-file-pdf text-danger"></i> Exportar PDF
    </button>

    {{-- Vista Previa en Lector Público --}}
    <a href="{{ route('periodico.public.show', $currentEdicion['id']) }}" target="_blank" class="id-btn id-btn-ghost">
      <i class="fas fa-eye"></i> Ver Lector
    </a>

    <div class="id-menubar-sep"></div>

    {{-- Publicar / Despublicar --}}
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

    {{-- Rulers & Grid --}}
    <div class="id-control-group">
      <button class="id-ctrl-btn" id="rulerToggleBtn" onclick="toggleRulers()" title="Mostrar Reglas"><i class="fas fa-ruler-combined"></i></button>
      <button class="id-ctrl-btn" id="gridToggleBtn" onclick="toggleGrid()" title="Mostrar Cuadrícula"><i class="fas fa-border-all"></i></button>
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
    <button class="id-tool-btn" id="tool-line" onclick="createSpecialElement('divider')" title="Pleca / Filete Divisor (L)">
      <i class="fas fa-minus" style="color:#94a3b8;"></i>
      <span class="hotkey">L</span>
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

    {{-- Masthead Inspector --}}
    <div class="inspector-section" id="inspMasthead" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-heading" style="color:var(--id-accent);margin-right:5px;"></i> Cabecera del Periódico</span>
      </div>
      <div class="inspector-body">
        <div>
          <label class="id-form-label">Nombre del Diario</label>
          <input type="text" class="inspector-input" id="inspMastheadName" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div>
          <label class="id-form-label">Subtítulo / Distintivo</label>
          <input type="text" class="inspector-input" id="inspMastheadBadge" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div>
          <label class="id-form-label">Lema / Slogan</label>
          <input type="text" class="inspector-input" id="inspMastheadMotto" onchange="applyMastheadProps()" style="width:100%;">
        </div>
        <div class="inspector-row">
          <div style="flex:1;">
            <label class="id-form-label">Oreja Izq (Patrocinio)</label>
            <input type="text" class="inspector-input" id="inspMastheadLeftEar" onchange="applyMastheadProps()" style="width:100%;">
          </div>
          <div style="flex:1;">
            <label class="id-form-label">Oreja Der (Dólar / Cotiz)</label>
            <input type="text" class="inspector-input" id="inspMastheadRightEar" onchange="applyMastheadProps()" style="width:100%;">
          </div>
        </div>
      </div>
    </div>

    {{-- Text & Headline Inspector --}}
    <div class="inspector-section" id="inspText" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-font" style="margin-right:5px;"></i> Tipografía & Texto</span>
      </div>
      <div class="inspector-body">
        <div class="inspector-row">
          <span class="inspector-label" style="min-width:48px;">Fuente</span>
          <select class="inspector-select" id="inspFontFamily" onchange="applyTextProps()">
            <option value="'Source Sans 3',sans-serif">Source Sans 3</option>
            <option value="'Playfair Display',serif">Playfair Display</option>
            <option value="'Anton',sans-serif">Anton</option>
            <option value="'Oswald',sans-serif">Oswald</option>
            <option value="'Bebas Neue',sans-serif">Bebas Neue</option>
            <option value="'Montserrat',sans-serif">Montserrat</option>
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
      </div>
    </div>

    {{-- Image Inspector --}}
    <div class="inspector-section" id="inspImage" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-image" style="margin-right:5px;"></i> Imagen & Fotonoticia</span>
      </div>
      <div class="inspector-body">
        <button class="insp-full-btn" onclick="openImagePicker()">
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

    {{-- Actions & Ordering --}}
    <div class="inspector-section" id="inspActions" style="display:none;">
      <div class="inspector-section-header">
        <span><i class="fas fa-layer-group" style="margin-right:5px;"></i> Capas y Acciones</span>
      </div>
      <div class="inspector-body" style="display:flex;flex-direction:column;gap:6px;">
        <div class="inspector-row">
          <button class="insp-full-btn" onclick="bringToFront()" style="flex:1;">
            <i class="fas fa-arrow-up"></i> Al Frente
          </button>
          <button class="insp-full-btn" onclick="sendToBack()" style="flex:1;">
            <i class="fas fa-arrow-down"></i> Al Fondo
          </button>
        </div>
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
            <i class="fas fa-newspaper"></i> Artículo Completo
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock({{ json_encode(["id"=>$noticia->id,"titulo"=>$noticia->titulo,"bajada"=>\Illuminate\Support\Str::limit(strip_tags($noticia->contenido),200),"contenido"=>strip_tags($noticia->contenido),"categoria"=>$noticia->category->name??"General","imagen"=>$noticia->imagenUrl,"autor"=>$noticia->autor??"REDACCIÓN"]) }}, "headline")'>
            <i class="fas fa-heading"></i> Solo Titular
          </button>
          <button class="dnc-btn" onclick='insertNewsBlock({{ json_encode(["id"=>$noticia->id,"titulo"=>$noticia->titulo,"bajada"=>\Illuminate\Support\Str::limit(strip_tags($noticia->contenido),200),"contenido"=>strip_tags($noticia->contenido),"categoria"=>$noticia->category->name??"General","imagen"=>$noticia->imagenUrl,"autor"=>$noticia->autor??"REDACCIÓN"]) }}, "photo_caption")'>
            <i class="fas fa-camera"></i> Fotonoticia
          </button>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL: TEMPLATE PRESETS (INDESIGN SUITE)
═══════════════════════════════════════════════════════ --}}
<div class="id-modal-overlay" id="templateModal">
  <div class="id-modal">
    <div class="id-modal-header">
      <div class="id-modal-title"><i class="fas fa-th-large" style="color:var(--id-accent);"></i> Biblioteca de Maquetas y Plantillas InDesign</div>
      <button class="id-modal-close" onclick="closeModal('templateModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="id-modal-body">
      <p style="font-size:0.75rem;color:#94a3b8;margin-bottom:16px;">
        Selecciona una plantilla profesional prediseñada para la página actual. Se generará la distribución editorial con cabeceras, orejas de cotización, columnas y fotos listas para editar.
      </p>
      <div class="template-grid">
        <div class="template-card" onclick="applyTemplate('la_estrella_portada')">
          <i class="fas fa-newspaper text-danger"></i>
          <h6>La Estrella del Oriente - Portada Oficial (Bolivia)</h6>
          <p>Cabecera cyan con distintivo rojo, orejas de cotización del dólar (Bs 12,58) y CRE, gran titular con antetítulo en rojo, sumario en 4 columnas y fotonoticia principal.</p>
        </div>
        <div class="template-card" onclick="applyTemplate('latitud18_broadsheet')">
          <i class="fas fa-columns" style="color:#0284c7;"></i>
          <h6>Latitud 18 / El Deber - Portada 5 Columnas</h6>
          <p>Gran formato Broadsheet con cintillo superior, titular dominante, doble fotonoticia central y módulos de opinión.</p>
        </div>
        <div class="template-card" onclick="applyTemplate('editorial_opinion')">
          <i class="fas fa-feather-alt" style="color:#f59e0b;"></i>
          <h6>Página 2: Editorial & Opinión</h6>
          <p>Columna editorial con letra capital (Drop Cap), cita destacada con comillas estilizadas, cuadro de cotizaciones y clima.</p>
        </div>
        <div class="template-card" onclick="applyTemplate('comunidad_cultura')">
          <i class="fas fa-landmark" style="color:#10b981;"></i>
          <h6>Página 3: Comunidad & Cultura</h6>
          <p>Fotonoticia de gran impacto, texto en 4 columnas con crédito de autor y columna lateral de breves informativos.</p>
        </div>
        <div class="template-card" onclick="applyTemplate('negocios_economia')">
          <i class="fas fa-chart-line" style="color:#8b5cf6;"></i>
          <h6>Página 4: Negocios & Economía</h6>
          <p>Apertura económica con cifra destacada, dos artículos independientes y gráficos financieros.</p>
        </div>
        <div class="template-card" onclick="applyTemplate('blank')">
          <i class="fas fa-square" style="color:#64748b;"></i>
          <h6>Página en Blanco</h6>
          <p>Lienzo vacío para construir una maquetación libre desde cero.</p>
        </div>
      </div>
    </div>
    <div class="id-modal-footer">
      <button class="id-btn id-btn-ghost" onclick="closeModal('templateModal')">Cancelar</button>
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
          <label class="id-form-label">Clonar desde edición existente:</label>
          <select name="clonar_de" class="id-form-input">
            <option value="">Plantilla predeterminada</option>
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
        Selecciona cómo deseas generar el documento PDF de <strong>{{ $currentEdicion['numero_edicion'] }}</strong>:
      </p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <button class="insp-full-btn" onclick="exportPdfDirect('current')" style="padding:12px;font-size:0.85rem;">
          <i class="fas fa-file-alt text-primary"></i> Descargar / Imprimir Página Actual
        </button>
        <button class="insp-full-btn" onclick="exportPdfDirect('all')" style="padding:12px;font-size:0.85rem;">
          <i class="fas fa-book text-danger"></i> Descargar Edición Completa (Todas las Páginas)
        </button>
      </div>
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

// Undo / Redo history
let undoHistory = [];
let redoHistory = [];

const PAPER_FORMATS = {
  tabloid:   { w: 720, h: 1040, name: 'Tabloide Moderno' },
  broadsheet:{ w: 820, h: 1160, name: 'Broadsheet Clásico' },
  compact:   { w: 680, h: 960,  name: 'Compacto A4' }
};

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
});

// ── FORMAT SWITCHER ─────────────────────────────────
function changePaperFormat(formatKey) {
    const f = PAPER_FORMATS[formatKey] || PAPER_FORMATS.tabloid;
    document.documentElement.style.setProperty('--paper-w', f.w + 'px');
    document.documentElement.style.setProperty('--paper-h', f.h + 'px');
    
    document.querySelectorAll('.id-paper-sheet').forEach(sheet => {
        sheet.style.width = f.w + 'px';
        sheet.style.minHeight = f.h + 'px';
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

    // Grid overlay
    const gridOverlay = document.createElement('div');
    gridOverlay.className = 'paper-grid-overlay';
    sheet.appendChild(gridOverlay);

    // Margin guide
    const marginGuide = document.createElement('div');
    marginGuide.className = 'paper-margin-guide';
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
        fData.h = 180;
        fData.columns = 4;
        fData.content = '<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11.5px;line-height:1.45;color:#1e293b;text-align:justify;">Texto del artículo periodístico. Haz doble clic para editar o insertar noticias desde la base de datos con el panel lateral. El texto fluye armónicamente en 4 columnas con justificación editorial.</p>';
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
    }

    if (!currentEdicion.paginas[activePageIndex].frames) {
        currentEdicion.paginas[activePageIndex].frames = [];
    }
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

    el.style.left = (fData.x || 20) + 'px';
    el.style.top = (fData.y || 20) + 'px';
    el.style.width = (fData.w || 200) + 'px';
    el.style.height = (fData.h || 100) + 'px';
    el.style.zIndex = fData.z || 10;
    if (fData.opacity !== undefined) el.style.opacity = fData.opacity;

    // Render inner content by type
    if (fData.type === 'masthead') {
        el.innerHTML = `
            <div class="frame-masthead-inner" style="border-bottom:3px solid #0284c7;padding-bottom:4px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <div style="background:#fef9c3;color:#854d0e;padding:4px 8px;border-radius:2px;font-size:9px;font-weight:700;width:140px;line-height:1.2;">
                        ${fData.leftEar || 'CRE 100%'}
                    </div>
                    <div style="text-align:center;flex:1;">
                        <span style="font-family:'Anton',sans-serif;font-size:46px;color:#0284c7;line-height:1;letter-spacing:1px;">${fData.newspaperName || 'LA ESTRELLA'}</span>
                        <span style="background:#D71920;color:#fff;font-family:'Anton',sans-serif;font-size:18px;padding:2px 8px;border-radius:2px;margin-left:4px;vertical-align:middle;">${fData.subBadge || 'del Oriente'}</span>
                        <div style="font-size:9px;font-weight:800;letter-spacing:1.5px;color:#64748b;text-transform:uppercase;margin-top:2px;">${fData.motto || 'EL PRIMER PERIÓDICO DE SANTA CRUZ'}</div>
                    </div>
                    <div style="background:#0284c7;color:#fff;padding:4px 8px;border-radius:2px;font-size:9px;font-weight:800;width:130px;text-align:right;line-height:1.2;">
                        ${fData.rightEar || 'DÓLAR: Bs 12,58'}
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;border-top:1px solid #e2e8f0;padding-top:3px;margin-top:4px;font-size:9px;color:#64748b;font-weight:600;">
                    <span>${fData.editionDate || 'Santa Cruz de la Sierra'}</span>
                    <span><strong>${fData.editionNumber || 'N° 11.986'}</strong></span>
                    <span>${fData.price || 'Bs 7,00'}</span>
                </div>
            </div>
        `;
    } else if (fData.type === 'headline') {
        el.innerHTML = `
            <div style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:center;">
                ${fData.kicker ? `<span style="font-size:11px;font-weight:800;color:#D71920;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">${fData.kicker}</span>` : ''}
                <div class="frame-text-inner" contenteditable="true" style="padding:0;font-family:'Oswald',sans-serif;font-size:28px;font-weight:700;line-height:1.1;">
                    ${fData.content || 'Titular de Noticia'}
                </div>
            </div>
        `;
    } else if (fData.type === 'image') {
        el.innerHTML = `
            <div style="width:100%;height:100%;display:flex;flex-direction:column;">
                <div style="flex:1;position:relative;overflow:hidden;">
                    <img src="${fData.src || 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=80'}" class="frame-img-inner">
                </div>
                ${fData.caption ? `<div style="font-size:9.5px;color:#475569;line-height:1.3;padding-top:4px;font-style:italic;">${fData.caption}</div>` : ''}
            </div>
        `;
    } else if (fData.type === 'quote') {
        el.innerHTML = `<div class="frame-quote-inner" contenteditable="true">${fData.content || '"Cita destacada periodística..."'}</div>`;
    } else if (fData.type === 'box') {
        el.innerHTML = `<div class="frame-box-inner" contenteditable="true">${fData.content || 'Caja de contenido'}</div>`;
    } else if (fData.type === 'divider') {
        el.innerHTML = `<div class="frame-divider-inner"><div style="width:100%;height:2px;background:${fData.color || '#cbd5e1'};"></div></div>`;
    } else {
        // Default text frame
        const cols = fData.columns || 1;
        el.innerHTML = `<div class="frame-text-inner" contenteditable="true" style="column-count:${cols > 1 ? cols : ''};column-gap:14px;">${fData.content || 'Texto periodístico...'}</div>`;
    }

    // Handles
    ['nw','n','ne','e','se','s','sw','w'].forEach(pos => {
        const handle = document.createElement('div');
        handle.className = `resize-handle ${pos}`;
        el.appendChild(handle);
    });

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
                move(event) {
                    const target = event.target;
                    const x = (parseFloat(target.style.left) || 0) + event.dx;
                    const y = (parseFloat(target.style.top)  || 0) + event.dy;
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
                move(event) {
                    const target = event.target;
                    let x = parseFloat(target.style.left) || 0;
                    let y = parseFloat(target.style.top)  || 0;

                    target.style.width  = Math.max(40, event.rect.width) + 'px';
                    target.style.height = Math.max(20, event.rect.height) + 'px';

                    x += event.deltaRect.left;
                    y += event.deltaRect.top;

                    target.style.left = Math.max(0, x) + 'px';
                    target.style.top  = Math.max(0, y) + 'px';

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

    document.getElementById('inspMasthead').style.display = type === 'masthead' ? '' : 'none';
    document.getElementById('inspText').style.display = (type === 'article' || type === 'headline' || type === 'text') ? '' : 'none';
    document.getElementById('inspImage').style.display = type === 'image' ? '' : 'none';
    document.getElementById('inspActions').style.display = '';

    if (type === 'masthead') {
        document.getElementById('inspMastheadName').value = fData.newspaperName || 'LA ESTRELLA';
        document.getElementById('inspMastheadBadge').value = fData.subBadge || 'del Oriente';
        document.getElementById('inspMastheadMotto').value = fData.motto || '';
        document.getElementById('inspMastheadLeftEar').value = fData.leftEar || '';
        document.getElementById('inspMastheadRightEar').value = fData.rightEar || '';
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

// ── LAYER ACTIONS ──────────────────────────────────
function bringToFront() {
    if (!selectedFrame) return;
    let maxZ = 10;
    document.querySelectorAll('.id-frame').forEach(f => {
        maxZ = Math.max(maxZ, parseInt(f.style.zIndex) || 10);
    });
    selectedFrame.style.zIndex = maxZ + 1;
    saveFrameZIndex(selectedFrame);
    markUnsaved();
}

function sendToBack() {
    if (!selectedFrame) return;
    selectedFrame.style.zIndex = 2;
    saveFrameZIndex(selectedFrame);
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

// ── TEMPLATES LIBRARY (BOLIVIA & LATITUD 18) ────────
function applyTemplate(type) {
    closeModal('templateModal');
    recordHistory();

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    sheet.querySelectorAll('.id-frame').forEach(el => el.remove());
    currentEdicion.paginas[activePageIndex].frames = [];

    const templates = {
        la_estrella_portada: () => {
            // 1. Cabecera Oficial
            createCustomFrame({ type:'masthead', x:20, y:20, w:680, h:112, z:10,
                newspaperName:'LA ESTRELLA', subBadge:'del Oriente',
                motto:'EL PRIMER PERIÓDICO DE SANTA CRUZ • FUNDADO EN 1864',
                editionDate:'Santa Cruz de la Sierra • ' + (currentEdicion.fecha || 'Domingo 6 de septiembre de 2026'),
                editionNumber: currentEdicion.numero_edicion || 'N° 11.986 • 32 páginas',
                price:'Precio en todo el país Bs 7,00',
                leftEar:'CRE 100% Tarifa Equitativa en Santa Cruz',
                rightEar:'DÓLAR BOLIVIA: Bs 12,58'
            });

            // 2. Titular Principal de Impacto con Kicker
            createCustomFrame({ type:'headline', x:20, y:140, w:680, h:110, z:9,
                kicker:'SEGURIDAD NACIONAL',
                content:'<p style="font-family:\'Oswald\',sans-serif;font-size:34px;font-weight:700;line-height:1.08;color:#09090b;margin:0;">Viacha: hallan booster, pólvora negra y material bélico en zona afectada por explosiones</p>'
            });

            // 3. Sumario en 4 Columnas con Filetes
            createCustomFrame({ type:'article', x:20, y:260, w:680, h:85, z:8, columns:4,
                content:'<p style="font-family:\'Source Sans 3\',sans-serif;font-size:11px;line-height:1.4;text-align:justify;color:#1e293b;margin:0;"><strong>RIESGO.</strong> La zona afectada por las explosiones en Viacha continúa en alto riesgo debido al hallazgo de pólvora negra y boosters de alto poder.<br><br><strong>INSPECCIÓN.</strong> El ministro de Defensa Ernesto Justiniano confirmó el uso de drones para identificar depósitos secundarios de munición militar. ► PÁG. 6</p>'
            });

            // 4. Filete Divisor
            createCustomFrame({ type:'divider', x:20, y:355, w:680, h:4, z:5, color:'#cbd5e1' });

            // 5. Fotonoticia Dominante
            createCustomFrame({ type:'image', x:20, y:370, w:450, h:380, z:7,
                src:'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1000&q=80',
                caption:'CELEBRACIÓN. Una noche de música, danza y tradición reunió a más de 600 personas en la retreta cultural organizada por la CRE en homenaje al grito libertario de 1810 frente al "Arco de la Cruceñidad". ► PÁG. 3'
            });

            // 6. Columna Lateral de Noticias
            createCustomFrame({ type:'box', x:485, y:370, w:215, h:185, z:6,
                content:'<span style="font-size:9.5px;font-weight:800;color:#0B1F3A;text-transform:uppercase;">SEGURIDAD</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:15px;font-weight:700;line-height:1.2;margin:4px 0;">SURTIDOR OCULTÓ 3.000 LITROS DE COMBUSTIBLE</h6><p style="font-size:10.5px;color:#475569;line-height:1.35;margin:0;">Un surtidor de YPFB en Cabezas habría ocultado miles de litros de gasolina. ► PÁG. 10</p>'
            });

            createCustomFrame({ type:'box', x:485, y:565, w:215, h:185, z:6,
                content:'<span style="font-size:9.5px;font-weight:800;color:#D71920;text-transform:uppercase;">DEPORTES</span><h6 style="font-family:\'Oswald\',sans-serif;font-size:15px;font-weight:700;line-height:1.2;margin:4px 0;">ORIENTE PETROLERO PIERDE 3 PUNTOS POR DEUDA</h6><p style="font-size:10.5px;color:#475569;line-height:1.35;margin:0;">El tribunal falló en contra por deuda pendiente con Diego Bejarano. ► PÁG. 15</p>'
            });

            // 7. Cintillo Inferior
            createCustomFrame({ type:'box', x:20, y:765, w:680, h:45, z:5,
                content:'<div style="display:flex;align-items:center;gap:8px;"><span style="background:#D71920;color:#fff;padding:2px 6px;font-size:9px;font-weight:800;">ALERTA</span><span style="font-size:11px;font-weight:700;color:#0f172a;">MENOR ABUSADA SEXUALMENTE FALLECE POR ENFERMEDAD DE TRANSMISIÓN ► PÁG. 9</span></div>'
            });
        },

        latitud18_broadsheet: () => {
            changePaperFormat('broadsheet');
            createCustomFrame({ type:'masthead', x:20, y:20, w:780, h:110, z:10,
                newspaperName:'LATITUD 18', subBadge:'EDICIÓN SEMANAL',
                motto:'PERIODISMO INDEPENDIENTE • INFORMACIÓN SIN RUIDO',
                editionDate:'Santa Cruz de la Sierra • Bolivia', editionNumber:'Edición Central', price:'Bs 7,00',
                leftEar:'PORTAL 24/7 EN VIVO', rightEar:'COTIZACIÓN: Bs 12,58'
            });
            createCustomFrame({ type:'headline', x:20, y:140, w:780, h:100, z:9,
                kicker:'INFORME ESPECIAL',
                content:'<p style="font-family:\'Playfair Display\',serif;font-size:38px;font-weight:900;line-height:1.05;color:#0B1F3A;margin:0;">Crecimiento económico y reactivación productiva en el oriente boliviano</p>'
            });
            createCustomFrame({ type:'article', x:20, y:250, w:780, h:200, z:8, columns:5,
                content:'<p style="font-family:\'Source Sans 3\',sans-serif;font-size:12px;line-height:1.5;text-align:justify;color:#1e293b;">Análisis exhaustivo sobre el desempeño de los sectores agroindustrial, forestal y tecnológico en el departamento de Santa Cruz durante el último trimestre.</p>'
            });
        },

        editorial_opinion: () => {
            createCustomFrame({ type:'box', x:20, y:20, w:680, h:36, z:5,
                content:'<div style="background:#0B1F3A;color:#fff;padding:6px 12px;font-family:\'Anton\',sans-serif;font-size:18px;letter-spacing:1px;">EDITORIAL & OPINIÓN REGIONAL</div>'
            });
            createCustomFrame({ type:'headline', x:20, y:65, w:450, h:60, z:6,
                content:'<p style="font-family:\'Playfair Display\',serif;font-size:24px;font-weight:900;font-style:italic;color:#0B1F3A;margin:0;">Preocupante inseguridad en recintos oficiales</p>'
            });
            createCustomFrame({ type:'article', x:20, y:130, w:450, h:380, z:6, columns:2,
                content:'<p style="font-family:\'Source Sans 3\',sans-serif;font-size:12.5px;line-height:1.6;text-align:justify;color:#1e293b;"><span style="font-family:\'Anton\',sans-serif;font-size:38px;float:left;line-height:0.8;margin-right:6px;color:#D71920;">E</span>n los últimos meses se registraron cuatro incidentes relacionados con la seguridad en instalaciones públicas. El último ocurrió en Viacha, encendiendo las alarmas sobre las condiciones de control.</p>'
            });
            createCustomFrame({ type:'quote', x:485, y:65, w:215, h:150, z:7,
                content:'<p style="font-size:14px;font-style:italic;line-height:1.35;margin:0;">"No solo se debe aclarar lo sucedido, sino adoptar medidas preventivas urgentes."</p><small style="display:block;margin-top:8px;font-weight:800;color:#D71920;">— CONSEJO EDITORIAL</small>'
            });
        },

        comunidad_cultura: () => {
            createCustomFrame({ type:'box', x:20, y:20, w:680, h:34, z:5,
                content:'<div style="background:#0284c7;color:#fff;padding:6px 12px;font-family:\'Oswald\',sans-serif;font-size:16px;font-weight:700;">COMUNIDAD & IDENTIDAD CRUCEÑA</div>'
            });
            createCustomFrame({ type:'image', x:20, y:65, w:680, h:320, z:6,
                src:'https://images.unsplash.com/photo-1511578314322-379afb476865?w=1000&q=80',
                caption:'TRADICIÓN. Más de 600 personas disfrutaron de taquiraris y danzas tradicionales.'
            });
            createCustomFrame({ type:'article', x:20, y:400, w:680, h:200, z:6, columns:4,
                content:'<p style="font-size:11.5px;line-height:1.45;text-align:justify;color:#1e293b;">Una velada histórica con música y cultura reunió a las familias de Santa Cruz en el paseo del Arco de la Cruceñidad.</p>'
            });
        },

        negocios_economia: () => {
            createCustomFrame({ type:'box', x:20, y:20, w:680, h:34, z:5,
                content:'<div style="background:#059669;color:#fff;padding:6px 12px;font-family:\'Oswald\',sans-serif;font-size:16px;font-weight:700;">NEGOCIOS & ECONOMÍA</div>'
            });
            createCustomFrame({ type:'headline', x:20, y:65, w:680, h:80, z:6,
                kicker:'RUEDA DE NEGOCIOS 2026',
                content:'<p style="font-family:\'Oswald\',sans-serif;font-size:28px;font-weight:700;color:#064e3b;margin:0;">Encuentro forestal cierra con $us 3,5 MM en intenciones comerciales</p>'
            });
            createCustomFrame({ type:'article', x:20, y:150, w:680, h:220, z:6, columns:4,
                content:'<p style="font-size:11.5px;line-height:1.45;text-align:justify;color:#1e293b;">Durante cuatro horas de intensas negociaciones, 195 empresas nacionales e internacionales consolidaron acuerdos para la industria forestal sostenible.</p>'
            });
        },

        blank: () => {}
    };

    if (templates[type]) templates[type]();
    markUnsaved();
    showToast('Plantilla aplicada exitosamente', 'success');
}

function createCustomFrame(fData) {
    fData.id = 'f-' + (++frameIdCounter);
    if (!currentEdicion.paginas[activePageIndex].frames) {
        currentEdicion.paginas[activePageIndex].frames = [];
    }
    currentEdicion.paginas[activePageIndex].frames.push(fData);

    const sheet = document.querySelector(`.id-paper-sheet[data-page="${activePageIndex}"]`);
    const el = buildFrameElement(fData, activePageIndex);
    sheet.appendChild(el);
}

// ── INSERT NEWS FROM DATABASE ──────────────────────
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
                content: `<p style="font-size:11.5px;line-height:1.45;text-align:justify;color:#1e293b;">${news.contenido || news.bajada}</p>`
            });
        } else {
            createCustomFrame({ type:'article', x:20, y:185, w:680, h:200, z:7, columns:3,
                content: `<p style="font-size:11.5px;line-height:1.45;text-align:justify;color:#1e293b;">${news.contenido || news.bajada}</p>`
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

// ── ZOOM & VIEWPORT CONTROLS ───────────────────────
function changeZoom(delta) {
    zoomLevel = Math.max(0.3, Math.min(2.5, zoomLevel + delta));
    applyZoom();
}

function resetZoom() {
    zoomLevel = 1.0;
    applyZoom();
}

function applyZoom() {
    document.querySelectorAll('.id-paper-sheet').forEach(sheet => {
        sheet.style.transform = `scale(${zoomLevel})`;
        sheet.style.transformOrigin = 'top center';
        sheet.style.marginBottom = `${(zoomLevel - 1) * 1040}px`;
    });
    updateZoomDisplay();
}

function updateZoomDisplay() {
    document.getElementById('zoomDisplay').textContent = Math.round(zoomLevel * 100) + '%';
}

function toggleRulers() {
    rulersVisible = !rulersVisible;
    document.getElementById('canvasStage').classList.toggle('rulers-visible', rulersVisible);
    document.getElementById('rulerToggleBtn').classList.toggle('active', rulersVisible);
}

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

// ── UNDO / REDO ────────────────────────────────────
function recordHistory() {
    undoHistory.push(JSON.parse(JSON.stringify(currentEdicion)));
    if (undoHistory.length > 25) undoHistory.shift();
    redoHistory = [];
}

function undoAction() {
    if (undoHistory.length === 0) return;
    redoHistory.push(JSON.parse(JSON.stringify(currentEdicion)));
    currentEdicion = undoHistory.pop();
    renderAllPages();
    switchPage(activePageIndex);
    markUnsaved();
    showToast('Acción deshecha', 'success');
}

function redoAction() {
    if (redoHistory.length === 0) return;
    undoHistory.push(JSON.parse(JSON.stringify(currentEdicion)));
    currentEdicion = redoHistory.pop();
    renderAllPages();
    switchPage(activePageIndex);
    markUnsaved();
    showToast('Acción rehecha', 'success');
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

        if (e.ctrlKey && e.key === 'z') { e.preventDefault(); undoAction(); }
        if (e.ctrlKey && e.key === 'y') { e.preventDefault(); redoAction(); }
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
        if (e.key === 'l' || e.key === 'L') createSpecialElement('divider');
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

function exportPdfDirect(scope) {
    closeModal('pdfExportModal');
    showToast('Generando vista de impresión y PDF...', 'success');
    window.print();
}

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