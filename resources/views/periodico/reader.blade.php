@extends('layouts.main')
@section('title', ($edicionActiva['titulo'] ?? 'Latitud 18') . ' - Periódico Digital')
@section('content')
<link rel="stylesheet" href="{{ asset('vendor/dflip/css/dflip.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/dflip/css/themify-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/periodico-reader.css') }}?v=2">
<section class="l18-reader" aria-label="Periódico digital Latitud 18">
    <header class="l18-reader-header">
        <a class="l18-reader-brand" href="{{ route('periodico.public.index') }}">LATITUD <b>18</b><small>INFORMACIÓN SIN RUIDO</small></a>
        <label>Ediciones
            <select id="readerEdition" aria-label="Seleccionar edición">
                @foreach($ediciones as $edicion)
                <option value="{{ route('periodico.public.show', $edicion['id']) }}" @selected(($edicionActiva['id'] ?? null) == $edicion['id'])>{{ $edicion['titulo'] ?? 'Edición' }}</option>
                @endforeach
            </select>
        </label>
    </header>
    @if($edicionActiva)
    <div id="readerStage" class="l18-reader-stage" data-pdf="{{ route('periodico.public.documento', $edicionActiva['id']) }}" data-vendor="{{ asset('vendor/dflip') }}/">
        <div id="readerBook" aria-label="Páginas de la edición"></div>
        <iframe id="readerPdfFallback" title="Lectura continua del periódico" hidden></iframe>
        <p id="readerStatus" class="l18-reader-status" role="status">Preparando la edición para leer…</p>
        <div id="readerError" class="l18-reader-error" hidden>
            <p id="readerErrorMessage">No se pudo abrir el lector 3D.</p>
            <button type="button" id="readerRetry">Reintentar 3D</button>
            <button type="button" id="reader2D">Usar modo 2D</button>
            <a href="{{ route('periodico.public.documento', $edicionActiva['id']) }}" target="_blank" rel="noopener">Abrir PDF para leer</a>
        </div>
    </div>
    <footer class="l18-reader-help">
        <span>Desliza o usa las flechas para pasar de página. Amplía con + para leer los detalles.</span>
        <a href="{{ route('periodico.public.documento', $edicionActiva['id']) }}" target="_blank" rel="noopener">Lectura continua en PDF</a>
        <a href="{{ route('periodico.public.pdf', $edicionActiva['id']) }}">Descargar edición</a>
    </footer>
    <script src="{{ asset('vendor/dflip/js/libs/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/dflip/js/dflip.min.js') }}"></script>
    <script src="{{ asset('js/periodico/reader.js') }}?v=2" defer></script>
    @else
    <p class="l18-reader-empty">Próximamente encontrarás aquí nuestra edición digital.</p>
    @endif
</section>
@endsection
