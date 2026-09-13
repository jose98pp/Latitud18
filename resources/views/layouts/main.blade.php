<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Latitud18">
    <title>@yield('title', 'Latitud18 - Información Sin Ruido')</title>
    @section('meta')
      <meta name="description" content="{{ setting('footer_about', 'Portal de noticias 24/7. Periodismo independiente y cobertura multimedia.') }}">
      <meta property="og:site_name" content="{{ setting('site_name', 'Latitud 18') }}">
      <meta property="og:type" content="website">
      <meta property="og:title" content="{{ setting('site_name', 'Latitud 18') }} — {{ setting('site_slogan', 'Información Sin Ruido') }}">
      <meta property="og:description" content="{{ setting('footer_about', 'Portal de noticias 24/7. Periodismo independiente.') }}">
      <meta property="og:url" content="{{ url()->current() }}">
      <meta name="twitter:card" content="summary_large_image">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B1F3A">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LATITUD 18">
    <link rel="apple-touch-icon" href="/images/Logo.jpg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Montserrat:wght@400;500;600;700;800;900&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        use App\Helpers\AssetHelper;
        $cssFiles = AssetHelper::getAllCssFiles();
        $jsFiles = ['resources/js/app.jsx'];
        $allAssets = array_merge($cssFiles, $jsFiles);
    @endphp
    @vite($allAssets)
    <style>
        :root {
            --color-red:#D71920;--color-red-dark:#b51218;--color-red-light:#fef2f2;
            --color-navy:#0B1F3A;--color-navy-dark:#061120;--color-navy-light:#162c4e;--color-navy-subtle:#f0f4f9;
            --color-bg-page:#F8FAFC;--color-card-bg:#FFFFFF;
            --color-text-main:#111827;--color-text-secondary:#374151;--color-text-muted:#6B7280;--color-border:#E5E7EB;
            --font-title-anton:'Anton',Impact,sans-serif;--font-title-bebas:'Bebas Neue',Impact,sans-serif;
            --font-title-montserrat:'Montserrat',sans-serif;--font-body:'Source Sans 3','Montserrat',-apple-system,sans-serif;
        }
        [data-theme="dark"] {
            --color-bg-page:#080E18;--color-card-bg:#0D1726;--color-navy-subtle:#132238;
            --color-text-main:#F9FAFB;--color-text-secondary:#D1D5DB;--color-text-muted:#9CA3AF;--color-border:#1E293B;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{overflow-x:hidden}
        body{font-family:var(--font-body);background:var(--color-bg-page);color:var(--color-text-main);-webkit-font-smoothing:antialiased;line-height:1.6;transition:background .3s,color .3s}
        .container{max-width:1240px;margin:0 auto;padding:0 16px}
        [data-theme="dark"] .site-top-bar{background:#040810!important;border-bottom-color:#1a2744!important}
        [data-theme="dark"] .site-header{background:#0D1726!important}
        [data-theme="dark"] .site-nav{background:#0B1F3A!important}
        [data-theme="dark"] .site-main-footer{background:#040810!important}
        [data-theme="dark"] [style*="background:var(--color-card-bg)"],[data-theme="dark"] [style*="background: var(--color-card-bg)"]{background-color:var(--color-card-bg)!important}
        [data-theme="dark"] [style*="background:var(--color-navy-subtle)"],[data-theme="dark"] [style*="background: var(--color-navy-subtle)"]{background-color:var(--color-navy-subtle)!important}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
        @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
        ::-webkit-scrollbar{width:8px}::-webkit-scrollbar-track{background:var(--color-bg-page)}::-webkit-scrollbar-thumb{background:var(--color-navy);border-radius:4px}::-webkit-scrollbar-thumb:hover{background:var(--color-red)}

        /* === MODAL SYSTEM === */
        .modal-overlay{position:fixed;inset:0;z-index:9998;background:rgba(6,17,32,.92);backdrop-filter:blur(8px);display:none;align-items:flex-start;justify-content:center;padding-top:60px;overflow-y:auto}
        .modal-overlay.active{display:flex}
        .modal-reader-card{background:var(--color-card-bg);border-radius:4px;width:90%;max-width:600px;box-shadow:0 20px 60px rgba(0,0,0,.4);animation:fadeIn .25s;margin-bottom:40px}
        .modal-header-bar{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:var(--color-navy);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.82rem;text-transform:uppercase;letter-spacing:1px;border-radius:4px 4px 0 0;border-bottom:2px solid var(--color-red)}
        .btn-modal-control{background:none;border:none;color:#94A3B8;font-size:1.2rem;cursor:pointer;transition:color .2s;padding:4px}.btn-modal-control:hover{color:var(--color-red)}

        /* === NEWSPAPER MODAL === */
        .newspaper-modal-overlay{position:fixed;inset:0;z-index:9999;background:rgba(6,17,32,.96);backdrop-filter:blur(10px);display:none;flex-direction:column}
        .newspaper-modal-overlay.active{display:flex}
        .newspaper-modal-header{display:flex;align-items:center;justify-content:space-between;padding:12px 24px;background:var(--color-navy-dark);border-bottom:2px solid var(--color-red);flex-shrink:0}
        .newspaper-modal-body-scroll{flex:1;overflow-y:auto;background:#E5E7EB;padding:24px}
        .newspaper-stage{display:flex;justify-content:center}
        .newspaper-sheet{background:#fff;width:840px;min-height:1100px;padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.15);margin-bottom:24px}
        .grid-5-columns{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
        .newspaper-pages-strip{display:flex;gap:10px;overflow-x:auto;padding:4px 0}
        .page-thumb-card{min-width:100px;cursor:pointer;border:2px solid transparent;border-radius:4px;overflow:hidden;transition:all .2s;text-align:center;background:rgba(255,255,255,.06)}
        .page-thumb-card:hover,.page-thumb-card.active{border-color:var(--color-red);background:rgba(215,25,32,.1)}
        .page-thumb-preview{height:60px;background:rgba(255,255,255,.08);margin:6px;border-radius:2px;display:flex;flex-direction:column;gap:3px;padding:6px}
        .page-thumb-preview .bar{height:4px;border-radius:1px}
        .page-thumb-label{font-size:.65rem;color:#94A3B8;padding:4px 6px;font-family:var(--font-title-montserrat);font-weight:700}
        .btn-newspaper-print,.btn-pdf-download{font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;padding:7px 14px;border-radius:2px;cursor:pointer;border:none;transition:all .2s}
        .btn-newspaper-print{background:var(--color-navy);color:#fff}.btn-newspaper-print:hover{background:var(--color-navy-light)}
        .btn-pdf-download{background:var(--color-red);color:#fff;box-shadow:0 2px 12px rgba(215,25,32,.3)}.btn-pdf-download:hover{background:var(--color-red-dark)}
        .btn-close-newspaper-modal{width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.08);border:none;color:#fff;font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s}.btn-close-newspaper-modal:hover{background:var(--color-red)}
        .view-mode-selector{display:flex;gap:4px;margin-right:12px}
        .btn-mode-tab{font-family:var(--font-title-montserrat);font-weight:700;font-size:.68rem;text-transform:uppercase;padding:6px 12px;border:1px solid rgba(255,255,255,.15);background:transparent;color:#94A3B8;border-radius:2px;cursor:pointer;transition:all .2s}
        .btn-mode-tab.active{background:var(--color-red);color:#fff;border-color:var(--color-red)}

        /* Newspaper page styles */
        .paper-top-folio{display:flex;justify-content:space-between;font-size:.7rem;color:var(--color-text-muted);border-bottom:1px solid var(--color-border);padding-bottom:8px;margin-bottom:16px}
        .paper-masthead-title{font-family:var(--font-title-anton);font-size:3rem;color:var(--color-navy);line-height:1}
        .paper-masthead-title span{background:var(--color-red);color:#fff;padding:2px 10px;border-radius:2px;font-size:1.8rem}
        .paper-masthead-slogan{font-family:var(--font-title-montserrat);font-weight:700;font-size:.6rem;text-transform:uppercase;letter-spacing:3px;color:var(--color-text-muted)}
        .paper-inner-header{background:var(--color-navy);color:#fff;padding:10px 16px;font-family:var(--font-title-montserrat);font-weight:900;font-size:.82rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;border-left:4px solid var(--color-red)}
        .paper-article-head{font-family:var(--font-title-montserrat);font-weight:900;font-size:1rem;color:var(--color-navy);line-height:1.25;margin-bottom:8px}
        .paper-body-text{font-size:.85rem;line-height:1.7;color:var(--color-text-secondary)}
        .paper-body-text.dropcap::first-letter{font-family:var(--font-title-anton);font-size:28pt;float:left;line-height:.8;margin-right:6px;color:var(--color-navy)}
        .numbered-points-box{background:var(--color-red-light);border:1px solid #FCA5A5;border-left:4px solid var(--color-red);padding:12px 16px;border-radius:2px;margin:12px 0;font-size:.82rem}
        .paper-bottom-footer{border-top:2px solid var(--color-navy);padding-top:8px;margin-top:20px;display:flex;justify-content:space-between;font-size:.65rem;color:var(--color-text-muted)}
        .cover-hero-image-wrap{position:relative;border-radius:2px;overflow:hidden;margin-bottom:16px}
        .cover-overlay-headline{position:absolute;bottom:0;left:0;right:0;padding:16px 20px;background:linear-gradient(transparent,rgba(0,0,0,.85))}
        .cover-big-headline{font-family:var(--font-title-bebas);font-size:2.2rem;color:#fff;line-height:1}
        .cover-section-pill{display:inline-block;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.6rem;text-transform:uppercase;padding:2px 8px;border-radius:2px;margin-bottom:8px}
        .cover-sub-stories{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
        .cover-sub-card{border:1px solid var(--color-border);border-radius:2px;padding:10px}
        .cover-sub-card h4{font-family:var(--font-title-montserrat);font-weight:800;font-size:.78rem;color:var(--color-navy);line-height:1.3;margin-bottom:4px}
        .cover-sub-card p{font-size:.72rem;color:var(--color-text-muted);line-height:1.4}

        /* === LIVE STREAMING MODAL === */
        .live-stream-modal-overlay{position:fixed;inset:0;z-index:9999;background:rgba(3,7,18,.96);backdrop-filter:blur(10px);display:none;flex-direction:column}
        .live-stream-modal-overlay.active{display:flex}
        .live-modal-header{display:flex;align-items:center;justify-content:space-between;padding:12px 24px;background:var(--color-navy-dark);border-bottom:2px solid var(--color-red);flex-shrink:0}
        .live-modal-body-scroll{flex:1;overflow-y:auto;padding:24px 0}
        .studio-grid{display:grid;grid-template-columns:2.2fr 1fr;gap:20px}
        .tv-set-wrapper{background:#000;border:2px solid #1a2744;border-radius:4px;overflow:hidden}
        .tv-screen-frame{position:relative;aspect-ratio:16/9;background:#0a0a0a}
        .tv-live-badge{position:absolute;top:12px;left:12px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.65rem;padding:4px 10px;border-radius:2px;display:flex;align-items:center;gap:6px}
        .tv-watermark{position:absolute;top:12px;right:12px;font-family:var(--font-title-anton);font-size:1rem;color:rgba(255,255,255,.2)}
        .lower-third-overlay{position:absolute;bottom:20px;left:20px;right:20px;background:rgba(11,31,58,.95);backdrop-filter:blur(8px);border-radius:2px;border-left:4px solid var(--color-red);padding:12px 16px;transition:all .3s}
        .lt-badge{font-family:var(--font-title-montserrat);font-weight:800;font-size:.6rem;text-transform:uppercase;color:var(--color-red);margin-bottom:4px}
        .lt-headline{font-family:var(--font-title-bebas);font-size:1.4rem;color:#fff;line-height:1.1}
        .lt-subheadline{font-size:.78rem;color:#94A3B8;margin-top:4px}
        .lt-location{font-size:.65rem;color:#64748B;margin-top:6px}
        .studio-control-bar{display:flex;align-items:center;gap:12px;padding:10px 14px;background:rgba(11,31,58,.9);border-top:1px solid rgba(255,255,255,.06);font-size:.75rem;color:#94A3B8}
        .btn-lt-switcher{font-family:var(--font-title-montserrat);font-weight:700;font-size:.68rem;padding:5px 12px;border:1px solid rgba(255,255,255,.15);background:transparent;color:#94A3B8;border-radius:2px;cursor:pointer;transition:all .2s}
        .btn-lt-switcher.active{background:var(--color-red);color:#fff;border-color:var(--color-red)}
        .live-chat-panel{background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:4px;display:flex;flex-direction:column;max-height:420px}
        .chat-header{padding:12px 16px;border-bottom:1px solid var(--color-border);display:flex;align-items:center;justify-content:space-between}
        .chat-header h3{font-family:var(--font-title-montserrat);font-weight:800;font-size:.82rem;color:var(--color-text-main)}
        .chat-viewers-count{font-size:.7rem;color:var(--color-text-muted)}
        .chat-messages-scroll{flex:1;overflow-y:auto;padding:12px 16px;min-height:200px}
        .chat-msg{padding:8px 0;border-bottom:1px solid var(--color-border);font-size:.8rem;color:var(--color-text-secondary)}
        .chat-msg.admin{border-left:3px solid var(--color-red);padding-left:10px;background:var(--color-red-light);margin:4px -16px;padding:8px 16px}
        .chat-input-form{display:flex;border-top:1px solid var(--color-border)}
        .chat-input{flex:1;padding:10px 14px;border:none;background:transparent;font-size:.85rem;color:var(--color-text-main);outline:none;font-family:var(--font-body)}
        .btn-send-chat{background:var(--color-red);border:none;color:#fff;padding:10px 16px;cursor:pointer;transition:background .2s}.btn-send-chat:hover{background:var(--color-red-dark)}
        .radio-studio-card{display:grid;grid-template-columns:1.1fr 1fr;gap:20px;margin-top:20px;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:4px;border-left:4px solid var(--color-red);padding:20px}
        .radio-icon-circle{width:44px;height:44px;background:var(--color-red);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem}
        .radio-visualizer-canvas{width:100%;height:60px;border-radius:4px;background:#0a0a0a;margin:12px 0}
        .btn-radio-play{width:44px;height:44px;background:var(--color-red);border:none;border-radius:50%;color:#fff;font-size:1rem;cursor:pointer;transition:transform .2s}.btn-radio-play:hover{transform:scale(1.05)}
        .podcast-item-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--color-border)}
        .podcast-item-row:last-child{border-bottom:none}
        .pod-title{font-size:.82rem;color:var(--color-text-main);font-weight:600}.pod-duration{font-size:.7rem;color:var(--color-text-muted)}
        .btn-play-pod-mini{background:none;border:none;color:var(--color-red);font-size:1.2rem;cursor:pointer}
    </style>
</head>
<body>

  <!-- TOP BAR -->
  <aside class="site-top-bar" style="background:var(--color-navy-dark);color:#94A3B8;font-size:.75rem;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.06)">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
      <div style="display:flex;align-items:center;gap:12px;min-width:0">
        <span id="topbar-date-text" style="font-weight:600;white-space:nowrap">Cargando fecha...</span>
        <span style="background:var(--color-red);color:#fff;font-weight:800;font-size:.6rem;padding:2px 8px;border-radius:2px;text-transform:uppercase;letter-spacing:.5px;animation:pulse 1.5s infinite;white-space:nowrap">
          <i class="fas fa-circle" style="font-size:5px;margin-right:3px;vertical-align:middle"></i>ÚLTIMA HORA
        </span>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        @if(setting('social_facebook'))
          <a href="{{ setting('social_facebook') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-facebook-f"></i></a>
        @endif
        @if(setting('social_youtube'))
          <a href="{{ setting('social_youtube') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-youtube"></i></a>
        @endif
        @if(setting('social_instagram'))
          <a href="{{ setting('social_instagram') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-instagram"></i></a>
        @endif
        @if(setting('social_twitter'))
          <a href="{{ setting('social_twitter') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-x-twitter"></i></a>
        @endif
        @if(setting('social_tiktok'))
          <a href="{{ setting('social_tiktok') }}" target="_blank" style="color:#94A3B8" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'"><i class="fab fa-tiktok"></i></a>
        @endif
        @if(setting('whatsapp_phone'))
          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('whatsapp_phone')) }}?text={{ urlencode(setting('whatsapp_message', 'Hola')) }}" target="_blank" style="color:#25D366" title="WhatsApp Denuncias"><i class="fab fa-whatsapp"></i></a>
        @endif
        <a href="{{ route('periodico.public.index') }}" style="background:var(--color-navy);color:#fff;border:1px solid rgba(255,255,255,.15);padding:4px 10px;border-radius:2px;font-family:var(--font-title-montserrat);font-weight:700;font-size:.6rem;text-transform:uppercase;text-decoration:none;display:inline-flex;align-items:center;transition:all .2s;white-space:nowrap" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy)';this.style.color='#fff'">
          <i class="fas fa-newspaper" style="margin-right:4px;color:var(--color-red)"></i> PERIÓDICO DIGITAL
        </a>
      </div>
    </div>
  </aside>

  <!-- MASTHEAD -->
  <header class="site-header" style="background:var(--color-card-bg);border-bottom:3px solid var(--color-red);padding:12px 0;transition:background .3s">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between">
      <a href="/" style="display:flex;align-items:center;gap:12px;text-decoration:none">
        <div>
          <div style="display:flex;align-items:baseline;gap:4px">
            <span style="font-family:var(--font-title-anton);font-size:2.6rem;color:var(--color-navy);line-height:1">LATITUD</span>
            <span style="font-family:var(--font-title-anton);font-size:1.8rem;background:var(--color-red);color:#fff;padding:2px 10px;border-radius:2px;line-height:1">18</span>
          </div>
          <p style="font-family:var(--font-title-montserrat);font-size:.55rem;font-weight:700;text-transform:uppercase;letter-spacing:3px;color:var(--color-text-muted);margin-top:2px">INFORMACIÓN SIN RUIDO</p>
        </div>
      </a>
      <div style="display:flex;align-items:center;gap:10px">
        <button onclick="openSearchModal()" style="display:flex;align-items:center;gap:6px;background:var(--color-navy-subtle);border:1px solid var(--color-border);color:var(--color-text-muted);padding:7px 14px;border-radius:2px;font-family:var(--font-title-montserrat);font-weight:700;font-size:.72rem;cursor:pointer;transition:all .2s" onmouseover="this.style.borderColor='var(--color-red)';this.style.color='var(--color-red)'" onmouseout="this.style.borderColor='var(--color-border)';this.style.color='var(--color-text-muted)'">
          <i class="fas fa-search"></i> BUSCAR
        </button>
        <button onclick="openLiveModal()" style="display:flex;align-items:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;padding:7px 14px;border-radius:2px;border:none;cursor:pointer;transition:background .2s" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
          <span style="width:8px;height:8px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> EN VIVO
        </button>
        <button id="btn-pwa-install" onclick="triggerPwaInstall()" style="display:none;align-items:center;gap:6px;background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2);font-family:var(--font-title-montserrat);font-weight:700;font-size:.7rem;text-transform:uppercase;padding:7px 12px;border-radius:2px;cursor:pointer;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.borderColor='var(--color-red)'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.borderColor='rgba(255,255,255,.2)'">
          <i class="fas fa-download"></i> INSTALAR APP
        </button>
        <a href="{{ route('contraataque.index') }}" style="display:flex;align-items:center;gap:6px;background:linear-gradient(135deg, #090d16 0%, #162032 100%);border:1px solid #00FF87;color:#00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.7rem;text-transform:uppercase;text-decoration:none;padding:7px 14px;border-radius:2px;box-shadow:0 0 10px rgba(0,255,135,0.25);transition:all .2s" onmouseover="this.style.background='#00FF87';this.style.color='#000'" onmouseout="this.style.background='linear-gradient(135deg, #090d16 0%, #162032 100%)';this.style.color='#00FF87'">
          <i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE
        </a>
        <a href="{{ route('periodico.public.index') }}" style="display:flex;align-items:center;gap:6px;background:var(--color-navy);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;text-decoration:none;padding:7px 14px;border-radius:2px;transition:background .2s" onmouseover="this.style.background='var(--color-navy-light)'" onmouseout="this.style.background='var(--color-navy)'">
          <i class="fas fa-newspaper"></i> PERIÓDICO DIGITAL
        </a>
        <button onclick="toggleTheme()" style="background:var(--color-navy-subtle);color:var(--color-navy);border:1px solid var(--color-border);padding:7px 9px;border-radius:2px;cursor:pointer;font-size:.85rem;transition:all .2s" onmouseover="this.style.background='var(--color-navy)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-navy)'">
          <i class="fas fa-sun" id="themeIconSun" style="display:none"></i>
          <i class="fas fa-moon" id="themeIconMoon"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- NAV -->
  <nav class="site-nav" style="background:var(--color-navy);position:sticky;top:0;z-index:100;border-bottom:2px solid var(--color-red);transition:background .3s">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between">
      <ul style="display:flex;align-items:center;gap:0;list-style:none;padding:0;margin:0;white-space:nowrap;overflow-x:auto;flex:1;scrollbar-width:none">
        <li><a href="/" class="nav-item-link" style="display:block;padding:12px 16px;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:border-color .2s"><i class="fas fa-home" style="margin-right:4px"></i> INICIO</a></li>
        @forelse($categorias ?? [] as $categoria)
          @php
            $isSports = str_contains(strtolower($categoria->name), 'deporte') || str_contains(strtolower($categoria->name), 'futbol');
          @endphp
          @if($isSports)
            <li style="background:linear-gradient(90deg, #090d16, #162032); border-left:1px solid #00FF87; border-right:1px solid #00FF87;">
              <a href="{{ route('contraataque.index') }}" class="nav-item-link" style="display:flex;align-items:center;gap:5px;padding:12px 14px;color:#00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:all .2s"><i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE</a>
            </li>
          @else
            <li><a href="{{ route('categoria.noticias', $categoria->id) }}" class="nav-item-link" style="display:block;padding:12px 16px;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:border-color .2s">{{ strtoupper($categoria->name) }}</a></li>
          @endif
        @empty
          @foreach(['Política','País','Santa Cruz','Economía'] as $cat)
            <li><a href="#" class="nav-item-link" style="display:block;padding:12px 16px;color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent">{{ $cat }}</a></li>
          @endforeach
        @endforelse
        <li style="background:linear-gradient(90deg, #090d16, #162032); border-left:1px solid #00FF87; border-right:1px solid #00FF87;">
          <a href="{{ route('contraataque.index') }}" class="nav-item-link" style="display:flex;align-items:center;gap:5px;padding:12px 14px;color:#00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:all .2s"><i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE</a>
        </li>
        <li><a href="{{ route('opinion.index') }}" class="nav-item-link" style="display:block;padding:12px 16px;color:#fca5a5;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;text-decoration:none;border-bottom:3px solid transparent;transition:border-color .2s"><i class="fa-solid fa-pen-nib" style="margin-right:4px"></i> OPINIÓN</a></li>
      </ul>
      <button onclick="openLiveModal()" style="display:flex;align-items:center;gap:6px;background:none;border:none;color:#ff8a8f;font-family:var(--font-title-montserrat);font-weight:800;font-size:.72rem;text-transform:uppercase;cursor:pointer;padding:12px 8px;white-space:nowrap">
        <i class="fas fa-radio"></i> RADIO
      </button>
      <button id="mobileMenuToggle" style="display:none;background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;padding:12px"><i class="fas fa-bars"></i></button>
    </div>
  </nav>

  <!-- MOBILE MENU -->
  <div id="mobileMenu" style="position:fixed;top:0;left:0;width:300px;height:100%;background:var(--color-card-bg);box-shadow:4px 0 24px rgba(0,0,0,.2);transform:translateX(-100%);transition:transform .3s;z-index:9999;overflow-y:auto">
    <div style="background:var(--color-navy);padding:20px;display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-family:var(--font-title-anton);font-size:1.3rem;color:#fff">LATITUD<span style="color:var(--color-red)">18</span></span>
      </div>
      <button onclick="closeMobileMenu()" style="background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer"><i class="fas fa-times"></i></button>
    </div>
    <div style="padding:16px">
      <a href="/" style="display:flex;align-items:center;gap:8px;padding:12px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-weight:600"><i class="fas fa-home"></i> Inicio</a>
      
      <!-- CONTRA ATAQUE PROMINENTE EN MOVIL -->
      <a href="{{ route('contraataque.index') }}" style="display:flex;align-items:center;gap:8px;padding:12px 14px;background:linear-gradient(90deg, #090d16, #162032);color:#00FF87;text-decoration:none;border-radius:4px;margin:12px 0;border:1px solid #00FF87;font-family:var(--font-title-montserrat);font-weight:900;font-size:.85rem;box-shadow:0 0 10px rgba(0,255,135,0.2)">
        <i class="fas fa-bolt" style="color:#FF3B30"></i> CONTRA ATAQUE (DEPORTES)
      </a>

      <div style="margin-top:12px">
        <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-text-muted);font-weight:700;margin-bottom:8px">Secciones</p>
        @forelse($categorias ?? [] as $categoria)
          @php
            $isSportsMob = str_contains(strtolower($categoria->name), 'deporte') || str_contains(strtolower($categoria->name), 'futbol');
          @endphp
          @if($isSportsMob)
            <a href="{{ route('contraataque.index') }}" style="display:flex;align-items:center;gap:8px;padding:10px 0;color:#00FF87;text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-weight:800"><i class="fas fa-bolt" style="font-size:.7rem;color:#FF3B30"></i> {{ $categoria->name }} (Contra Ataque)</a>
          @else
            <a href="{{ route('categoria.noticias', $categoria->id) }}" style="display:flex;align-items:center;gap:8px;padding:10px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;transition:color .2s" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-text-main)'"><i class="fas fa-chevron-right" style="font-size:.6rem;color:var(--color-red)"></i> {{ $categoria->name }}</a>
          @endif
        @empty
        @endforelse
      </div>
      <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px">
        <button onclick="closeMobileMenu();openSearchModal()" style="display:flex;align-items:center;gap:8px;padding:10px 0;color:var(--color-text-main);background:none;border:none;border-bottom:1px solid var(--color-border);font-size:.88rem;cursor:pointer;text-align:left;width:100%;font-family:var(--font-body)"><i class="fas fa-search" style="color:var(--color-red)"></i> Buscar Noticias</button>
        <a href="{{ route('periodico.public.index') }}" style="display:flex;align-items:center;gap:8px;padding:10px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-family:var(--font-body)"><i class="fas fa-newspaper" style="color:var(--color-red)"></i> Periódico Digital</a>
        <a href="{{ route('opinion.index') }}" style="display:flex;align-items:center;gap:8px;padding:10px 0;color:var(--color-text-main);text-decoration:none;border-bottom:1px solid var(--color-border);font-size:.88rem;font-family:var(--font-body)"><i class="fa-solid fa-pen-nib" style="color:var(--color-red)"></i> Opinión & Análisis</a>
      </div>
      <div style="margin-top:16px">
        <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--color-text-muted);font-weight:700;margin-bottom:8px">Síguenos</p>
        <div style="display:flex;gap:8px">
          <a href="https://facebook.com/uhtvbolivia" target="_blank" style="width:34px;height:34px;background:var(--color-navy-subtle);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-facebook-f" style="font-size:.8rem"></i></a>
          <a href="https://www.youtube.com/@UHTVBolivia" target="_blank" style="width:34px;height:34px;background:var(--color-navy-subtle);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-youtube" style="font-size:.8rem"></i></a>
          <a href="https://instagram.com/uhtvbolivia" target="_blank" style="width:34px;height:34px;background:var(--color-navy-subtle);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='var(--color-navy-subtle)';this.style.color='var(--color-text-muted)'"><i class="fab fa-instagram" style="font-size:.8rem"></i></a>
        </div>
      </div>
    </div>
  </div>
  <div id="mobileMenuOverlay" onclick="closeMobileMenu()" style="position:fixed;inset:0;background:rgba(0,0,0,.5);opacity:0;pointer-events:none;transition:opacity .3s;z-index:9998"></div>

  @if(isset($banners['footer']) && $banners['footer']->count() > 0)
    @foreach($banners['footer'] as $banner)
      <div style="display:flex;justify-content:center;padding:8px 0">
        <a href="{{ $banner->link ?? '#' }}" target="_blank" style="display:block;max-width:970px;width:100%"><img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width:100%;border-radius:2px" loading="lazy"></a>
      </div>
    @endforeach
  @endif

  <main style="padding:16px 0 40px">@yield('content')</main>

  <!-- FOOTER -->
  <footer class="site-main-footer" style="background:var(--color-navy);border-top:4px solid var(--color-red);color:#94A3B8;padding:48px 0 24px;transition:background .3s">
    <div class="container">
      <div style="display:grid;grid-template-columns:1.8fr 1.2fr 1.2fr 1fr 1.5fr;gap:32px">
        <div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
            <div><span style="font-family:var(--font-title-anton);font-size:1.5rem;color:#fff">{{ strtoupper(setting('site_name', 'LATITUD 18')) }}</span></div>
          </div>
          <p style="font-size:.82rem;line-height:1.6;margin-bottom:16px;color:#94A3B8">
            {{ setting('footer_about', 'Portal de noticias 24/7. Información sin ruido para Bolivia y el mundo.') }}
          </p>
          <div style="display:flex;gap:8px">
            @if(setting('social_facebook'))
              <a href="{{ setting('social_facebook') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-facebook-f" style="font-size:.8rem"></i></a>
            @endif
            @if(setting('social_twitter'))
              <a href="{{ setting('social_twitter') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-x-twitter" style="font-size:.8rem"></i></a>
            @endif
            @if(setting('social_instagram'))
              <a href="{{ setting('social_instagram') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-instagram" style="font-size:.8rem"></i></a>
            @endif
            @if(setting('social_youtube'))
              <a href="{{ setting('social_youtube') }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#94A3B8;text-decoration:none;transition:all .2s" onmouseover="this.style.background='var(--color-red)';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#94A3B8'"><i class="fab fa-youtube" style="font-size:.8rem"></i></a>
            @endif
            @if(setting('whatsapp_phone'))
              <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', setting('whatsapp_phone')) }}?text={{ urlencode(setting('whatsapp_message', 'Hola')) }}" target="_blank" style="width:34px;height:34px;background:rgba(255,255,255,.08);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#25D366;text-decoration:none;transition:all .2s" onmouseover="this.style.background='#25D366';this.style.color='#fff'" onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.color='#25D366'"><i class="fab fa-whatsapp" style="font-size:.85rem"></i></a>
            @endif
          </div>
        </div>
        <div>
          <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">Secciones</h4>
          <ul style="list-style:none;padding:0;margin:0">
            @forelse($categorias ?? [] as $categoria)
              <li style="margin-bottom:8px"><a href="{{ route('categoria.noticias', $categoria->id) }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem;transition:color .2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">{{ $categoria->name }}</a></li>
            @empty
              @foreach(['Política','País','Santa Cruz','Economía','Deportes'] as $cat)
                <li style="margin-bottom:8px"><a href="#" style="color:#94A3B8;text-decoration:none;font-size:.82rem">{{ $cat }}</a></li>
              @endforeach
            @endforelse
          </ul>
        </div>
        <div>
          <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">Editorial & Especiales</h4>
          <ul style="list-style:none;padding:0;margin:0">
            <li style="margin-bottom:8px"><a href="{{ route('contraataque.index') }}" style="color:#00FF87;font-weight:700;text-decoration:none;font-size:.82rem;transition:color .2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#00FF87'"><i class="fas fa-bolt" style="color:#FF3B30;margin-right:4px"></i> Contra Ataque (Deportes)</a></li>
            <li style="margin-bottom:8px"><a href="{{ route('opinion.index') }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">Opinión y Columnistas</a></li>
            <li style="margin-bottom:8px"><a href="{{ route('periodico.public.index') }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">Periódico Semanal</a></li>
            <li style="margin-bottom:8px"><a href="{{ route('search') }}" style="color:#94A3B8;text-decoration:none;font-size:.82rem" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94A3B8'">Buscador de Archivo</a></li>
          </ul>
        </div>
        <div>
          <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">En Vivo</h4>
          <button onclick="openLiveModal()" style="display:inline-flex;align-items:center;gap:6px;background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.7rem;text-transform:uppercase;padding:8px 16px;border-radius:2px;border:none;cursor:pointer;margin-bottom:12px"><span style="width:6px;height:6px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> EN VIVO</button>
          <p style="font-size:.78rem;color:#64748B;line-height:1.5">Transmisión multimedia digital 24/7.</p>
        </div>
        <div>
          <h4 style="font-family:var(--font-title-montserrat);font-weight:900;color:#fff;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px">Contacto & Redacción</h4>
          <div style="font-size:.82rem;line-height:1.8">
            <p><i class="fas fa-map-marker-alt" style="color:var(--color-red);margin-right:6px"></i> {{ setting('contact_address', 'Santa Cruz de la Sierra, Bolivia') }}</p>
            <p><i class="fas fa-envelope" style="color:var(--color-red);margin-right:6px"></i> {{ setting('contact_email', 'info@latitud18.com') }}</p>
            <p><i class="fas fa-phone" style="color:var(--color-red);margin-right:6px"></i> {{ setting('contact_phone', '+591 (2) 211-4500') }}</p>
          </div>
        </div>
      </div>

      <!-- BANNER NEWSLETTER EN FOOTER -->
      <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-left:4px solid var(--color-red);border-radius:4px;padding:20px 24px;margin-top:36px">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px">
          <div style="flex:1;min-width:260px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
              <i class="fas fa-envelope-open-text" style="color:var(--color-red);font-size:1.2rem"></i>
              <span style="font-family:var(--font-title-montserrat);font-weight:800;color:#fff;font-size:.95rem;text-transform:uppercase;letter-spacing:.5px">Boletín Informativo Diario</span>
            </div>
            <p style="font-size:.78rem;color:#94A3B8;margin:0">Recibe las noticias más importantes y el resumen del día directamente en tu correo electrónico. Cero spam.</p>
          </div>
          <form id="footer-newsletter-form" onsubmit="handleNewsletterSubmit(event, 'Footer')" style="display:flex;gap:8px;flex:1;min-width:280px;max-width:500px">
            <input type="email" id="footer-newsletter-email" name="email" required placeholder="Tu correo electrónico..." style="flex:1;padding:10px 14px;border:1px solid rgba(255,255,255,.2);background:rgba(0,0,0,.3);color:#fff;border-radius:2px;font-size:.85rem;outline:none">
            <button type="submit" id="footer-newsletter-btn" style="background:var(--color-red);color:#fff;font-family:var(--font-title-montserrat);font-weight:800;font-size:.75rem;text-transform:uppercase;padding:10px 18px;border:none;border-radius:2px;cursor:pointer;white-space:nowrap;transition:background .2s" onmouseover="this.style.background='var(--color-red-dark)'" onmouseout="this.style.background='var(--color-red)'">
              SUSCRIBIRME
            </button>
          </form>
        </div>
        <div id="footer-newsletter-msg" style="display:none;margin-top:10px;font-size:.8rem;padding:6px 12px;border-radius:2px"></div>
      </div>

      <div style="border-top:1px solid rgba(255,255,255,.08);margin-top:24px;padding-top:16px;text-align:center;font-size:.75rem;color:#64748B">
        {{ setting('copyright_text', '© ' . date('Y') . ' LATITUD 18 / UHTV Bolivia. Todos los derechos reservados.') }}
      </div>
    </div>
  </footer>

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

  <!-- ========== MODAL: EN VIVO TV & RADIO ========== -->
  @php
    $tvActive = setting('streaming_tv_active', '1') == '1';
    $tvYtId = setting('streaming_tv_youtube_id', 'jfKfPfyJRdk');
    $tvTitle = setting('streaming_tv_title', 'UHTV En Vivo — Transmisión Digital 24/7');
    $radioActive = setting('streaming_radio_active', '1') == '1';
    $radioTitle = setting('streaming_radio_title', 'Radio Latitud 18 FM — Señal Online');
    $radioUrl = setting('streaming_radio_url', 'https://stream.zeno.fm/f3wvbbqmdg8uv');
  @endphp
  <div class="live-stream-modal-overlay" id="live-streaming-modal-suite">
    <div class="live-modal-header">
      <div style="display:flex;align-items:center;gap:12px">
        <span style="background:var(--color-red);color:#fff;font-weight:800;font-size:.65rem;padding:3px 10px;border-radius:2px;animation:pulse 1.5s infinite">EN VIVO HD</span>
        <h2 style="font-family:var(--font-title-anton);font-size:1.4rem;color:#fff">{{ strtoupper($tvTitle) }}</h2>
      </div>
      <button class="btn-close-newspaper-modal" onclick="closeLiveModal()" title="Cerrar"><i class="fas fa-times"></i></button>
    </div>
    <div class="live-modal-body-scroll">
      <div class="container">
        <div class="studio-grid" style="margin-bottom:30px">
          <div class="tv-set-wrapper">
            <div class="tv-screen-frame">
              <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
                @if($tvActive && !empty($tvYtId))
                  <iframe src="https://www.youtube.com/embed/{{ $tvYtId }}?autoplay=1" style="width:100%;height:100%;border:none" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen></iframe>
                @else
                  <div style="color:#94A3B8;text-align:center;padding:20px;">
                    <i class="fas fa-tv" style="font-size:3rem;margin-bottom:12px;display:block;opacity:0.4;"></i>
                    <h4 style="font-family:var(--font-title-montserrat);font-weight:700;color:#fff;">Transmisión TV en pausa</h4>
                    <p style="font-size:0.85rem;">La señal en vivo iniciará en breve. Permanece atento.</p>
                  </div>
                @endif
              </div>
              <div class="tv-live-badge"><span style="width:7px;height:7px;background:#fff;border-radius:50%;animation:pulse 1.5s infinite"></span> SEÑAL MATRIZ HD</div>
              <div class="tv-watermark">LATITUD<span style="color:var(--color-red)">18</span> TV</div>
              <div class="lower-third-overlay" id="lower-third-box">
                <div class="lt-badge" id="lt-badge-text">ÚLTIMO MOMENTO</div>
                <div>
                  <div class="lt-headline" id="lt-headline-text">COBERTURA ESPECIAL EN VIVO</div>
                  <div class="lt-subheadline" id="lt-subheadline-text">{{ $tvTitle }}</div>
                  <div class="lt-location">BOLIVIA • {{ date('H:i') }}</div>
                </div>
              </div>
            </div>
            <div class="studio-control-bar">
              <span><i class="fas fa-sliders" style="margin-right:4px"></i> Gráficos:</span>
              <button class="btn-lt-switcher active" onclick="switchLowerThird('ÚLTIMO MOMENTO','COBERTURA ESPECIAL EN VIVO','Transmisión continua de noticias',this)"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:3px;color:#fff"></i> Último Momento</button>
              <button class="btn-lt-switcher" onclick="switchLowerThird('NOTICIAS','RESUMEN INFORMATIVO DEL DÍA','Cobertura completa de los acontecimientos',this)"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:3px;color:#fff"></i> Noticias</button>
              <button class="btn-lt-switcher" onclick="switchLowerThird('ENTREVISTA','ESPACIO DE DIÁLOGO Y ANÁLISIS','Espacio de opinión y debate constructivo',this)"><i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:3px;color:#fff"></i> Entrevistas</button>
            </div>
          </div>
          <div class="live-chat-panel">
            <div class="chat-header"><h3><i class="fas fa-comments" style="color:var(--color-red);margin-right:4px"></i> Comunidad en Vivo</h3><div class="chat-viewers-count"><i class="fas fa-eye"></i> Transmisión Oficial</div></div>
            <div class="chat-messages-scroll" id="chat-messages-container">
              <div class="chat-msg admin"><strong style="color:var(--color-red)">LATITUD18:</strong> Bienvenidos a la transmisión en vivo de Latitud 18 / UHTV. ¡Participa con tus comentarios!</div>
            </div>
            <form class="chat-input-form" onsubmit="sendChatMessage(event)">
              <input type="text" class="chat-input" id="chat-input-text" placeholder="Escribe tu mensaje en vivo..." autocomplete="off">
              <button type="submit" class="btn-send-chat"><i class="fas fa-paper-plane"></i></button>
            </form>
          </div>
        </div>
        <div class="radio-studio-card">
          <div class="radio-player-box">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
              <div class="radio-icon-circle"><i class="fas fa-radio"></i></div>
              <div><div style="font-family:var(--font-title-montserrat);font-weight:900;font-size:.9rem;color:var(--color-navy)">{{ $radioTitle }}</div><div style="font-size:.7rem;color:var(--color-text-muted)">FRECUENCIA DIGITAL ONLINE • 24/7 SIN RUIDO</div></div>
            </div>
            @if($radioActive && !empty($radioUrl))
              <audio id="global-radio-audio" src="{{ $radioUrl }}" preload="none"></audio>
            @endif
            <canvas id="radio-visualizer" class="radio-visualizer-canvas"></canvas>
            <div style="display:flex;align-items:center;gap:12px">
              <button class="btn-radio-play" id="btn-main-radio-play" onclick="toggleRadioPlay()"><i class="fas fa-play" id="radio-play-icon"></i></button>
              <div><div style="font-family:var(--font-title-montserrat);font-weight:700;font-size:.85rem;color:var(--color-text-main)">Transmisión de Audio en Directo</div><div style="font-size:.72rem;color:var(--color-text-muted)">Señal continua vía streaming online</div></div>
            </div>
          </div>
          <div>
            <div style="font-family:var(--font-title-montserrat);font-weight:800;font-size:.82rem;text-transform:uppercase;color:var(--color-navy);margin-bottom:10px"><i class="fas fa-podcast" style="color:var(--color-red);margin-right:4px"></i> Información en Directo</div>
            <p style="font-size:0.82rem;color:var(--color-text-muted);line-height:1.6;">
              Escucha la señal de radio en vivo desde cualquier dispositivo móvil o computadora con calidad digital HD.
            </p>
            <div style="margin-top:12px;">
              <span class="badge bg-danger text-white px-2 py-1" style="font-size:0.7rem;"><i class="fas fa-wifi me-1"></i> SEÑAL EN LÍNEA</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/dark-mode.js') }}"></script>
  <script src="{{ asset('js/error-handler.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Theme
  function toggleTheme(){const h=document.documentElement,c=h.getAttribute('data-theme'),n=c==='dark'?'light':'dark';h.setAttribute('data-theme',n);localStorage.setItem('latitud18-theme',n);applyTheme(n)}
  function applyTheme(t){const s=document.getElementById('themeIconSun'),m=document.getElementById('themeIconMoon');if(s)s.style.display=t==='dark'?'inline':'none';if(m)m.style.display=t==='light'?'inline':'none';const me=document.querySelector('meta[name="theme-color"]');if(me)me.setAttribute('content',t==='dark'?'#080E18':'#0B1F3A')}
  (function(){const s=localStorage.getItem('latitud18-theme'),pd=window.matchMedia&&window.matchMedia('(prefers-color-scheme:dark)').matches,t=s||(pd?'dark':'light');document.documentElement.setAttribute('data-theme',t);applyTheme(t)})();

  // Mobile menu
  function openMobileMenu(){document.getElementById('mobileMenu').style.transform='translateX(0)';document.getElementById('mobileMenuOverlay').style.opacity='1';document.getElementById('mobileMenuOverlay').style.pointerEvents='auto';document.body.style.overflow='hidden'}
  function closeMobileMenu(){document.getElementById('mobileMenu').style.transform='translateX(-100%)';document.getElementById('mobileMenuOverlay').style.opacity='0';document.getElementById('mobileMenuOverlay').style.pointerEvents='none';document.body.style.overflow=''}

  // Search modal
  function openSearchModal(){document.getElementById('search-modal-suite').classList.add('active');setTimeout(()=>{const i=document.getElementById('site-search-input');if(i)i.focus()},100)}
  function closeSearchModal(){document.getElementById('search-modal-suite').classList.remove('active')}

  // Newspaper modal
  function openNewspaperModal(){document.getElementById('newspaper-modal-suite').classList.add('active');document.body.style.overflow='hidden';initNewspaperThumbs()}
  function closeNewspaperModal(){document.getElementById('newspaper-modal-suite').classList.remove('active');document.body.style.overflow=''}
  function initNewspaperThumbs(){const strip=document.getElementById('newspaper-thumbs-strip');if(!strip||strip.children.length>0)return;const pages=[{n:1,t:'Portada',c:'var(--color-red)'},{n:2,t:'Editorial',c:'var(--color-navy)'},{n:3,t:'Política',c:'#1e40af'},{n:4,t:'Política',c:'#1e40af'},{n:5,t:'Santa Cruz',c:'#059669'},{n:6,t:'Santa Cruz',c:'#059669'},{n:7,t:'País',c:'#d97706'},{n:8,t:'País',c:'#d97706'},{n:9,t:'Economía',c:'#7c3aed'},{n:10,t:'Economía',c:'#7c3aed'},{n:11,t:'Judicial',c:'#475569'},{n:12,t:'Mundo/Deportes',c:'var(--color-red)'}];pages.forEach((p,i)=>{const d=document.createElement('div');d.className='page-thumb-card'+(i===0?' active':'');d.onclick=()=>showNewspaperPage(p.n,d);d.innerHTML=`<div class="page-thumb-preview"><div class="bar" style="background:${p.c};width:80%"></div><div class="bar" style="background:${p.c};width:60%;opacity:.5"></div><div class="bar" style="background:${p.c};width:40%;opacity:.3"></div></div><div class="page-thumb-label">Pág ${p.n}<br>${p.t}</div>`;strip.appendChild(d)})}
  function showNewspaperPage(num,el){document.querySelectorAll('.newspaper-sheet').forEach(s=>s.style.display='none');document.querySelector(`.newspaper-sheet[data-page="${num}"]`).style.display='block';document.querySelectorAll('.page-thumb-card').forEach(c=>c.classList.remove('active'));if(el)el.classList.add('active')}

  // Live modal
  function openLiveModal(){document.getElementById('live-streaming-modal-suite').classList.add('active');document.body.style.overflow='hidden'}
  function closeLiveModal(){
    document.getElementById('live-streaming-modal-suite').classList.remove('active');
    document.body.style.overflow='';
    const a = document.getElementById('global-radio-audio');
    if (a && !a.paused) { a.pause(); const icon = document.getElementById('radio-play-icon'); if (icon) { icon.className = 'fas fa-play'; } }
  }

  // Radio streaming player
  function toggleRadioPlay(){
    const a = document.getElementById('global-radio-audio');
    const icon = document.getElementById('radio-play-icon');
    if (!a) return;
    if (a.paused) {
      a.play().then(() => { if (icon) icon.className = 'fas fa-pause'; }).catch(e => console.log('Error playing stream:', e));
    } else {
      a.pause();
      if (icon) icon.className = 'fas fa-play';
    }
  }

  // Lower third switcher
  function switchLowerThird(badge,headline,sub,btn){document.getElementById('lt-badge-text').textContent=badge;document.getElementById('lt-headline-text').textContent=headline;document.getElementById('lt-subheadline-text').textContent=sub;document.querySelectorAll('.btn-lt-switcher').forEach(b=>b.classList.remove('active'));btn.classList.add('active')}

  // Chat
  function sendChatMessage(e){e.preventDefault();const input=document.getElementById('chat-input-text'),container=document.getElementById('chat-messages-container');if(!input.value.trim())return;const msg=document.createElement('div');msg.className='chat-msg';msg.innerHTML=`<strong>Tú:</strong> ${input.value}`;container.appendChild(msg);container.scrollTop=container.scrollHeight;input.value='';setTimeout(()=>{const reply=document.createElement('div');reply.className='chat-msg admin';reply.innerHTML=`<strong style="color:var(--color-red)">LATITUD18:</strong> Gracias por tu mensaje. ¡Sigue interactuando!`;container.appendChild(reply);container.scrollTop=container.scrollHeight},1500)}

  // Newspaper mode toggle
  document.getElementById('btn-mode-single')?.addEventListener('click',function(){this.classList.add('active');document.getElementById('btn-mode-all').classList.remove('active');document.querySelectorAll('.newspaper-sheet').forEach((s,i)=>s.style.display=i===0?'block':'none')});
  document.getElementById('btn-mode-all')?.addEventListener('click',function(){this.classList.add('active');document.getElementById('btn-mode-single').classList.remove('active');document.querySelectorAll('.newspaper-sheet').forEach(s=>s.style.display='block')});

  // PWA Service Worker & Install Prompt
  let deferredPrompt;
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js')
        .then(reg => console.log('PWA Service Worker registrado con éxito:', reg.scope))
        .catch(err => console.log('Error registrando Service Worker:', err));
    });
  }

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const btn = document.getElementById('btn-pwa-install');
    if (btn) btn.style.display = 'inline-flex';
  });

  function triggerPwaInstall() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('El usuario aceptó la instalación de la PWA');
      }
      deferredPrompt = null;
      const btn = document.getElementById('btn-pwa-install');
      if (btn) btn.style.display = 'none';
    });
  }

  window.addEventListener('appinstalled', () => {
    const btn = document.getElementById('btn-pwa-install');
    if (btn) btn.style.display = 'none';
    deferredPrompt = null;
  });

  // AJAX Newsletter Subscription Handler
  function handleNewsletterSubmit(event, origin = 'Portal') {
    event.preventDefault();
    const form = event.target;
    const emailInput = form.querySelector('input[type="email"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const msgContainer = form.id === 'footer-newsletter-form' 
      ? document.getElementById('footer-newsletter-msg') 
      : (document.getElementById(form.dataset.msgTarget) || document.getElementById('footer-newsletter-msg'));

    if (!emailInput || !emailInput.value.trim()) return;

    const email = emailInput.value.trim();
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const token = csrfMeta ? csrfMeta.getAttribute('content') : '';

    fetch('{{ route("newsletter.subscribe") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email: email, origen: origin })
    })
    .then(res => res.json())
    .then(data => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;

      if (msgContainer) {
        msgContainer.style.display = 'block';
        if (data.success) {
          msgContainer.style.background = 'rgba(16, 185, 129, 0.2)';
          msgContainer.style.color = '#34d399';
          msgContainer.style.border = '1px solid rgba(16, 185, 129, 0.4)';
          msgContainer.innerHTML = `<i class="fas fa-check-circle me-1"></i> ${data.message}`;
          emailInput.value = '';
        } else {
          msgContainer.style.background = 'rgba(239, 68, 68, 0.2)';
          msgContainer.style.color = '#f87171';
          msgContainer.style.border = '1px solid rgba(239, 68, 68, 0.4)';
          msgContainer.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${data.message || 'Hubo un error al suscribirse.'}`;
        }
      } else {
        alert(data.message);
      }
    })
    .catch(err => {
      console.error(err);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
      if (msgContainer) {
        msgContainer.style.display = 'block';
        msgContainer.style.background = 'rgba(239, 68, 68, 0.2)';
        msgContainer.style.color = '#f87171';
        msgContainer.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
      }
    });
  }

  document.addEventListener('DOMContentLoaded',function(){
    const el=document.getElementById('topbar-date-text');
    if(el){const o={weekday:'long',day:'numeric',month:'long',year:'numeric'};let d=new Date().toLocaleDateString('es-ES',o);d=d.split(' ').map(w=>w.length>2&&w!=='de'?w.charAt(0).toUpperCase()+w.slice(1):w).join(' ');el.innerHTML='<i class="far fa-calendar-alt" style="margin-right:6px;color:var(--color-red)"></i>'+d+', Bolivia'}
    document.querySelectorAll('.nav-item-link').forEach(l=>{if(l.getAttribute('href')===window.location.pathname)l.style.borderBottomColor='#D71920'});
    const t=document.getElementById('mobileMenuToggle');if(t){t.style.display=window.innerWidth>=1024?'none':'block';t.addEventListener('click',openMobileMenu);window.addEventListener('resize',()=>{t.style.display=window.innerWidth>=1024?'none':'block'})}
    document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeMobileMenu();closeSearchModal();closeNewspaperModal();closeLiveModal()}});
  });
  </script>

  @if(isset($banners['popup']) && $banners['popup']->count() > 0)
    @php $popupBanner = $banners['popup']->first(); @endphp
    <div id="promoModal" style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.85);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .3s">
      <div id="promoModalContent" style="max-width:500px;width:90%;transform:scale(.9);opacity:0;transition:transform .3s,opacity .3s">
        <div style="position:relative;background:#000;border:6px solid var(--color-navy);border-radius:2px;overflow:hidden">
          <button onclick="closePromoModal()" style="position:absolute;top:8px;right:12px;z-index:10;background:none;border:none;color:var(--color-red);font-size:1.5rem;cursor:pointer"><i class="fas fa-times"></i></button>
          <a href="{{ $popupBanner->link ?? '#' }}" {{ $popupBanner->link ? 'target="_blank"' : '' }} style="display:block"><img src="{{ asset($popupBanner->image_path) }}" alt="{{ $popupBanner->title }}" style="width:100%;height:auto;display:block"></a>
        </div>
        <div style="text-align:center;margin-top:12px"><span style="font-size:.7rem;background:rgba(0,0,0,.6);color:#94A3B8;padding:4px 12px;border-radius:2px">Presiona [Z] para cerrar</span></div>
      </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded',function(){setTimeout(()=>{const m=document.getElementById('promoModal'),c=document.getElementById('promoModalContent');if(m&&c){m.style.opacity='1';m.style.pointerEvents='auto';c.style.transform='scale(1)';c.style.opacity='1';document.body.style.overflow='hidden'}},1500);document.addEventListener('keydown',e=>{if(e.key==='z'||e.key==='Z')closePromoModal()})});
    function closePromoModal(){const m=document.getElementById('promoModal'),c=document.getElementById('promoModalContent');if(m&&c){c.style.transform='scale(.9)';c.style.opacity='0';m.style.opacity='0';m.style.pointerEvents='none';document.body.style.overflow=''}}
    </script>
  @endif
</body>
</html>
