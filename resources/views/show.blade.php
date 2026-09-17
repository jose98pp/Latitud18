@extends('layouts.main')

@section('title', $noticia->titulo . ' - Latitud18')

@section('meta')
<meta name="description" content="{{ $noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 160) }}">
<!-- Open Graph / Facebook / WhatsApp -->
<meta property="og:type" content="article">
<meta property="og:site_name" content="Latitud18">
<meta property="og:title" content="{{ $noticia->titulo }}">
<meta property="og:description" content="{{ $noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 160) }}">
<meta property="og:url" content="{{ $noticia->url }}">
<meta property="og:image" content="{{ $noticia->imagenUrl }}">
<meta property="og:image:alt" content="{{ $noticia->titulo }}">
<meta property="article:published_time" content="{{ $noticia->created_at ? $noticia->created_at->toIso8601String() : '' }}">
<meta property="article:section" content="{{ $noticia->category->name ?? 'General' }}">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@UhtvBol">
<meta name="twitter:title" content="{{ $noticia->titulo }}">
<meta name="twitter:description" content="{{ $noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 160) }}">
<meta name="twitter:image" content="{{ $noticia->imagenUrl }}">

<!-- Schema.org NewsArticle JSON-LD -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ $noticia->url }}"
  },
  "headline": "{{ addslashes($noticia->titulo) }}",
  "description": "{{ addslashes($noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 160)) }}",
  "image": [
    "{{ $noticia->imagenUrl }}"
  ],
  "datePublished": "{{ $noticia->created_at ? $noticia->created_at->toIso8601String() : '' }}",
  "dateModified": "{{ $noticia->updated_at ? $noticia->updated_at->toIso8601String() : '' }}",
  "author": {
    "@type": "Organization",
    "name": "Redacción Latitud18"
  },
  "publisher": {
    "@type": "NewsMediaOrganization",
    "name": "Latitud18",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('img/core-img/latitud18_logo.svg') }}"
    }
  }
}
</script>
@endsection

@section('content')
<!-- Breadcrumb -->
<style>
  .article-page-grid {
    display: grid;
    grid-template-columns: 2.6fr 1fr;
    gap: 28px;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
  }
  .article-more-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  .reading-toolbar {
    background: var(--color-navy-subtle);
    border: 1px solid var(--color-border);
    border-radius: 4px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
  }
  .toolbar-btn {
    background: var(--color-card-bg);
    border: 1px solid var(--color-border);
    color: var(--color-text-main);
    font-size: 0.78rem;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 3px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
  }
  .toolbar-btn:hover {
    border-color: var(--color-red);
    color: var(--color-red);
  }
  .toolbar-btn.active {
    background: var(--color-red);
    border-color: var(--color-red);
    color: #fff !important;
  }
  .reactions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 14px;
  }
  .reaction-card-btn {
    background: var(--color-card-bg);
    border: 2px solid var(--color-border);
    border-radius: 6px;
    padding: 12px 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
    width: 100%;
  }
  .reaction-card-btn:hover {
    border-color: var(--color-navy);
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  }
  .reaction-card-btn.selected {
    border-color: var(--color-red);
    background: #fff1f2;
  }
  [data-theme="dark"] .reaction-card-btn.selected {
    background: rgba(225, 29, 72, 0.15);
  }
  @media (max-width: 991px) {
    .article-page-grid {
      grid-template-columns: 1fr !important;
    }
    .article-more-grid {
      grid-template-columns: repeat(2, 1fr) !important;
    }
  }
  @media (max-width: 640px) {
    .article-main-column {
      padding: 16px 12px !important;
    }
    .article-page-headline {
      font-size: clamp(1.75rem, 6vw, 2.4rem) !important;
      line-height: 1.1 !important;
      word-break: break-word;
      overflow-wrap: break-word;
    }
    .article-page-lead {
      font-size: 0.95rem !important;
      line-height: 1.55 !important;
      padding: 12px 14px !important;
    }
    .article-author-byline-bar {
      flex-wrap: wrap;
      gap: 10px;
    }
    .article-more-grid {
      grid-template-columns: 1fr !important;
    }
    .article-top-badge-row {
      flex-wrap: wrap;
    }
    .reactions-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
    }
    .reading-toolbar {
      flex-direction: column;
      align-items: stretch;
      gap: 10px;
    }
    .photo-gallery-section {
      padding: 12px !important;
    }
    .gallery-grid {
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)) !important;
      gap: 8px !important;
    }
  }
  @media print {
    header, footer, aside, .reading-toolbar, .article-top-badge-row div:last-child, #comentarios-section, .reactions-box, section:last-of-type, .top-header-area, .main-header-area {
      display: none !important;
    }
    body {
      background: #fff !important;
      color: #000 !important;
    }
    .article-page-grid {
      display: block !important;
      max-width: 100% !important;
    }
    .article-main-column {
      border: none !important;
      padding: 0 !important;
    }
  }
</style>

<section style="background:var(--color-navy-subtle);border-bottom:1px solid var(--color-border);padding:10px 0;">
  <div class="container">
    <nav style="display:flex;align-items:center;gap:8px;font-size:0.78rem;flex-wrap:wrap;">
      <a href="{{ route('portada') }}" style="color:var(--color-navy);text-decoration:none;font-weight:600;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-navy)'"><i class="fas fa-home" style="margin-right:4px;"></i> Inicio</a>
      <span style="color:var(--color-text-muted);">›</span>
      <a href="{{ route('categoria.noticias', $noticia->category->slug ?? ($noticia->category->id ?? 'general')) }}" style="color:var(--color-navy);text-decoration:none;font-weight:600;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-navy)'">{{ $noticia->category->name ?? 'Noticias' }}</a>
      <span style="color:var(--color-text-muted);">›</span>
      <span style="color:var(--color-text-muted);">{{ Str::limit($noticia->titulo, 50) }}</span>
    </nav>
  </div>
