{{-- ============================================================
     EXPERIENCIA DE LECTURA MEJORADA (referencia: elmundo.com.bo)
     - Barra de progreso de lectura
     - Header compacto sticky al hacer scroll
     - Modo lectura nocturna (sepia) / nocturnal
     - Columna de lectura enfocada (68ch) / ancha
     - Tipografía serif / sans + interlineado
     - Key Points (resumen ejecutivo)
     - Pull Quote automático
     - Tabla de contenido automática
     - Social share flotante
     - "Volver arriba" y volver a leer
     ============================================================ --}}

{{-- 1) BARRA DE PROGRESO + BACK TO TOP --}}
<div id="readProgressBar" aria-hidden="true"><span id="readProgressFill"></span></div>

<button id="readBackToTop" type="button" aria-label="Volver arriba" title="Volver arriba">
  <i class="fas fa-chevron-up"></i>
</button>

{{-- 2) HEADER COMPACTO STICKY --}}
<div id="readStickyHeader" aria-hidden="true">
  <div class="rsh-inner">
    <a href="{{ route('portada') }}" class="rsh-brand">LATITUD<span>18</span></a>
    <div class="rsh-divider"></div>
    <span class="rsh-cat" id="rshCat">{{ $noticia->category->name ?? 'General' }}</span>
    <h2 class="rsh-title">{{ Str::limit($noticia->titulo, 70) }}</h2>
    <div class="rsh-actions">
      <span class="rsh-read" id="rshReadTime">{{ ceil(str_word_count(strip_tags($noticia->contenido)) / 200) }} min</span>
      <button type="button" class="rsh-btn" id="rshNightBtn" title="Modo lectura nocturna" aria-label="Modo lectura nocturna">
        <i class="fas fa-moon"></i>
      </button>
    </div>
  </div>
</div>

{{-- 3) SOCIAL SHARE FLOTANTE --}}
<div id="readShareRail" aria-label="Compartir artículo">
  <span class="rsr-label">Compartir</span>
  <a href="https://twitter.com/intent/tweet?text={{ urlencode($noticia->titulo) }}&url={{ urlencode($noticia->url) }}"
     target="_blank" class="rsr-btn" data-net="x" title="Compartir en X" aria-label="X"><i class="fab fa-x-twitter"></i></a>
  <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($noticia->url) }}"
     target="_blank" class="rsr-btn" data-net="fb" title="Compartir en Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
  <a href="https://api.whatsapp.com/send?text={{ urlencode($noticia->titulo . ' ' . $noticia->url) }}"
     target="_blank" class="rsr-btn" data-net="wa" title="Compartir en WhatsApp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
  <button type="button" class="rsr-btn" data-net="copy" data-url="{{ $noticia->url ?? url()->current() }}" title="Copiar enlace" aria-label="Copiar enlace"><i class="fas fa-link"></i></button>
</div>

<style>
/* ============================================================
   TOKENS
   ============================================================ */
#readExperience {
  --rx-scale: 1;
  --rx-lh: 1.85;
  --rx-measure: 68ch;
  --rx-serif: 'Source Serif 4', Georgia, 'Times New Roman', serif;
  --rx-sans: var(--font-body, 'Source Sans 3', system-ui, sans-serif);
  --rx-ink: #1A1A2E;
  --rx-ink-soft: #3F4654;
  --rx-paper: #FFFFFF;
  --rx-paper-2: #FAF9F7;
  --rx-line: #E5E7EB;
  --rx-accent: var(--color-red, #D71920);
  --rx-navy: var(--color-navy, #0B1F3A);
}

/* Modo NOCTURNO (sepia cálido, sin brillo azul) */
html[data-read="night"] #readExperience {
  --rx-ink: #E8DFCB;
  --rx-ink-soft: #C4B99F;
  --rx-paper: #1A1613;
  --rx-paper-2: #221D18;
  --rx-line: #3A322A;
  --rx-accent: #E06060;
  --rx-navy: #D8CFB8;
}
/* Modo NOCTURNO PURO (oscuro) */
html[data-read="dark"] #readExperience {
  --rx-ink: #D8DEE9;
  --rx-ink-soft: #9AA6B8;
  --rx-paper: #0D1219;
  --rx-paper-2: #131A24;
  --rx-line: #253040;
  --rx-accent: #FF5A62;
  --rx-navy: #AFBED4;
}

/* ============================================================
   BARRA DE PROGRESO
   ============================================================ */
