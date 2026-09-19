@extends('layouts.contraataque')

@section('title', 'Contra Ataque — Portal Deportivo Oficial')

@section('content')
<div class="container">

    <!-- ══════════════════════════════════════════════════════
         BLOQUE 1: NOTICIA HERO (EL GOLPE DE LA FECHA) + LATERAL
    ══════════════════════════════════════════════════════ -->
    <div class="row g-4 mb-5">
        <!-- Hero Principal -->
        <div class="col-lg-8">
            @php
                $heroId = is_object($heroNews) ? ($heroNews->id ?? 1) : ($heroNews['id'] ?? 1);
                $heroTitulo = is_object($heroNews) ? ($heroNews->titulo ?? '') : ($heroNews['titulo'] ?? '');
                $heroBajada = is_object($heroNews) ? ($heroNews->bajada ?? $heroNews->resumen ?? '') : ($heroNews['bajada'] ?? '');
                $heroImg = '';
                if (is_object($heroNews)) {
                    $heroImg = method_exists($heroNews, 'getImageUrl') ? $heroNews->getImageUrl() : ($heroNews->imagen ?? $heroNews->foto ?? '');
                } else {
                    $heroImg = $heroNews['imagen'] ?? $heroNews['foto'] ?? '';
                }
                $heroCat = is_object($heroNews) ? ($heroNews->category->name ?? 'FÚTBOL BOLIVIANO') : ($heroNews['category']->name ?? 'FÚTBOL BOLIVIANO');
                $heroFecha = is_object($heroNews) ? ($heroNews->created_at ? $heroNews->created_at->diffForHumans() : 'Hace 2 horas') : 'Hace 2 horas';
                $heroSlug = \Illuminate\Support\Str::slug($heroTitulo) ?: 'noticia';
                $heroUrl = is_object($heroNews) && isset($heroNews->url) ? $heroNews->url : route('contraataque.show', ['id' => $heroId, 'slug' => $heroSlug]);
            @endphp
            <div class="ca-card h-100 position-relative ca-hero-main-card" style="min-height: 480px; display: flex; flex-direction: column; justify-content: flex-end; overflow: hidden;">
                <!-- Imagen de fondo con overlay degradado oscuro -->
                <div style="position: absolute; inset: 0; z-index: 1;">
                    <a href="{{ $heroUrl }}">
                        <img src="{{ $heroImg ?: 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1200&q=80' }}" 
                             alt="{{ $heroTitulo }}" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                             onmouseover="this.style.transform='scale(1.03)'" 
                             onmouseout="this.style.transform='scale(1)'"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1200&q=80'">
                    </a>
                    <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(9,13,22,0.2) 0%, rgba(9,13,22,0.85) 65%, rgba(9,13,22,0.98) 100%); pointer-events: none;"></div>
                </div>

                <!-- Contenido sobre la imagen -->
                <div class="ca-hero-content" style="position: relative; z-index: 2; padding: 28px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge" style="background: var(--ca-red); color: #fff; font-family: var(--ca-font-display); font-size: 0.75rem; letter-spacing: 1px; font-weight: 900;">
                            🔥 EL GOLPE DE LA FECHA
                        </span>
                        <span class="ca-kicker">{{ $heroCat }}</span>
                        <span class="text-muted small">• {{ $heroFecha }}</span>
                    </div>

                    <h1 class="ca-title-hero">
                        <a href="{{ $heroUrl }}" style="color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.8);">
                            {{ $heroTitulo }}
                        </a>
                    </h1>

                    <p style="color: #CBD5E1; font-size: 0.95rem; line-height: 1.5; max-width: 90%; margin-bottom: 16px;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($heroBajada), 180) }}
                    </p>

                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ $heroUrl }}" class="btn btn-sm fw-bold px-3 py-2" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display); font-size: 0.9rem; letter-spacing: 0.5px;">
                            LEER COBERTURA COMPLETA <i class="fas fa-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Lateral: Marcadores en Tiempo Real Multi-Liga -->
        <div class="col-lg-4">
            <div class="ca-card h-100 p-3 d-flex flex-column" style="background: var(--ca-bg-card);">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ca-live-dot-pulse"></span>
                        <span class="ca-sec-title text-white mb-0" style="font-size: 1.05rem; letter-spacing: 0.5px;">MARCADORES EN DIRECTO</span>
                    </div>
                    <span class="badge bg-danger text-white" style="font-family: var(--ca-font-display); font-size: 0.65rem; letter-spacing: 0.5px;">
                        EN TIEMPO REAL
                    </span>
                </div>

                <!-- Selector de Ligas / Pestañas Desplazables -->
                <div class="ca-leagues-nav-wrap mb-3">
                    <div class="ca-leagues-nav" id="caLeaguesNav">
                        @foreach($ligasDisponibles ?? [] as $key => $lg)
                            <button type="button" 
                                    class="ca-league-tab-btn {{ ($ligaActiva ?? 'bolivia') === $key ? 'active' : '' }}" 
                                    data-league="{{ $key }}"
                                    onclick="switchLeagueScoreboard('{{ $key }}', this)">
                                <span class="me-1">{{ $lg['flag'] }}</span> {{ $lg['short'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Contenedor dinámico de partidos -->
                <div id="caMatchesContainer" class="d-flex flex-column gap-2 flex-grow-1" style="min-height: 280px;">
                    @include('contraataque.partials.matches-list', ['partidos' => $partidosVivo])
                </div>

                <div class="mt-3 pt-2 border-top border-secondary border-opacity-25 text-center">
                    <a href="#tablaPosiciones" class="btn btn-sm w-100" style="background: rgba(0, 255, 135, 0.1); border: 1px solid #00FF87; color: #00FF87; font-family: var(--ca-font-display); font-weight: 700; font-size: 0.78rem; text-decoration: none;">
                        <i class="fas fa-list-ol me-1"></i> VER TABLA DE POSICIONES COMPLETA
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         BLOQUE 2: ZONA CALIENTE (4 NOTICIAS DESTACADAS)
    ══════════════════════════════════════════════════════ -->
    <div class="mb-5">
        <div class="ca-sec-header">
            <h2 class="ca-sec-title">ZONA CALIENTE • NOTICIAS DESTACADAS</h2>
            <a href="{{ route('contraataque.seccion', 'futbol-boliviano') }}" class="btn btn-sm btn-outline-light" style="font-family: var(--ca-font-display); font-size: 0.8rem;">
                VER TODAS <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @foreach($destacadas as $d)
                @php
                    $dId = is_object($d) ? ($d->id ?? 1) : ($d['id'] ?? 1);
                    $dTitulo = is_object($d) ? ($d->titulo ?? '') : ($d['titulo'] ?? '');
                    $dImg = '';
                    if (is_object($d)) {
                        $dImg = method_exists($d, 'getImageUrl') ? $d->getImageUrl() : ($d->imagen ?? $d->foto ?? '');
                    } else {
                        $dImg = $d['imagen'] ?? $d['foto'] ?? '';
                    }
                    $dCat = is_object($d) ? ($d->category->name ?? 'DEPORTES') : ($d['category']->name ?? 'DEPORTES');
                    $dFecha = is_object($d) ? ($d->created_at ? $d->created_at->diffForHumans() : 'Hace 3 horas') : 'Hace 3 horas';
                    $dSlug = \Illuminate\Support\Str::slug($dTitulo) ?: 'noticia';
                    $dUrl = is_object($d) && isset($d->url) ? $d->url : route('contraataque.show', ['id' => $dId, 'slug' => $dSlug]);
                @endphp
                <div class="col-md-6 col-lg-3">
                    <div class="ca-card h-100 d-flex flex-column">
                        <div style="height: 170px; overflow: hidden; position: relative;">
                            <a href="{{ $dUrl }}">
                                <img src="{{ $dImg ?: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80' }}" 
                                     alt="{{ $dTitulo }}" 
                                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                                     onmouseover="this.style.transform='scale(1.06)'" 
                                     onmouseout="this.style.transform='scale(1)'"
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80'">
                            </a>
                            <span class="badge position-absolute top-2 start-2" style="background: rgba(0,0,0,0.8); color: var(--ca-volt); font-family: var(--ca-font-display); font-size: 0.68rem; letter-spacing: 0.5px;">
                                {{ $dCat }}
                            </span>
                        </div>
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <small class="text-muted mb-1" style="font-size: 0.72rem;">{{ $dFecha }}</small>
                            <h3 class="ca-title-card" style="font-size: 1.05rem;">
                                <a href="{{ $dUrl }}">{{ $dTitulo }}</a>
                            </h3>
                            <div class="mt-auto pt-2 border-top border-secondary d-flex justify-content-between align-items-center">
                                <span class="ca-kicker" style="font-size: 0.68rem;">CONTRA ATAQUE</span>
                                <a href="{{ $dUrl }}" class="text-white" style="font-size: 0.75rem;">
                                    Leer <i class="fas fa-arrow-right text-success ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         BLOQUE 3: TABLA DE POSICIONES + OPINIÓN Y COLUMNAS
    ══════════════════════════════════════════════════════ -->
    <div class="row g-4 mb-5" id="tablaPosiciones">
        <!-- Tabla de Posiciones División Profesional -->
        <div class="col-lg-7">
            <div class="ca-card p-4 h-100">
                <div class="ca-sec-header mb-3">
                    <h2 class="ca-sec-title" style="font-size: 1.3rem;">TABLA DE POSICIONES • CLAUSURA</h2>
                    <span class="badge" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display); font-weight: 900;">2026</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-2" style="font-size: 0.82rem; vertical-align: middle;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--ca-volt); font-family: var(--ca-font-display); font-size: 0.85rem; color: #94A3B8;">
                                <th style="width: 35px;">#</th>
                                <th>CLUB</th>
                                <th class="text-center">PJ</th>
                                <th class="text-center">G</th>
                                <th class="text-center">E</th>
                                <th class="text-center">P</th>
                                <th class="text-center">DG</th>
                                <th class="text-center" style="color: var(--ca-volt); font-size: 0.95rem;">PTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tablaPosiciones ?? [] as $row)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <td>
                                        @if($row['zona'] === 'libertadores')
                                            <span class="badge" style="background: #16A34A; width: 22px; padding: 3px 0;">{{ $row['pos'] }}</span>
                                        @elseif($row['zona'] === 'sudamericana')
                                            <span class="badge" style="background: #0284C7; width: 22px; padding: 3px 0;">{{ $row['pos'] }}</span>
                                        @else
                                            <span class="text-muted fw-bold ps-1">{{ $row['pos'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if(!empty($row['logo']))
                                                <img src="{{ $row['logo'] }}" alt="{{ $row['club'] }}" style="width: 20px; height: 20px; object-fit: contain;" class="me-2" onerror="this.style.display='none'">
                                            @endif
                                            <strong class="text-white">{{ $row['club'] }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $row['pj'] }}</td>
                                    <td class="text-center text-muted">{{ $row['g'] }}</td>
                                    <td class="text-center text-muted">{{ $row['e'] }}</td>
                                    <td class="text-center text-muted">{{ $row['p'] }}</td>
                                    <td class="text-center fw-bold {{ str_contains($row['dg'], '+') ? 'text-success' : 'text-danger' }}">{{ $row['dg'] }}</td>
                                    <td class="text-center fw-bold" style="color: var(--ca-volt); font-size: 1rem;">{{ $row['pts'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center gap-3 pt-2 text-muted" style="font-size: 0.7rem;">
                    <div><span class="badge bg-success me-1">1-3</span> Copa Libertadores</div>
                    <div><span class="badge bg-primary me-1">4-7</span> Copa Sudamericana</div>
                </div>
            </div>
        </div>

        <!-- Columna de Opinión Deportiva -->
        <div class="col-lg-5">
            <div class="ca-card p-4 h-100" style="background: linear-gradient(180deg, #162032 0%, #111827 100%);">
                <div class="ca-sec-header mb-3">
                    <h2 class="ca-sec-title" style="font-size: 1.3rem;">EL OJO DE CONTRAATAQUE</h2>
                    <span style="color: var(--ca-gold); font-size: 0.75rem; font-weight: 800; font-family: var(--ca-font-display);">OPINIÓN DEPORTIVA</span>
                </div>

                <div class="d-flex flex-column gap-4">
                    @foreach($columnistasDeportes ?? [] as $col)
                        <div class="p-3 rounded" style="background: rgba(0,0,0,0.3); border-left: 3px solid var(--ca-volt);">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="{{ $col['foto'] }}" alt="{{ $col['autor'] }}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--ca-volt);">
                                <div>
                                    <h4 class="h6 mb-0 text-white fw-bold">{{ $col['autor'] }}</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $col['cargo'] }}</small>
                                </div>
                            </div>
                            <h5 class="ca-title-card" style="font-size: 1rem; color: var(--ca-volt);">"{{ $col['titulo'] }}"</h5>
                            <p class="text-light small mb-0" style="line-height: 1.45; font-style: italic;">
                                {{ $col['extracto'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         BLOQUE 4: VIDEOS Y JUGADAS DESTACADAS
    ══════════════════════════════════════════════════════ -->
    <div class="mb-5">
        <div class="ca-sec-header">
            <h2 class="ca-sec-title">GOLES & JUGADAS • MULTIMEDIA</h2>
            <span class="badge bg-danger"><i class="fas fa-play me-1"></i> VIDEO RESÚMENES</span>
        </div>

        <div class="row g-4">
            @foreach($videosDestacados ?? [] as $v)
                <div class="col-md-4">
                    <div class="ca-card h-100">
                        <div style="height: 190px; position: relative; overflow: hidden;">
                            <a href="{{ route('contraataque.show', $v['id']) }}">
                                <img src="{{ $v['imagen'] }}" alt="{{ $v['titulo'] }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=800&q=80'">
                                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 52px; height: 52px; background: var(--ca-red); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; box-shadow: 0 0 20px rgba(255,59,48,0.6); transition: transform 0.2s;">
                                        <i class="fas fa-play ms-1"></i>
                                    </div>
                                </div>
                            </a>
                            <span class="badge bg-dark position-absolute bottom-2 end-2 text-white" style="font-family: var(--ca-font-display);">{{ $v['duracion'] }}</span>
                            <span class="badge position-absolute top-2 start-2" style="background: var(--ca-volt); color: #000; font-family: var(--ca-font-display); font-weight: 800; font-size: 0.65rem;">
                                {{ $v['categoria'] }}
                            </span>
                        </div>
                        <div class="p-3">
                            <h4 class="ca-title-card" style="font-size: 1rem;">
                                <a href="{{ route('contraataque.show', $v['id']) }}">{{ $v['titulo'] }}</a>
                            </h4>
                            <small class="text-muted"><i class="fas fa-eye me-1"></i>{{ $v['vistas'] }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         BLOQUE 5: MÁS NOTICIAS DEPORTIVAS
    ══════════════════════════════════════════════════════ -->
    <div>
        <div class="ca-sec-header">
            <h2 class="ca-sec-title">MÁS NOTICIAS EN CONTRA ATAQUE</h2>
        </div>

        <div class="row g-4">
            @foreach($masNoticias as $m)
                @php
                    $mId = is_object($m) ? ($m->id ?? 1) : ($m['id'] ?? 1);
                    $mTitulo = is_object($m) ? ($m->titulo ?? '') : ($m['titulo'] ?? '');
                    $mBajada = is_object($m) ? ($m->bajada ?? $m->resumen ?? '') : ($m['bajada'] ?? '');
                    $mImg = '';
                    if (is_object($m)) {
                        $mImg = method_exists($m, 'getImageUrl') ? $m->getImageUrl() : ($m->imagen ?? $m->foto ?? '');
                    } else {
                        $mImg = $m['imagen'] ?? $m['foto'] ?? '';
                    }
                    $mCat = is_object($m) ? ($m->category->name ?? 'DEPORTES') : ($m['category']->name ?? 'DEPORTES');
                    $mFecha = is_object($m) ? ($m->created_at ? $m->created_at->diffForHumans() : 'Hace unas horas') : 'Hace unas horas';
                    $mSlug = \Illuminate\Support\Str::slug($mTitulo) ?: 'noticia';
                    $mUrl = is_object($m) && isset($m->url) ? $m->url : route('contraataque.show', ['id' => $mId, 'slug' => $mSlug]);
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="ca-card h-100 p-3 d-flex gap-3">
                        <div style="width: 100px; height: 100px; min-width: 100px; border-radius: 6px; overflow: hidden;">
                            <a href="{{ $mUrl }}">
                                <img src="{{ $mImg ?: 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=800&q=80' }}" 
                                     alt="{{ $mTitulo }}" 
                                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                                     onmouseover="this.style.transform='scale(1.05)'"
                                     onmouseout="this.style.transform='scale(1)'"
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=800&q=80'">
                            </a>
                        </div>
                        <div class="d-flex flex-column justify-content-between">
                            <div>
                                <span class="ca-kicker" style="font-size: 0.65rem;">{{ $mCat }}</span>
                                <h4 class="ca-title-card" style="font-size: 0.95rem; margin-top: 2px;">
                                    <a href="{{ $mUrl }}">{{ \Illuminate\Support\Str::limit($mTitulo, 65) }}</a>
                                </h4>
                            </div>
                            <small class="text-muted" style="font-size: 0.68rem;">{{ $mFecha }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

<style>
.ca-leagues-nav-wrap {
    overflow-x: auto;
    scrollbar-width: thin;
    padding-bottom: 4px;
}
.ca-leagues-nav-wrap::-webkit-scrollbar {
    height: 4px;
}
.ca-leagues-nav-wrap::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.15);
    border-radius: 4px;
}
.ca-leagues-nav {
    display: flex;
    gap: 6px;
    white-space: nowrap;
}
.ca-league-tab-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    font-family: var(--ca-font-display);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.ca-league-tab-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.25);
}
.ca-league-tab-btn.active {
    background: var(--ca-volt);
    color: #000000;
    border-color: var(--ca-volt);
    box-shadow: 0 0 12px rgba(0, 255, 135, 0.4);
}
.ca-team-logo {
    width: 20px;
    height: 20px;
    object-fit: contain;
    flex-shrink: 0;
}
.ca-live-dot-pulse {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #ef4444;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: caPulseDot 1.5s infinite;
}
@keyframes caPulseDot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.ca-blink {
    animation: caBlink 1s infinite alternate;
}
@keyframes caBlink {
    from { opacity: 1; }
    to { opacity: 0.3; }
}
</style>

<script>
function switchLeagueScoreboard(leagueKey, btn) {
    const nav = document.getElementById('caLeaguesNav');
    if (nav) {
        nav.querySelectorAll('.ca-league-tab-btn').forEach(b => b.classList.remove('active'));
    }
    if (btn) {
        btn.classList.add('active');
    }

    const container = document.getElementById('caMatchesContainer');
    if (!container) return;

    // Estado visual de carga elegante
    container.innerHTML = `
        <div class="p-4 text-center text-muted" style="min-height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border spinner-border-sm text-success mb-2" role="status" style="width: 1.5rem; height: 1.5rem; color: var(--ca-volt) !important;"></div>
            <span style="font-size: 0.76rem; letter-spacing: 0.5px; color: #94A3B8;">Actualizando marcadores en tiempo real...</span>
        </div>
    `;

    fetch('{{ route("contraataque.api.partidos") }}?liga=' + encodeURIComponent(leagueKey))
        .then(response => {
            if (!response.ok) throw new Error('Error de red');
            return response.json();
        })
        .then(data => {
            renderMatchesList(data.matches || [], container);
        })
        .catch(err => {
            container.innerHTML = `
                <div class="p-3 text-center text-muted small rounded" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.08);">
                    <i class="fas fa-exclamation-triangle text-warning mb-1 fs-5"></i>
                    <p class="mb-0">No se pudieron cargar los datos en este momento.</p>
                </div>
            `;
        });
}

function renderMatchesList(matches, container) {
    if (!matches || matches.length === 0) {
        container.innerHTML = `
            <div class="p-4 text-center rounded" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                <i class="fas fa-futbol text-muted mb-2 fs-3"></i>
                <p class="small text-muted mb-0">No hay partidos programados en vivo para esta fecha.</p>
            </div>
        `;
        return;
    }

    let html = '';
    matches.forEach(p => {
        let badgeHtml = '';
        if (p.is_live) {
            badgeHtml = `<span class="ca-badge-live"><i class="fas fa-circle ca-blink" style="font-size: 5px;"></i> VIVO ${p.minuto || ''}</span>`;
        } else if (p.is_finished) {
            badgeHtml = `<span class="ca-badge-ft">FINAL</span>`;
        } else {
            badgeHtml = `<span style="color: var(--ca-gold); font-weight: 700;"><i class="far fa-clock me-1"></i>${p.estado || ''}</span>`;
        }

        const localLogo = p.local_logo 
            ? `<img src="${p.local_logo}" alt="${p.local}" class="ca-team-logo" onerror="this.style.display='none'">` 
            : `<span style="width: 10px; height: 10px; border-radius: 50%; background: ${p.local_color || '#00FF87'}; display: inline-block; flex-shrink: 0;"></span>`;
            
        const visitLogo = p.visitante_logo 
            ? `<img src="${p.visitante_logo}" alt="${p.visitante}" class="ca-team-logo" onerror="this.style.display='none'">` 
            : `<span style="width: 10px; height: 10px; border-radius: 50%; background: ${p.visitante_color || '#FFFFFF'}; display: inline-block; flex-shrink: 0;"></span>`;

        html += `
            <div class="p-2 rounded ca-match-item" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); transition: all 0.2s ease;">
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary border-opacity-10" style="font-size: 0.68rem;">
                    <span class="text-truncate me-2" style="color: #94A3B8; font-weight: 600;">
                        <span class="me-1">${p.flag || '⚽'}</span> ${p.torneo || 'Fútbol'}
                    </span>
                    ${badgeHtml}
                </div>

                <div class="d-flex align-items-center justify-content-between py-1">
                    <div class="d-flex align-items-center gap-2" style="width: 40%; overflow: hidden;">
                        ${localLogo}
                        <span class="fw-bold text-white text-truncate" style="font-size: 0.84rem;" title="${p.local_full || p.local}">
                            ${p.local}
                        </span>
                    </div>

                    <div class="text-center px-2 py-1 rounded ca-score-box" style="background: #000; font-family: var(--ca-font-display); font-weight: 900; font-size: 1.05rem; color: var(--ca-volt); min-width: 54px; letter-spacing: 1px; border: 1px solid rgba(0,255,135,0.25);">
                        ${p.goles_local} : ${p.goles_visitante}
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2" style="width: 40%; overflow: hidden;">
                        <span class="fw-bold text-white text-truncate text-end" style="font-size: 0.84rem;" title="${p.visitante_full || p.visitante}">
                            ${p.visitante}
                        </span>
                        ${visitLogo}
                    </div>
                </div>

                ${p.estadio ? `
                    <div class="d-flex align-items-center mt-1 pt-1 border-top border-secondary border-opacity-10 text-muted" style="font-size: 0.66rem;">
                        <span class="text-truncate"><i class="fas fa-location-dot me-1 text-danger"></i>${p.estadio}</span>
                    </div>
                ` : ''}
            </div>
        `;
    });

    container.innerHTML = html;
}
</script>
@endsection