</section>

<!-- Artículo Principal -->
<article style="padding:28px 0 36px;overflow-x:hidden;">
  <div class="container">
    <div class="article-page-grid">

      <!-- Columna Principal -->
      <div class="article-main-column" style="background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;padding:36px;min-width:0;">
        
        <!-- Badge de Categoría + Tiempo de Lectura -->
        <div class="article-top-badge-row" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid var(--color-border);">
          <span class="card-cat-tag" style="background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.65rem;text-transform:uppercase;padding:3px 10px;border-radius:2px;">{{ $noticia->category->name ?? 'General' }}</span>
          <span style="font-size:0.75rem;color:var(--color-text-muted);display:flex;align-items:center;gap:4px;"><i class="far fa-clock" style="color:var(--color-red);"></i> {{ ceil(str_word_count(strip_tags($noticia->contenido)) / 200) }} min de lectura</span>
          <span style="flex:1;"></span>
          <!-- Compartir Rápido -->
          <div style="display:flex;gap:6px;">
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($noticia->titulo) }}&url={{ urlencode($noticia->url) }}" target="_blank" class="btn-share-social-pill" title="Compartir en X" style="width:30px;height:30px;background:var(--color-navy-subtle);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='#1DA1F2';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-x-twitter" style="font-size:0.7rem;"></i></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($noticia->url) }}" target="_blank" class="btn-share-social-pill" title="Compartir en Facebook" style="width:30px;height:30px;background:var(--color-navy-subtle);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='#1877F2';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-facebook-f" style="font-size:0.7rem;"></i></a>
            <a href="https://api.whatsapp.com/send?text={{ urlencode($noticia->titulo . ' ' . $noticia->url) }}" target="_blank" class="btn-share-social-pill" title="Compartir en WhatsApp" style="width:30px;height:30px;background:var(--color-navy-subtle);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='#25D366';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-whatsapp" style="font-size:0.7rem;"></i></a>
            <button onclick="copyToClipboard('{{ $noticia->url }}')" title="Copiar enlace" style="width:30px;height:30px;background:var(--color-navy-subtle);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);border:none;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='var(--color-navy)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fas fa-link" style="font-size:0.7rem;"></i></button>
          </div>
        </div>

        <!-- Título Principal -->
        <h1 class="article-page-headline" style="font-family:var(--font-title-bebas);font-size:3.4rem;color:var(--color-navy);line-height:1.05;margin-bottom:20px;letter-spacing:-0.5px;">{{ $noticia->titulo }}</h1>

        <!-- Lead / Resumen -->
        <div class="article-page-lead" style="font-size:1.2rem;color:var(--color-text-secondary);line-height:1.7;margin-bottom:24px;padding:16px 20px;border-left:4px solid var(--color-red);background:var(--color-navy-subtle);">
          {{ $noticia->excerptLimpio ?? Str::limit(strip_tags($noticia->contenido), 250) }}
        </div>

        <!-- Barra del Autor -->
        <div class="article-author-byline-bar" style="display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid var(--color-border);margin-bottom:16px;">
          <div class="author-info-flex" style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;background:var(--color-navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.8rem;">L</div>
            <div>
              <span style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.82rem;color:var(--color-text-main);">Redacción Latitud18</span>
              <span style="display:block;font-size:0.7rem;color:var(--color-text-muted);">{{ \Carbon\Carbon::parse($noticia->created_at)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</span>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;font-size:0.72rem;color:var(--color-text-muted);">
            <i class="fas fa-eye" style="color:var(--color-red);"></i> Lectura recomendada
          </div>
        </div>

        <!-- BARRA DE HERRAMIENTAS DE LECTURA (TTS + ZOOM + IMPRIMIR) -->
        <div class="reading-toolbar">
          <!-- Text-to-Speech (Escuchar Noticia) -->
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <button id="btnTtsToggle" class="toolbar-btn" onclick="toggleTextToSpeech()" title="Escuchar noticia con voz humana">
              <i id="ttsIcon" class="fas fa-volume-up" style="color:var(--color-red);"></i>
              <span id="ttsLabel">Escuchar Noticia</span>
            </button>
            <button id="btnTtsStop" class="toolbar-btn" onclick="stopTextToSpeech()" style="display:none;" title="Detener audio">
              <i class="fas fa-stop"></i>
            </button>
            <div id="ttsSpeedControls" style="display:none;align-items:center;gap:4px;">
              <span style="font-size:0.7rem;color:var(--color-text-muted);">Vel:</span>
              <button class="toolbar-btn" style="padding:4px 8px;font-size:0.7rem;" onclick="setTtsSpeed(1, this)" id="speed1x">1x</button>
              <button class="toolbar-btn" style="padding:4px 8px;font-size:0.7rem;" onclick="setTtsSpeed(1.25, this)">1.25x</button>
              <button class="toolbar-btn" style="padding:4px 8px;font-size:0.7rem;" onclick="setTtsSpeed(1.5, this)">1.5x</button>
            </div>
          </div>

          <!-- Ajuste de Tipografía y Utilidades -->
          <div style="display:flex;align-items:center;gap:6px;">
            <span style="font-size:0.7rem;color:var(--color-text-muted);margin-right:2px;"><i class="fas fa-font"></i> Tamaño:</span>
            <button class="toolbar-btn" onclick="adjustFontSize(-1)" title="Reducir tamaño de letra" style="padding:4px 10px;">A-</button>
            <button class="toolbar-btn" onclick="adjustFontSize(0)" title="Tamaño predeterminado" style="padding:4px 10px;">A</button>
            <button class="toolbar-btn" onclick="adjustFontSize(1)" title="Aumentar tamaño de letra" style="padding:4px 10px;">A+</button>
            <button class="toolbar-btn" onclick="window.print()" title="Imprimir artículo" style="margin-left:4px;">
              <i class="fas fa-print"></i> Imprimir
            </button>
          </div>
        </div>

        <!-- Imagen Principal -->
        @if($noticia->has_valid_image)
          <figure style="margin-bottom:28px;">
            <div style="position:relative;overflow:hidden;border-radius:2px;">
              <img src="{{ $noticia->imagenUrl }}" alt="{{ $noticia->titulo }}" style="width:100%;height:auto;max-height:480px;object-fit:cover;" onerror="handleImageError(this)">
            </div>
            <figcaption style="font-size:0.78rem;color:var(--color-text-muted);margin-top:8px;font-style:italic;"><i class="fas fa-camera" style="margin-right:4px;"></i> {{ $noticia->titulo }}</figcaption>
          </figure>
        @endif

        <!-- Contenido del Artículo -->
        <div id="articleBody" class="article-rich-body" style="font-size:1.15rem;line-height:1.8;color:var(--color-text-main);margin-bottom:32px;transition:font-size 0.2s ease;">
          <style>.article-rich-body p:first-of-type::first-letter { font-family:var(--font-title-anton);font-size:4rem;float:left;line-height:0.75;margin-right:10px;margin-top:4px;color:var(--color-navy); }</style>
          {!! $noticia->contenidoSanitizado ?? nl2br(e($noticia->contenido)) !!}
        </div>

        <!-- FOTOGALERÍA INTERACTIVA (SI EXISTE) -->
        @if($noticia->galeria && $noticia->galeria->count() > 0)
          <div class="photo-gallery-section" style="margin-bottom:32px;background:var(--color-navy-subtle);border:1px solid var(--color-border);border-left:4px solid var(--color-red);border-radius:4px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
              <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:0.95rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--color-navy);margin:0;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-images" style="color:var(--color-red);"></i> Fotogalería del Artículo
              </h3>
              <span class="badge bg-danger text-white px-2 py-1" style="font-size:0.7rem;font-weight:700;">
                {{ $noticia->galeria->count() }} {{ $noticia->galeria->count() === 1 ? 'Fotografía' : 'Fotografías' }}
              </span>
            </div>

            <div class="gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:12px;">
              @foreach($noticia->galeria as $index => $foto)
                <div class="gallery-thumb-wrap" onclick="openLightbox({{ $index }})" style="position:relative;aspect-ratio:4/3;border-radius:4px;overflow:hidden;cursor:pointer;background:#000;box-shadow:0 2px 6px rgba(0,0,0,0.1);transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                  <img src="{{ $foto->image_url }}" alt="{{ $foto->pie_de_foto ?? 'Fotografía ' . ($index + 1) }}" style="width:100%;height:100%;object-fit:cover;opacity:0.92;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.92'">
                  <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 60%);display:flex;align-items:flex-end;padding:8px;">
                    <span style="color:#fff;font-size:0.7rem;line-height:1.2;text-shadow:0 1px 2px rgba(0,0,0,0.8);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                      {{ $foto->pie_de_foto ?: 'Ver foto ' . ($index + 1) }}
                    </span>
                  </div>
                  <div style="position:absolute;top:6px;right:6px;width:24px;height:24px;background:rgba(0,0,0,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.65rem;">
                    <i class="fas fa-search-plus"></i>
                  </div>
                </div>
              @endforeach
            </div>
            <p style="font-size:0.75rem;color:var(--color-text-muted);margin-top:12px;margin-bottom:0;text-align:right;">
              <i class="fas fa-info-circle me-1"></i> Haz clic en cualquier foto para abrirla a pantalla completa.
            </p>
          </div>
        @endif

        <!-- Video de YouTube -->
        @if($noticia->youtube_id)
          <div style="margin-bottom:28px;">
            <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-navy);margin-bottom:12px;display:flex;align-items:center;gap:6px;"><i class="fab fa-youtube" style="color:var(--color-red);"></i> Video Relacionado</h3>
            <div style="position:relative;padding-bottom:56.25%;border-radius:2px;overflow:hidden;">
              <iframe style="position:absolute;top:0;left:0;width:100%;height:100%;" src="https://www.youtube.com/embed/{{ $noticia->youtube_id }}?rel=0&enablejsapi=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
          </div>
        @endif

        <!-- Tags -->
        <div style="padding:16px 0;border-top:1px solid var(--color-border);border-bottom:1px solid var(--color-border);margin-bottom:24px;">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.75rem;color:var(--color-navy);">Etiquetas:</span>
            <span style="background:var(--color-navy-subtle);color:var(--color-navy);font-family:var(--font-title-montserrat);font-weight:700;font-size:0.7rem;padding:3px 10px;border-radius:2px;border:1px solid var(--color-border);">{{ $noticia->category->name ?? 'General' }}</span>
            <span style="background:var(--color-navy-subtle);color:var(--color-navy);font-family:var(--font-title-montserrat);font-weight:700;font-size:0.7rem;padding:3px 10px;border-radius:2px;border:1px solid var(--color-border);">Noticias</span>
            <span style="background:var(--color-navy-subtle);color:var(--color-navy);font-family:var(--font-title-montserrat);font-weight:700;font-size:0.7rem;padding:3px 10px;border-radius:2px;border:1px solid var(--color-border);">Latitud18</span>
          </div>
        </div>

        <!-- REACCIONES DE LOS LECTORES -->
        @php
          $counts = $noticia->reacciones_counts;
          $userReact = $noticia->user_reaction;
        @endphp
        <div class="reactions-box" style="background:var(--color-navy-subtle);border:1px solid var(--color-border);border-radius:4px;padding:20px;margin-bottom:28px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:0.95rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--color-navy);margin:0;display:flex;align-items:center;gap:8px;">
              <i class="fas fa-heart" style="color:var(--color-red);"></i> ¿Qué opinas de esta noticia?
            </h3>
            <span id="reactionsTotalBadge" style="font-size:0.75rem;color:var(--color-text-muted);font-weight:600;">
              {{ $counts['total'] }} reacciones
            </span>
          </div>

          <div class="reactions-grid">
            <button type="button" class="reaction-card-btn {{ $userReact === 'me_informa' ? 'selected' : '' }}" data-reaction="me_informa" onclick="submitReaction('me_informa')">
              <span style="font-size:1.6rem;display:block;margin-bottom:4px;">👍</span>
              <span style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;color:var(--color-navy);display:block;">Me informa</span>
              <span class="count-badge" id="count-me_informa" style="font-size:0.72rem;color:var(--color-text-muted);font-weight:700;">{{ $counts['me_informa'] }}</span>
            </button>

            <button type="button" class="reaction-card-btn {{ $userReact === 'interesante' ? 'selected' : '' }}" data-reaction="interesante" onclick="submitReaction('interesante')">
              <span style="font-size:1.6rem;display:block;margin-bottom:4px;">💡</span>
              <span style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;color:var(--color-navy);display:block;">Interesante</span>
              <span class="count-badge" id="count-interesante" style="font-size:0.72rem;color:var(--color-text-muted);font-weight:700;">{{ $counts['interesante'] }}</span>
            </button>

            <button type="button" class="reaction-card-btn {{ $userReact === 'me_indigna' ? 'selected' : '' }}" data-reaction="me_indigna" onclick="submitReaction('me_indigna')">
              <span style="font-size:1.6rem;display:block;margin-bottom:4px;">😠</span>
              <span style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;color:var(--color-navy);display:block;">Me indigna</span>
              <span class="count-badge" id="count-me_indigna" style="font-size:0.72rem;color:var(--color-text-muted);font-weight:700;">{{ $counts['me_indigna'] }}</span>
            </button>

            <button type="button" class="reaction-card-btn {{ $userReact === 'recomiendo' ? 'selected' : '' }}" data-reaction="recomiendo" onclick="submitReaction('recomiendo')">
              <span style="font-size:1.6rem;display:block;margin-bottom:4px;">👏</span>
              <span style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;color:var(--color-navy);display:block;">Excelente</span>
              <span class="count-badge" id="count-recomiendo" style="font-size:0.72rem;color:var(--color-text-muted);font-weight:700;">{{ $counts['recomiendo'] }}</span>
            </button>
          </div>
        </div>

        <!-- Compartir en Redes -->
        <div style="background:var(--color-navy-subtle);border:1px solid var(--color-border);border-radius:2px;padding:24px;text-align:center;margin-bottom:28px;">
          <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1rem;color:var(--color-navy);margin-bottom:6px;">¿Te gustó esta noticia?</h3>
          <p style="font-size:0.85rem;color:var(--color-text-muted);margin-bottom:16px;">Compártela con tus contactos</p>
          <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px;">
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($noticia->titulo) }}&url={{ urlencode($noticia->url) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;background:#1DA1F2;color:#fff;font-family:var(--font-title-montserrat);font-weight:700;font-size:0.75rem;padding:8px 16px;border-radius:2px;text-decoration:none;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'"><i class="fab fa-x-twitter"></i> Twitter</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($noticia->url) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;background:#1877F2;color:#fff;font-family:var(--font-title-montserrat);font-weight:700;font-size:0.75rem;padding:8px 16px;border-radius:2px;text-decoration:none;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'"><i class="fab fa-facebook-f"></i> Facebook</a>
            <a href="https://api.whatsapp.com/send?text={{ urlencode($noticia->titulo . ' ' . $noticia->url) }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;background:#25D366;color:#fff;font-family:var(--font-title-montserrat);font-weight:700;font-size:0.75rem;padding:8px 16px;border-radius:2px;text-decoration:none;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'"><i class="fab fa-whatsapp"></i> WhatsApp</a>
          </div>
        </div>

        <!-- TARJETA DE SUSCRIPCIÓN AL BOLETÍN -->
        <div class="article-newsletter-card" style="background:linear-gradient(135deg, var(--color-navy) 0%, var(--color-navy-dark) 100%);color:#fff;border-radius:4px;padding:24px;margin-bottom:28px;border-left:4px solid var(--color-red);">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <div style="width:36px;height:36px;background:var(--color-red);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;">
              <i class="fas fa-envelope-open-text"></i>
            </div>
            <div>
              <h4 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1rem;color:#fff;margin:0;text-transform:uppercase;">Únete al Boletín Latitud 18</h4>
              <p style="font-size:0.78rem;color:#94A3B8;margin:0;">Recibe análisis exclusivos y noticias destacadas en tu bandeja de entrada.</p>
            </div>
          </div>
          <form onsubmit="handleNewsletterSubmit(event, 'Detalle Noticia')" data-msg-target="article-newsletter-msg" style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;">
            <input type="email" required placeholder="Ingresa tu correo electrónico..." style="flex:1;min-width:220px;padding:9px 12px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:2px;font-size:0.85rem;outline:none;">
            <button type="submit" style="background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;text-transform:uppercase;padding:9px 18px;border:none;border-radius:2px;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
              Suscribirme
            </button>
          </form>
          <div id="article-newsletter-msg" style="display:none;margin-top:10px;font-size:0.8rem;padding:6px 12px;border-radius:2px;"></div>
        </div>

        <!-- SECCIÓN DE COMENTARIOS -->
        <div id="comentarios-section" style="border-top:2px solid var(--color-navy);padding-top:24px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1.1rem;text-transform:uppercase;color:var(--color-navy);margin:0;display:flex;align-items:center;gap:8px;">
              <i class="fas fa-comments text-danger" style="color:var(--color-red);"></i> Comentarios ({{ $noticia->comentariosAprobados->count() }})
            </h3>
            <span style="font-size:0.75rem;color:var(--color-text-muted);">Comunidad de lectores</span>
          </div>

          @if(session('comment_success'))
            <div style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:2px;margin-bottom:20px;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
              <i class="fas fa-check-circle" style="font-size:1.1rem;color:#10b981;"></i>
              <div>{{ session('comment_success') }}</div>
            </div>
          @endif

          @if(session('error'))
            <div style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:2px;margin-bottom:20px;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
              <i class="fas fa-exclamation-circle" style="font-size:1.1rem;color:#ef4444;"></i>
              <div>{{ session('error') }}</div>
            </div>
          @endif

          <!-- Formulario para Comentar -->
          <div style="background:var(--color-navy-subtle);border:1px solid var(--color-border);border-radius:2px;padding:20px;margin-bottom:28px;">
            <h4 style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.88rem;color:var(--color-navy);margin-bottom:12px;">Deja tu comentario</h4>
            <form action="{{ route('noticias.comentarios.store', $noticia->id) }}" method="POST">
              @csrf
              {{-- Honeypot anti-spam --}}
              <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                  <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--color-text-secondary);margin-bottom:4px;">Nombre <span style="color:var(--color-red);">*</span></label>
                  <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Tu nombre o alias" style="width:100%;padding:8px 12px;font-size:0.85rem;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;color:var(--color-text-main);outline:none;">
                  @error('nombre')
                    <span style="color:var(--color-red);font-size:0.72rem;">{{ $message }}</span>
                  @enderror
                </div>
                <div>
                  <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--color-text-secondary);margin-bottom:4px;">Correo electrónico <span style="font-size:0.7rem;color:var(--color-text-muted);">(Opcional, no será público)</span></label>
                  <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" style="width:100%;padding:8px 12px;font-size:0.85rem;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;color:var(--color-text-main);outline:none;">
                  @error('email')
                    <span style="color:var(--color-red);font-size:0.72rem;">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.75rem;font-weight:700;color:var(--color-text-secondary);margin-bottom:4px;">Comentario <span style="color:var(--color-red);">*</span></label>
                <textarea name="contenido" rows="3" required placeholder="Escribe tu opinión respetando las normas de convivencia..." style="width:100%;padding:10px 12px;font-size:0.88rem;font-family:var(--font-body);background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;color:var(--color-text-main);outline:none;resize:vertical;">{{ old('contenido') }}</textarea>
                @error('contenido')
                  <span style="color:var(--color-red);font-size:0.72rem;">{{ $message }}</span>
                @enderror
              </div>

              <div style="display:flex;justify-content:flex-end;">
                <button type="submit" style="background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.75rem;text-transform:uppercase;padding:8px 20px;border-radius:2px;border:none;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
                  <i class="fas fa-paper-plane" style="margin-right:6px;"></i> Publicar Comentario
                </button>
              </div>
            </form>
          </div>

          <!-- Lista de Comentarios -->
          <div class="comments-list">
            @forelse($noticia->comentariosAprobados as $comentario)
              <div style="background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;padding:16px;margin-bottom:12px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                  <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;background:var(--color-navy);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.8rem;">
                      {{ strtoupper(substr($comentario->nombre, 0, 1)) }}
                    </div>
                    <div>
                      <span style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.85rem;color:var(--color-text-main);">{{ $comentario->nombre }}</span>
                      <span style="display:block;font-size:0.68rem;color:var(--color-text-muted);"><i class="far fa-clock" style="margin-right:3px;"></i> {{ \Carbon\Carbon::parse($comentario->created_at)->locale('es')->diffForHumans() }}</span>
                    </div>
                  </div>
                </div>
                <p style="font-size:0.9rem;color:var(--color-text-secondary);line-height:1.55;margin:0;">
                  {{ $comentario->contenido }}
                </p>
              </div>
            @empty
              <div style="text-align:center;padding:24px 0;color:var(--color-text-muted);">
                <i class="far fa-comment-dots" style="font-size:2rem;margin-bottom:8px;display:block;opacity:0.4;"></i>
                <p style="font-size:0.85rem;margin:0;">Aún no hay comentarios. ¡Sé el primero en compartir tu opinión!</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <aside>
        <!-- Noticias Relacionadas -->
        <div style="background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:2px;overflow:hidden;margin-bottom:16px;">
          <div style="background:var(--color-navy);color:#fff;padding:12px 14px;font-family:var(--font-title-montserrat);font-weight:900;font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-newspaper" style="font-size:0.8rem;"></i> Te Puede Interesar
          </div>
          <div style="padding:12px;">
            @foreach($noticias as $index => $otraNoticia)
              <a href="{{ $otraNoticia->url }}" style="display:flex;gap:10px;padding:10px 0;text-decoration:none;{{ !$loop->last ? 'border-bottom:1px solid var(--color-border);' : '' }}">
                <div style="width:80px;min-width:80px;height:56px;border-radius:2px;overflow:hidden;">
                  <img src="{{ $otraNoticia->imagenUrl ?? asset('images/default-news.svg') }}" alt="{{ $otraNoticia->titulo }}" style="width:100%;height:100%;object-fit:cover;" onerror="handleImageError(this)">
                </div>
                <div>
                  <span style="font-size:0.65rem;color:var(--color-red);font-family:var(--font-title-montserrat);font-weight:800;text-transform:uppercase;">{{ $otraNoticia->category->name ?? 'General' }}</span>
                  <h4 style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.78rem;color:var(--color-text-main);line-height:1.3;margin-top:3px;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'">{{ Str::limit($otraNoticia->titulo, 55) }}</h4>
                </div>
              </a>
            @endforeach
          </div>
        </div>

        <!-- Widget suscripción -->
        <div style="background:var(--color-navy);border-radius:2px;padding:20px;text-align:center;color:#fff;">
          <i class="fas fa-bell" style="font-size:1.5rem;color:var(--color-red);margin-bottom:10px;display:block;"></i>
          <h4 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:0.82rem;margin-bottom:6px;">NO TE PIERDAS NADA</h4>
          <p style="font-size:0.75rem;color:#94A3B8;margin-bottom:14px;">Síguenos en nuestras redes sociales</p>
          <div style="display:flex;justify-content:center;gap:6px;">
            <a href="https://facebook.com/uhtvbolivia" target="_blank" style="width:32px;height:32px;background:rgba(255,255,255,0.1);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.1)';this.style.color='#94A3B8'"><i class="fab fa-facebook-f" style="font-size:0.75rem;"></i></a>
            <a href="https://x.com/UhtvBol" target="_blank" style="width:32px;height:32px;background:rgba(255,255,255,0.1);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.1)';this.style.color='#94A3B8'"><i class="fab fa-x-twitter" style="font-size:0.75rem;"></i></a>
            <a href="https://www.youtube.com/@UHTVBolivia" target="_blank" style="width:32px;height:32px;background:rgba(255,255,255,0.1);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.1)';this.style.color='#94A3B8'"><i class="fab fa-youtube" style="font-size:0.75rem;"></i></a>
          </div>
        </div>
      </aside>

    </div>
  </div>
