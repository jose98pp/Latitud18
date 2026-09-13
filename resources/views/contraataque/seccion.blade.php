@extends('layouts.contraataque')

@section('title', $seccionTitulo . ' — Contra Ataque Deportes')

@section('content')
<div class="container py-2">

    <!-- Header de Sección -->
    <div class="p-4 mb-4 rounded" style="background: linear-gradient(90deg, #111827 0%, #162032 100%); border-left: 5px solid var(--ca-volt); border-top: 1px solid var(--ca-border); border-right: 1px solid var(--ca-border); border-bottom: 1px solid var(--ca-border);">
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('contraataque.index') }}" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Portada Contra Ataque</a>
            <span class="text-muted small">/</span>
            <span class="ca-kicker" style="font-size: 0.75rem;">SECCIÓN ESPECIAL</span>
        </div>
        <h1 class="h3 fw-bold text-white mb-0" style="font-family: var(--ca-font-display); letter-spacing: 0.5px;">
            {{ $seccionTitulo }}
        </h1>
    </div>

    <div class="row g-4">
        <!-- Listado de Noticias de la Sección -->
        <div class="col-lg-8">
            <div class="row g-4">
                @foreach($noticias as $noticia)
                    @php
                        $nId = is_object($noticia) ? ($noticia->id ?? 1) : ($noticia['id'] ?? 1);
                        $nTitulo = is_object($noticia) ? ($noticia->titulo ?? '') : ($noticia['titulo'] ?? '');
                        $nBajada = is_object($noticia) ? ($noticia->bajada ?? $noticia->resumen ?? '') : ($noticia['bajada'] ?? '');
                        $nImg = is_object($noticia) ? ($noticia->imagen_url ?? $noticia->foto ?? '') : ($noticia['imagen'] ?? $noticia['foto'] ?? '');
                        $nCat = is_object($noticia) ? ($noticia->category->name ?? 'DEPORTES') : ($noticia['category']->name ?? 'DEPORTES');
                    @endphp
                    <div class="col-md-6">
                        <div class="ca-card h-100 d-flex flex-column">
                            <div style="height: 180px; overflow: hidden; position: relative;">
                                <img src="{{ $nImg ?: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80' }}" 
                                     alt="{{ $nTitulo }}" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <span class="badge position-absolute top-2 start-2" style="background: rgba(0,0,0,0.85); color: var(--ca-volt); font-family: var(--ca-font-display);">
                                    {{ $nCat }}
                                </span>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h3 class="ca-title-card" style="font-size: 1.05rem;">
                                    <a href="{{ route('contraataque.show', $nId) }}">{{ $nTitulo }}</a>
                                </h3>
                                <p class="text-muted small mb-3" style="line-height: 1.4;">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($nBajada), 100) }}
                                </p>
                                <div class="mt-auto pt-2 border-top border-secondary">
                                    <a href="{{ route('contraataque.show', $nId) }}" class="text-success fw-bold small">
                                        Leer Cobertura <i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Lateral -->
        <div class="col-lg-4">
            <!-- Partidos -->
            <div class="ca-card p-3 mb-4">
                <div class="ca-sec-header mb-3">
                    <span class="ca-sec-title" style="font-size: 1.05rem;">PARTIDOS EN VIVO</span>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($partidosVivo ?? [] as $p)
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.03);">
                            <div class="d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                                <span class="text-white fw-bold">{{ $p['local_code'] }} vs {{ $p['visitante_code'] }}</span>
                                <span class="text-success fw-bold">{{ $p['goles_local'] }} - {{ $p['goles_visitante'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tabla -->
            <div class="ca-card p-3">
                <div class="ca-sec-header mb-3">
                    <span class="ca-sec-title" style="font-size: 1.05rem;">LÍDERES DE LA LIGA</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
                        <thead>
                            <tr style="color: #94A3B8;">
                                <th>#</th>
                                <th>Club</th>
                                <th class="text-center" style="color: var(--ca-volt);">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($tablaPosiciones ?? [], 0, 5) as $r)
                                <tr>
                                    <td>{{ $r['pos'] }}</td>
                                    <td><strong class="text-white">{{ $r['club'] }}</strong></td>
                                    <td class="text-center fw-bold" style="color: var(--ca-volt);">{{ $r['pts'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
