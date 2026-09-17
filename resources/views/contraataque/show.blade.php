@extends('layouts.contraataque')

@php
    $titulo = $noticia->titulo ?? '';
    $bajada = $noticia->bajada ?? $noticia->resumen ?? '';
    $contenido = $noticia->contenido ?? '';
    $img = method_exists($noticia, 'getImageUrl') ? $noticia->getImageUrl() : ($noticia->imagen ?? $noticia->foto ?? '');
    $cat = $noticia->category->name ?? 'DEPORTES';
    $catSlug = \Illuminate\Support\Str::slug($cat);
    $autor = $noticia->autor ?? ($noticia->user->name ?? 'Redacción Contra Ataque');
    $fechaIso = $noticia->created_at ? $noticia->created_at->toIso8601String() : '';
    $fechaHuman = $noticia->created_at ? $noticia->created_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm') : 'Hoy';
    $visitas = $noticia->views ?? 150;
    $minLectura = ceil(str_word_count(strip_tags($contenido)) / 200) ?: 2;
    $url = $noticia->url ?? url()->current();
    $excerpt = $noticia->excerptLimpio ?? \Illuminate\Support\Str::limit(strip_tags($bajada ?: $contenido), 160);
    $counts = $noticia->reacciones_counts ?? ['me_informa' => 0, 'interesante' => 0, 'me_indigna' => 0, 'recomiendo' => 0, 'total' => 0];
    $userReact = $noticia->user_reaction ?? null;
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
@endphp

@section('title', $titulo . ' — Contra Ataque Deportes')
@section('meta_description', $excerpt)

@section('meta')
<!-- Open Graph / Facebook / WhatsApp -->
<meta property="og:type" content="article">
<meta property="og:site_name" content="Contra Ataque — Latitud 18">
<meta property="og:title" content="{{ $titulo }}">
<meta property="og:description" content="{{ $excerpt }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ $img }}">
<meta property="article:published_time" content="{{ $fechaIso }}">
<meta property="article:section" content="{{ $cat }}">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@UhtvBol">
<meta name="twitter:title" content="{{ $titulo }}">
<meta name="twitter:description" content="{{ $excerpt }}">
<meta name="twitter:image" content="{{ $img }}">

<!-- Schema.org NewsArticle JSON-LD -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ $url }}"
  },
  "headline": "{{ addslashes($titulo) }}",
  "description": "{{ addslashes($excerpt) }}",
  "image": ["{{ $img }}"],
  "datePublished": "{{ $fechaIso }}",
  "author": {
    "@type": "Organization",
    "name": "{{ addslashes($autor) }}"
  },
  "publisher": {
    "@type": "NewsMediaOrganization",
    "name": "Contra Ataque Deportes",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('img/core-img/latitud18_logo.svg') }}"
    }
  }
}
</script>
@endsection

@section('content')
<style>
  /* ══════════════════════════════════════════════════════
     ESTILOS ESPECÍFICOS PARA EL DETALLE DE NOTICIA DEPORTIVA
  ══════════════════════════════════════════════════════ */
  .ca-reading-toolbar {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--ca-border);
    border-radius: 6px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
  }
  .ca-toolbar-btn {
    background: var(--ca-bg-card);
    border: 1px solid var(--ca-border);
    color: var(--ca-text);
    font-family: var(--ca-font-display);
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
  }
  .ca-toolbar-btn:hover {
    border-color: var(--ca-volt);
    color: var(--ca-volt);
    box-shadow: 0 0 10px var(--ca-volt-glow);
  }
  .ca-toolbar-btn.active {
    background: var(--ca-volt);
    border-color: var(--ca-volt);
    color: #000 !important;
  }
  .ca-reactions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 14px;
  }
  .ca-reaction-btn {
    background: rgba(255, 255, 255, 0.03);
    border: 2px solid var(--ca-border);
    border-radius: 8px;
    padding: 14px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
    width: 100%;
    color: var(--ca-text);
  }
  .ca-reaction-btn:hover {
    border-color: var(--ca-volt);
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(0, 255, 135, 0.15);
  }
  .ca-reaction-btn.selected {
    border-color: var(--ca-volt);
    background: rgba(0, 255, 135, 0.12);
    box-shadow: 0 0 16px var(--ca-volt-glow);
  }
  .ca-article-body {
    font-size: 1.12rem;
    line-height: 1.85;
    color: #E2E8F0;
    transition: font-size 0.2s ease;
  }
  .ca-article-body p {
    margin-bottom: 1.25rem;
  }
  .ca-article-body p:first-of-type::first-letter {
    font-family: var(--ca-font-display);
    font-size: 3.8rem;
    float: left;
    line-height: 0.8;
    margin-right: 10px;
    margin-top: 4px;
    color: var(--ca-volt);
    font-weight: 900;
  }
  .ca-article-body img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    margin: 1.5rem 0;
    border: 1px solid var(--ca-border);
  }
  .ca-article-body iframe {
    max-width: 100%;
    border-radius: 6px;
    margin: 1.5rem 0;
  }
  .ca-gallery-thumb {
    position: relative;
    aspect-ratio: 4/3;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    background: #000;
    border: 1px solid var(--ca-border);
    transition: all 0.25s;
  }
  .ca-gallery-thumb:hover {
    transform: scale(1.03);
    border-color: var(--ca-volt);
    box-shadow: 0 4px 16px var(--ca-volt-glow);
  }
  @media (max-width: 640px) {
    .ca-article-main-card {
      padding: 16px 12px !important;
    }
    .ca-title-hero {
      font-size: clamp(1.5rem, 5.5vw, 2.2rem) !important;
      line-height: 1.15 !important;
      word-break: break-word;
      overflow-wrap: break-word;
    }
    .ca-reactions-grid {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 8px !important;
    }
    .ca-reaction-btn {
      padding: 10px 6px !important;
    }
    .ca-reading-toolbar {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 10px !important;
    }
  }
