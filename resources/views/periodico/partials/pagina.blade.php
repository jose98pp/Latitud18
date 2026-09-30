{{--
    Renderiza el CONTENIDO REAL de una página del periódico como HTML de diario.
    Se usa dentro del flipbook 3D (text seleccionable, imágenes, notas, clima).
    Clases con prefijo "np-" para no colisionar con el resto del lector.

    @param array $pg      Página de la edición
    @param array $edicion  Datos de la edición (masthead)
    @param bool  $masthead Mostrar cabecera tipo diario (solo portada)
--}}

@php
    $tipo    = $pg['tipo'] ?? 'portada';
    $esMast  = $masthead ?? ($tipo === 'portada');
    $pagRef  = fn($v) => !empty($v) ? '<div class="np-ref">' . $v . '</div>' : '';
@endphp

<article class="np-page np-t-{{ $tipo }}">

    {{-- ============ MASTHEAD (portada) ============ --}}
    @if ($esMast)
        <header class="np-masthead">
            <div class="np-slogan">{{ $edicion['slogan'] ?? 'El periódico digital de Latitud 18' }}</div>
            <h1 class="np-logo">LATITUD <span>18</span></h1>
            <div class="np-sub">{{ $edicion['subtitulo'] ?? 'Información Sin Ruido' }}</div>
            <div class="np-meta">
                <span>{{ $edicion['numero_edicion'] ?? '' }}</span>
                <span class="np-meta-city">{{ $edicion['ciudad'] ?? 'Santa Cruz' }}</span>
                <span>{{ $edicion['fecha'] ?? '' }}</span>
                <span>{{ $edicion['precio'] ?? '' }}</span>
            </div>
        </header>
    @else
        <header class="np-folio">
            <span class="np-folio-brand">LATITUD <span>18</span></span>
            <span class="np-folio-sec">{{ $pg['seccion_titulo'] ?? $pg['nombre'] ?? 'Edición' }}</span>
            <span class="np-folio-num">{{ $edicion['numero_edicion'] ?? '' }} · PÁG. {{ $pg['numero'] ?? '' }}</span>
        </header>
    @endif

    {{-- ============ PORTADA ============ --}}
    @if ($tipo === 'portada')

        @if (!empty($pg['titular_principal']))
            <div class="np-kicker">{{ $pg['titular_principal']['antetitulo'] ?? '' }}</div>
            <h2 class="np-titular-main">{{ $pg['titular_principal']['titulo'] ?? '' }}</h2>
        @endif

        @if (!empty($pg['noticia_central']['imagen']))
            <figure class="np-fig">
                <img src="{{ $pg['noticia_central']['imagen'] }}" alt="{{ $pg['noticia_central']['titulo_sobre_foto'] ?? '' }}" loading="lazy">
                @if (!empty($pg['noticia_central']['titulo_sobre_foto']))
                    <figcaption>
                        <strong>{{ $pg['noticia_central']['titulo_sobre_foto'] }}</strong>
                        @if (!empty($pg['noticia_central']['epigrafe']))
                            <span>{{ $pg['noticia_central']['epigrafe'] }}</span>
                        @endif
                        {!! $pagRef($pg['noticia_central']['pagina_ref'] ?? null) !!}
                    </figcaption>
                @endif
            </figure>
        @endif

        @if (!empty($pg['titular_principal']['columnas']))
            <div class="np-cols">
                @foreach ($pg['titular_principal']['columnas'] as $col)
                    <div class="np-col">
                        @if (!empty($col['destacado']))
                            <div class="np-destacado">{{ $col['destacado'] }}</div>
                        @endif
                        <p>{{ $col['texto'] ?? '' }}</p>
                        {!! $pagRef($col['pagina_ref'] ?? null) !!}
                    </div>
                @endforeach
            </div>
        @endif

        <div class="np-rule np-rule-strong"></div>

        <div class="np-two">
            <div class="np-two-main">
                @foreach ($pg['lateral_noticias'] ?? [] as $ln)
                    <div class="np-note">
                        @if (!empty($ln['imagen']))
                            <img src="{{ $ln['imagen'] }}" alt="" loading="lazy">
                        @endif
                        <div class="np-note-body">
                            <span class="np-tag" @if (!empty($ln['color_tag'])) style="background:{{ $ln['color_tag'] }}" @endif>{{ $ln['categoria'] ?? '' }}</span>
                            <h4>{{ $ln['titulo'] ?? '' }}</h4>
                            <p>{{ $ln['texto'] ?? '' }}</p>
                            {!! $pagRef($ln['pagina_ref'] ?? null) !!}
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="np-two-side">
                @if (!empty($pg['cintillo_inferior']))
                    <div class="np-box">
                        <div class="np-kicker">{{ $pg['cintillo_inferior']['categoria'] ?? '' }}</div>
                        @if (!empty($pg['cintillo_inferior']['imagen']))
                            <img src="{{ $pg['cintillo_inferior']['imagen'] }}" alt="" loading="lazy">
                        @endif
                        <h4>{{ $pg['cintillo_inferior']['titulo'] ?? '' }}</h4>
                        <p>{{ $pg['cintillo_inferior']['texto'] ?? '' }}</p>
                        {!! $pagRef($pg['cintillo_inferior']['pagina_ref'] ?? null) !!}
                    </div>
                @endif

                @if (($pg['dolar_compra'] ?? null) !== null)
                    <div class="np-box np-box-sm">
                        <div class="np-box-title">Dólar hoy</div>
                        <div class="np-row"><span>Compra</span><b>{{ $pg['dolar_compra'] }}</b></div>
                        <div class="np-row"><span>Venta</span><b>{{ $pg['dolar_venta'] ?? '' }}</b></div>
                    </div>
                @endif
            </aside>
        </div>

    {{-- ============ EDITORIAL / OPINIÓN / SERVICIOS ============ --}}
    @elseif ($tipo === 'editorial')

        <div class="np-two">
            <div class="np-two-main">
                @if (!empty($pg['editorial']['titulo']))
                    <h2 class="np-titular">{{ $pg['editorial']['titulo'] }}</h2>
                    <div class="np-rule"></div>
                    <div class="np-cols">
                        <div class="np-col">
                            @foreach ($pg['editorial']['parrafos_col1'] ?? [] as $par)
                                <p>{{ $par }}</p>
                            @endforeach
                        </div>
                        <div class="np-col">
                            @if (!empty($pg['editorial']['cita_destacada']))
                                <blockquote class="np-quote">{{ $pg['editorial']['cita_destacada'] }}</blockquote>
                            @endif
                            @foreach ($pg['editorial']['parrafos_col2'] ?? [] as $par)
                                <p>{{ $par }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="np-two-side">
                @if (!empty($pg['principios']))
                    <div class="np-box">
                        <div class="np-box-title">{{ $pg['principios']['titulo'] ?? '' }}</div>
                        @if (!empty($pg['principios']['imagen']))
                            <img src="{{ $pg['principios']['imagen'] }}" alt="" loading="lazy">
                        @endif
                        <p>{{ $pg['principios']['texto'] ?? '' }}</p>
                    </div>
                @endif

                @if (!empty($pg['opinion']))
                    <div class="np-rule"></div>
                    <div class="np-kicker">Opinión · {{ $pg['opinion']['autor'] ?? '' }}</div>
                    <h3 class="np-titular-sm">{{ $pg['opinion']['titulo'] ?? '' }}</h3>
                    <div class="np-cols">
                        @foreach ($pg['opinion']['columnas'] ?? [] as $i => $par)
                            <div class="np-col">
                                <p>{{ $par }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="np-rule"></div>

                <div class="np-cols">
                    <div class="np-col">
                        @if (!empty($pg['servicios']['clima']))
                            <div class="np-box np-box-sm">
                                <div class="np-box-title">Clima</div>
                                @foreach (['hoy', 'manana'] as $k)
                                    @if (!empty($pg['servicios']['clima'][$k]))
                                        <div class="np-row">
                                            <span>{{ $pg['servicios']['clima'][$k]['icono'] ?? '' }} {{ $pg['servicios']['clima'][$k]['dia'] ?? '' }}</span>
                                            <b>{{ $pg['servicios']['clima'][$k]['min'] ?? '' }} / {{ $pg['servicios']['clima'][$k]['max'] ?? '' }}</b>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="np-col">
                        @if (!empty($pg['servicios']['cotizaciones']))
                            <div class="np-box np-box-sm">
                                <div class="np-box-title">Cotizaciones</div>
                                @foreach (['dolar_compra' => 'Dólar compra', 'dolar_venta' => 'Dólar venta', 'ufv' => 'UFV', 'real' => 'Real', 'euro' => 'Euro'] as $k => $lbl)
                                    @if (!empty($pg['servicios']['cotizaciones'][$k]))
                                        <div class="np-row"><span>{{ $lbl }}</span><b>{{ $pg['servicios']['cotizaciones'][$k] }}</b></div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        @if (!empty($pg['staff']))
            <div class="np-rule"></div>
            <div class="np-staff">
                <div>
                    <b>Fundado:</b> {{ $pg['staff']['fundado'] ?? '' }}<br>
                    <b>Director:</b> {{ $pg['staff']['director'] ?? '' }}<br>
                    <b>Subdirectora:</b> {{ $pg['staff']['subdirectora'] ?? '' }}<br>
                    <b>Gerente:</b> {{ $pg['staff']['gerente'] ?? '' }}
                </div>
                <div>
                    <b>Seguridad:</b> {{ $pg['staff']['seguridad'] ?? '' }}<br>
                    <b>Comunidad:</b> {{ $pg['staff']['comunidad'] ?? '' }}<br>
                    {{ $pg['staff']['central'] ?? '' }}<br>
                    <span class="np-staff-legal">{{ $pg['staff']['editorial_imprenta'] ?? '' }}</span>
                </div>
            </div>
        @endif

    {{-- ============ ARTÍCULO CON FOTO + NOTAS (comunidad / negocios / genérica) ============ --}}
    @else

        @php
            $principal = $pg['articulo_principal'] ?? $pg['articulo_superior'] ?? null;
            $secundario = $pg['articulo_inferior'] ?? null;
        @endphp

        @if ($principal)
            <div class="np-kicker">{{ $principal['antetitulo'] ?? '' }}</div>
            <h2 class="np-titular">{{ $principal['titulo'] ?? '' }}</h2>
            @if (!empty($principal['bajada']))
                <p class="np-bajada">{{ $principal['bajada'] }}</p>
            @endif
            @if (!empty($principal['imagen']))
                <figure class="np-fig">
                    <img src="{{ $principal['imagen'] }}" alt="{{ $principal['titulo'] ?? '' }}" loading="lazy">
                    @if (!empty($principal['pie_foto']))
                        <figcaption>{{ $principal['pie_foto'] }}</figcaption>
                    @endif
                </figure>
            @endif
        @endif

        <div class="np-rule"></div>

        <div class="np-two">
            <div class="np-two-main">
                @if ($principal)
                    <div class="np-cols">
                        @foreach (array_chunk($principal['columnas'] ?? [], 2) as $par)
                            <div class="np-col">
                                @foreach ($par as $p2)
                                    <p>{{ $p2 }}</p>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    @if (!empty($principal['autor']))
                        <div class="np-ref">{{ $principal['autor'] }}</div>
                    @endif
                @endif

                @if ($secundario)
                    <div class="np-rule"></div>
                    <div class="np-kicker">{{ $secundario['antetitulo'] ?? '' }}</div>
                    <h3 class="np-titular-sm">{{ $secundario['titulo'] ?? '' }}</h3>
                    @if (!empty($secundario['imagen']))
                        <figure class="np-fig">
                            <img src="{{ $secundario['imagen'] }}" alt="{{ $secundario['titulo'] ?? '' }}" loading="lazy">
                            @if (!empty($secundario['pie_foto']))
                                <figcaption>{{ $secundario['pie_foto'] }}</figcaption>
                            @endif
                        </figure>
                    @endif
                    <div class="np-cols">
                        @foreach (array_chunk($secundario['columnas'] ?? [], 2) as $par)
                            <div class="np-col">
                                @foreach ($par as $p2)
                                    <p>{{ $p2 }}</p>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    @if (!empty($secundario['autor']))
                        <div class="np-ref">{{ $secundario['autor'] }}</div>
                    @endif
                @endif
            </div>

            <aside class="np-two-side">
                @foreach ($pg['lateral_noticias'] ?? [] as $ln)
                    <div class="np-note">
                        @if (!empty($ln['imagen']))
                            <img src="{{ $ln['imagen'] }}" alt="" loading="lazy">
                        @endif
                        <div class="np-note-body">
                            <div class="np-kicker">{{ $ln['antetitulo'] ?? $ln['categoria'] ?? '' }}</div>
                            <h4>{{ $ln['titulo'] ?? '' }}</h4>
                            <p>{{ $ln['texto'] ?? $ln['resumen'] ?? '' }}</p>
                        </div>
                    </div>
                    <div class="np-rule"></div>
                @endforeach

                @foreach ($pg['noticias'] ?? [] as $n)
                    <div class="np-note">
                        <div class="np-note-body">
                            <div class="np-kicker">{{ $n['categoria'] ?? '' }}</div>
                            <h4>{{ $n['titulo'] ?? '' }}</h4>
                            <p>{{ $n['texto'] ?? $n['resumen'] ?? '' }}</p>
                        </div>
                    </div>
                    <div class="np-rule"></div>
                @endforeach
            </aside>
        </div>
    @endif

    <footer class="np-legal">
        {{ $edicion['titulo'] ?? 'Latitud 18' }} · {{ $edicion['numero_edicion'] ?? '' }} ·
        {{ $edicion['fecha'] ?? '' }} · {{ $edicion['ciudad'] ?? 'Santa Cruz de la Sierra' }}, Bolivia
    </footer>
</article>