</article>

<!-- Banner Publicitario -->
@if(isset($banners['show_bottom']) && $banners['show_bottom']->count() > 0)
  <section style="padding:20px 0;">
    <div class="container" style="text-align:center;">
      <span style="font-size:0.65rem;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">PUBLICIDAD</span>
      @foreach($banners['show_bottom'] as $banner)
        <a href="{{ $banner->link ?? '#' }}" target="_blank" style="display:block;max-width:800px;margin:8px auto 0;">
          <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width:100%;border-radius:2px;box-shadow:0 2px 8px rgba(0,0,0,0.08);" loading="lazy">
        </a>
      @endforeach
    </div>
  </section>
@endif

<!-- Más Noticias -->
<section style="padding:28px 0 36px;background:var(--color-navy-subtle);border-top:1px solid var(--color-border);">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:10px;border-bottom:2px solid var(--color-navy);">
      <div style="display:flex;align-items:center;gap:10px;">
        <h2 style="font-family:var(--font-title-montserrat);font-weight:900;font-size:1.1rem;text-transform:uppercase;letter-spacing:0.5px;color:var(--color-navy);">Más Noticias</h2>
        <div style="width:32px;height:3px;background:var(--color-red);border-radius:1px;"></div>
      </div>
      <a href="{{ route('portada') }}" style="font-family:var(--font-title-montserrat);font-weight:700;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--color-red);text-decoration:none;transition:opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">VER PORTADA <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></a>
    </div>

    <div class="article-more-grid">
      @foreach($noticias as $otraNoticia)
        <a href="{{ $otraNoticia->url }}" style="display:block;text-decoration:none;border-radius:2px;overflow:hidden;background:var(--color-card-bg);border:1px solid var(--color-border);transition:all 0.3s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
          <div style="position:relative;overflow:hidden;">
            <img src="{{ $otraNoticia->imagenUrl ?? asset('images/default-news.svg') }}" alt="{{ $otraNoticia->titulo }}" style="width:100%;height:160px;object-fit:cover;transition:transform 0.5s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onerror="handleImageError(this)">
            <span class="card-cat-tag" style="position:absolute;top:8px;left:8px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:0.6rem;text-transform:uppercase;padding:2px 7px;border-radius:2px;">{{ $otraNoticia->category->name ?? 'General' }}</span>
          </div>
          <div style="padding:12px 14px;">
            <span style="font-size:0.7rem;color:var(--color-text-muted);display:flex;align-items:center;gap:4px;margin-bottom:6px;"><i class="far fa-clock" style="color:var(--color-red);"></i> {{ \Carbon\Carbon::parse($otraNoticia->created_at)->locale('es')->diffForHumans() }}</span>
            <h3 style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.88rem;color:var(--color-text-main);line-height:1.3;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'">{{ Str::limit($otraNoticia->titulo, 65) }}</h3>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>