</style>

<div class="container py-2">

    <!-- Migas de pan deportivas -->
    <div class="d-flex align-items-center gap-2 mb-3 text-muted" style="font-size: 0.8rem;">
        <a href="{{ route('contraataque.index') }}" class="text-white hover:text-emerald-400">
            <i class="fas fa-bolt text-warning me-1"></i> Contra Ataque
        </a>
        <span>/</span>
        <a href="{{ route('contraataque.seccion', $catSlug) }}" class="text-success fw-bold" style="color: var(--ca-volt) !important;">
            {{ $cat }}
        </a>
        <span>/</span>
        <span class="text-truncate text-muted" style="max-width: 280px;">{{ $titulo }}</span>
    </div>

    <div class="row g-4">
        <!-- Columna Principal: Noticia Completa -->
        <div class="col-lg-8">
            <article class="ca-card ca-article-main-card p-4">

                <!-- Kicker y Tiempo de Lectura -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.08) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display); font-weight: 900; font-size: 0.78rem; letter-spacing: 1px;">
                            {{ $cat }}
                        </span>
                        <span class="text-muted small" style="font-size: 0.75rem;">
                            <i class="far fa-clock me-1 text-success"></i> {{ $minLectura }} min de lectura
                        </span>
                    </div>
                    
                    <!-- Botones de Compartir Rápido -->
                    <div class="d-flex align-items-center gap-1">
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($titulo) }}&url={{ urlencode($url) }}" target="_blank" class="btn btn-sm text-white" style="background: #1DA1F2; font-size: 0.7rem; padding: 3px 8px; border-radius: 4px;" title="Compartir en X"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" target="_blank" class="btn btn-sm text-white" style="background: #1877F2; font-size: 0.7rem; padding: 3px 8px; border-radius: 4px;" title="Compartir en Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($titulo . ' ' . $url) }}" target="_blank" class="btn btn-sm text-white" style="background: #25D366; font-size: 0.7rem; padding: 3px 8px; border-radius: 4px;" title="Compartir en WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <button type="button" onclick="copySportsLink('{{ $url }}')" class="btn btn-sm text-white" style="background: #334155; font-size: 0.7rem; padding: 3px 8px; border-radius: 4px; border: none;" title="Copiar enlace"><i class="fas fa-link"></i></button>
                    </div>
                </div>
                
                <!-- Título Principal -->
                <h1 class="ca-title-hero" style="font-size: 2.3rem; line-height: 1.15; margin-bottom: 16px; letter-spacing: -0.5px;">
                    {{ $titulo }}
                </h1>

                <!-- Bajada / Lead de la noticia -->
                @if(!empty($bajada))
                    <div style="font-size: 1.15rem; line-height: 1.6; color: #CBD5E1; font-weight: 500; margin-bottom: 22px; border-left: 4px solid var(--ca-volt); padding-left: 16px; background: rgba(0, 255, 135, 0.04); border-radius: 0 6px 6px 0; padding: 12px 16px;">
                        {!! nl2br(e($bajada)) !!}
                    </div>
                @endif

                <!-- Meta bar del autor y fecha -->
                <div class="d-flex align-items-center justify-content-between border-top border-bottom py-2 mb-4" style="border-color: rgba(255,255,255,0.08) !important; font-size: 0.82rem; color: #94A3B8;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--ca-volt); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 900; font-family: var(--ca-font-display);">
                            {{ strtoupper(substr($autor, 0, 1)) }}
                        </div>
                        <div>
                            <strong class="text-white d-block" style="line-height: 1.2;">{{ $autor }}</strong>
                            <span style="font-size: 0.72rem;">{{ $fechaHuman }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background: rgba(255,255,255,0.06); color: var(--ca-text);"><i class="fas fa-eye me-1 text-info"></i> {{ number_format($visitas) }} lecturas</span>
                    </div>
                </div>

                <!-- BARRA DE HERRAMIENTAS DE LECTURA (TTS + ZOOM + IMPRIMIR) -->
                <div class="ca-reading-toolbar">
                    <!-- Text-to-Speech (Escuchar Noticia Deportiva) -->
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <button id="btnTtsToggle" class="ca-toolbar-btn" onclick="toggleSportsTts()" title="Escuchar noticia en audio">
                            <i id="ttsIcon" class="fas fa-volume-up" style="color:var(--ca-volt);"></i>
                            <span id="ttsLabel">ESCUCHAR NOTICIA</span>
                        </button>
                        <button id="btnTtsStop" class="ca-toolbar-btn" onclick="stopSportsTts()" style="display:none;" title="Detener audio">
                            <i class="fas fa-stop text-danger"></i>
                        </button>
                        <div id="ttsSpeedControls" style="display:none;align-items:center;gap:4px;">
                            <span style="font-size:0.75rem;color:var(--ca-text-muted);font-family:var(--ca-font-display);">VEL:</span>
                            <button class="ca-toolbar-btn" style="padding:4px 8px;font-size:0.75rem;" onclick="setSportsTtsSpeed(1, this)" id="speed1x">1x</button>
                            <button class="ca-toolbar-btn" style="padding:4px 8px;font-size:0.75rem;" onclick="setSportsTtsSpeed(1.25, this)">1.25x</button>
                            <button class="ca-toolbar-btn" style="padding:4px 8px;font-size:0.75rem;" onclick="setSportsTtsSpeed(1.5, this)">1.5x</button>
                        </div>
                    </div>

                    <!-- Ajuste de Tipografía y Utilidades -->
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:0.75rem;color:var(--ca-text-muted);font-family:var(--ca-font-display);margin-right:2px;"><i class="fas fa-font"></i> TEXTO:</span>
                        <button class="ca-toolbar-btn" onclick="adjustSportsFontSize(-1)" title="Reducir letra" style="padding:4px 10px;">A-</button>
                        <button class="ca-toolbar-btn" onclick="adjustSportsFontSize(0)" title="Tamaño estándar" style="padding:4px 10px;">A</button>
                        <button class="ca-toolbar-btn" onclick="adjustSportsFontSize(1)" title="Aumentar letra" style="padding:4px 10px;">A+</button>
                        <button class="ca-toolbar-btn" onclick="window.print()" title="Imprimir" style="margin-left:4px;">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>

                <!-- Imagen principal de la noticia -->
                @if($img)
                    <figure class="mb-4">
                        <div class="rounded overflow-hidden position-relative" style="max-height: 480px; border: 1px solid var(--ca-border);">
                            <img src="{{ $img }}" alt="{{ $titulo }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1200&q=80'">
                        </div>
                        <figcaption class="mt-2 text-muted small" style="font-size: 0.78rem;">
                            <i class="fas fa-camera text-success me-1"></i> {{ $titulo }}
                        </figcaption>
                    </figure>
                @endif

                <!-- Contenido Principal del Artículo -->
                <div id="sportsArticleBody" class="ca-article-body mb-4">
                    {!! $contenido !!}
                </div>

                <!-- FOTOGALERÍA INTERACTIVA (SI EXISTE) -->
                @if($noticia->galeria && $noticia->galeria->count() > 0)
                    <div class="photo-gallery-section p-4 my-4 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid var(--ca-border); border-left: 4px solid var(--ca-volt);">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="ca-sec-title mb-0" style="font-size: 1.15rem;">
                                <i class="fas fa-images text-success me-2"></i> FOTOGALERÍA DEL PARTIDO
                            </h3>
                            <span class="badge" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display); font-weight: 900;">
                                {{ $noticia->galeria->count() }} {{ $noticia->galeria->count() === 1 ? 'Foto' : 'Fotos' }}
                            </span>
                        </div>

                        <div class="row g-3">
                            @foreach($noticia->galeria as $index => $foto)
                                <div class="col-6 col-md-4">
                                    <div class="ca-gallery-thumb" onclick="openSportsLightbox({{ $index }})">
                                        <img src="{{ $foto->image_url }}" alt="{{ $foto->pie_de_foto ?? 'Foto ' . ($index + 1) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 60%); display: flex; align-items: flex-end; padding: 8px;">
                                            <span style="color: #fff; font-size: 0.72rem; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.9); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $foto->pie_de_foto ?: 'Ver foto ' . ($index + 1) }}
                                            </span>
                                        </div>
                                        <div style="position: absolute; top: 6px; right: 6px; width: 24px; height: 24px; background: rgba(0,0,0,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--ca-volt); font-size: 0.7rem;">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="small text-muted text-end mt-2 mb-0" style="font-size: 0.72rem;">
                            <i class="fas fa-info-circle me-1"></i> Haz clic en cualquier imagen para abrirla a pantalla completa.
                        </p>
                    </div>
                @endif

                <!-- VIDEO DE YOUTUBE ASOCIADO -->
                @if($noticia->youtube_id)
                    <div class="my-4 p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid var(--ca-border); border-left: 4px solid var(--ca-red);">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="ca-sec-title mb-0" style="font-size: 1.15rem; color: #fff;">
                                <i class="fab fa-youtube text-danger me-2"></i> RESUMEN EN VIDEO & JUGADAS
                            </h3>
                            <span class="badge bg-danger text-white fw-bold" style="font-family: var(--ca-font-display);">HD 1080p</span>
                        </div>
                        <div style="position: relative; padding-bottom: 56.25%; border-radius: 6px; overflow: hidden; border: 1px solid var(--ca-border);">
                            <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                                    src="https://www.youtube.com/embed/{{ $noticia->youtube_id }}?rel=0&enablejsapi=1" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                @endif

                <!-- REACCIONES DE LOS LECTORES DEPORTIVOS -->
                <div class="ca-card p-4 my-4" style="background: linear-gradient(180deg, #111827 0%, #162032 100%); border: 1px solid var(--ca-border);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <h3 class="ca-sec-title mb-0" style="font-size: 1.15rem;">
                            <i class="fas fa-heart text-danger me-2"></i> ¿QUÉ OPINAS DE ESTA NOTICIA?
                        </h3>
                        <span id="reactionsTotalBadge" class="badge" style="background: rgba(255,255,255,0.08); color: var(--ca-volt); font-family: var(--ca-font-display); font-size: 0.8rem;">
                            {{ $counts['total'] }} reacciones
                        </span>
                    </div>
                    <p class="text-muted small mb-3">Deja tu reacción en caliente sobre esta jugada, resultado o polémica deportiva.</p>

                    <div class="ca-reactions-grid">
                        <button type="button" class="ca-reaction-btn {{ $userReact === 'me_informa' ? 'selected' : '' }}" data-reaction="me_informa" onclick="submitSportsReaction('me_informa')">
                            <span style="font-size: 1.8rem; display: block; margin-bottom: 4px;">👍</span>
                            <span style="font-family: var(--ca-font-display); font-weight: 800; font-size: 0.85rem; display: block;">ME INFORMA</span>
                            <span class="count-badge" id="count-me_informa" style="font-size: 0.78rem; color: var(--ca-volt); font-weight: 700;">{{ $counts['me_informa'] }}</span>
                        </button>

                        <button type="button" class="ca-reaction-btn {{ $userReact === 'interesante' ? 'selected' : '' }}" data-reaction="interesante" onclick="submitSportsReaction('interesante')">
                            <span style="font-size: 1.8rem; display: block; margin-bottom: 4px;">💡</span>
                            <span style="font-family: var(--ca-font-display); font-weight: 800; font-size: 0.85rem; display: block;">INTERESANTE</span>
                            <span class="count-badge" id="count-interesante" style="font-size: 0.78rem; color: var(--ca-volt); font-weight: 700;">{{ $counts['interesante'] }}</span>
                        </button>

                        <button type="button" class="ca-reaction-btn {{ $userReact === 'me_indigna' ? 'selected' : '' }}" data-reaction="me_indigna" onclick="submitSportsReaction('me_indigna')">
                            <span style="font-size: 1.8rem; display: block; margin-bottom: 4px;">😠</span>
                            <span style="font-family: var(--ca-font-display); font-weight: 800; font-size: 0.85rem; display: block;">ME INDIGNA</span>
                            <span class="count-badge" id="count-me_indigna" style="font-size: 0.78rem; color: var(--ca-volt); font-weight: 700;">{{ $counts['me_indigna'] }}</span>
                        </button>

                        <button type="button" class="ca-reaction-btn {{ $userReact === 'recomiendo' ? 'selected' : '' }}" data-reaction="recomiendo" onclick="submitSportsReaction('recomiendo')">
                            <span style="font-size: 1.8rem; display: block; margin-bottom: 4px;">👏</span>
                            <span style="font-family: var(--ca-font-display); font-weight: 800; font-size: 0.85rem; display: block;">EXCELENTE</span>
                            <span class="count-badge" id="count-recomiendo" style="font-size: 0.78rem; color: var(--ca-volt); font-weight: 700;">{{ $counts['recomiendo'] }}</span>
                        </button>
                    </div>
                </div>

                <!-- COMPARTIR EN REDES -->
                <div class="p-4 my-4 rounded text-center" style="background: rgba(255,255,255,0.02); border: 1px solid var(--ca-border);">
                    <h4 class="fw-bold text-white mb-1" style="font-family: var(--ca-font-display); font-size: 1.1rem; letter-spacing: 0.5px;">
                        ¿TE GUSTÓ ESTA COBERTURA DEPORTIVA?
                    </h4>
                    <p class="text-muted small mb-3">Compártela en tus grupos de fútbol, amigos y redes sociales</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($titulo) }}&url={{ urlencode($url) }}" target="_blank" class="btn btn-sm text-white px-3 py-2 fw-bold" style="background: #1DA1F2; font-family: var(--ca-font-display); letter-spacing: 0.5px;"><i class="fab fa-x-twitter me-1"></i> Twitter / X</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" target="_blank" class="btn btn-sm text-white px-3 py-2 fw-bold" style="background: #1877F2; font-family: var(--ca-font-display); letter-spacing: 0.5px;"><i class="fab fa-facebook-f me-1"></i> Facebook</a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($titulo . ' ' . $url) }}" target="_blank" class="btn btn-sm text-white px-3 py-2 fw-bold" style="background: #25D366; font-family: var(--ca-font-display); letter-spacing: 0.5px;"><i class="fab fa-whatsapp me-1"></i> WhatsApp</a>
                        <button type="button" onclick="copySportsLink('{{ $url }}')" class="btn btn-sm text-white px-3 py-2 fw-bold" style="background: #475569; font-family: var(--ca-font-display); letter-spacing: 0.5px; border: none;"><i class="fas fa-link me-1"></i> Copiar Enlace</button>
                    </div>
                </div>

                <!-- SECCIÓN DE COMENTARIOS REALES -->
                <div id="comentarios-section" class="pt-4 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="ca-sec-title mb-0" style="font-size: 1.25rem;">
                            <i class="fas fa-comments text-danger me-2"></i> COMENTARIOS ({{ $noticia->comentariosAprobados->count() }})
                        </h3>
                        <span class="text-muted small">Tribuna de Hinchas</span>
                    </div>

                    @if(session('comment_success'))
                        <div class="p-3 mb-4 rounded d-flex align-items-center gap-2" style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #6ee7b7; font-size: 0.85rem;">
                            <i class="fas fa-check-circle fs-5"></i>
                            <div>{{ session('comment_success') }}</div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="p-3 mb-4 rounded d-flex align-items-center gap-2" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; font-size: 0.85rem;">
                            <i class="fas fa-exclamation-circle fs-5"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <!-- Formulario para Comentar -->
                    <div class="p-4 mb-4 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--ca-border);">
                        <h4 class="fw-bold text-white mb-3" style="font-family: var(--ca-font-display); font-size: 1.05rem;">
                            <i class="fas fa-pen-nib text-success me-1"></i> DEJA TU OPINIÓN EN LA TRIBUNA
                        </h4>
                        <form action="{{ route('noticias.comentarios.store', $noticia->id) }}" method="POST">
                            @csrf
                            {{-- Honeypot anti-spam --}}
                            <input type="text" name="website_hp" style="display:none;" tabindex="-1" autocomplete="off">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-light">Tu Nombre o Alias <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Carlos Gol" class="form-control" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.15); color: #fff; font-size: 0.88rem;">
                                    @error('nombre')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-light">Correo Electrónico <span class="text-muted">(Opcional, no visible)</span></label>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" class="form-control" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.15); color: #fff; font-size: 0.88rem;">
                                    @error('email')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-light">Comentario <span class="text-danger">*</span></label>
                                <textarea name="contenido" rows="3" required placeholder="Escribe tu análisis, crítica o pronóstico con respeto..." class="form-control" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.15); color: #fff; font-size: 0.88rem; resize: vertical;">{{ old('contenido') }}</textarea>
                                @error('contenido')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-sm fw-bold px-4 py-2" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display); font-size: 0.9rem; letter-spacing: 0.5px;">
                                    <i class="fas fa-paper-plane me-1"></i> PUBLICAR COMENTARIO
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de Comentarios Aprobados -->
                    <div class="comments-list d-flex flex-column gap-3">
                        @forelse($noticia->comentariosAprobados as $comentario)
                            <div class="p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid var(--ca-border);">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 32px; height: 32px; background: var(--ca-volt); color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-family: var(--ca-font-display); font-size: 0.85rem;">
                                            {{ strtoupper(substr($comentario->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="text-white d-block" style="font-size: 0.88rem;">{{ $comentario->nombre }}</strong>
                                            <small class="text-muted" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($comentario->created_at)->locale('es')->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0 text-light small" style="line-height: 1.55; color: #CBD5E1 !important;">
                                    {{ $comentario->contenido }}
                                </p>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="far fa-comment-dots fs-2 mb-2 d-block opacity-50"></i>
                                <p class="small mb-0">Aún no hay comentarios en esta noticia. ¡Sé el primero en opinar!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </article>

            <!-- NOTICIAS RELACIONADAS DEPORTIVAS REALES -->
            <div class="mt-5">
                <div class="ca-sec-header">
                    <h3 class="ca-sec-title" style="font-size: 1.25rem;">MÁS NOTICIAS EN CONTRA ATAQUE</h3>
                    <a href="{{ route('contraataque.index') }}" class="btn btn-sm btn-outline-light" style="font-family: var(--ca-font-display); font-size: 0.78rem;">
                        VER TODAS <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-3">
                    @foreach($relacionadas as $rel)
                        @php
                            $rId = $rel->id;
                            $rTitulo = $rel->titulo;
                            $rImg = method_exists($rel, 'getImageUrl') ? $rel->getImageUrl() : ($rel->imagen ?? $rel->foto ?? '');
                            $rCat = $rel->category->name ?? 'DEPORTES';
                            $rFecha = $rel->created_at ? $rel->created_at->diffForHumans() : 'Hoy';
                            $rSlug = \Illuminate\Support\Str::slug($rTitulo) ?: 'noticia';
                            $rUrl = is_object($rel) && isset($rel->url) ? $rel->url : route('contraataque.show', ['id' => $rId, 'slug' => $rSlug]);
                        @endphp
                        <div class="col-md-6">
                            <div class="ca-card p-3 d-flex gap-3 h-100">
                                <div style="width: 88px; height: 88px; min-width: 88px; border-radius: 6px; overflow: hidden; border: 1px solid var(--ca-border);">
                                    <a href="{{ $rUrl }}">
                                        <img src="{{ $rImg ?: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80' }}" 
                                             alt="{{ $rTitulo }}" 
                                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                                             onmouseover="this.style.transform='scale(1.05)'" 
                                             onmouseout="this.style.transform='scale(1)'"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80'">
                                    </a>
                                </div>
                                <div class="d-flex flex-column justify-content-between">
                                    <div>
                                        <span class="badge" style="background: rgba(0, 255, 135, 0.1); color: var(--ca-volt); font-size: 0.65rem; font-family: var(--ca-font-display);">
                                            {{ $rCat }}
                                        </span>
                                        <h4 style="font-size: 0.92rem; font-weight: 700; line-height: 1.25; margin-top: 4px; margin-bottom: 4px;">
                                            <a href="{{ $rUrl }}" class="text-white hover:text-emerald-400">
                                                {{ \Illuminate\Support\Str::limit($rTitulo, 65) }}
                                            </a>
                                        </h4>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $rFecha }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Columna Lateral: Marcadores, Posiciones y Widget -->
        <div class="col-lg-4">

            <!-- Partidos de la Fecha -->
            <div class="ca-card p-3 mb-4">
                <div class="ca-sec-header mb-3">
                    <span class="ca-sec-title" style="font-size: 1.05rem;">PARTIDOS DE LA FECHA</span>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($partidosVivo ?? [] as $p)
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.68rem;">
                                <span class="text-muted">{{ $p['torneo'] }}</span>
                                @if($p['estado'] === 'EN VIVO')
                                    <span class="ca-badge-live"><i class="fas fa-circle" style="font-size:4px;"></i> {{ $p['minuto'] }}</span>
                                @elseif($p['estado'] === 'FINAL')
                                    <span class="ca-badge-ft">FINAL</span>
                                @else
                                    <span style="color: var(--ca-gold); font-weight: 700;">{{ $p['estado'] }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-white small text-truncate" style="max-width: 42%;">{{ $p['local'] }}</span>
                                <span class="fw-bold text-success px-2 py-0 rounded" style="background:#000; font-family: var(--ca-font-display); font-size: 0.95rem;">{{ $p['goles_local'] }} - {{ $p['goles_visitante'] }}</span>
                                <span class="fw-bold text-white small text-truncate text-end" style="max-width: 42%;">{{ $p['visitante'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tabla de Posiciones Compacta -->
            <div class="ca-card p-3 mb-4">
                <div class="ca-sec-header mb-3">
                    <span class="ca-sec-title" style="font-size: 1.05rem;">POSICIONES LIGA</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
                        <thead>
                            <tr style="color: #94A3B8; border-bottom: 1px solid var(--ca-volt); font-family: var(--ca-font-display);">
                                <th>#</th>
                                <th>Club</th>
                                <th class="text-center">PJ</th>
                                <th class="text-center" style="color: var(--ca-volt);">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($tablaPosiciones ?? [], 0, 6) as $r)
                                <tr>
                                    <td>{{ $r['pos'] }}</td>
                                    <td><strong class="text-white">{{ $r['club'] }}</strong></td>
                                    <td class="text-center text-muted">{{ $r['pj'] }}</td>
                                    <td class="text-center fw-bold" style="color: var(--ca-volt);">{{ $r['pts'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <a href="{{ route('contraataque.index') }}#tablaPosiciones" class="btn btn-sm btn-outline-secondary w-100" style="font-size: 0.75rem; font-family: var(--ca-font-display);">VER TABLA COMPLETA</a>
                </div>
            </div>

            <!-- Widget Redes Sociales Contra Ataque -->
            <div class="ca-card p-4 text-center">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--ca-volt); color: #000; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 12px; font-weight: 900;">
                    <i class="fas fa-running"></i>
                </div>
                <h4 class="fw-bold text-white mb-1" style="font-family: var(--ca-font-display); font-size: 1.15rem;">SÍGUENOS EN LA CANCHA</h4>
                <p class="text-muted small mb-3">No te pierdas los goles al instante, fichajes y coberturas exclusivas.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="https://facebook.com/uhtvbolivia" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.06); color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 6px;"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/UhtvBol" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.06); color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 6px;"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://youtube.com/@UHTVBolivia" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.06); color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 6px;"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- LIGHTBOX MODAL PARA FOTOGALERÍA -->
@if($noticia->galeria && $noticia->galeria->count() > 0)
<div id="sports-lightbox-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(5,8,14,0.96);backdrop-filter:blur(8px);flex-direction:column;align-items:center;justify-content:space-between;padding:16px;">
    <!-- Barra Superior -->
    <div style="width:100%;max-width:1200px;display:flex;align-items:center;justify-content:space-between;color:#fff;padding:8px 12px;border-bottom:1px solid rgba(255,255,255,0.1);">
        <div style="font-family:var(--ca-font-display);font-weight:800;font-size:0.9rem;display:flex;align-items:center;gap:8px;">
            <span class="badge" style="background:var(--ca-volt);color:#000;font-weight:900;"><i class="fas fa-camera me-1"></i> CONTRA ATAQUE</span>
            <span id="sports-lightbox-counter" style="color:#94A3B8;">1 / {{ $noticia->galeria->count() }}</span>
        </div>
        <button type="button" onclick="closeSportsLightbox()" style="background:none;border:none;color:#fff;font-size:1.8rem;cursor:pointer;line-height:1;padding:4px 8px;transition:color 0.2s;" onmouseover="this.style.color='var(--ca-volt)'" onmouseout="this.style.color='#fff'">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Escenario Principal -->
    <div style="position:relative;width:100%;max-width:1100px;flex:1;display:flex;align-items:center;justify-content:center;padding:16px 0;min-height:0;">
        <button type="button" onclick="prevSportsLightbox(event)" style="position:absolute;left:10px;z-index:10;background:rgba(0,0,0,0.7);border:1px solid rgba(255,255,255,0.2);color:#fff;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--ca-volt)';this.style.color='var(--ca-volt)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='#fff'">
            <i class="fas fa-chevron-left"></i>
        </button>

        <img id="sports-lightbox-img" src="" alt="Fotografía deportiva" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:6px;box-shadow:0 10px 40px rgba(0,0,0,0.8);border:1px solid var(--ca-border);">

        <button type="button" onclick="nextSportsLightbox(event)" style="position:absolute;right:10px;z-index:10;background:rgba(0,0,0,0.7);border:1px solid rgba(255,255,255,0.2);color:#fff;width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--ca-volt)';this.style.color='var(--ca-volt)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='#fff'">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Pie de Foto -->
    <div style="width:100%;max-width:900px;text-align:center;padding:12px 16px;background:rgba(17,24,39,0.85);border-radius:6px;border-left:3px solid var(--ca-volt);color:#fff;">
        <p id="sports-lightbox-caption" style="margin:0;font-size:0.9rem;color:#E2E8F0;line-height:1.4;"></p>
    </div>
</div>
@endif

<script>
// --- COPIAR ENLACE ---
function copySportsLink(text) {
    navigator.clipboard.writeText(text).then(function() {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:var(--ca-bg-card);border:1px solid var(--ca-volt);color:#fff;padding:12px 20px;border-radius:6px;font-family:var(--ca-font-display);font-weight:700;font-size:0.88rem;box-shadow:0 6px 20px rgba(0,255,135,0.3);transform:translateX(120%);transition:transform 0.3s;';
        toast.innerHTML = '<i class="fas fa-check-circle" style="margin-right:8px;color:var(--ca-volt);"></i> Enlace copiado al portapapeles';
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 50);
        setTimeout(() => { toast.style.transform = 'translateX(120%)'; setTimeout(() => document.body.removeChild(toast), 300); }, 2500);
    });
}

// --- AJUSTE DE TAMAÑO DE FUENTE ---
let sportsFontSizeIndex = 1;
const sportsFontSizes = ['0.95rem', '1.12rem', '1.3rem', '1.45rem'];
function adjustSportsFontSize(delta) {
    if (delta === 0) {
        sportsFontSizeIndex = 1;
    } else {
        sportsFontSizeIndex = Math.max(0, Math.min(sportsFontSizes.length - 1, sportsFontSizeIndex + delta));
    }
    const bodyEl = document.getElementById('sportsArticleBody');
    if (bodyEl) {
        bodyEl.style.fontSize = sportsFontSizes[sportsFontSizeIndex];
    }
}

// --- TEXT TO SPEECH (ESCUCHAR NOTICIA DEPORTIVA) ---
let sportsSynth = window.speechSynthesis;
let sportsUtterance = null;
let sportsIsSpeaking = false;
let sportsIsPaused = false;
let sportsTtsSpeed = 1;

function getSportsCleanText() {
    const title = "{{ addslashes($titulo) }}";
    const lead = "{{ addslashes(strip_tags($bajada)) }}";
    const bodyEl = document.getElementById('sportsArticleBody');
    const bodyText = bodyEl ? bodyEl.innerText : "";
    return `${title}. ${lead}. ${bodyText}`;
}

function toggleSportsTts() {
    if (!('speechSynthesis' in window)) {
        alert('Tu navegador no cuenta con soporte para síntesis de voz.');
        return;
    }

    const btnToggle = document.getElementById('btnTtsToggle');
    const ttsIcon = document.getElementById('ttsIcon');
    const ttsLabel = document.getElementById('ttsLabel');
    const btnStop = document.getElementById('btnTtsStop');
    const speedControls = document.getElementById('ttsSpeedControls');

    if (sportsIsSpeaking && !sportsIsPaused) {
        sportsSynth.pause();
        sportsIsPaused = true;
        ttsIcon.className = 'fas fa-play';
        ttsLabel.innerText = 'REANUDAR';
        btnToggle.classList.remove('active');
    } else if (sportsIsSpeaking && sportsIsPaused) {
        sportsSynth.resume();
        sportsIsPaused = false;
        ttsIcon.className = 'fas fa-pause';
        ttsLabel.innerText = 'PAUSAR';
        btnToggle.classList.add('active');
    } else {
        sportsSynth.cancel();
        const text = getSportsCleanText();
        sportsUtterance = new SpeechSynthesisUtterance(text);
        sportsUtterance.lang = 'es-ES';
        sportsUtterance.rate = sportsTtsSpeed;

        const voices = sportsSynth.getVoices();
        const esVoice = voices.find(v => v.lang.startsWith('es'));
        if (esVoice) sportsUtterance.voice = esVoice;

        sportsUtterance.onstart = function() {
            sportsIsSpeaking = true;
            sportsIsPaused = false;
            ttsIcon.className = 'fas fa-pause';
            ttsLabel.innerText = 'PAUSAR';
            btnToggle.classList.add('active');
            btnStop.style.display = 'inline-flex';
            speedControls.style.display = 'inline-flex';
        };

        sportsUtterance.onend = function() {
            stopSportsTts();
        };

        sportsUtterance.onerror = function() {
            stopSportsTts();
        };

        sportsSynth.speak(sportsUtterance);
    }
}

function stopSportsTts() {
    if ('speechSynthesis' in window) {
        sportsSynth.cancel();
    }
    sportsIsSpeaking = false;
    sportsIsPaused = false;
    const btnToggle = document.getElementById('btnTtsToggle');
    const ttsIcon = document.getElementById('ttsIcon');
    const ttsLabel = document.getElementById('ttsLabel');
    const btnStop = document.getElementById('btnTtsStop');
    const speedControls = document.getElementById('ttsSpeedControls');

    if (btnToggle) {
        btnToggle.classList.remove('active');
        ttsIcon.className = 'fas fa-volume-up';
        ttsLabel.innerText = 'ESCUCHAR NOTICIA';
        btnStop.style.display = 'none';
        speedControls.style.display = 'none';
    }
}

function setSportsTtsSpeed(speed, btn) {
    sportsTtsSpeed = speed;
    document.querySelectorAll('#ttsSpeedControls .ca-toolbar-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    if (sportsIsSpeaking) {
        sportsSynth.cancel();
        sportsIsSpeaking = false;
        toggleSportsTts();
    }
}

if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => { window.speechSynthesis.getVoices(); };
}

// --- REACCIONES INTERACTIVAS CON AJAX ---
function submitSportsReaction(tipo) {
    const btns = document.querySelectorAll('.ca-reaction-btn');

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
            document.getElementById('count-me_informa').innerText = data.counts.me_informa;
            document.getElementById('count-interesante').innerText = data.counts.interesante;
            document.getElementById('count-me_indigna').innerText = data.counts.me_indigna;
            document.getElementById('count-recomiendo').innerText = data.counts.recomiendo;
            document.getElementById('reactionsTotalBadge').innerText = data.counts.total + ' reacciones';

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

// --- LIGHTBOX INTERACTIVO DE FOTOGALERÍA ---
@if($noticia->galeria && $noticia->galeria->count() > 0)
const sportsGalleryData = @json($noticia->galeria->map(function($f) {
    return [
        'url' => $f->image_url,
        'caption' => $f->pie_de_foto ?? ''
    ];
}));

let currentSportsLightboxIndex = 0;

function openSportsLightbox(index) {
    currentSportsLightboxIndex = index;
    updateSportsLightboxUI();
    const modal = document.getElementById('sports-lightbox-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeSportsLightbox() {
    const modal = document.getElementById('sports-lightbox-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function nextSportsLightbox(e) {
    if (e) e.stopPropagation();
    if (sportsGalleryData.length === 0) return;
    currentSportsLightboxIndex = (currentSportsLightboxIndex + 1) % sportsGalleryData.length;
    updateSportsLightboxUI();
}

function prevSportsLightbox(e) {
    if (e) e.stopPropagation();
    if (sportsGalleryData.length === 0) return;
    currentSportsLightboxIndex = (currentSportsLightboxIndex - 1 + sportsGalleryData.length) % sportsGalleryData.length;
    updateSportsLightboxUI();
}

function updateSportsLightboxUI() {
    const photo = sportsGalleryData[currentSportsLightboxIndex];
    if (!photo) return;

    const img = document.getElementById('sports-lightbox-img');
    const counter = document.getElementById('sports-lightbox-counter');
    const caption = document.getElementById('sports-lightbox-caption');

    if (img) img.src = photo.url;
    if (counter) counter.innerText = `${currentSportsLightboxIndex + 1} / ${sportsGalleryData.length}`;
    if (caption) caption.innerText = photo.caption || `Fotografía ${currentSportsLightboxIndex + 1}`;
}

document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('sports-lightbox-modal');
    if (modal && modal.style.display === 'flex') {
        if (e.key === 'Escape') closeSportsLightbox();
        if (e.key === 'ArrowRight') nextSportsLightbox();
        if (e.key === 'ArrowLeft') prevSportsLightbox();
    }
});
@endif
</script>
@endsection
