<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $edicion['titulo'] ?? 'Periódico' }} — {{ $edicion['fecha'] ?? '' }}</title>
<style>
/* ============================================================
   LATITUD 18 — PLANTILLA DE IMPRESIÓN / PDF REAL
   Optimizada para DomPDF: solo tablas, sin flexbox ni grid.
   Formato broadsheet 300 x 430 mm
   ============================================================ */

@font-face {
    font-family: 'Montserrat';
    font-style: normal;
    font-weight: normal;
    src: url('{{ public_path("fonts/Montserrat-Regular.ttf") }}') format('truetype');
}
@font-face {
    font-family: 'Montserrat';
    font-style: normal;
    font-weight: 600;
    src: url('{{ public_path("fonts/Montserrat-SemiBold.ttf") }}') format('truetype');
}
@font-face {
    font-family: 'Montserrat';
    font-style: normal;
    font-weight: bold;
    src: url('{{ public_path("fonts/Montserrat-Bold.ttf") }}') format('truetype');
}
@font-face {
    font-family: 'Bebas Neue';
    font-style: normal;
    font-weight: normal;
    src: url('{{ public_path("fonts/BebasNeue-Regular.ttf") }}') format('truetype');
}

@page {
    size: 300mm 430mm;
    margin: 10mm 11mm 12mm 11mm;
}

* { margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 8.2pt;
    line-height: 1.34;
    color: #14181D;
}

/* ---------- Utilidades ---------- */
.clear { clear: both; height: 0; }
table { border-collapse: collapse; width: 100%; }
td, th { vertical-align: top; }

