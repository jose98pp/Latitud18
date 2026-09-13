@extends('layouts.contraataque')

@php
    $titulo = is_object($noticia) ? ($noticia->titulo ?? '') : ($noticia['titulo'] ?? '');
    $bajada = is_object($noticia) ? ($noticia->bajada ?? $noticia->resumen ?? '') : ($noticia['bajada'] ?? '');
    $contenido = is_object($noticia) ? ($noticia->contenido ?? '') : ($noticia['contenido'] ?? '');
    $img = '';
    if (is_object($noticia)) {
        $img = method_exists($noticia, 'getImageUrl') ? $noticia->getImageUrl() : ($noticia->imagen ?? $noticia->foto ?? '');
    } else {
        $img = $noticia['imagen'] ?? $noticia['foto'] ?? '';
    }
    $cat = is_object($noticia) ? ($noticia->category->name ?? 'FÚTBOL BOLIVIANO') : ($noticia['category']->name ?? 'FÚTBOL BOLIVIANO');
    $autor = is_object($noticia) ? ($noticia->autor ?? 'Redacción Contra Ataque') : ($noticia['autor'] ?? 'Redacción Contra Ataque');
    $fecha = is_object($noticia) ? (($noticia->created_at instanceof \Carbon\Carbon) ? $noticia->created_at->format('d/m/Y H:i') : (is_string($noticia->created_at ?? null) ? $noticia->created_at : 'Hoy')) : 'Hoy';
    $visitas = is_object($noticia) ? ($noticia->visitas ?? 120) : ($noticia['visitas'] ?? 120);
@endphp

@section('title', $titulo . ' — Contra Ataque Deportes')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($bajada ?: $contenido), 150))

@section('content')
<div class="container py-2">

    <!-- Migas de pan deportivas -->
    <div class="d-flex align-items-center gap-2 mb-3 text-muted" style="font-size: 0.78rem;">
        <a href="{{ route('contraataque.index') }}" class="text-white"><i class="fas fa-home me-1"></i> Contra Ataque</a>
        <span>/</span>
        <span class="text-success fw-bold">{{ $cat }}</span>
        <span>/</span>
        <span class="text-truncate" style="max-width: 300px;">{{ $titulo }}</span>
    </div>

    <div class="row g-4">
        <!-- Columna Principal: Noticia -->
        <div class="col-lg-8">
            <article class="ca-card p-4">
                <span class="ca-kicker mb-2">{{ $cat }}</span>
                
                <h1 class="ca-title-hero" style="font-size: 2.2rem; line-height: 1.15; margin-bottom: 14px;">
                    {{ $titulo }}
                </h1>

                @if(!empty($bajada))
                    <div style="font-size: 1.1rem; line-height: 1.5; color: #CBD5E1; font-weight: 500; margin-bottom: 20px; border-left: 3px solid var(--ca-volt); padding-left: 14px;">
                        {!! nl2br(e($bajada)) !!}
                    </div>
                @endif

                <!-- Meta bar del autor -->
                <div class="d-flex align-items-center justify-content-between border-top border-bottom border-secondary py-2 mb-4" style="font-size: 0.8rem; color: #94A3B8;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-edit text-success"></i>
                        <strong class="text-white">{{ $autor }}</strong>
                        <span>•</span>
                        <span>{{ $fecha }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span><i class="fas fa-eye me-1 text-info"></i> {{ $visitas }} vistas</span>
                        <!-- Compartir -->
                        <div class="d-flex gap-1">
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($titulo . ' ' . url()->current()) }}" target="_blank" class="btn btn-sm btn-success py-0 px-2" style="font-size: 0.75rem;"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Imagen principal -->
                @if($img)
                    <div class="mb-4 rounded overflow-hidden" style="max-height: 480px;">
                        <img src="{{ $img }}" alt="{{ $titulo }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                @endif

                <!-- Contenido del Artículo -->
                <div style="font-size: 1.05rem; line-height: 1.8; color: #E2E8F0;" class="ca-article-body">
                    {!! $contenido !!}
                </div>

                <!-- Banner publicitario o llamado -->
                <div class="p-3 my-4 rounded text-center" style="background: linear-gradient(90deg, #111827 0%, #162032 100%); border: 1px solid var(--ca-border);">
                    <h5 class="fw-bold text-white mb-1" style="font-family: var(--ca-font-display);">¡Sigue la pasión del fútbol en CONTRA ATAQUE!</h5>
                    <p class="small text-muted mb-2">Entérate primero de los goles, traspasos y polémicas de la División Profesional y la Selección Boliviana.</p>
                    <a href="{{ route('contraataque.index') }}" class="btn btn-sm fw-bold px-3 py-1" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display);">
                        VER MÁS DEPORTES
                    </a>
                </div>
            </article>

            <!-- Noticias Relacionadas -->
            <div class="mt-4">
                <div class="ca-sec-header">
                    <h3 class="ca-sec-title" style="font-size: 1.2rem;">MÁS EN CONTRA ATAQUE</h3>
                </div>
                <div class="row g-3">
                    @foreach($relacionadas as $rel)
                        @php
                            $rId = is_object($rel) ? ($rel->id ?? 1) : ($rel['id'] ?? 1);
                            $rTitulo = is_object($rel) ? ($rel->titulo ?? '') : ($rel['titulo'] ?? '');
                            $rImg = is_object($rel) ? ($rel->imagen_url ?? $rel->foto ?? '') : ($rel['imagen'] ?? $rel['foto'] ?? '');
                        @endphp
                        <div class="col-md-6">
                            <div class="ca-card p-3 d-flex gap-2 h-100">
                                <div style="width: 80px; height: 80px; min-width: 80px; border-radius: 4px; overflow: hidden;">
                                    <img src="{{ $rImg ?: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80' }}" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <h4 style="font-size: 0.88rem; font-weight: 700; line-height: 1.2; margin-bottom: 4px;">
                                        <a href="{{ route('contraataque.show', $rId) }}">{{ \Illuminate\Support\Str::limit($rTitulo, 60) }}</a>
                                    </h4>
                                    <a href="{{ route('contraataque.show', $rId) }}" class="text-success" style="font-size: 0.72rem;">Leer nota <i class="fas fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Columna Lateral: Marcadores y Posiciones -->
        <div class="col-lg-4">
            <!-- Partidos -->
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
                                @else
                                    <span style="color: var(--ca-gold); font-weight: 700;">{{ $p['estado'] }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-white small">{{ $p['local'] }}</span>
                                <span class="fw-bold text-success px-2 py-0 rounded" style="background:#000;">{{ $p['goles_local'] }} - {{ $p['goles_visitante'] }}</span>
                                <span class="fw-bold text-white small">{{ $p['visitante'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tabla Compacta -->
            <div class="ca-card p-3">
                <div class="ca-sec-header mb-3">
                    <span class="ca-sec-title" style="font-size: 1.05rem;">POSICIONES LIGA</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
                        <thead>
                            <tr style="color: #94A3B8; border-bottom: 1px solid var(--ca-volt);">
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
                    <a href="{{ route('contraataque.index') }}#tablaPosiciones" class="btn btn-sm btn-outline-secondary w-100" style="font-size: 0.72rem;">Ver Tabla Completa</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
