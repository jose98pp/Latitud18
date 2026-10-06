@extends('layouts.main')

@section('title', 'Latitud 18 | Noticias y PeriÃ³dico Digital Semanal - InformaciÃ³n Sin Ruido')

@section('content')
<div class="container">

  {{-- 0. Banner Superior de Portada (portada_top) --}}
  <x-ad-slot location="portada_top" :max-width="970" label="Publicidad" :banners="$banners ?? null" />

  {{-- 1. Hero 3 Columnas: Carrusel Principal + Tarjetas Apiladas + Lo Mï¿½ï¿½s Leï¿½ï¿½do --}}
  @include('portada.hero')

  {{-- 2. Ticker DinÃ¡mico de Ãšltimas Noticias --}}
  @include('portada.ticker')

  {{-- 3. EdiciÃ³n Impresa / PeriÃ³dico Digital (Kiosko - estilo El Mundo) --}}
  @include('portada.edicion-impresa')

  {{-- 4. Fila 1 de CategorÃ­as (Split Layout: 3 Columnas) --}}
  @include('portada.categorias-top')

  {{-- 5. Fila 2 de CategorÃ­as (3 Columnas + Latitud 18 Investiga) --}}
  @include('portada.categorias-secondary')

  {{-- 6. Fila 3: OpiniÃ³n (4 Columnistas) & Latitud 18 TV (Videos) --}}
  @include('portada.opinion-tv')

  {{-- 7. Canal Oficial de YouTube & GalerÃ­a de Videos --}}
  @include('portada.youtube-gallery')

  {{-- 8. Espacio Publicitario, Newsletter Exclusivo & Explorador de Secciones --}}
  @include('portada.ads-newsletter')

</div>

{{-- Scripts e interactividad de la portada (Carrusel & Ticker) --}}
@include('portada.scripts')

@endsection