<script>
// --- COPIAR ENLACE ---
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const n = document.createElement('div');
        n.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;background:var(--color-navy);color:#fff;padding:10px 20px;border-radius:2px;font-family:var(--font-title-montserrat);font-weight:700;font-size:0.8rem;box-shadow:0 4px 12px rgba(0,0,0,0.2);transform:translateX(120%);transition:transform 0.3s;';
        n.innerHTML = '<i class="fas fa-check" style="margin-right:6px;color:var(--color-red);"></i>Enlace copiado al portapapeles';
        document.body.appendChild(n);
        setTimeout(() => { n.style.transform = 'translateX(0)'; }, 100);
        setTimeout(() => { n.style.transform = 'translateX(120%)'; setTimeout(() => document.body.removeChild(n), 300); }, 2500);
    });
}

// --- AJUSTE DE TAMAÑO DE FUENTE ---
let currentFontSizeStep = 0; // -1: 0.95rem, 0: 1.15rem, 1: 1.35rem, 2: 1.55rem
const fontSizes = ['0.95rem', '1.15rem', '1.35rem', '1.55rem'];
function adjustFontSize(delta) {
    if (delta === 0) {
        currentFontSizeStep = 1;
    } else {
        currentFontSizeStep = Math.max(0, Math.min(fontSizes.length - 1, (currentFontSizeStep || 1) + delta));
    }
    const bodyEl = document.getElementById('articleBody');
    if (bodyEl) {
        bodyEl.style.fontSize = fontSizes[currentFontSizeStep];
    }
}

