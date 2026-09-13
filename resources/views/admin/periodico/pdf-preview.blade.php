<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $edicion['titulo'] ?? 'La Estrella' }} - {{ $edicion['numero_edicion'] ?? 'Edición Semanal' }}</title>
    
    <!-- Google Fonts Especializadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: 'Source Sans 3', -apple-system, sans-serif;
            background: #2D3748;
            color: #111827;
        }

        .no-print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #111827;
            color: #FFF;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            font-family: 'Montserrat', sans-serif;
        }

        .btn-print {
            background: #D71920;
            color: #FFF;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: background 0.2s;
        }

        .btn-print:hover { background: #b8141b; }

        .btn-back {
            background: transparent;
            border: 1px solid #4B5563;
            color: #E5E7EB;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .btn-back:hover { background: #374151; }

        .pages-wrap {
            padding: 70px 0 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        /* NEWSPAPER PAGE (A4 / TABLOID) */
        .print-page {
            width: 210mm;
            min-height: 297mm;
            background: #FFF;
            padding: 12mm 14mm 14mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }

        .print-page:last-child {
            page-break-after: avoid;
        }

        @media print {
            body { background: #FFF; }
            .no-print-toolbar { display: none !important; }
            .pages-wrap { padding: 0 !important; gap: 0 !important; }
            .print-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                height: 100vh !important;
                page-break-after: always !important;
                padding: 10mm 12mm !important;
            }
        }

        /* ESTILOS EDITORIALES */
        .top-masthead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .masthead-title {
            font-family: 'Anton', Impact, sans-serif;
            font-size: 2.8rem;
            line-height: 0.95;
            color: #0B1F3A;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .masthead-badge {
            background: #D71920;
            color: #FFF;
            font-family: 'Source Sans 3', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            padding: 1px 8px;
            border-radius: 2px;
            vertical-align: middle;
            margin-left: 4px;
        }

        .exchange-box {
            border: 1.5px solid #0B1F3A;
            background: #F8FAFC;
            padding: 4px 8px;
            text-align: right;
            min-width: 150px;
        }

        .meta-strip {
            background: #000;
            color: #FFF;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.58rem;
            padding: 3px 8px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            letter-spacing: 0.4px;
        }

        .slogan-strip {
            border-left: 3px solid #D71920;
            background: #F9FAFB;
            padding: 3px 8px;
            font-size: 0.62rem;
            font-weight: 600;
            color: #4B5563;
            margin-bottom: 10px;
        }

        .headline-big {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.85rem;
            font-weight: 900;
            line-height: 1.05;
            color: #000;
            margin-bottom: 8px;
        }

        .lead-4-cols {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            font-size: 0.68rem;
            line-height: 1.4;
            text-align: justify;
            border-bottom: 1.5px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .lead-4-cols strong {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            color: #000;
        }

        .page-badge {
            background: #000;
            color: #FFF;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.52rem;
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
        }

        .portada-body-grid {
            display: grid;
            grid-template-columns: 2.3fr 1fr;
            gap: 12px;
            margin-bottom: 10px;
        }

        .center-photo-card {
            border: 2px solid #D71920;
            position: relative;
            background: #000;
        }

        .center-photo-wrap {
            height: 250px;
            position: relative;
            overflow: hidden;
        }

        .center-photo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .photo-title-overlay {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.85);
            color: #FFF;
            padding: 8px 12px;
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 900;
            line-height: 1.15;
            border-left: 3px solid #D71920;
        }

        .photo-caption {
            background: #FFF;
            padding: 8px 10px;
            font-size: 0.68rem;
            line-height: 1.35;
            color: #1F2937;
        }

        .side-news-card {
            border: 1px solid #E5E7EB;
            padding: 6px;
            margin-bottom: 8px;
            background: #FFF;
        }

        .side-news-card:last-child { margin-bottom: 0; }

        .side-news-tag {
            color: #D71920;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.52rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .side-news-img {
            height: 70px;
            margin-bottom: 4px;
            overflow: hidden;
        }

        .side-news-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .side-news-title {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 0.72rem;
            line-height: 1.15;
            color: #000;
            margin-bottom: 3px;
        }

        .side-news-desc {
            font-size: 0.62rem;
            line-height: 1.3;
            color: #4B5563;
        }

        .breaking-strip {
            background: #D71920;
            color: #FFF;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
        }

        .inner-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .inner-tag {
            font-family: 'Anton', Impact, sans-serif;
            font-size: 1.4rem;
            color: #0B1F3A;
            background: #F3F4F6;
            padding: 1px 10px;
            border-left: 4px solid #D71920;
        }

        .pull-quote {
            border-top: 1.5px solid #D71920;
            border-bottom: 1.5px solid #D71920;
            padding: 8px 10px;
            margin: 8px 0;
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-weight: 700;
            font-size: 0.78rem;
            line-height: 1.3;
            color: #0B1F3A;
            background: #FFF5F5;
        }

        .dropcap::first-letter {
            font-family: 'Anton', Impact, sans-serif;
            font-size: 2.2rem;
            float: left;
            line-height: 0.8;
            margin-right: 5px;
            color: #0B1F3A;
        }

        .staff-footer {
            border-top: 1.5px solid #000;
            padding-top: 6px;
            margin-top: auto;
            font-size: 0.52rem;
            line-height: 1.35;
            color: #4B5563;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            background: #FAFAFA;
            padding: 6px 8px;
        }
    </style>
</head>
<body>

    <!-- TOOLBAR (NO SE IMPRIME) -->
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('admin.periodico.index', ['edicion_id' => $edicion['id']]) }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver al Editor
            </a>
            <div>
                <strong>{{ $edicion['titulo'] ?? 'La Estrella' }}</strong> • {{ $edicion['numero_edicion'] }} ({{ $edicion['fecha'] }})
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Imprimir / Guardar en PDF
            </button>
        </div>
    </div>

    <div class="pages-wrap">
        @foreach($edicion['paginas'] as $pIndex => $p)
            <div class="print-page">
                @if(($p['tipo'] ?? '') === 'portada')
                    <!-- PORTADA -->
                    <div class="top-masthead">
                        <div class="masthead-title">
                            {{ $edicion['titulo'] ?? 'La Estrella' }}
                            <span class="masthead-badge">{{ $edicion['subtitulo'] ?? 'del Oriente' }}</span>
                        </div>
                        <div class="exchange-box">
                            <div style="font-family:'Montserrat',sans-serif; font-size:0.52rem; font-weight:800; color:#0B1F3A;">TIPO DE CAMBIO DÓLAR EN BOLIVIA</div>
                            <div style="font-family:'Bebas Neue',sans-serif; font-size:1.4rem; color:#0B1F3A; line-height:1;">Bs {{ $p['dolar_venta'] ?? '12,58' }}</div>
                        </div>
                    </div>

                    <div class="meta-strip">
                        <span>{{ $edicion['ciudad'] ?? 'Santa Cruz de la Sierra' }}</span>
                        <span>•</span>
                        <span>{{ $edicion['fecha'] ?? 'Domingo 6 de septiembre de 2026' }}</span>
                        <span>•</span>
                        <span>{{ $edicion['numero_edicion'] ?? 'N° 11.986' }}</span>
                        <span>•</span>
                        <span>{{ count($edicion['paginas']) }} páginas</span>
                        <span>•</span>
                        <span>Precio: {{ $edicion['precio'] ?? 'Bs 7,00' }}</span>
                    </div>

                    <div class="slogan-strip">{{ $edicion['slogan'] ?? 'El 100% de los hogares atendidos por CRE pagan la misma tarifa equitativa' }}</div>

                    <!-- TITULAR -->
                    <div class="headline-big">{{ $p['titular_principal']['titulo'] ?? '' }}</div>
                    <div class="lead-4-cols">
                        @foreach($p['titular_principal']['columnas'] ?? [] as $col)
                            <div>
                                <strong>{{ $col['destacado'] ?? '' }}</strong> {{ $col['texto'] ?? '' }}
                                @if(!empty($col['pagina_ref']))
                                    <span class="page-badge">▶ {{ $col['pagina_ref'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- CUERPO CENTRAL -->
                    <div class="portada-body-grid">
                        <div class="center-photo-card">
                            <div class="center-photo-wrap">
                                <img src="{{ $p['noticia_central']['imagen'] ?? '' }}">
                                <div class="photo-title-overlay">{{ $p['noticia_central']['titulo_sobre_foto'] ?? '' }}</div>
                            </div>
                            <div class="photo-caption">
                                {{ $p['noticia_central']['epigrafe'] ?? '' }}
                                <span class="page-badge">▶ {{ $p['noticia_central']['pagina_ref'] ?? 'PÁG. 3' }}</span>
                            </div>
                        </div>

                        <div>
                            @foreach($p['lateral_noticias'] ?? [] as $item)
                                <div class="side-news-card">
                                    <div class="side-news-tag">{{ $item['categoria'] ?? '' }}</div>
                                    <div class="side-news-img"><img src="{{ $item['imagen'] ?? '' }}"></div>
                                    <div class="side-news-title">{{ $item['titulo'] ?? '' }}</div>
                                    <div class="side-news-desc">{{ $item['texto'] ?? '' }} <span class="page-badge">▶ {{ $item['pagina_ref'] ?? '' }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- CINTILLO -->
                    <div class="breaking-strip">
                        <span style="background:#FFF; color:#D71920; font-family:'Montserrat',sans-serif; font-weight:900; font-size:0.55rem; padding:1px 6px; border-radius:2px;">{{ $p['cintillo_inferior']['categoria'] ?? 'SEGURIDAD' }}</span>
                        <span style="font-family:'Playfair Display',serif; font-weight:900; font-size:0.75rem; flex:1;">{{ $p['cintillo_inferior']['texto'] ?? '' }}</span>
                        <span style="background:#FFF; color:#D71920; font-family:'Montserrat',sans-serif; font-weight:900; font-size:0.55rem; padding:1px 6px; border-radius:2px;">▶ {{ $p['cintillo_inferior']['pagina_ref'] ?? 'PÁG. 9' }}</span>
                    </div>

                @elseif(($p['tipo'] ?? '') === 'editorial')
                    <!-- PÁGINA 2: EDITORIAL & SERVICIOS -->
                    <div class="inner-header">
                        <div class="inner-tag">EDITORIAL</div>
                        <div style="font-family:'Montserrat',sans-serif; font-size:0.58rem; color:#6B7280; font-weight:700;">
                            {{ $edicion['fecha'] ?? '' }} // latitud18.com // <strong>PÁG. 2</strong>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap: 14px; margin-bottom: 12px; border-bottom: 1px solid #000; padding-bottom: 10px;">
                        <div>
                            <div style="font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.55rem; color:#D71920; margin-bottom:2px;">EDITORIAL</div>
                            <h2 style="font-family:'Playfair Display',serif; font-size:1.25rem; font-weight:900; line-height:1.1; margin-bottom:6px;">{{ $p['editorial']['titulo'] ?? '' }}</h2>
                            <div style="column-count:2; column-gap:12px; font-size:0.65rem; line-height:1.45; text-align:justify;">
                                @foreach($p['editorial']['parrafos_col1'] ?? [] as $par) <p style="margin-bottom:4px;">{{ $par }}</p> @endforeach
                            </div>
                            <div class="pull-quote">"{{ $p['editorial']['cita_destacada'] ?? '' }}"</div>
                            <div style="column-count:2; column-gap:12px; font-size:0.65rem; line-height:1.45; text-align:justify;">
                                @foreach($p['editorial']['parrafos_col2'] ?? [] as $par) <p style="margin-bottom:4px;">{{ $par }}</p> @endforeach
                            </div>
                        </div>

                        <div>
                            <!-- PRINCIPIOS -->
                            <div style="background:#F8FAFC; border:1px solid #E5E7EB; padding:6px; margin-bottom:8px;">
                                <div style="background:#000; color:#FFF; font-family:'Montserrat',sans-serif; font-size:0.55rem; font-weight:800; padding:2px 6px; display:inline-block; margin-bottom:4px;">TODOS SOMOS IGUALES</div>
                                <p style="font-size:0.6rem; line-height:1.3; color:#374151;">{{ $p['principios']['texto'] ?? '' }}</p>
                            </div>

                            <!-- CLIMA -->
                            <div style="background:#F8FAFC; border:1px solid #E5E7EB; padding:6px; margin-bottom:8px;">
                                <div style="background:#0B1F3A; color:#FFF; font-family:'Montserrat',sans-serif; font-size:0.55rem; font-weight:800; padding:2px 6px; display:inline-block; margin-bottom:4px;">Pronóstico del Tiempo</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:0.6rem; text-align:center;">
                                    <div style="background:#FFF; border:1px solid #E5E7EB; padding:4px;">
                                        <strong>HOY</strong> ☀️<br><strong>{{ $p['servicios']['clima']['hoy']['min'] ?? '17°C' }}</strong> / <strong>{{ $p['servicios']['clima']['hoy']['max'] ?? '22°C' }}</strong>
                                    </div>
                                    <div style="background:#FFF; border:1px solid #E5E7EB; padding:4px;">
                                        <strong>MAÑANA</strong> ⛅<br><strong>{{ $p['servicios']['clima']['manana']['min'] ?? '16°C' }}</strong> / <strong>{{ $p['servicios']['clima']['manana']['max'] ?? '26°C' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- COTIZACIONES -->
                            <div style="background:#F8FAFC; border:1px solid #E5E7EB; padding:6px;">
                                <div style="background:#0B1F3A; color:#FFF; font-family:'Montserrat',sans-serif; font-size:0.55rem; font-weight:800; padding:2px 6px; display:inline-block; margin-bottom:4px;">Cotización del Boliviano</div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px; font-size:0.58rem;">
                                    <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">DÓLARES: <strong>{{ $p['servicios']['cotizaciones']['dolar_compra'] ?? '12,58' }}</strong></div>
                                    <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">UFV: <strong>{{ $p['servicios']['cotizaciones']['ufv'] ?? '3.34041' }}</strong></div>
                                    <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">REAL: <strong>{{ $p['servicios']['cotizaciones']['real'] ?? '2.45272' }}</strong></div>
                                    <div style="background:#FFF; padding:2px 4px; border-bottom:1px solid #E5E7EB;">EURO: <strong>{{ $p['servicios']['cotizaciones']['euro'] ?? '14.60922' }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OPINIÓN -->
                    <div style="margin-bottom:10px;">
                        <div style="background:#D71920; color:#FFF; font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.65rem; padding:2px 8px; display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span>OPINIÓN</span>
                            <span>{{ $p['opinion']['autor'] ?? '' }}</span>
                        </div>
                        <h3 style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:900; line-height:1.15; margin-bottom:6px;">{{ $p['opinion']['titulo'] ?? '' }}</h3>
                        <div style="column-count:4; column-gap:10px; font-size:0.64rem; line-height:1.45; text-align:justify;" class="dropcap">
                            @foreach($p['opinion']['columnas'] ?? [] as $col) <p style="margin-bottom:4px;">{{ $col }}</p> @endforeach
                        </div>
                    </div>

                    <!-- STAFF -->
                    <div class="staff-footer">
                        <div><strong>Director:</strong> {{ $p['staff']['director'] ?? 'Dr. Carlos Subirana' }}<br><strong>Subdirectora:</strong> {{ $p['staff']['subdirectora'] ?? 'Lic. Ximena Suárez' }}</div>
                        <div><strong>Gerente:</strong> {{ $p['staff']['gerente'] ?? 'Dr. Pedro Alberto Subirana' }}<br><strong>Seguridad:</strong> {{ $p['staff']['seguridad'] ?? 'Carol Suárez' }}</div>
                        <div><strong>Oficinas:</strong> {{ $p['staff']['central'] ?? 'Calle Celso Castedo Nro. 46' }}<br>Santa Cruz de la Sierra</div>
                        <div><strong>Edita e Imprime:</strong><br>{{ $p['staff']['editorial_imprenta'] ?? 'Editorial CSS Ltda.' }}</div>
                    </div>

                @elseif(($p['tipo'] ?? '') === 'negocios')
                    <!-- PÁGINA NEGOCIOS -->
                    <div class="inner-header">
                        <div class="inner-tag" style="border-left-color:#D71920;">{{ $p['seccion_titulo'] ?? 'NEGOCIOS' }}</div>
                        <div style="font-family:'Montserrat',sans-serif; font-size:0.58rem; color:#6B7280; font-weight:700;">
                            {{ $edicion['fecha'] ?? '' }} // latitud18.com // <strong>PÁG. {{ $p['numero'] ?? ($pIndex + 1) }}</strong>
                        </div>
                    </div>

                    <!-- ARTÍCULO 1 -->
                    <div style="margin-bottom:14px; border-bottom:1.5px solid #000; padding-bottom:10px;">
                        <div style="color:#D71920; font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.6rem; text-transform:uppercase;">{{ $p['articulo_superior']['antetitulo'] ?? '' }}</div>
                        <h2 style="font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:900; line-height:1.05; margin-bottom:6px;">{{ $p['articulo_superior']['titulo'] ?? '' }}</h2>
                        <div style="font-size:0.75rem; font-weight:600; line-height:1.35; color:#374151; margin-bottom:8px;">{{ $p['articulo_superior']['bajada'] ?? '' }}</div>

                        <div style="display:grid; grid-template-columns: 1fr 1.8fr; gap:12px;">
                            <div>
                                <img src="{{ $p['articulo_superior']['imagen'] ?? '' }}" style="width:100%; height:140px; object-fit:cover; display:block;">
                                <small style="font-size:0.58rem; color:#6B7280; display:block; margin-top:2px;">{{ $p['articulo_superior']['pie_foto'] ?? '' }}</small>
                            </div>
                            <div style="column-count:3; column-gap:10px; font-size:0.65rem; line-height:1.45; text-align:justify;" class="dropcap">
                                @foreach($p['articulo_superior']['columnas'] ?? [] as $col) <p style="margin-bottom:4px;">{{ $col }}</p> @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- ARTÍCULO 2 -->
                    <div>
                        <div style="color:#D71920; font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.6rem; text-transform:uppercase;">{{ $p['articulo_inferior']['antetitulo'] ?? '' }}</div>
                        <h3 style="font-family:'Playfair Display',serif; font-size:1.15rem; font-weight:900; margin-bottom:6px;">{{ $p['articulo_inferior']['titulo'] ?? '' }}</h3>
                        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px;">
                            <div>
                                <img src="{{ $p['articulo_inferior']['imagen'] ?? '' }}" style="width:100%; height:105px; object-fit:cover; display:block;">
                            </div>
                            <div style="column-count:3; column-gap:10px; font-size:0.65rem; line-height:1.45; text-align:justify;">
                                @foreach($p['articulo_inferior']['columnas'] ?? [] as $col) <p style="margin-bottom:4px;">{{ $col }}</p> @endforeach
                            </div>
                        </div>
                    </div>

                @else
                    <!-- PÁGINA COMUNIDAD / DEPORTES / OTRAS -->
                    <div class="inner-header">
                        <div class="inner-tag">{{ $p['seccion_titulo'] ?? 'COMUNIDAD' }}</div>
                        <div style="font-family:'Montserrat',sans-serif; font-size:0.58rem; color:#6B7280; font-weight:700;">
                            {{ $edicion['fecha'] ?? '' }} // latitud18.com // <strong>PÁG. {{ $p['numero'] ?? ($pIndex + 1) }}</strong>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 2.2fr 1fr; gap:14px;">
                        <div>
                            <div style="color:#D71920; font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.6rem; text-transform:uppercase;">{{ $p['articulo_principal']['antetitulo'] ?? '' }}</div>
                            <h2 style="font-family:'Playfair Display',serif; font-size:1.55rem; font-weight:900; line-height:1.08; margin-bottom:6px;">{{ $p['articulo_principal']['titulo'] ?? '' }}</h2>
                            <div style="font-size:0.75rem; font-weight:600; line-height:1.35; color:#374151; margin-bottom:8px;">{{ $p['articulo_principal']['bajada'] ?? '' }}</div>

                            <img src="{{ $p['articulo_principal']['imagen'] ?? '' }}" style="width:100%; height:190px; object-fit:cover; display:block; margin-bottom:4px;">
                            <div style="font-size:0.6rem; color:#6B7280; margin-bottom:6px;">{{ $p['articulo_principal']['pie_foto'] ?? '' }}</div>
                            <div style="font-size:0.58rem; font-weight:700; color:#374151; margin-bottom:6px; border-bottom:1px solid #E5E7EB; padding-bottom:2px;">{{ $p['articulo_principal']['autor'] ?? '' }}</div>

                            <div style="column-count:4; column-gap:10px; font-size:0.65rem; line-height:1.45; text-align:justify;" class="dropcap">
                                @foreach($p['articulo_principal']['columnas'] ?? [] as $col) <p style="margin-bottom:4px;">{{ $col }}</p> @endforeach
                            </div>
                        </div>

                        <div>
                            @foreach($p['lateral_noticias'] ?? [] as $lat)
                                <div class="side-news-card">
                                    <div class="side-news-tag">{{ $lat['antetitulo'] ?? '' }}</div>
                                    <div class="side-news-title">{{ $lat['titulo'] ?? '' }}</div>
                                    @if(!empty($lat['imagen']))
                                        <div class="side-news-img"><img src="{{ $lat['imagen'] }}"></div>
                                    @endif
                                    <div class="side-news-desc">{{ $lat['texto'] ?? '' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(!empty($p['bloques_adicionales']))
                        <div style="margin-top:12px; border-top:1px dashed #CBD5E0; padding-top:10px; display:flex; flex-direction:column; gap:10px;">
                            @foreach($p['bloques_adicionales'] as $blk)
                                <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:10px; border-radius:3px;">
                                    <div style="color:#D71920; font-family:'Montserrat',sans-serif; font-weight:800; font-size:0.58rem; text-transform:uppercase;">{{ $blk['antetitulo'] ?? 'DESTACADO' }}</div>
                                    <h3 style="font-family:'Playfair Display',serif; font-size:1rem; font-weight:900; margin-bottom:4px;">{{ $blk['titulo'] ?? '' }}</h3>
                                    <p style="font-size:0.65rem; line-height:1.4; color:#374151; margin-bottom:0;">{{ $blk['contenido'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

</body>
</html>