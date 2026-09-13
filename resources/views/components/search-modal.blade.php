<!-- ========== MODAL: BUSCADOR ========== -->
<div class="modal-overlay" id="search-modal-suite">
  <div class="modal-reader-card">
    <div class="modal-header-bar">
      <span><i class="fas fa-search" style="margin-right:6px"></i> BUSCADOR EN TIEMPO REAL</span>
      <button class="btn-modal-control" onclick="closeSearchModal()"><i class="fas fa-times"></i></button>
    </div>
    <div style="padding:20px">
      <form action="{{ route('search') }}" method="GET">
        <input type="text" name="q" id="site-search-input" placeholder="Buscar por tema, palabra clave o personaje..." style="width:100%;padding:12px;font-size:1rem;font-family:var(--font-body);border:2px solid var(--color-navy);border-radius:2px;outline:none;background:var(--color-card-bg);color:var(--color-text-main);transition:border-color .2s" onfocus="this.style.borderColor='var(--color-red)'" onblur="this.style.borderColor='var(--color-navy)'">
      </form>
    </div>
    <div id="site-search-results-list" style="max-height:350px;overflow-y:auto;padding:0 20px 20px">
      <div style="padding:20px;color:var(--color-text-muted);text-align:center;font-size:.88rem">Escribe para buscar noticias, economía o reportajes...</div>
    </div>
  </div>
</div>
