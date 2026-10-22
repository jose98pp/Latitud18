@extends('layouts.contraataque')
@section('title', $seccionTitulo . ' — Contra Ataque Deportes')
@section('content')
<style>
.ca-section-header{background:linear-gradient(100deg,#111827,#162032);border:1px solid var(--ca-border);border-left:5px solid var(--ca-volt);padding:24px;border-radius:8px;margin-bottom:24px}.ca-section-header h1{font-family:var(--ca-font-display);font-weight:900;font-size:clamp(1.7rem,4vw,2.5rem);margin:14px 0 8px}.ca-section-search{display:flex;gap:8px;margin:20px 0;flex-wrap:wrap}.ca-section-search input{flex:1;min-width:180px;background:#111827;color:#fff;border:1px solid var(--ca-border);padding:10px 12px;border-radius:5px}.ca-section-search button{border:0;background:var(--ca-volt);color:#071322;font-weight:800;padding:10px 20px;border-radius:5px}.ca-section-card{overflow:hidden;border:1px solid var(--ca-border);height:100%;display:flex;flex-direction:column;background:var(--ca-bg-card);border-radius:8px}.ca-section-card img.cover{width:100%;aspect-ratio:16/10;object-fit:cover}.ca-section-card-body{padding:20px;display:flex;flex-direction:column;flex:1}.ca-section-card h2{font-family:var(--ca-font-display);font-size:1.25rem;line-height:1.35;font-weight:800;margin:12px 0}.ca-section-summary{font-size:.9rem;line-height:1.6;color:#cbd5e1}.ca-section-card a{color:inherit}.ca-section-read{color:var(--ca-volt)!important;font-weight:700;margin-top:auto;padding-top:14px}.ca-section-meta{font-size:.78rem;color:#94a3b8;display:flex;gap:10px;flex-wrap:wrap}.ca-section-author{display:flex;gap:10px;align-items:center;margin:8px 0}.ca-section-author img{width:40px;height:40px;border-radius:50%;object-fit:cover}.ca-section-empty{padding:40px 24px;border:1px dashed var(--ca-border);border-radius:8px;text-align:center}.ca-section-nav a{display:block;padding:12px 0;border-bottom:1px solid var(--ca-border);color:#e2e8f0}.ca-section-nav a[aria-current]{color:var(--ca-volt);font-weight:800}.ca-section-search input:focus-visible,.ca-section-search button:focus-visible{outline:3px solid var(--ca-volt);outline-offset:3px}
</style>
<div class="container py-2">
    <header class="ca-section-header">
        <a href="{{ route('contraataque.index') }}" style="color:#94a3b8;font-size:.85rem">← Portada Contra Ataque</a>
        <h1>{{ $seccionTitulo }}</h1>
        <p class="mb-0" style="color:#cbd5e1">{{ $seccion === 'opinion' ? 'Columnas y análisis deportivos de nuestros autores.' : 'Actualidad y cobertura de ' . $seccionTitulo . '.' }}</p>
    </header>
    <div class="row g-4"><div class="col-lg-8">
        <form class="ca-section-search" action="{{ route('contraataque.seccion', $seccion) }}" method="GET" role="search" aria-label="Buscar en esta sección">
            <label for="section-search" class="visually-hidden">Buscar en {{ $seccionTitulo }}</label><input id="section-search" type="search" name="q" maxlength="150" value="{{ $search }}" placeholder="Buscar en esta sección"><button type="submit">Buscar</button>
            @if($search !== '')<a class="btn btn-outline-light" href="{{ route('contraataque.seccion', $seccion) }}">Limpiar</a>@endif
        </form>
        <p class="ca-section-meta">{{ $noticias->total() }} {{ $seccion === 'opinion' ? 'artículos' : 'noticias' }}{{ $search !== '' ? ' con estos filtros' : ' en esta sección' }}</p>
        <div class="row g-4">
        @forelse($noticias as $noticia)
            @php
                $esOpinion = $seccion === 'opinion';
                $url = $esOpinion ? route('opinion.articulo', $noticia->id) : $noticia->url;
            @endphp
            <div class="col-md-6"><article class="ca-section-card">
                @unless($esOpinion)<a href="{{ $url }}" tabindex="-1" aria-hidden="true"><img class="cover" loading="lazy" src="{{ $noticia->getImageUrl() }}" alt="" onerror="this.onerror=null;this.src='/images/default-news.svg'"></a>@endunless
                <div class="ca-section-card-body">
                    <div class="ca-section-meta"><span>{{ $esOpinion ? $noticia->tipo : $seccionTitulo }}</span><time datetime="{{ $noticia->created_at->toAtomString() }}">{{ $noticia->created_at->format('d/m/Y') }}</time></div>
                    @if($esOpinion)<div class="ca-section-author"><img src="{{ $noticia->columnista->avatar_url }}" alt="" onerror="this.onerror=null;this.src='/images/default-news.svg'"><span>{{ $noticia->columnista->nombre }}</span></div>@endif
                    <h2><a href="{{ $url }}">{{ $noticia->titulo }}</a></h2>
                    <p class="ca-section-summary">{{ app(\App\Services\ContentSanitizationService::class)->getCleanExcerpt($noticia->contenido, 150) }}</p>
                    <a href="{{ $url }}" class="ca-section-read">{{ $esOpinion ? 'Leer análisis' : 'Leer noticia' }} →</a>
                </div>
            </article></div>
        @empty
            <div class="col-12"><div class="ca-section-empty"><h2 class="h4">{{ $search !== '' ? 'No encontramos resultados' : 'Todavía no hay publicaciones en esta sección' }}</h2><p style="color:#94a3b8">{{ $search !== '' ? 'Prueba otra palabra o limpia la búsqueda.' : 'Las nuevas coberturas aparecerán aquí cuando se publiquen.' }}</p><a class="btn btn-outline-light" href="{{ route('contraataque.index') }}">Ver todas las noticias deportivas</a></div></div>
        @endforelse
        </div>
        <nav class="mt-4" aria-label="Páginas de esta sección">{{ $noticias->links('pagination::bootstrap-5') }}</nav>
    </div><aside class="col-lg-4">
        <div class="ca-card p-3 mb-4"><h2 class="h5 fw-bold text-white">Explorar Contra Ataque</h2><nav class="ca-section-nav" aria-label="Más secciones deportivas">@foreach(\App\Support\SportsSections::LABELS as $key => $label)<a href="{{ route('contraataque.seccion', $key) }}" @if($seccion === $key) aria-current="page" @endif>{{ $label }}</a>@endforeach<a href="{{ route('contraataque.seccion', 'opinion') }}" @if($seccion === 'opinion') aria-current="page" @endif>El Ojo de Contraataque</a></nav></div>
        @if($esSeccionFutbol)
            <div class="ca-card p-3 mb-4"><h2 class="h5 text-white">Partidos y resultados · Bolivia</h2>@include('contraataque.partials.matches-list', ['partidos' => $partidosVivo, 'feed' => $matchFeed])</div>
            <div class="ca-card p-3"><h2 class="h5 text-white">Liga Boliviana</h2>@include('contraataque.partials.standings')<a class="btn btn-outline-light btn-sm mt-3" href="{{ route('contraataque.index') }}#tablaPosiciones">Ver marcador y ligas</a></div>
        @endif
    </aside></div>
</div>
@endsection
