<!-- ========== MODAL: PERIÓDICO DIGITAL ========== -->
<div class="newspaper-modal-overlay" id="newspaper-modal-suite">
  <div class="newspaper-modal-header">
    <div style="display:flex;align-items:center;gap:12px">
      <div><span style="font-family:var(--font-title-anton);font-size:1.4rem;color:#fff">LATITUD</span><span style="font-family:var(--font-title-anton);font-size:.9rem;background:var(--color-red);color:#fff;padding:1px 6px;border-radius:2px">18</span></div>
      <div>
        <div style="font-family:var(--font-title-montserrat);font-weight:800;font-size:.82rem;letter-spacing:1px;color:#fff">PERIÓDICO DIGITAL SEMANAL</div>
        <div style="font-size:.68rem;color:#CBD5E1">Edición Tabloide • 5 Columnas • Santa Cruz de la Sierra</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <div class="view-mode-selector">
        <button class="btn-mode-tab active" id="btn-mode-single"><i class="fas fa-file"></i> Pág. Individual</button>
        <button class="btn-mode-tab" id="btn-mode-all"><i class="fas fa-book-open"></i> Ver 12 Págs</button>
      </div>
      <button class="btn-newspaper-print" onclick="window.print()" title="Imprimir"><i class="fas fa-print"></i> Imprimir</button>
      <button class="btn-pdf-download" id="btn-download-full-pdf" title="Descargar PDF"><i class="fas fa-download"></i> Descargar PDF</button>
      <button class="btn-close-newspaper-modal" onclick="closeNewspaperModal()" title="Cerrar"><i class="fas fa-times"></i></button>
    </div>
  </div>
  <div style="background:var(--color-navy);padding:10px 24px;border-bottom:1px solid rgba(255,255,255,.1)">
    <div class="newspaper-pages-strip" id="newspaper-thumbs-strip"></div>
  </div>
  <div class="newspaper-modal-body-scroll">
    <div class="newspaper-stage">
      <div id="newspaper-print-document">
        <!-- Portada -->
        <div class="newspaper-sheet" data-page="1">
          <div class="paper-top-folio"><span>LATITUD 18</span><span>Edición Semanal</span><span>Santa Cruz de la Sierra</span></div>
          <div style="text-align:center;padding:12px 0"><div class="paper-masthead-title">LATITUD<span>18</span></div><div class="paper-masthead-slogan">INFORMACIÓN SIN RUIDO</div></div>
          <div class="cover-hero-image-wrap" style="height:300px;background:linear-gradient(135deg,var(--color-navy),var(--color-navy-light));display:flex;align-items:center;justify-content:center">
            <div style="text-align:center;color:#fff"><i class="fas fa-newspaper" style="font-size:3rem;opacity:.3;margin-bottom:8px;display:block"></i><span style="font-family:var(--font-title-bebas);font-size:1.5rem;opacity:.5">PORTADA</span></div>
          </div>
          <div style="padding:16px 0">
            <div class="cover-section-pill">DESTACADO</div>
            <h2 style="font-family:var(--font-title-bebas);font-size:1.8rem;color:var(--color-navy);line-height:1;margin-bottom:8px">TITULAR PRINCIPAL DE LA EDICIÓN SEMANAL</h2>
            <p class="paper-body-text">La información más importante de la semana en Santa Cruz de la Sierra y Bolivia. Análisis, reportajes y las noticias que importan.</p>
          </div>
          <div class="cover-sub-stories">
            <div class="cover-sub-card"><h4>Economía regional crece</h4><p>Indicadores muestran recuperación del sector productivo cruceño.</p></div>
            <div class="cover-sub-card"><h4>Nuevas inversiones</h4><p>Empresas internacionales apuestan por el desarrollo sostenible.</p></div>
            <div class="cover-sub-card"><h4>Deportes en alto</h4><p>Club Always Ready clasifica a fase internacional.</p></div>
          </div>
          <div class="paper-bottom-footer"><span>LATITUD 18 • Página 1</span><span>latitud18.com</span></div>
        </div>
        <!-- Páginas interiores -->
        @for($i = 2; $i <= 12; $i++)
        <div class="newspaper-sheet" data-page="{{ $i }}" style="display:none">
          <div class="paper-top-folio"><span>LATITUD 18</span><span>Página {{ $i }}</span><span>Edición Semanal</span></div>
          <div class="paper-inner-header">{{ ['','PORTADA','EDITORIAL','POLÍTICA','POLÍTICA','SANTA CRUZ','SANTA CRUZ','PAÍS','PAÍS','ECONOMÍA','ECONOMÍA','SEGURIDAD'][$i] ?? 'MUNDO' }}</div>
          <div style="padding:8px 0"><h3 class="paper-article-head">Contenido de la sección — Página {{ $i }}</h3><p class="paper-body-text dropcap">La información periodística de calidad es la base de una sociedad informada. En esta edición presentamos los análisis más profundos sobre los acontecimientos que marcan la agenda pública regional y nacional.</p>
            <div class="numbered-points-box"><strong>Puntos Clave:</strong><br>• Análisis de la situación política actual<br>• Impacto económico en el sector productivo<br>• Perspectivas para los próximos meses</div>
          </div>
          <div class="paper-bottom-footer"><span>LATITUD 18 • Página {{ $i }}</span><span>latitud18.com</span></div>
        </div>
        @endfor
      </div>
    </div>
  </div>
</div>