/* ---------- Paleta ---------- */
.rojo    { color: #D71920; }
.marine  { color: #0B1F3A; }
.gris    { color: #5A6470; }
.grisclaro { color: #8A93A0; }
.negro   { color: #000; }

/* ---------- Cabecera / folio ---------- */
.masthead { text-align: center; border-bottom: 2.5pt solid #0B1F3A; padding-bottom: 3mm; }
.masthead .slogan-top {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt; letter-spacing: 0.6pt; text-transform: uppercase;
    color: #D71920; font-weight: bold; padding-bottom: 1mm;
}
.masthead .logo {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 32pt; font-weight: bold; letter-spacing: 1.5pt;
    color: #0B1F3A; line-height: 1;
}
.masthead .logo span { color: #D71920; }
.masthead .sub {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 7.5pt; letter-spacing: 2.4pt; text-transform: uppercase;
    color: #0B1F3A; padding-top: 1mm;
}
.masthead .barra-meta {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.4pt; color: #3A4450; padding-top: 1.6mm;
    border-top: 0.6pt solid #C8CED6; margin-top: 1.8mm; padding-top: 1.2mm;
}

.folio {
    border-bottom: 1.4pt solid #0B1F3A;
    padding-bottom: 1.2mm; margin-bottom: 3mm;
    font-family: 'DejaVu Sans', Arial, sans-serif;
}
.folio .marca {
    font-size: 12pt; font-weight: bold; color: #0B1F3A; letter-spacing: 0.6pt;
}
.folio .marca span { color: #D71920; }
.folio .sec { font-size: 8pt; font-weight: bold; color: #D71920; text-transform: uppercase; letter-spacing: 1.2pt; }
.folio .ref { font-size: 6.4pt; color: #5A6470; text-align: right; }

/* ---------- Rompepaginas ---------- */
.pagina { page-break-after: always; page-break-inside: avoid; }

/* --- Páginas del editor visual (frames) ---
   El canvas se imprime a tamaño real (1:1), sin transform: DomPDF no lo soporta. */
.pagina .np-canvas-wrap { position: relative; width: 100%; height: auto; margin: 0; }
.pagina .np-canvas { position: absolute; top: 0; left: 0; overflow: hidden; }
.pagina .np-frame { position: absolute; box-sizing: border-box; overflow: hidden; }
.pagina .np-ftext { font-family: 'Source Sans 3', sans-serif; font-size: 11px; line-height: 1.45; color: #1e293b; }
.pagina .np-ftext p { margin: 0 0 6px; }
.pagina .np-fquote { font-family: 'Source Serif 4', Georgia, serif; font-style: italic; font-size: 15px; line-height: 1.35; color: #334155; }
.pagina .np-fimg { width: 100%; height: 100%; object-fit: cover; display: block; }
.pagina .np-frame-headline .np-ftext { font-family: 'Oswald', sans-serif; font-size: 28px; font-weight: bold; line-height: 1.1; color: #0f172a; }
.pagina:last-child { page-break-after: auto; }

/* ---------- Etiquetas / kickers ---------- */
.kicker {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.4pt; font-weight: bold; letter-spacing: 0.8pt;
    text-transform: uppercase; color: #D71920;
    padding-bottom: 0.8mm;
}
.tag {
    display: inline-block;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.6pt; font-weight: bold; letter-spacing: 0.5pt;
    text-transform: uppercase; color: #fff; background: #0B1F3A;
    padding: 0.6mm 1.4mm;
}
.tag.rojo { background: #D71920; }

/* ---------- Títulos ---------- */
.titular-principal {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 21pt; font-weight: bold; line-height: 1.05;
    color: #0B1F3A; padding: 1.5mm 0 2mm 0;
}
.titular-medio {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12.5pt; font-weight: bold; line-height: 1.12;
    color: #0B1F3A;
}
.titular-chico {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 8.6pt; font-weight: bold; line-height: 1.18;
    color: #14181D;
}
.bajada {
    font-size: 8pt; line-height: 1.32; color: #3A4450;
    padding-top: 1mm;
}
.antetitulo {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.8pt; letter-spacing: 0.6pt; text-transform: uppercase;
    color: #D71920; font-weight: bold;
}

/* ---------- Cuerpos ---------- */
.cuerpo { font-size: 8.2pt; line-height: 1.36; text-align: justify; }
.cuerpo p { padding-bottom: 1.6mm; }
.cuerpo .destacado {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.6pt; font-weight: bold; color: #D71920;
    text-transform: uppercase; letter-spacing: 0.4pt;
}
.cuerpo-mini { font-size: 7.1pt; line-height: 1.3; text-align: justify; color: #232A32; }

/* ---------- Cita destacada ---------- */
.cita {
    border-left: 2.4pt solid #D71920;
    background: #F6F7F9;
    padding: 2.4mm 3mm;
    margin: 2mm 0;
    font-family: 'DejaVu Serif', Georgia, serif;
    font-size: 9pt; font-style: italic; line-height: 1.36; color: #0B1F3A;
}

/* ---------- Imágenes ---------- */
img.foto { border: 0.5pt solid #C8CED6; }
.epigrafe {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.9pt; line-height: 1.28; color: #5A6470;
    padding-top: 0.9mm; border-top: 0.4pt solid #DDE2E8; margin-top: 0.9mm;
}

/* ---------- Separadores ---------- */
.regla { border-top: 0.7pt solid #C8CED6; height: 0; margin: 2mm 0; }
.regla-fuerte { border-top: 1.6pt solid #0B1F3A; height: 0; margin: 2.5mm 0; }
.vsep { border-left: 0.7pt solid #C8CED6; }
.gap { width: 4mm; }
.gap-s { width: 2.5mm; }

/* ---------- Ref. de página ---------- */
.pag-ref {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.8pt; font-weight: bold; color: #D71920;
    text-transform: uppercase; letter-spacing: 0.4pt;
}

/* ---------- Bloques de servicio ---------- */
.servicio-caja {
    border: 0.6pt solid #C8CED6;
    background: #FAFBFC;
    padding: 2mm;
}
.servicio-titulo {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.4pt; font-weight: bold; letter-spacing: 0.7pt;
    text-transform: uppercase; color: #0B1F3A;
    border-bottom: 0.8pt solid #0B1F3A; padding-bottom: 0.9mm; margin-bottom: 1.4mm;
}
.dato { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 6.6pt; }
.dato-label { color: #5A6470; }
.dato-valor { font-weight: bold; color: #0B1F3A; }

/* ---------- Piestaff ---------- */
.staff { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 5.9pt; color: #3A4450; line-height: 1.45; }
.staff b { color: #0B1F3A; }
.legal {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.2pt; color: #8A93A0; line-height: 1.34; text-align: center;
    border-top: 0.6pt solid #C8CED6; margin-top: 2.5mm; padding-top: 1.4mm;
}
</style>
</head>
<body>

@php
    $paginas = collect($edicion['paginas'] ?? [])->sortBy('numero')->values()->all();
    $edicion  = $edicion;
@endphp

@forelse ($paginas as $p)
    <div class="pagina">

        @if (!empty($p['frames']))
            {{-- Páginas maquetadas con el editor visual: mismo render que el lector --}}
            @php
                $pgW = (float) ($p['ancho'] ?? 720);
                $pgH = (float) ($p['alto'] ?? 1040);
                $npSafe = function (?string $html): string {
                    $html = (string) $html;
                    if ($html === '') { return ''; }
                    $html = preg_replace('#<(script|iframe|object|embed|link|meta|base|form)\b[^>]*>.*?</\1>#is', '', $html);
                    $html = preg_replace('#<(script|iframe|object|embed|link|meta|base|form)\b[^>]*/?>#i', '', $html);
                    $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
                    $html = preg_replace('#\s(href|src|xlink:href|action|formaction)\s*=\s*("|\')\s*(javascript|vbscript|data)\s*:#i', ' $1=$2#', $html);
                    return str_replace(['<!--', '-->'], '', $html);
                };
            @endphp
            @include('periodico.partials.frames', ['pg' => $p, 'pgW' => $pgW, 'pgH' => $pgH, 'npSafe' => $npSafe])

        @else
        {{-- ============ PORTADA ============ --}}
        @if (($p['tipo'] ?? '') === 'portada')

            <div class="masthead">
                <div class="slogan-top">{{ $edicion['slogan'] ?? 'El periódico digital de Latitud 18' }}</div>
                <div class="logo">LATITUD <span>18</span></div>
                <div class="sub">{{ $edicion['subtitulo'] ?? 'Información Sin Ruido' }}</div>
                <div class="barra-meta">
                    <table>
                        <tr>
                            <td style="text-align:left;">{{ $edicion['numero_edicion'] ?? '' }}</td>
                            <td style="text-align:center;font-weight:bold;color:#0B1F3A;">{{ $edicion['ciudad'] ?? 'Santa Cruz de la Sierra' }}</td>
                            <td style="text-align:right;">{{ $edicion['fecha'] ?? '' }}</td>
                            <td style="text-align:right;">{{ $edicion['precio'] ?? '' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if (!empty($p['titular_principal']))
                <div class="regla-fuerte"></div>
                <table>
                    <tr>
                        <td style="width:68%;">
                            <div class="antetitulo">{{ $p['titular_principal']['antetitulo'] ?? '' }}</div>
                            <div class="titular-principal">{{ $p['titular_principal']['titulo'] ?? '' }}</div>
                        </td>
                        <td class="gap-s"></td>
                        <td style="width:32%;">
                            @if (!empty($p['noticia_central']['imagen']))
                                <img class="foto" src="{{ $p['noticia_central']['imagen'] }}" style="width:100%;height:52mm;object-fit:cover;">
                                <div class="titular-medio" style="padding-top:1.2mm;">{{ $p['noticia_central']['titulo_sobre_foto'] ?? '' }}</div>
                                <div class="cuerpo-mini" style="padding-top:0.8mm;">{{ $p['noticia_central']['epigrafe'] ?? '' }}</div>
                                @if (!empty($p['noticia_central']['pagina_ref']))
                                    <div class="pag-ref" style="padding-top:1mm;">{{ $p['noticia_central']['pagina_ref'] }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="regla"></div>

                @if (!empty($p['titular_principal']['columnas']))
                    <table>
                        <tr>
                            @foreach ($p['titular_principal']['columnas'] as $i => $col)
                                <td style="width:{{ 100 / max(count($p['titular_principal']['columnas']), 1) }}%;{!! $i > 0 ? 'border-left:0.6pt solid #C8CED6;padding-left:2.5mm;' : '' !!}">
                                    <div class="cuerpo">
                                        @if (!empty($col['destacado']))
                                            <div class="destacado">{{ $col['destacado'] }}</div>
                                        @endif
                                        <p>{{ $col['texto'] ?? '' }}</p>
                                    </div>
                                    @if (!empty($col['pagina_ref']))
                                        <div class="pag-ref" style="padding-bottom:1.5mm;">{{ $col['pagina_ref'] }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </table>
                @endif
            @endif

            <div class="regla-fuerte"></div>

            {{-- Noticias laterales + cintillo --}}
            <table>
                <tr>
                    <td style="width:64%;">
                        @if (!empty($p['lateral_noticias']))
                            @foreach ($p['lateral_noticias'] as $ln)
                                <table style="margin-bottom:2.5mm;">
                                    <tr>
                                        <td style="width:26mm;">
                                            @if (!empty($ln['imagen']))
                                                <img class="foto" src="{{ $ln['imagen'] }}" style="width:26mm;height:17mm;object-fit:cover;">
                                            @endif
                                        </td>
                                        <td style="width:2.5mm;"></td>
                                        <td>
                                            <span class="tag" @if (!empty($ln['color_tag'])) style="background:{{ $ln['color_tag'] }};" @endif>{{ $ln['categoria'] ?? '' }}</span>
                                            <div class="titular-chico" style="padding-top:0.8mm;">{{ $ln['titulo'] ?? '' }}</div>
                                            <div class="cuerpo-mini" style="padding-top:0.6mm;">{{ $ln['texto'] ?? '' }}</div>
                                            @if (!empty($ln['pagina_ref']))
                                                <div class="pag-ref" style="padding-top:0.8mm;">{{ $ln['pagina_ref'] }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        @endif
                    </td>
                    <td style="width:4mm;"></td>
                    <td class="vsep" style="width:1mm;"></td>
                    <td style="width:4mm;"></td>
                    <td style="width:30%;">
                        @if (!empty($p['cintillo_inferior']))
                            <div class="servicio-caja">
                                <div class="kicker">{{ $p['cintillo_inferior']['categoria'] ?? '' }}</div>
                                @if (!empty($p['cintillo_inferior']['imagen']))
                                    <img class="foto" src="{{ $p['cintillo_inferior']['imagen'] }}" style="width:100%;height:26mm;object-fit:cover;">
                                @endif
                                <div class="titular-chico" style="padding-top:1.2mm;">{{ $p['cintillo_inferior']['titulo'] ?? '' }}</div>
                                <div class="cuerpo-mini" style="padding-top:0.7mm;">{{ $p['cintillo_inferior']['texto'] ?? '' }}</div>
                                @if (!empty($p['cintillo_inferior']['pagina_ref']))
                                    <div class="pag-ref" style="padding-top:1mm;">{{ $p['cintillo_inferior']['pagina_ref'] }}</div>
                                @endif
                            </div>
                        @endif

                        @if (($p['dolar_compra'] ?? null) !== null)
                            <div class="servicio-caja" style="margin-top:2.5mm;">
                                <div class="servicio-titulo">Dólar hoy</div>
                                <table>
                                    <tr>
                                        <td class="dato dato-label">Compra</td>
                                        <td class="dato dato-valor" style="text-align:right;">{{ $p['dolar_compra'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="dato dato-label">Venta</td>
                                        <td class="dato dato-valor" style="text-align:right;">{{ $p['dolar_venta'] }}</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

        {{-- ============ EDITORIAL / OPINIÓN / SERVICIOS ============ --}}
        @elseif (($p['tipo'] ?? '') === 'editorial')

            <div class="folio">
                <table>
                    <tr>
                        <td style="width:40%;"><span class="marca">LATITUD <span>18</span></span></td>
                        <td style="text-align:center;"><span class="sec">{{ $p['seccion_titulo'] ?? 'Editorial' }}</span></td>
                        <td style="width:30%;text-align:right;" class="ref">{{ $edicion['numero_edicion'] ?? '' }} · PÁG. {{ $p['numero'] ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <table>
                <tr>
                    {{-- Editorial --}}
                    <td style="width:46%;padding-right:3mm;">
                        @if (!empty($p['editorial']['titulo']))
                            <div class="titular-medio">{{ $p['editorial']['titulo'] }}</div>
                            <div class="regla"></div>
                            <table>
                                <tr>
                                    <td style="width:48%;padding-right:2.5mm;">
                                        <div class="cuerpo">
                                            @foreach ($p['editorial']['parrafos_col1'] ?? [] as $par)
                                                <p>{{ $par }}</p>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td style="width:2%;border-left:0.6pt solid #C8CED6;"></td>
                                    <td style="width:48%;padding-left:2.5mm;">
                                        <div class="cuerpo">
                                            @if (!empty($p['editorial']['cita_destacada']))
                                                <div class="cita">{{ $p['editorial']['cita_destacada'] }}</div>
                                            @endif
                                            @foreach ($p['editorial']['parrafos_col2'] ?? [] as $par)
                                                <p>{{ $par }}</p>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>

                    <td class="vsep" style="width:1mm;"></td>
                    <td style="width:3mm;"></td>

                    {{-- Principios / Opinión / Servicios --}}
                    <td style="width:50%;">
                        @if (!empty($p['principios']))
                            <table style="margin-bottom:3mm;">
                                <tr>
                                    <td style="width:24mm;">
                                        @if (!empty($p['principios']['imagen']))
                                            <img class="foto" src="{{ $p['principios']['imagen'] }}" style="width:24mm;height:24mm;object-fit:cover;">
                                        @endif
                                    </td>
                                    <td style="width:2.5mm;"></td>
                                    <td>
                                        <div class="servicio-titulo">{{ $p['principios']['titulo'] ?? '' }}</div>
                                        <div class="cuerpo-mini">{{ $p['principios']['texto'] ?? '' }}</div>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <div class="regla"></div>

                        @if (!empty($p['opinion']))
                            <div class="antetitulo">Opinión · {{ $p['opinion']['autor'] ?? '' }}</div>
                            <div class="titular-medio" style="padding:0.8mm 0 1.2mm 0;">{{ $p['opinion']['titulo'] ?? '' }}</div>
                            <div class="cuerpo">
                                @foreach ($p['opinion']['columnas'] ?? [] as $par)
                                    <p>{{ $par }}</p>
                                @endforeach
                            </div>
                        @endif

                        <div class="regla"></div>

                        <table>
                            <tr>
                                <td style="width:50%;padding-right:2mm;">
                                    @if (!empty($p['servicios']['clima']))
                                        <div class="servicio-caja">
                                            <div class="servicio-titulo">Clima · {{ $p['servicios']['clima']['ciudad'] ?? '' }}</div>
                                            <table>
                                                @foreach (['hoy', 'manana'] as $k)
                                                    @if (!empty($p['servicios']['clima'][$k]))
                                                        <tr>
                                                            <td class="dato" style="width:6mm;">{{ $p['servicios']['clima'][$k]['icono'] ?? '' }}</td>
                                                            <td class="dato">{{ $p['servicios']['clima'][$k]['dia'] ?? '' }}</td>
                                                            <td class="dato dato-valor" style="text-align:right;">{{ $p['servicios']['clima'][$k]['min'] ?? '' }} / {{ $p['servicios']['clima'][$k]['max'] ?? '' }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </table>
                                            @if (!empty($p['servicios']['clima']['hoy']['viento']))
                                                <div class="dato dato-label" style="padding-top:1mm;">Viento: {{ $p['servicios']['clima']['hoy']['viento'] }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="width:2mm;"></td>
                                <td style="width:50%;">
                                    @if (!empty($p['servicios']['cotizaciones']))
                                        <div class="servicio-caja">
                                            <div class="servicio-titulo">Cotizaciones</div>
                                            <table>
                                                <tr><td class="dato dato-label">Dólar compra</td><td class="dato dato-valor" style="text-align:right;">{{ $p['servicios']['cotizaciones']['dolar_compra'] ?? '' }}</td></tr>
                                                <tr><td class="dato dato-label">Dólar venta</td><td class="dato dato-valor" style="text-align:right;">{{ $p['servicios']['cotizaciones']['dolar_venta'] ?? '' }}</td></tr>
                                                <tr><td class="dato dato-label">UFV</td><td class="dato dato-valor" style="text-align:right;">{{ $p['servicios']['cotizaciones']['ufv'] ?? '' }}</td></tr>
                                                <tr><td class="dato dato-label">Real</td><td class="dato dato-valor" style="text-align:right;">{{ $p['servicios']['cotizaciones']['real'] ?? '' }}</td></tr>
                                                <tr><td class="dato dato-label">Euro</td><td class="dato dato-valor" style="text-align:right;">{{ $p['servicios']['cotizaciones']['euro'] ?? '' }}</td></tr>
                                            </table>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Staff --}}
            @if (!empty($p['staff']))
                <div class="regla"></div>
                <div class="staff">
                    <table>
                        <tr>
                            <td style="width:50%;">
                                <b>Fundado:</b> {{ $p['staff']['fundado'] ?? '' }}<br>
                                <b>Director:</b> {{ $p['staff']['director'] ?? '' }}<br>
                                <b>Subdirectora:</b> {{ $p['staff']['subdirectora'] ?? '' }}<br>
                                <b>Gerente:</b> {{ $p['staff']['gerente'] ?? '' }}
                            </td>
                            <td style="width:50%;">
                                <b>Seguridad:</b> {{ $p['staff']['seguridad'] ?? '' }}<br>
                                <b>Comunidad:</b> {{ $p['staff']['comunidad'] ?? '' }}<br>
                                {{ $p['staff']['central'] ?? '' }}<br>
                                <span style="color:#8A93A0;">{{ $p['staff']['editorial_imprenta'] ?? '' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

        {{-- ============ COMUNIDAD ============ --}}
        @elseif (($p['tipo'] ?? '') === 'comunidad')

            <div class="folio">
                <table>
                    <tr>
                        <td style="width:40%;"><span class="marca">LATITUD <span>18</span></span></td>
                        <td style="text-align:center;"><span class="sec">{{ $p['seccion_titulo'] ?? 'Comunidad' }}</span></td>
                        <td style="width:30%;text-align:right;" class="ref">{{ $edicion['numero_edicion'] ?? '' }} · PÁG. {{ $p['numero'] ?? '' }}</td>
                    </tr>
                </table>
            </div>

            @if (!empty($p['articulo_principal']))
                <div class="antetitulo">{{ $p['articulo_principal']['antetitulo'] ?? '' }}</div>
                <div class="titular-principal" style="font-size:16pt;">{{ $p['articulo_principal']['titulo'] ?? '' }}</div>
                @if (!empty($p['articulo_principal']['bajada']))
                    <div class="bajada">{{ $p['articulo_principal']['bajada'] }}</div>
                @endif

                @if (!empty($p['articulo_principal']['imagen']))
                    <img class="foto" src="{{ $p['articulo_principal']['imagen'] }}" style="width:100%;height:62mm;object-fit:cover;margin-top:2mm;">
                    @if (!empty($p['articulo_principal']['pie_foto']))
                        <div class="epigrafe">{{ $p['articulo_principal']['pie_foto'] }}</div>
                    @endif
                @endif

                <div class="regla"></div>

                <table>
                    <tr>
                        <td style="width:72%;padding-right:4mm;">
                            <div class="cuerpo">
                                @foreach ($p['articulo_principal']['columnas'] ?? [] as $par)
                                    <p>{{ $par }}</p>
                                @endforeach
                            </div>
                            @if (!empty($p['articulo_principal']['autor']))
                                <div class="pag-ref" style="padding-top:1.5mm;">{{ $p['articulo_principal']['autor'] }}</div>
                            @endif
                        </td>
                        <td class="vsep" style="width:1mm;"></td>
                        <td style="width:4mm;"></td>
                        <td style="width:24%;">
                            @if (!empty($p['lateral_noticias']))
                                @foreach ($p['lateral_noticias'] as $ln)
                                    <div style="margin-bottom:3mm;">
                                        @if (!empty($ln['imagen']))
                                            <img class="foto" src="{{ $ln['imagen'] }}" style="width:100%;height:22mm;object-fit:cover;">
                                        @endif
                                        <div class="kicker" style="padding-top:1mm;">{{ $ln['antetitulo'] ?? '' }}</div>
                                        <div class="titular-chico">{{ $ln['titulo'] ?? '' }}</div>
                                        <div class="cuerpo-mini" style="padding-top:0.6mm;">{{ $ln['texto'] ?? '' }}</div>
                                    </div>
                                    <div class="regla"></div>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </table>
            @endif

        {{-- ============ NEGOCIOS / ECONOMÍA ============ --}}
        @elseif (($p['tipo'] ?? '') === 'negocios')

            <div class="folio">
                <table>
                    <tr>
                        <td style="width:40%;"><span class="marca">LATITUD <span>18</span></span></td>
                        <td style="text-align:center;"><span class="sec">{{ $p['seccion_titulo'] ?? 'Negocios' }}</span></td>
                        <td style="width:30%;text-align:right;" class="ref">{{ $edicion['numero_edicion'] ?? '' }} · PÁG. {{ $p['numero'] ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <table>
                <tr>
                    <td style="width:50%;padding-right:4mm;">
                        @if (!empty($p['articulo_superior']))
                            <div class="antetitulo">{{ $p['articulo_superior']['antetitulo'] ?? '' }}</div>
                            <div class="titular-medio" style="padding-top:0.6mm;">{{ $p['articulo_superior']['titulo'] ?? '' }}</div>
                            @if (!empty($p['articulo_superior']['bajada']))
                                <div class="bajada">{{ $p['articulo_superior']['bajada'] }}</div>
                            @endif
                            @if (!empty($p['articulo_superior']['imagen']))
                                <img class="foto" src="{{ $p['articulo_superior']['imagen'] }}" style="width:100%;height:48mm;object-fit:cover;margin-top:1.5mm;">
                                @if (!empty($p['articulo_superior']['pie_foto']))
                                    <div class="epigrafe">{{ $p['articulo_superior']['pie_foto'] }}</div>
                                @endif
                            @endif
                            <div class="cuerpo" style="padding-top:1.5mm;">
                                @foreach ($p['articulo_superior']['columnas'] ?? [] as $par)
                                    <p>{{ $par }}</p>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="vsep" style="width:1mm;"></td>
                    <td style="width:4mm;"></td>
                    <td style="width:50%;">
                        @if (!empty($p['articulo_inferior']))
                            <div class="antetitulo">{{ $p['articulo_inferior']['antetitulo'] ?? '' }}</div>
                            <div class="titular-medio" style="padding-top:0.6mm;">{{ $p['articulo_inferior']['titulo'] ?? '' }}</div>
                            @if (!empty($p['articulo_inferior']['imagen']))
                                <img class="foto" src="{{ $p['articulo_inferior']['imagen'] }}" style="width:100%;height:40mm;object-fit:cover;margin-top:1.5mm;">
                                @if (!empty($p['articulo_inferior']['pie_foto']))
                                    <div class="epigrafe">{{ $p['articulo_inferior']['pie_foto'] }}</div>
                                @endif
                            @endif
                            <div class="cuerpo" style="padding-top:1.5mm;">
                                @foreach ($p['articulo_inferior']['columnas'] ?? [] as $par)
                                    <p>{{ $par }}</p>
                                @endforeach
                            </div>
                            @if (!empty($p['articulo_inferior']['autor']))
                                <div class="pag-ref" style="padding-top:1.5mm;">{{ $p['articulo_inferior']['autor'] }}</div>
                            @endif
                        @endif

                        @if (!empty($p['lateral_noticias']))
                            <div class="regla"></div>
                            @foreach ($p['lateral_noticias'] as $ln)
                                <div class="cuerpo-mini" style="padding-bottom:1.5mm;">
                                    <span class="kicker">{{ $ln['antetitulo'] ?? $ln['categoria'] ?? '' }}</span>
                                    <b>{{ $ln['titulo'] ?? '' }}</b> — {{ $ln['texto'] ?? '' }}
                                </div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            </table>

        {{-- ============ GENÉRICA (cualquier otro tipo) ============ --}}
        @else
            <div class="folio">
                <table>
                    <tr>
                        <td style="width:40%;"><span class="marca">LATITUD <span>18</span></span></td>
                        <td style="text-align:center;"><span class="sec">{{ $p['seccion_titulo'] ?? $p['nombre'] ?? 'Edición' }}</span></td>
                        <td style="width:30%;text-align:right;" class="ref">{{ $edicion['numero_edicion'] ?? '' }} · PÁG. {{ $p['numero'] ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <div class="cuerpo">
                @if (!empty($p['noticias']))
                    @foreach ($p['noticias'] as $n)
                        <div style="margin-bottom:3mm;">
                            <div class="kicker">{{ $n['categoria'] ?? '' }}</div>
                            <div class="titular-medio">{{ $n['titulo'] ?? '' }}</div>
                            <div class="cuerpo-mini" style="padding-top:0.8mm;">{{ $n['texto'] ?? $n['resumen'] ?? '' }}</div>
                        </div>
                        <div class="regla"></div>
                    @endforeach
                @else
                    <p class="gris">Esta sección no tiene contenido asignado en la edición.</p>
                @endif
            </div>
        @endif

        {{-- Pie legal en todas las páginas --}}
        <div class="legal">
            {{ $edicion['titulo'] ?? 'Latitud 18' }} · {{ $edicion['numero_edicion'] ?? '' }} · {{ $edicion['fecha'] ?? '' }} ·
            Santa Cruz de la Sierra, Bolivia · Documento generado automáticamente
        </div>

        @endif {{-- fin @else de frames --}}

    </div>
@empty
    <div class="pagina">
        <div class="masthead">
            <div class="slogan-top">{{ $edicion['slogan'] ?? '' }}</div>
            <div class="logo">LATITUD <span>18</span></div>
            <div class="sub">{{ $edicion['subtitulo'] ?? '' }}</div>
        </div>
        <p class="gris" style="padding-top:6mm;">La edición aún no tiene páginas composadas.</p>
    </div>
@endforelse

</body>
</html>
