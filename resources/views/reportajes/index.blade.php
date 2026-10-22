@extends('layouts.main')
@section('title', 'Investigación y reportajes - Latitud18')
@section('meta')
<meta name="description" content="Investigaciones y reportajes de Latitud18. Contexto, historias y cobertura en profundidad.">
<link rel="canonical" href="{{ route('reportajes.index') }}">
@endsection
@section('content')
<style>
.reports-wrap{max-width:1180px;margin:auto;padding:0 20px}.reports-head{padding:40px 0;background:var(--color-navy);color:#fff;border-bottom:4px solid var(--color-red)}.reports-eyebrow{color:#fca5a5;font-size:.75rem;letter-spacing:2px;text-transform:uppercase;font-weight:800}.reports-head h1{font-family:var(--font-title-bebas),sans-serif;font-size:clamp(2rem,6vw,3.8rem);margin:8px 0}.reports-head p{max-width:650px;color:#cbd5e1;line-height:1.6}.reports-filter{display:flex;gap:12px;align-items:end;flex-wrap:wrap;padding:24px 0}.reports-filter label{display:flex;flex:1;min-width:180px;flex-direction:column;font-weight:700;font-size:.85rem;gap:6px}.reports-filter input,.reports-filter select{padding:11px 12px;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:5px;color:var(--color-text-main);min-width:0;width:100%}.reports-button{background:var(--color-red);color:#fff;padding:11px 20px;border:0;border-radius:5px;font-weight:800;text-decoration:none;display:inline-block}.reports-clear{padding:11px;color:var(--color-text-main)}.reports-feature{display:grid;grid-template-columns:1.25fr 1fr;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:8px;overflow:hidden;margin:16px 0 30px}.reports-feature img{width:100%;height:100%;min-height:300px;max-height:420px;object-fit:cover}.reports-feature-text{padding:28px;align-self:center}.reports-feature h2{font-family:var(--font-title-montserrat);font-size:clamp(1.4rem,3vw,2rem);font-weight:900;line-height:1.25;margin:12px 0}.reports-feature a,.reports-card a{color:inherit;text-decoration:none}.reports-meta{font-size:.78rem;color:var(--color-text-muted);display:flex;gap:10px;flex-wrap:wrap}.reports-summary{line-height:1.65;color:var(--color-text-muted);margin:12px 0}.reports-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px;margin:20px 0 32px}.reports-card{display:flex;flex-direction:column;background:var(--color-card-bg);border:1px solid var(--color-border);border-radius:8px;overflow:hidden}.reports-card img{width:100%;aspect-ratio:16/10;object-fit:cover}.reports-card-body{padding:20px;display:flex;flex-direction:column;flex:1}.reports-card h2{font-family:var(--font-title-montserrat);font-size:1.12rem;line-height:1.4;font-weight:900;margin:12px 0}.reports-read{margin-top:auto;font-weight:800;color:var(--color-red)!important;padding-top:14px}.reports-empty{padding:48px 24px;text-align:center;border:1px dashed var(--color-border);border-radius:8px;margin:20px 0}.reports-filter button:focus-visible,.reports-wrap a:focus-visible,.reports-filter input:focus-visible,.reports-filter select:focus-visible{outline:3px solid #d71920;outline-offset:3px}@media(max-width:900px){.reports-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.reports-grid,.reports-feature{grid-template-columns:1fr}.reports-feature img{min-height:0;max-height:260px}.reports-wrap{padding:0 16px}.reports-head{padding:28px 0}.reports-feature-text{padding:20px}.reports-filter label{flex-basis:100%}}
.reports-pagination{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.reports-pagination a,.reports-pagination span{padding:10px 14px;min-width:44px;text-align:center;border:1px solid var(--color-border);border-radius:5px;text-decoration:none;color:var(--color-text-main)}.reports-pagination [aria-current]{background:var(--color-red);color:#fff;border-color:var(--color-red)}.reports-pagination [aria-disabled]{opacity:.5}
</style>
<header class="reports-head"><div class="reports-wrap">
    <a href="{{ route('portada') }}" style="color:#cbd5e1;font-size:.8rem">Inicio</a>
    <div class="reports-eyebrow" style="margin-top:20px">Latitud 18 Investiga</div>
    <h1>Investigación y reportajes</h1>
    <p>Historias que necesitan más contexto. Explora nuestras investigaciones y reportajes por tema.</p>
</div></header>
<div class="reports-wrap">
    <form action="{{ route('reportajes.index') }}" method="GET" class="reports-filter" role="search" aria-label="Buscar reportajes">
        <label>Buscar en reportajes<input type="search" name="q" maxlength="150" value="{{ $search }}" placeholder="Tema, título o palabra clave"></label>
        <label>Categoría<select name="categoria"><option value="">Todas las categorías</option>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected((string) request('categoria') === (string) $categoria->id)>{{ $categoria->name }}</option>@endforeach</select></label>
        <button class="reports-button" type="submit">Buscar</button>
        @if($search !== '' || request()->filled('categoria'))<a class="reports-clear" href="{{ route('reportajes.index') }}">Limpiar filtros</a>@endif
    </form>
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    <p style="color:var(--color-text-muted)">{{ $reportajes->total() }} {{ $reportajes->total() === 1 ? 'reportaje disponible' : 'reportajes disponibles' }}</p>
    @php $destacado = $reportajes->currentPage() === 1 && $search === '' && !request()->filled('categoria') ? $reportajes->first() : null; @endphp
    @if($destacado)
    <article class="reports-feature">
        <a href="{{ $destacado->url }}" tabindex="-1" aria-hidden="true"><img src="{{ $destacado->getImageUrl() }}" alt="" fetchpriority="high" onerror="this.onerror=null;this.src='/images/default-news.svg'"></a>
        <div class="reports-feature-text"><div class="reports-eyebrow" style="color:var(--color-red)">En profundidad</div><h2><a href="{{ $destacado->url }}">{{ $destacado->titulo }}</a></h2><div class="reports-meta"><span>{{ $destacado->category->name ?? 'Reportajes' }}</span><time datetime="{{ $destacado->created_at->toAtomString() }}">{{ $destacado->created_at->format('d/m/Y') }}</time></div><p class="reports-summary">{{ app(\App\Services\ContentSanitizationService::class)->getCleanExcerpt($destacado->contenido, 220) }}</p><a class="reports-read" href="{{ $destacado->url }}">Leer reportaje →</a></div>
    </article>
    @endif
    <div class="reports-grid">
    @forelse($reportajes as $reportaje)
        @continue($destacado && $reportaje->id === $destacado->id)
        <article class="reports-card"><a href="{{ $reportaje->url }}" tabindex="-1" aria-hidden="true"><img loading="lazy" src="{{ $reportaje->getImageUrl() }}" alt="" onerror="this.onerror=null;this.src='/images/default-news.svg'"></a><div class="reports-card-body"><div class="reports-meta"><span>{{ $reportaje->category->name ?? 'Reportajes' }}</span><time datetime="{{ $reportaje->created_at->toAtomString() }}">{{ $reportaje->created_at->format('d/m/Y') }}</time></div><h2><a href="{{ $reportaje->url }}">{{ $reportaje->titulo }}</a></h2><p class="reports-summary">{{ app(\App\Services\ContentSanitizationService::class)->getCleanExcerpt($reportaje->contenido, 140) }}</p><a href="{{ $reportaje->url }}" class="reports-read">Leer reportaje →</a></div></article>
    @empty
        <div class="reports-empty" style="grid-column:1/-1"><h2 style="font-size:1.4rem;font-weight:800">{{ $search !== '' || request()->filled('categoria') ? 'No encontramos reportajes con esos filtros' : 'Próximamente, nuevas investigaciones' }}</h2><p class="reports-summary">{{ $search !== '' || request()->filled('categoria') ? 'Prueba otro tema o consulta todas las categorías.' : 'Aquí se reunirán nuestros reportajes publicados.' }}</p><a href="{{ route('reportajes.index') }}" class="reports-read">Ver todos los reportajes</a></div>
    @endforelse
    </div>
    <nav aria-label="Páginas de reportajes" style="margin-bottom:32px">{{ $reportajes->links('pagination.reportajes') }}</nav>
</div>
@endsection