// --- TEXT TO SPEECH (ESCUCHAR NOTICIA) ---
let synth = window.speechSynthesis;
let utterance = null;
let isSpeaking = false;
let isPaused = false;
let currentSpeed = 1;

function getArticleCleanText() {
    const title = "{{ addslashes($noticia->titulo) }}";
    const lead = "{{ addslashes(strip_tags($noticia->excerptLimpio ?? '')) }}";
    const bodyEl = document.getElementById('articleBody');
    const bodyText = bodyEl ? bodyEl.innerText : "";
    return `${title}. ${lead}. ${bodyText}`;
}

function toggleTextToSpeech() {
    if (!('speechSynthesis' in window)) {
        alert('Tu navegador no soporta la función de lectura de voz.');
        return;
    }

    const btnToggle = document.getElementById('btnTtsToggle');
    const ttsIcon = document.getElementById('ttsIcon');
    const ttsLabel = document.getElementById('ttsLabel');
    const btnStop = document.getElementById('btnTtsStop');
    const speedControls = document.getElementById('ttsSpeedControls');

    if (isSpeaking && !isPaused) {
        synth.pause();
        isPaused = true;
        ttsIcon.className = 'fas fa-play';
        ttsLabel.innerText = 'Reanudar';
        btnToggle.classList.remove('active');
    } else if (isSpeaking && isPaused) {
        synth.resume();
        isPaused = false;
        ttsIcon.className = 'fas fa-pause';
        ttsLabel.innerText = 'Pausar';
        btnToggle.classList.add('active');
    } else {
        synth.cancel();
        const text = getArticleCleanText();
        utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'es-ES';
        utterance.rate = currentSpeed;

        // Selección de voz en español si está disponible
        const voices = synth.getVoices();
        const esVoice = voices.find(v => v.lang.startsWith('es'));
        if (esVoice) utterance.voice = esVoice;

        utterance.onstart = function() {
            isSpeaking = true;
            isPaused = false;
            ttsIcon.className = 'fas fa-pause';
            ttsLabel.innerText = 'Pausar';
            btnToggle.classList.add('active');
            btnStop.style.display = 'inline-flex';
            speedControls.style.display = 'inline-flex';
        };

        utterance.onend = function() {
            stopTextToSpeech();
        };

        utterance.onerror = function() {
            stopTextToSpeech();
        };

        synth.speak(utterance);
    }
}

