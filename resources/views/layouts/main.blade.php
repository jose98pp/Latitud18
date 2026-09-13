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
        $jsFiles = ['resources/js/app.js'];
        $allAssets = array_merge($cssFiles, $jsFiles);
    @endphp
    @vite($allAssets)
</head>
<body>

  {{-- Top Bar & Header Masthead --}}
  @include('components.header')

  {{-- Main Navigation Menu & Mobile Drawer --}}
  @include('components.navbar')

  {{-- Footer Banners (If any) --}}
  @if(isset($banners['footer']) && $banners['footer']->count() > 0)
    @foreach($banners['footer'] as $banner)
      <div style="display:flex;justify-content:center;padding:8px 0">
        <a href="{{ $banner->link ?? '#' }}" target="_blank" style="display:block;max-width:970px;width:100%"><img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width:100%;border-radius:2px" loading="lazy"></a>
      </div>
    @endforeach
  @endif

  {{-- Main Content Injection --}}
  <main style="padding:16px 0 40px">
    @yield('content')
  </main>

  {{-- Global 5-Column Footer --}}
  @include('components.footer')

  {{-- Search Realtime Modal --}}
  @include('components.search-modal')

  {{-- Tabloid Digital Newspaper Modal --}}
  @include('components.newspaper-modal')

  {{-- Live TV & Radio Streaming Player Studio Modal --}}
  @include('components.live-player')

  {{-- Promotional Popup Banner --}}
  @include('components.popup')

  {{-- Global Scripts, PWA & Interactivity --}}
  @include('components.pwa-scripts')

</body>
</html>