#readProgressBar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: transparent;
  z-index: 9998;
  pointer-events: none;
}
#readProgressFill {
  display: block;
  height: 100%;
  width: 0%;
  background: linear-gradient(90deg, var(--rx-accent) 0%, #F0A500 100%);
  transition: width 0.08s linear;
}

/* ============================================================
   BACK TO TOP
   ============================================================ */
#readBackToTop {
  position: fixed;
  right: 18px; bottom: 18px;
  width: 42px; height: 42px;
  border: none;
  border-radius: 50%;
  background: var(--color-navy, #0B1F3A);
  color: #fff;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 14px rgba(0,0,0,0.22);
  opacity: 0; visibility: hidden;
  transform: translateY(12px);
  transition: all 0.25s;
  z-index: 9997;
}
#readBackToTop.show { opacity: 1; visibility: visible; transform: translateY(0); }
#readBackToTop:hover { background: var(--color-red, #D71920); }

/* ============================================================
   HEADER COMPACTO STICKY
   ============================================================ */
#readStickyHeader {
  position: fixed;
  top: 3px; left: 0; right: 0;
  background: var(--color-card-bg, #fff);
  border-bottom: 1px solid var(--color-border, #E5E7EB);
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  transform: translateY(-102%);
  transition: transform 0.28s cubic-bezier(0.4,0,0.2,1);
  z-index: 9996;
}
#readStickyHeader.show { transform: translateY(0); }
.rsh-inner {
  max-width: 1240px;
  margin: 0 auto;
  padding: 8px 16px;
  display: flex; align-items: center; gap: 10px;
}
.rsh-brand {
  font-family: var(--font-title-anton, 'Anton'), sans-serif;
  font-size: 1.05rem;
  color: var(--color-navy, #0B1F3A);
  text-decoration: none;
  letter-spacing: 1px;
  flex-shrink: 0;
}
.rsh-brand span { background: var(--color-red, #D71920); color: #fff; padding: 0 4px; border-radius: 2px; font-size: 0.8rem; }
.rsh-divider { width: 1px; height: 18px; background: var(--color-border, #E5E7EB); flex-shrink: 0; }
.rsh-cat {
  font-family: var(--font-title-montserrat, sans-serif);
  font-weight: 800; font-size: 0.6rem;
  text-transform: uppercase; letter-spacing: 0.8px;
  color: var(--color-red, #D71920);
  flex-shrink: 0;
}
.rsh-title {
  font-family: var(--font-title-montserrat, sans-serif);
  font-weight: 700; font-size: 0.82rem;
  color: var(--color-text-main, #1A1A2E);
  margin: 0;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  flex: 1; min-width: 0;
}
.rsh-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.rsh-read { font-size: 0.68rem; color: var(--color-text-muted, #6B7280); white-space: nowrap; }
.rsh-btn {
  width: 30px; height: 30px;
  border: 1px solid var(--color-border, #E5E7EB);
  border-radius: 4px;
  background: transparent;
  color: var(--color-text-secondary, #3F4654);
  cursor: pointer; font-size: 0.75rem;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.rsh-btn:hover { border-color: var(--color-red, #D71920); color: var(--color-red, #D71920); }
html[data-read="night"] .rsh-btn, html[data-read="dark"] .rsh-btn {
  background: rgba(255,255,255,0.05); border-color: var(--rx-line); color: var(--rx-ink);
}

/* ============================================================
   SOCIAL SHARE FLOTANTE
   ============================================================ */
#readShareRail {
  position: fixed;
  left: 14px;
  top: 50%;
  transform: translateY(-50%) translateX(-140%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7px;
  padding: 12px 8px;
  background: var(--color-card-bg, #fff);
  border: 1px solid var(--color-border, #E5E7EB);
  border-radius: 8px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.1);
  transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  z-index: 9995;
}
#readShareRail.show { transform: translateY(-50%) translateX(0); }
.rsr-label {
  font-family: var(--font-title-montserrat, sans-serif);
  font-weight: 800; font-size: 0.52rem;
  text-transform: uppercase; letter-spacing: 1px;
  color: var(--color-text-muted, #6B7280);
  writing-mode: vertical-rl;
  margin-bottom: 4px;
}
.rsr-btn {
  width: 32px; height: 32px;
  border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  color: var(--color-text-muted, #6B7280);
  background: var(--color-navy-subtle, #F1F3F7);
  text-decoration: none;
  border: none; cursor: pointer;
  font-size: 0.78rem;
  transition: all 0.2s;
}
.rsr-btn:hover { color: #fff; }
.rsr-btn[data-net="x"]:hover  { background: #1DA1F2; }
.rsr-btn[data-net="fb"]:hover { background: #1877F2; }
.rsr-btn[data-net="wa"]:hover { background: #25D366; }
.rsr-btn[data-net="copy"]:hover { background: var(--color-navy, #0B1F3A); }
.rsr-btn.flash { background: #10B981 !important; color: #fff !important; }
html[data-read="night"] .rsr-btn, html[data-read="dark"] .rsr-btn {
  background: rgba(255,255,255,0.06); color: var(--rx-ink); border: 1px solid var(--rx-line);
}

/* ============================================================
   KEY POINTS (resumen ejecutivo)
   ============================================================ */
.rx-keypoints {
  background: var(--rx-paper-2);
  border: 1px solid var(--rx-line);
  border-left: 4px solid var(--rx-accent);
  border-radius: 4px;
  padding: 18px 20px;
  margin: 0 0 28px;
}
.rx-keypoints-head {
  display: flex; align-items: center; justify-content: space-between;
  gap: 10px; margin-bottom: 12px;
}
.rx-keypoints-title {
  font-family: var(--font-title-montserrat, sans-serif);
  font-weight: 900; font-size: 0.72rem;
  text-transform: uppercase; letter-spacing: 1.2px;
  color: var(--rx-accent);
  display: flex; align-items: center; gap: 7px;
  margin: 0;
}
.rx-kp-toggle {
  background: transparent;
  border: 1px solid var(--rx-line);
  border-radius: 3px;
  color: var(--color-text-muted, #6B7280);
  font-size: 0.62rem; font-weight: 700;
  padding: 3px 8px; cursor: pointer;
  text-transform: uppercase; letter-spacing: 0.5px;
  transition: all 0.2s;
}
.rx-kp-toggle:hover { border-color: var(--rx-accent); color: var(--rx-accent); }
.rx-kp-list { margin: 0; padding: 0; list-style: none; }
.rx-kp-list li {
  position: relative;
  padding: 7px 0 7px 24px;
  font-family: var(--rx-sans);
  font-size: 0.9rem;
  line-height: 1.6;
  color: var(--rx-ink-soft);
  border-bottom: 1px dashed var(--rx-line);
}
.rx-kp-list li:last-child { border-bottom: none; }
.rx-kp-list li::before {
  content: '';
  position: absolute; left: 6px; top: 15px;
  width: 6px; height: 6px;
  background: var(--rx-accent);
  border-radius: 50%;
}
.rx-kp-list.collapsed { display: none; }

/* ============================================================
   TABLA DE CONTENIDO
   ============================================================ */
.rx-toc {
  background: var(--rx-paper-2);
  border: 1px solid var(--rx-line);
  border-radius: 4px;
  padding: 16px 18px;
  margin: 0 0 28px;
}
.rx-toc-title {
  font-family: var(--font-title-montserrat, sans-serif);
  font-weight: 900; font-size: 0.72rem;
  text-transform: uppercase; letter-spacing: 1.2px;
  color: var(--rx-navy);
  margin: 0 0 10px;
  display: flex; align-items: center; gap: 7px;
  cursor: pointer;
}
.rx-toc-title i { transition: transform 0.2s; font-size: 0.65rem; }
.rx-toc.collapsed .rx-toc-title i { transform: rotate(-90deg); }
.rx-toc.collapsed .rx-toc-list { display: none; }
.rx-toc-list { margin: 0; padding: 0; list-style: none; columns: 2; column-gap: 24px; }
.rx-toc-list li { break-inside: avoid; margin-bottom: 4px; }
.rx-toc-list a {
  font-family: var(--rx-sans);
  font-size: 0.82rem;
  color: var(--rx-ink-soft);
  text-decoration: none;
  display: flex; align-items: baseline; gap: 7px;
  padding: 3px 0;
  transition: color 0.2s;
}
.rx-toc-list a::before {
  content: counter(rx-toc, decimal-leading-zero);
  counter-increment: rx-toc;
  font-family: var(--font-title-bebas, 'Bebas Neue'), sans-serif;
  font-size: 0.85rem;
  color: var(--rx-accent);
  flex-shrink: 0;
}
.rx-toc-list { counter-reset: rx-toc; }
.rx-toc-list a:hover { color: var(--rx-accent); }
.rx-toc-list li.lvl-3 a { padding-left: 14px; font-size: 0.78rem; opacity: 0.85; }

/* ============================================================
   COLUMN / TIPOGRAFÍA / COLOR DEL CUERPO
   ============================================================ */
.rx-reader {
  font-family: var(--rx-serif);
  font-size: calc(1.12rem * var(--rx-scale));
  line-height: var(--rx-lh);
  color: var(--rx-ink);
  max-width: var(--rx-measure);
  margin: 0 auto 32px;
  transition: max-width 0.3s, font-size 0.2s;
}
html[data-read="serif"] .rx-reader { font-family: var(--rx-serif); }
html[data-read="sans"]  .rx-reader { font-family: var(--rx-sans); font-size: calc(1.06rem * var(--rx-scale)); }

.rx-reader.wide { --rx-measure: 100%; }
.rx-reader p { margin-bottom: 1.25em; text-align: justify; hyphens: auto; }
.rx-reader p:last-child { margin-bottom: 0; }
.rx-reader a { color: var(--rx-accent); text-decoration: underline; text-underline-offset: 2px; }
.rx-reader h2, .rx-reader h3, .rx-reader h4 {
  font-family: var(--font-title-montserrat, sans-serif);
  color: var(--rx-navy);
  line-height: 1.25;
  margin: 1.6em 0 0.6em;
  scroll-margin-top: 70px;
}
.rx-reader h2 { font-size: 1.35rem; font-weight: 800; padding-bottom: 6px; border-bottom: 2px solid var(--rx-accent); display: inline-block; }
.rx-reader h3 { font-size: 1.12rem; font-weight: 800; }
.rx-reader h4 { font-size: 1rem; font-weight: 700; }
.rx-reader img, .rx-reader video, .rx-reader iframe { max-width: 100%; height: auto; }
.rx-reader ul, .rx-reader ol { margin: 0 0 1.25em; padding-left: 1.4em; }
.rx-reader li { margin-bottom: 0.5em; }
.rx-reader blockquote {
  margin: 1.6em 0;
  padding: 4px 0 4px 18px;
  border-left: 4px solid var(--rx-accent);
  font-style: italic;
  color: var(--rx-ink-soft);
}
.rx-reader hr { border: none; border-top: 1px solid var(--rx-line); margin: 2em 0; }

/* Drop cap elegante */
.rx-reader > p:first-of-type::first-letter,
.rx-reader > p.rx-dropcap::first-letter {
  font-family: var(--font-title-anton, 'Anton'), sans-serif;
  font-size: 4.2em;
  float: left;
  line-height: 0.78;
  margin: 0.06em 0.09em 0 0;
  color: var(--rx-accent);
  font-weight: 400;
}

/* PULL QUOTE */
.rx-reader blockquote.rx-pullquote {
  float: left;
  width: 100%;
  margin: 1.8em 0;
  padding: 22px 26px;
  background: var(--rx-paper-2);
  border: 1px solid var(--rx-line);
  border-left: 5px solid var(--rx-accent);
  border-radius: 3px;
  font-style: normal;
}
.rx-pullquote .rx-pq-text {
  font-family: var(--font-title-anton, 'Anton'), sans-serif;
  font-size: 1.42rem;
  line-height: 1.32;
  color: var(--rx-navy);
  margin: 0 0 8px;
  letter-spacing: -0.2px;
}
.rx-pullquote .rx-pq-attr {
  font-family: var(--font-title-montserrat, sans-serif);
  font-size: 0.66rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1px;
  color: var(--rx-accent);
  display: flex; align-items: center; gap: 6px;
}
.rx-pullquote .rx-pq-attr::before { content: ''; width: 22px; height: 2px; background: var(--rx-accent); }

/* ============================================================
   TOAST
   ============================================================ */
.rx-toast {
  position: fixed;
  bottom: 18px; left: 50%;
  transform: translate(-50%, 90px);
  background: var(--color-navy, #0B1F3A);
  color: #fff;
  padding: 11px 20px;
  border-radius: 3px;
  font-family: var(--font-title-montserrat, sans-serif);
  font-weight: 700; font-size: 0.8rem;
  display: flex; align-items: center; gap: 8px;
  box-shadow: 0 6px 22px rgba(0,0,0,0.24);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
  z-index: 9999;
  pointer-events: none;
}
.rx-toast.show { transform: translate(-50%, 0); opacity: 1; }
.rx-toast i { color: var(--color-red, #D71920); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1200px) {
  #readShareRail { display: none; }
}
@media (max-width: 768px) {
  .rsh-cat, .rsh-read, .rsh-divider { display: none; }
  .rx-toc-list { columns: 1; }
  .rx-reader { font-size: calc(1.05rem * var(--rx-scale)); }
  .rx-reader p { text-align: left; }
  .rx-pullquote .rx-pq-text { font-size: 1.2rem; }
  #readBackToTop { right: 12px; bottom: 12px; width: 38px; height: 38px; }
}
@media print {
  #readProgressBar, #readBackToTop, #readStickyHeader, #readShareRail,
  .rx-keypoints, .rx-toc, .rx-toast { display: none !important; }
  .rx-reader { max-width: 100% !important; font-size: 11pt !important; color: #000 !important; }
}
</style>

<script>
(function () {
  'use strict';

  var RX = {
    root: document.documentElement,
    bar: document.getElementById('readProgressFill'),
    sticky: document.getElementById('readStickyHeader'),
    share: document.getElementById('readShareRail'),
    toTop: document.getElementById('readBackToTop'),
    reader: null,          // .rx-reader
    body: document.getElementById('articleBody'),
    KEY_SCALE: 'rx-scale',
    KEY_MODE: 'rx-mode',
    KEY_TYPE: 'rx-type'
  };

  /* ---------- TOAST ---------- */
  var toastEl = null;
  function toast(msg, icon) {
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.className = 'rx-toast';
      document.body.appendChild(toastEl);
    }
    toastEl.innerHTML = '<i class="' + (icon || 'fas fa-check') + '"></i><span>' + msg + '</span>';
    toastEl.classList.add('show');
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(function () { toastEl.classList.remove('show'); }, 2200);
  }
  window.rxToast = toast;

  /* ---------- STORAGE SAFE ---------- */
  function lsGet(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
  function lsSet(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }

  /* ---------- 1) MIGRAR CONTENIDO Y AJUSTAR LECTURA ---------- */
  function setupReader() {
    if (!RX.body) return;

    // Envolver el cuerpo del artículo para heredar los tokens de lectura
    var wrap = document.createElement('div');
    wrap.id = 'readExperience';
    RX.body.parentNode.insertBefore(wrap, RX.body);
    wrap.appendChild(RX.body);
    RX.body.classList.add('rx-reader');
    RX.reader = RX.body;

    buildKeyPoints();
    buildToc();
    buildPullQuote();
    restorePrefs();
  }

  /* ---------- 2) KEY POINTS (resumen ejecutivo) ---------- */
  function buildKeyPoints() {
    var lead = document.querySelector('.article-page-lead');
    if (!lead) return;

    var sentences = (lead.textContent || '')
      .replace(/\s+/g, ' ')
      .split(/(?<=[.!?…])\s+/)
      .map(function (s) { return s.trim(); })
      .filter(function (s) { return s.length > 25; })
      .slice(0, 4);

    if (sentences.length < 2) return;

    var box = document.createElement('div');
    box.className = 'rx-keypoints';

    var head = document.createElement('div');
    head.className = 'rx-keypoints-head';
    head.innerHTML =
      '<h2 class="rx-keypoints-title"><i class="fas fa-bolt"></i> En Puntos Clave</h2>';
    var toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'rx-kp-toggle';
    toggle.textContent = 'Ver más';
    head.appendChild(toggle);

    var list = document.createElement('ul');
    list.className = 'rx-kp-list';
    sentences.forEach(function (s) {
      var li = document.createElement('li');
      li.textContent = s;
      list.appendChild(li);
    });

    toggle.addEventListener('click', function () {
      var c = list.classList.toggle('collapsed');
      toggle.textContent = c ? 'Ver todo' : 'Ver más';
    });

    box.appendChild(head);
    box.appendChild(list);
    lead.parentNode.insertBefore(box, lead);
  }

  /* ---------- 3) TABLA DE CONTENIDO ---------- */
  function buildToc() {
    var heads = RX.body.querySelectorAll('h2, h3');
    if (heads.length < 2) return;

    var toc = document.createElement('div');
    toc.className = 'rx-toc';

    var title = document.createElement('div');
    title.className = 'rx-toc-title';
    title.innerHTML = '<i class="fas fa-chevron-down"></i> En este artículo';
    var list = document.createElement('ul');
    list.className = 'rx-toc-list';

    var i = 0;
    heads.forEach(function (h) {
      i++;
      if (!h.id) h.id = 'rx-h-' + i;
      var li = document.createElement('li');
      if (h.tagName === 'H3') li.className = 'lvl-3';
      var a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = h.textContent.trim();
      li.appendChild(a);
      list.appendChild(li);
    });

    title.addEventListener('click', function () { toc.classList.toggle('collapsed'); });
    toc.appendChild(title);
    toc.appendChild(list);

    RX.body.parentNode.insertBefore(toc, RX.body);

    // Resaltar sección activa
    if ('IntersectionObserver' in window) {
      var links = list.querySelectorAll('a');
      var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (!en.isIntersecting) return;
          links.forEach(function (l) {
            l.style.color = '';
            l.style.fontWeight = '';
          });
          var act = list.querySelector('a[href="#' + en.target.id + '"]');
          if (act) { act.style.color = 'var(--rx-accent)'; act.style.fontWeight = '700'; }
        });
      }, { rootMargin: '-80px 0px -70% 0px' });
      heads.forEach(function (h) { obs.observe(h); });
    }
  }

  /* ---------- 4) PULL QUOTE AUTOMÁTICO ---------- */
  function buildPullQuote() {
    var paras = Array.prototype.slice.call(RX.body.querySelectorAll(':scope > p'));
    if (paras.length < 3) return;

    // Buscar el párrafo entre 90 y 320 caracteres más "citable"
    var best = null, bestScore = -1;
    paras.forEach(function (p, idx) {
      if (idx === 0) return;
      var t = (p.textContent || '').trim();
      if (t.length < 90 || t.length > 320) return;
      var score = 0;
      if (/[0-9]/.test(t)) score += 2;
      if (/\b(primero|según|declaró|afirmó|aseguró|importante|clave|además|sin embargo)\b/i.test(t)) score += 3;
      if (/[0-9]{2,}/.test(t)) score += 2;
      score -= Math.abs(t.length - 180) / 60;
      if (score > bestScore) { bestScore = score; best = { p: p, text: t }; }
    });

    if (!best) return;

    var pq = document.createElement('blockquote');
    pq.className = 'rx-pullquote';
    pq.setAttribute('data-rx-pq', '1');
    pq.innerHTML =
      '<p class="rx-pq-text">“' + best.text + '”</p>' +
      '<div class="rx-pq-attr">Latitud18</div>';

    best.p.parentNode.insertBefore(pq, best.p);
  }

  /* ---------- 5) PREFERENCIAS ---------- */
  function restorePrefs() {
    var scale = parseFloat(lsGet(RX.KEY_SCALE));
    if (!isNaN(scale) && scale) applyScale(scale, false);

    var type = lsGet(RX.KEY_TYPE);
    if (type) applyType(type, false);

    var mode = lsGet(RX.KEY_MODE);
    if (mode) applyMode(mode, false);
  }

  function applyScale(v, announce) {
    v = Math.max(0.85, Math.min(1.6, v));
    RX.root.style.setProperty('--rx-scale', v);
    lsSet(RX.KEY_SCALE, v);
    if (announce) toast('Tamaño de texto ' + Math.round(v * 100) + '%', 'fas fa-font');
  }

  function applyType(v, announce) {
    RX.root.setAttribute('data-read', RX.root.getAttribute('data-read') === v ? '' : v);
    if (RX.root.getAttribute('data-read') === v) {
      lsSet(RX.KEY_TYPE, v);
      if (announce) toast(v === 'serif' ? 'Tipografía serif' : 'Tipografía sans', 'fas fa-text-height');
    }
  }

  function applyMode(v, announce) {
    // night | dark | ''  (vacío = respeta el tema del sitio)
    var cur = RX.root.getAttribute('data-read');
    if (v === '' && (cur === 'night' || cur === 'dark')) {
      RX.root.setAttribute('data-read', '');
    } else if (v === '') {
      return;
    } else {
      RX.root.setAttribute('data-read', v);
    }
    lsSet(RX.KEY_MODE, v);
    if (announce) {
      toast(v === 'night' ? 'Modo sepia activado' : v === 'dark' ? 'Modo oscuro activado' : 'Modo normal', 'fas fa-moon');
    }
    syncModeBtn();
  }
  window.rxApplyMode = applyMode;

  function syncModeBtn() {
    var b = document.getElementById('rshNightBtn');
    if (!b) return;
    var m = RX.root.getAttribute('data-read');
    b.innerHTML = (m === 'night') ? '<i class="fas fa-sun"></i>'
             : (m === 'dark')  ? '<i class="fas fa-sun"></i>'
             : '<i class="fas fa-moon"></i>';
    b.setAttribute('title', (m === 'night' || m === 'dark') ? 'Volver al modo normal' : 'Modo lectura nocturna');
  }

  /* ---------- 6) SCROLL: PROGRESO + STICKY + SHARE + BACKTOP ---------- */
  function onScroll() {
    var st = window.pageYOffset || document.documentElement.scrollTop;
    var docH = document.documentElement.scrollHeight - window.innerHeight;

    // progreso
    if (RX.bar) RX.bar.style.width = (docH > 0 ? Math.min(100, (st / docH) * 100) : 0) + '%';

    // header sticky: después del headline
    var anchor = document.getElementById('rxStickyAnchor');
    var trigger = anchor ? anchor.offsetTop : 420;
    if (RX.sticky) RX.sticky.classList.toggle('show', st > trigger);

    // share rail
    if (RX.share) RX.share.classList.toggle('show', st > 500);

    // back to top
    if (RX.toTop) RX.toTop.classList.toggle('show', st > 700);
  }

  var ticking = false;
  window.addEventListener('scroll', function () {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () { onScroll(); ticking = false; });
  }, { passive: true });

  /* ---------- 7) EVENTOS UI ---------- */
  function bindUI() {
    // Modo lectura (sticky)
    var nb = document.getElementById('rshNightBtn');
    if (nb) nb.addEventListener('click', function () {
      var m = RX.root.getAttribute('data-read');
      applyMode(m === 'night' ? 'dark' : m === 'dark' ? '' : 'night', true);
    });

    // Botón de modo en la barra de herramientas existente
    document.querySelectorAll('[data-rx-mode]').forEach(function (b) {
      b.addEventListener('click', function () {
        applyMode(b.getAttribute('data-rx-mode'), true);
      });
    });

    // Tamaño
    document.querySelectorAll('[data-rx-font]').forEach(function (b) {
      b.addEventListener('click', function () {
        var d = parseInt(b.getAttribute('data-rx-font'), 10) || 0;
        var cur = parseFloat(lsGet(RX.KEY_SCALE)) || 1;
        applyScale(cur + d, true);
      });
    });

    // Tipografía
    document.querySelectorAll('[data-rx-type]').forEach(function (b) {
      b.addEventListener('click', function () {
        applyType(b.getAttribute('data-rx-type'), true);
      });
    });

    // Columna
    document.querySelectorAll('[data-rx-width]').forEach(function (b) {
      b.addEventListener('click', function () {
        if (RX.reader) {
          RX.reader.classList.toggle('wide');
          toast(RX.reader.classList.contains('wide') ? 'Columna ancha' : 'Columna de lectura', 'fas fa-arrows-alt-h');
        }
      });
    });

    // Interlineado
    document.querySelectorAll('[data-rx-lh]').forEach(function (b) {
      b.addEventListener('click', function () {
        var v = parseFloat(b.getAttribute('data-rx-lh')) || 1.85;
        RX.root.style.setProperty('--rx-lh', v);
        RX.reader && RX.reader.classList.remove('wide');
        toast('Interlineado ' + v.toFixed(2), 'fas fa-align-left');
      });
    });

    // Back to top
    if (RX.toTop) RX.toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Copy en el share rail
    var copyBtn = document.querySelector('#readShareRail .rsr-btn[data-net="copy"]');
    if (copyBtn) copyBtn.addEventListener('click', function () {
      var url = copyBtn.getAttribute('data-url') || window.location.href;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () {
          copyBtn.classList.add('flash');
          copyBtn.innerHTML = '<i class="fas fa-check"></i>';
          toast('Enlace copiado', 'fas fa-link');
          setTimeout(function () {
            copyBtn.classList.remove('flash');
            copyBtn.innerHTML = '<i class="fas fa-link"></i>';
          }, 1800);
        });
      } else {
        toast('No se pudo copiar', 'fas fa-exclamation-triangle');
      }
    });
  }

  /* ---------- INIT ---------- */
  function init() {
    setupReader();
    bindUI();
    syncModeBtn();
    onScroll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>
