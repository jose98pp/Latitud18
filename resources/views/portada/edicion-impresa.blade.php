{{-- ============================================================
     EDICIÓN IMPRESA / PERIÓDICO DIGITAL  (Estilo: El Mundo)
     Muestra la portada activa en formato kiosko con sumario
     ============================================================ --}}
@if(!empty($edicionPeriodico))
@php
    $ep = $edicionPeriodico;
    $epId = $ep['id'] ?? '';
    $epTitulo = $ep['titulo'] ?? 'LATITUD 18';
    $epSubtitulo = $ep['subtitulo'] ?? 'Periódico Digital';
    $epFecha = $ep['fecha'] ?? '';
    $epNumero = $ep['numero_edicion'] ?? '';
    $epPrecio = $ep['precio'] ?? '';
    $epPaginas = $ep['paginas'] ?? [];
    $epPdfUrl = $ep['pdf_url'] ?? null;

    // Imagen representativa de portada
    $epImagenPortada = null;
    foreach (($epPaginas[0]['frames'] ?? []) as $f) {
        if (($f['type'] ?? '') === 'image' && !empty($f['src'])) {
            $epImagenPortada = $f['src'];
            break;
        }
    }
    if (!$epImagenPortada && !empty($epPaginas[0]['noticia_central']['imagen'])) {
        $epImagenPortada = $epPaginas[0]['noticia_central']['imagen'];
    }

    $epTitularPrincipal = $epPaginas[0]['titular_principal']['titulo'] ?? null;
    $epCintillo = $epPaginas[0]['cintillo_inferior']['texto'] ?? null;

    // Sumario de noticias del interior
    $sumario = [];
    foreach ($epPaginas as $pg) {
        if (!empty($pg['titular_principal']['titulo'])) {
            $sumario[] = [
                'cat'   => $pg['titular_principal']['antetitulo'] ?? ($pg['nombre'] ?? 'NOTICIAS'),
                'tit'   => $pg['titular_principal']['titulo'],
                'pag'   => 'Pág. ' . ($pg['numero'] ?? '—'),
                'color' => '#D71920',
            ];
        }
        if (count($sumario) >= 4) break;
    }
@endphp

