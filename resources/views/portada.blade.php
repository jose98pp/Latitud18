@extends('layouts.main')

@section('title', 'Latitud 18 | Noticias y Periódico Digital Semanal - Información Sin Ruido')

@section('content')
<div class="container">

  {{-- 1. Hero 3 Columnas: Carrusel Principal + Tarjetas Apiladas + Lo Más Leído --}}
  @include('portada.hero')

  {{-- 2. Ticker Dinámico de Últimas Noticias --}}
  @include('portada.ticker')

  {{-- 3. Fila 1 de Categorías (Split Layout: 3 Columnas) --}}
  @include('portada.categorias-top')

  {{-- 4. Fila 2 de Categorías (3 Columnas + Latitud 18 Investiga) --}}
  @include('portada.categorias-secondary')

  {{-- 5. Fila 3: Opinión (4 Columnistas) & Latitud 18 TV (Videos) --}}
  @include('portada.opinion-tv')

  {{-- 6. Espacio Publicitario, Newsletter Exclusivo & Explorador de Secciones --}}
  @include('portada.ads-newsletter')

</div>

{{-- Scripts e interactividad de la portada (Carrusel & Ticker) --}}
@include('portada.scripts')

@endsection