function stopTextToSpeech() {
    if ('speechSynthesis' in window) {
        synth.cancel();
    }
    isSpeaking = false;
    isPaused = false;
    const btnToggle = document.getElementById('btnTtsToggle');
    const ttsIcon = document.getElementById('ttsIcon');
    const ttsLabel = document.getElementById('ttsLabel');
    const btnStop = document.getElementById('btnTtsStop');
    const speedControls = document.getElementById('ttsSpeedControls');

    if (btnToggle) {
        btnToggle.classList.remove('active');
        ttsIcon.className = 'fas fa-volume-up';
        ttsLabel.innerText = 'Escuchar Noticia';
        btnStop.style.display = 'none';
        speedControls.style.display = 'none';
    }
}

function setTtsSpeed(speed, btn) {
    currentSpeed = speed;
    document.querySelectorAll('#ttsSpeedControls .toolbar-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    if (isSpeaking) {
        // Re-iniciar con la nueva velocidad
        const wasPaused = isPaused;
        synth.cancel();
        isSpeaking = false;
        toggleTextToSpeech();
    }
}

// Pre-cargar voces del sintetizador
if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => { window.speechSynthesis.getVoices(); };
}

// --- REACCIONES INTERACTIVAS CON AJAX ---
function submitReaction(tipo) {
    const btns = document.querySelectorAll('.reaction-card-btn');
    
    fetch("{{ route('noticias.react', $noticia->id) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ tipo: tipo })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Actualizar contadores
            document.getElementById('count-me_informa').innerText = data.counts.me_informa;
            document.getElementById('count-interesante').innerText = data.counts.interesante;
            document.getElementById('count-me_indigna').innerText = data.counts.me_indigna;
            document.getElementById('count-recomiendo').innerText = data.counts.recomiendo;
            document.getElementById('reactionsTotalBadge').innerText = data.counts.total + ' reacciones';

            // Actualizar clase seleccionada
            btns.forEach(btn => {
                if (btn.dataset.reaction === data.active) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });
        }
    })
    .catch(err => {
        console.error('Error al registrar la reacción:', err);
    });
}