<section class="ep-kiosko-section" aria-label="Edición Impresa y Periódico Digital">
  <div class="ep-kiosko-inner">

    {{-- Encabezado de sección --}}
    <div class="ep-section-head">
      <div class="ep-section-label">
        <i class="fas fa-newspaper"></i>
        EDICIÓN IMPRESA &amp; PERIÓDICO DIGITAL
      </div>
      <a href="{{ route('periodico.public.index') }}" class="ep-ver-todo">
        Ver todas las ediciones <i class="fas fa-arrow-right"></i>
      </a>
    </div>

    <div class="ep-kiosko-layout">

      {{-- TAPA DEL PERIÓDICO EN 3D --}}
      <div class="ep-cover-col">
        <div class="ep-book-wrapper">
          <div class="ep-book-spine"></div>
          <div class="ep-book-body">
            <div class="ep-book-cover">
              <div class="ep-cover-masthead">
                <div class="ep-cover-name">{{ strtoupper($epTitulo) }}<span class="ep-cover-sub">{{ $epSubtitulo }}</span></div>
                <div class="ep-cover-meta-row">
                  <span>{{ $epNumero }}</span>
                  <span class="ep-cover-dot">·</span>
                  <span>{{ $epPrecio }}</span>
                </div>
              </div>

              @if($epImagenPortada)
                <div class="ep-cover-img-wrap">
                  <img src="{{ $epImagenPortada }}" alt="Portada {{ $epTitulo }}" class="ep-cover-img" loading="lazy">
                  <div class="ep-cover-img-gradient"></div>
                </div>
              @else
                <div class="ep-cover-placeholder">
                  <i class="fas fa-newspaper"></i>
                  <span>Periódico Digital</span>
                </div>
              @endif

              @if($epTitularPrincipal)
                <div class="ep-cover-headline">{{ Str::limit($epTitularPrincipal, 80) }}</div>
              @endif

              @if($epCintillo)
                <div class="ep-cover-cintillo">
                  <span class="ep-cintillo-cat">URGENTE</span>
                  {{ Str::limit($epCintillo, 70) }}
                </div>
              @endif
            </div>
          </div>
          <div class="ep-book-shadow"></div>
        </div>

        <div class="ep-cover-date">
          <i class="fas fa-calendar-alt"></i> {{ $epFecha }}
        </div>

        <div class="ep-cover-actions">
          <a href="{{ route('periodico.public.index') }}" class="ep-btn ep-btn-primary">
            <i class="fas fa-book-open"></i> Leer Edición
          </a>
          @if($epPdfUrl)
            <a href="{{ $epPdfUrl }}" target="_blank" rel="noopener" class="ep-btn ep-btn-secondary">
              <i class="fas fa-file-pdf"></i> PDF
            </a>
          @else
            <a href="{{ route('periodico.public.pdf', $epId) }}" target="_blank" rel="noopener" class="ep-btn ep-btn-secondary">
              <i class="fas fa-print"></i> Imprimir
            </a>
          @endif
        </div>
      </div>

      {{-- PANEL EDITORIAL: Ficha + Sumario + CTA --}}
      <div class="ep-content-col">

        <div class="ep-ficha">
          <div class="ep-ficha-logo">
            <span class="ep-ficha-nombre">{{ strtoupper($epTitulo) }}</span>
            @if($epSubtitulo)
              <span class="ep-ficha-sub">{{ $epSubtitulo }}</span>
            @endif
          </div>
          <div class="ep-ficha-datos">
            @if($epNumero)
              <div class="ep-ficha-dato">
                <span class="ep-ficha-dato-label">Número</span>
                <span class="ep-ficha-dato-val">{{ $epNumero }}</span>
              </div>
            @endif
            <div class="ep-ficha-dato">
              <span class="ep-ficha-dato-label">Páginas</span>
              <span class="ep-ficha-dato-val">{{ count($epPaginas) }}</span>
            </div>
            @if($epPrecio)
              <div class="ep-ficha-dato">
                <span class="ep-ficha-dato-label">Precio</span>
                <span class="ep-ficha-dato-val">{{ $epPrecio }}</span>
              </div>
            @endif
          </div>
          <div class="ep-ficha-badge">
            <span class="ep-ficha-badge-dot"></span>
            EDICIÓN DISPONIBLE
          </div>
        </div>

        @if(count($sumario) > 0)
        <div class="ep-sumario">
          <div class="ep-sumario-head"><i class="fas fa-list-ul"></i> EN ESTA EDICIÓN</div>
          <div class="ep-sumario-items">
            @foreach($sumario as $item)
              <a href="{{ route('periodico.public.index') }}" class="ep-sumario-item">
                <div class="ep-sumario-cat" style="color:{{ $item['color'] }}">{{ $item['cat'] }}</div>
                <div class="ep-sumario-tit">{{ Str::limit($item['tit'], 90) }}</div>
                <div class="ep-sumario-pag">{{ $item['pag'] }}</div>
              </a>
            @endforeach
          </div>
        </div>
        @endif

        <div class="ep-cta-lectura">
          <div class="ep-cta-text">
            <i class="fas fa-book-reader ep-cta-icon"></i>
            <div>
              <div class="ep-cta-title">Periódico Digital Semanal</div>
              <div class="ep-cta-desc">Lee la edición completa en formato digital. Disponible en todos tus dispositivos.</div>
            </div>
          </div>
          <a href="{{ route('periodico.public.index') }}" class="ep-btn ep-btn-primary ep-btn-full">
            <i class="fas fa-book-open"></i> Abrir Periódico Digital
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<style>
.ep-kiosko-section {
  background: linear-gradient(135deg, #0B1F3A 0%, #1a2c45 60%, #0B1F3A 100%);
  border-radius: 12px;
  margin: 32px 0;
  overflow: hidden;
  position: relative;
  box-shadow: 0 8px 40px rgba(0,0,0,0.25);
}
.ep-kiosko-section::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
  background-size: 20px 20px;
  pointer-events: none;
}
.ep-kiosko-inner { padding: 28px 32px 32px; position: relative; z-index: 1; }
.ep-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(215,25,32,0.6);
}
.ep-section-label {
  font-family: 'Montserrat', sans-serif;
  font-weight: 800;
  font-size: 0.72rem;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 8px;
}
.ep-section-label i { color: #D71920; font-size: 1rem; }
.ep-ver-todo {
  font-family: 'Montserrat', sans-serif;
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  text-decoration: none;
  transition: color 0.2s;
  display: flex;
  align-items: center;
  gap: 5px;
}
.ep-ver-todo:hover { color: #fff; }
.ep-kiosko-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 32px;
  align-items: start;
}
.ep-cover-col { display: flex; flex-direction: column; gap: 14px; align-items: center; }
.ep-book-wrapper {
  position: relative;
  width: 190px;
  height: 265px;
  perspective: 800px;
}
.ep-book-body {
  position: absolute;
  inset: 0;
  transform: rotateY(-10deg) rotateX(2deg);
  transform-style: preserve-3d;
  transition: transform 0.35s ease;
  box-shadow: 8px 10px 30px rgba(0,0,0,0.6), inset -3px 0 8px rgba(0,0,0,0.25);
  border-radius: 2px 4px 4px 2px;
}
.ep-book-wrapper:hover .ep-book-body { transform: rotateY(-4deg) rotateX(1deg); }
.ep-book-spine {
  position: absolute;
  left: -12px; top: 0; bottom: 0;
  width: 14px;
  background: linear-gradient(to right, #07101f, #1a3055, #07101f);
  border-radius: 2px 0 0 2px;
  box-shadow: 2px 0 8px rgba(0,0,0,0.5);
  z-index: 0;
}
.ep-book-cover {
  width: 100%;
  height: 100%;
  background: #fff;
  border-radius: 0 3px 3px 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  position: relative;
}
.ep-cover-masthead {
  background: #D71920;
  color: #fff;
  padding: 6px 8px 4px;
  text-align: center;
  flex-shrink: 0;
}
.ep-cover-name {
  font-family: 'Anton', 'Bebas Neue', sans-serif;
  font-size: 1.05rem;
  letter-spacing: 1px;
  line-height: 1;
}
.ep-cover-sub {
  font-family: 'Montserrat', sans-serif;
  font-size: 0.42rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  display: block;
  opacity: 0.9;
}
.ep-cover-meta-row {
  font-size: 0.42rem;
  font-weight: 600;
  opacity: 0.85;
  display: flex;
  justify-content: center;
  gap: 4px;
  margin-top: 2px;
}
.ep-cover-dot { opacity: 0.5; }
.ep-cover-img-wrap { flex: 1; position: relative; overflow: hidden; }
.ep-cover-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ep-cover-img-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, transparent 45%, rgba(0,0,0,0.7) 100%);
}
.ep-cover-placeholder {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: linear-gradient(135deg, #1e3a5f, #0b1f3a);
  color: rgba(255,255,255,0.35);
}
.ep-cover-placeholder i { font-size: 2rem; }
.ep-cover-placeholder span { font-size: 0.5rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
.ep-cover-headline {
  position: absolute;
  bottom: 24px; left: 0; right: 0;
  padding: 5px 7px 3px;
  background: rgba(0,0,0,0.8);
  color: #fff;
  font-family: 'Source Sans 3', sans-serif;
  font-size: 0.5rem;
  font-weight: 700;
  line-height: 1.3;
}
.ep-cover-cintillo {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  background: #D71920;
  color: #fff;
  font-size: 0.4rem;
  font-weight: 700;
  padding: 3px 6px;
  line-height: 1.3;
  text-transform: uppercase;
}
.ep-cintillo-cat {
  background: #fff;
  color: #D71920;
  font-weight: 900;
  padding: 0 3px;
  margin-right: 3px;
  border-radius: 1px;
}
.ep-book-shadow {
  position: absolute;
  bottom: -16px;
  left: 5%; width: 90%; height: 18px;
  background: radial-gradient(ellipse, rgba(0,0,0,0.5) 0%, transparent 80%);
  filter: blur(5px);
}
.ep-cover-date {
  font-family: 'Montserrat', sans-serif;
  font-size: 0.72rem;
  font-weight: 600;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 5px;
}
.ep-cover-date i { color: #D71920; }
.ep-cover-actions { display: flex; gap: 8px; width: 100%; }
.ep-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 9px 14px;
  border-radius: 4px;
  font-family: 'Montserrat', sans-serif;
  font-weight: 800;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-decoration: none;
  transition: all 0.2s;
  white-space: nowrap;
  cursor: pointer;
}
.ep-btn-primary { background: #D71920; color: #fff; flex: 1; }
.ep-btn-primary:hover { background: #b8141b; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(215,25,32,0.4); }
.ep-btn-secondary { background: rgba(255,255,255,0.1); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.2); }
.ep-btn-secondary:hover { background: rgba(255,255,255,0.2); color: #fff; }
.ep-btn-full { width: 100%; }
.ep-content-col { display: flex; flex-direction: column; gap: 14px; }
.ep-ficha {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  padding: 14px 16px;
  border-left: 3px solid #D71920;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.ep-ficha-logo { display: flex; flex-direction: column; gap: 1px; }
.ep-ficha-nombre { font-family: 'Anton', sans-serif; font-size: 1.35rem; color: #fff; letter-spacing: 1px; line-height: 1; }
.ep-ficha-sub { font-family: 'Montserrat', sans-serif; font-size: 0.62rem; font-weight: 700; color: #94a3b8; letter-spacing: 1px; text-transform: uppercase; }
.ep-ficha-datos { display: flex; gap: 16px; flex-wrap: wrap; }
.ep-ficha-dato { display: flex; flex-direction: column; gap: 1px; }
.ep-ficha-dato-label { font-family: 'Montserrat', sans-serif; font-size: 0.58rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.ep-ficha-dato-val { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 0.85rem; color: #e2e8f0; }
.ep-ficha-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(215,25,32,0.15);
  border: 1px solid rgba(215,25,32,0.35);
  color: #fb7185;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.6rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 3px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  align-self: flex-start;
}
.ep-ficha-badge-dot {
  width: 6px; height: 6px;
  background: #D71920;
  border-radius: 50%;
  animation: pulse 1.5s infinite;
  flex-shrink: 0;
}
.ep-sumario {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  overflow: hidden;
}
.ep-sumario-head {
  background: rgba(215,25,32,0.88);
  color: #fff;
  font-family: 'Montserrat', sans-serif;
  font-weight: 800;
  font-size: 0.65rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 7px 12px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.ep-sumario-items { display: flex; flex-direction: column; }
.ep-sumario-item {
  display: grid;
  grid-template-columns: 90px 1fr 60px;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  text-decoration: none;
  transition: background 0.15s;
}
.ep-sumario-item:last-child { border-bottom: none; }
.ep-sumario-item:hover { background: rgba(255,255,255,0.05); }
.ep-sumario-cat { font-family: 'Montserrat', sans-serif; font-size: 0.58rem; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
.ep-sumario-tit { font-family: 'Source Sans 3', sans-serif; font-size: 0.78rem; font-weight: 600; color: #e2e8f0; line-height: 1.3; }
.ep-sumario-pag { font-family: 'Montserrat', sans-serif; font-size: 0.58rem; font-weight: 700; color: #64748b; text-align: right; }
.ep-cta-lectura {
  background: linear-gradient(135deg, rgba(215,25,32,0.15), rgba(215,25,32,0.06));
  border: 1px solid rgba(215,25,32,0.3);
  border-radius: 8px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.ep-cta-text { display: flex; align-items: flex-start; gap: 10px; }
.ep-cta-icon { font-size: 1.4rem; color: #D71920; margin-top: 2px; flex-shrink: 0; }
.ep-cta-title { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 0.85rem; color: #fff; }
.ep-cta-desc { font-family: 'Source Sans 3', sans-serif; font-size: 0.73rem; color: #94a3b8; line-height: 1.4; margin-top: 2px; }
@media (max-width: 768px) {
  .ep-kiosko-layout { grid-template-columns: 1fr; }
  .ep-cover-col { align-items: center; }
  .ep-kiosko-inner { padding: 20px 16px 24px; }
  .ep-sumario-item { grid-template-columns: 80px 1fr; }
  .ep-sumario-pag { display: none; }
}
</style>
@endif
