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
            <div class="ca-card h-100 position-relative" style="min-height: 480px; display: flex; flex-direction: column; justify-content: flex-end; overflow: hidden;">
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
                <div style="position: relative; z-index: 2; padding: 28px;">
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

        <!-- Columna Lateral: Partidos en Vivo y Próximos -->
        <div class="col-lg-4">
            <div class="ca-card h-100 p-3" style="background: var(--ca-bg-card);">
                <div class="ca-sec-header mb-3">
                    <span class="ca-sec-title" style="font-size: 1.15rem;">DIVISIÓN PROFESIONAL</span>
                    <span class="badge bg-danger text-white small" style="font-family: var(--ca-font-display);">FECHA 18</span>
                </div>

                <div class="d-flex flex-column gap-3">
                    @foreach($partidosVivo ?? [] as $p)
                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--ca-volt)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.06)'">
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.7rem;">
                                <span style="color: #94A3B8;">{{ $p['torneo'] }}</span>
                                @if($p['estado'] === 'EN VIVO')
                                    <span class="ca-badge-live"><i class="fas fa-circle" style="font-size:5px;"></i> VIVO {{ $p['minuto'] }}</span>
                                @elseif($p['estado'] === 'FINAL')
                                    <span class="ca-badge-ft">FINALIZADO</span>
                                @else
                                    <span style="color: var(--ca-gold); font-weight: 700;">{{ $p['estado'] }}</span>
                                @endif
                            </div>

                            <div class="d-flex align-items-center justify-content-between py-1">
                                <!-- Local -->
                                <div class="d-flex align-items-center gap-2" style="width: 40%;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $p['local_color'] }}; display: inline-block;"></span>
                                    <span class="fw-bold text-white text-truncate" style="font-size: 0.85rem;">{{ $p['local'] }}</span>
                                </div>

                                <!-- Marcador -->
                                <div class="text-center px-2 py-1 rounded" style="background: #000; font-family: var(--ca-font-display); font-weight: 900; font-size: 1.1rem; color: var(--ca-volt); min-width: 50px;">
                                    {{ $p['goles_local'] }} : {{ $p['goles_visitante'] }}
                                </div>

                                <!-- Visitante -->
                                <div class="d-flex align-items-center justify-content-end gap-2" style="width: 40%;">
                                    <span class="fw-bold text-white text-truncate text-end">{{ $p['visitante'] }}</span>
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $p['visitante_color'] }}; display: inline-block;"></span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-1 pt-1 border-top border-secondary text-muted" style="font-size: 0.68rem;">
                                <span><i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $p['estadio'] }}</span>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-2 text-center">
                        <a href="#tablaPosiciones" class="btn btn-sm w-100" style="background: rgba(0, 255, 135, 0.1); border: 1px solid #00FF87; color: #00FF87; font-family: var(--ca-font-display); font-weight: 700; font-size: 0.78rem; text-decoration: none;">
                            <i class="fas fa-list-ol me-1"></i> VER TABLA DE POSICIONES COMPLETA
                        </a>
                    </div>
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
                                        <strong class="text-white">{{ $row['club'] }}</strong>
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
@endsection