// --- FOTOGALERÍA LIGHTBOX INTERACTIVA ---
@if($noticia->galeria && $noticia->galeria->count() > 0)
const galleryData = @json($noticia->galeria->map(function($foto) {
    return [
        'url' => $foto->image_url,
        'caption' => $foto->pie_de_foto ?? ''
    ];
}));

let currentLightboxIndex = 0;

function openLightbox(index) {
    currentLightboxIndex = index;
    updateLightboxUI();
    const modal = document.getElementById('lightbox-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeLightbox() {
    const modal = document.getElementById('lightbox-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function nextLightbox(e) {
    if (e) e.stopPropagation();
    if (galleryData.length === 0) return;
    currentLightboxIndex = (currentLightboxIndex + 1) % galleryData.length;
    updateLightboxUI();
}

function prevLightbox(e) {
    if (e) e.stopPropagation();
    if (galleryData.length === 0) return;
    currentLightboxIndex = (currentLightboxIndex - 1 + galleryData.length) % galleryData.length;
    updateLightboxUI();
}

function updateLightboxUI() {
    const photo = galleryData[currentLightboxIndex];
    if (!photo) return;

    const img = document.getElementById('lightbox-current-img');
    const counter = document.getElementById('lightbox-counter');
    const caption = document.getElementById('lightbox-caption');

    if (img) img.src = photo.url;
    if (counter) counter.innerText = `${currentLightboxIndex + 1} / ${galleryData.length}`;
    if (caption) {
        caption.innerText = photo.caption || `Fotografía ${currentLightboxIndex + 1}`;
    }
}

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('lightbox-modal');
    if (modal && modal.style.display === 'flex') {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') nextLightbox();
        if (e.key === 'ArrowLeft') prevLightbox();
    }
});
@endif
</script>

<!-- LIGHTBOX MODAL HTML -->
@if($noticia->galeria && $noticia->galeria->count() > 0)
<div id="lightbox-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(3,7,18,0.95);backdrop-filter:blur(8px);flex-direction:column;align-items:center;justify-content:space-between;padding:16px;">
  <!-- Top Bar -->
  <div style="width:100%;max-width:1200px;display:flex;align-items:center;justify-content:space-between;color:#fff;padding:8px 12px;border-bottom:1px solid rgba(255,255,255,0.1);">
    <div style="font-family:var(--font-title-montserrat);font-weight:800;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
      <span class="badge bg-danger text-white px-2 py-1"><i class="fas fa-camera me-1"></i> LATITUD 18</span>
      <span id="lightbox-counter" style="color:#94A3B8;">1 / 1</span>
    </div>
    <button onclick="closeLightbox()" style="background:none;border:none;color:#fff;font-size:1.6rem;cursor:pointer;line-height:1;padding:4px 8px;transition:color 0.2s;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='#fff'">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <!-- Main Image Stage -->
  <div style="position:relative;width:100%;max-width:1100px;flex:1;display:flex;align-items:center;justify-content:center;padding:16px 0;min-height:0;">
    <button onclick="prevLightbox(event)" style="position:absolute;left:10px;z-index:10;background:rgba(0,0,0,0.6);border:1px solid rgba(255,255,255,0.2);color:#fff;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)'" onmouseout="this.style.background='rgba(0,0,0,0.6)'">
      <i class="fas fa-chevron-left"></i>
    </button>

    <img id="lightbox-current-img" src="" alt="Fotografía" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:4px;box-shadow:0 10px 40px rgba(0,0,0,0.5);">

    <button onclick="nextLightbox(event)" style="position:absolute;right:10px;z-index:10;background:rgba(0,0,0,0.6);border:1px solid rgba(255,255,255,0.2);color:#fff;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='var(--color-red)'" onmouseout="this.style.background='rgba(0,0,0,0.6)'">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>

  <!-- Bottom Caption -->
  <div style="width:100%;max-width:900px;text-align:center;padding:12px 16px;background:rgba(11,31,58,0.7);border-radius:4px;border-left:3px solid var(--color-red);color:#fff;">
    <p id="lightbox-caption" style="margin:0;font-size:0.88rem;color:#E2E8F0;line-height:1.4;"></p>
  </div>
</div>
@endif
@endsection

