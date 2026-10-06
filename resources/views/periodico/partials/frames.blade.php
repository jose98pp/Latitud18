{{--
    Pinta una página maquetada con el EDITOR VISUAL (frames / elementos).
    Es la única fuente de maquetación: la usan el lector público y el PDF,
    así ambos muestran exactamente lo mismo que se ve en el administrador.

    El canvas se construye a tamaño real del lienzo (ancho x alto de la página)
    y el lector lo escala con transform; en el PDF se imprime a 1:1.

    Espera: $pg, $pgW, $pgH, $npSafe
--}}

        <div class="np-canvas-wrap" data-w="{{ $pgW }}" data-h="{{ $pgH }}">
        <div class="np-canvas"
             style="width:{{ $pgW }}px;height:{{ $pgH }}px;background:{{ $pg['fondo_color'] ?? '#ffffff' }}">

            @foreach ($pg['frames'] as $f)
                @php
                    $ftype = $f['type'] ?? 'text';
                    $fw = (float) ($f['w'] ?? 200);
                    $fh = (float) ($f['h'] ?? 100);
                    $fx = (float) ($f['x'] ?? 20);
                    $fy = (float) ($f['y'] ?? 20);
                    $fz = (int) ($f['z'] ?? 10);
                    $fop = array_key_exists('opacity', $f) ? (float) $f['opacity'] : 1;
                @endphp
                <div class="np-frame np-frame-{{ $ftype }}"
                     style="left:{{ $fx }}px;top:{{ $fy }}px;width:{{ $fw }}px;height:{{ $fh }}px;z-index:{{ $fz }};opacity:{{ $fop }}">

                    @if ($ftype === 'divider')
                        <div style="width:100%;height:2px;background:{{ $f['color'] ?? '#cbd5e1' }};"></div>

                    @elseif ($ftype === 'masthead')
                        <div style="border-bottom:3px solid #0284c7;padding-bottom:4px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div style="background:#fef9c3;color:#854d0e;padding:4px 8px;border-radius:2px;font-size:9px;font-weight:700;width:140px;line-height:1.2;">
                                    {{ $f['leftEar'] ?? 'CRE 100%' }}
                                </div>
                                <div style="text-align:center;flex:1;">
                                    <span style="font-family:'Anton',sans-serif;font-size:46px;color:#0284c7;line-height:1;letter-spacing:1px;">{{ $f['newspaperName'] ?? 'LA ESTRELLA' }}</span>
                                    <span style="background:#D71920;color:#fff;font-family:'Anton',sans-serif;font-size:18px;padding:2px 8px;border-radius:2px;margin-left:4px;vertical-align:middle;">{{ $f['subBadge'] ?? 'del Oriente' }}</span>
                                    <div style="font-size:9px;font-weight:800;letter-spacing:1.5px;color:#64748b;text-transform:uppercase;margin-top:2px;">{{ $f['motto'] ?? 'EL PRIMER PERIÓDICO DE SANTA CRUZ' }}</div>
                                </div>
                                <div style="background:#0284c7;color:#fff;padding:4px 8px;border-radius:2px;font-size:9px;font-weight:800;width:130px;text-align:right;line-height:1.2;">
                                    {{ $f['rightEar'] ?? 'DÓLAR: Bs 12,58' }}
                                </div>
                            </div>
                            <div style="display:flex;justify-content:space-between;border-top:1px solid #e2e8f0;padding-top:3px;margin-top:4px;font-size:9px;color:#64748b;font-weight:600;">
                                <span>{{ $f['editionDate'] ?? ($edicion['ciudad'] ?? 'Santa Cruz de la Sierra') }}</span>
                                <span><strong>{{ $f['editionNumber'] ?? ($edicion['numero_edicion'] ?? '') }}</strong></span>
                                <span>{{ $f['price'] ?? ($edicion['precio'] ?? '') }}</span>
                            </div>
                        </div>

                    @elseif ($ftype === 'headline')
                        <div style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:center;">
                            @if (!empty($f['kicker']))
                                <span style="font-size:11px;font-weight:800;color:#D71920;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">{{ $f['kicker'] }}</span>
                            @endif
                            <div class="np-ftext">{!! $npSafe($f['content'] ?? 'Titular de Noticia') !!}</div>
                        </div>

                    @elseif ($ftype === 'image')
                        <div style="width:100%;height:100%;display:flex;flex-direction:column;">
                            <div style="flex:1;position:relative;overflow:hidden;">
                                <img src="{{ $f['src'] ?? '' }}" alt="{{ $f['caption'] ?? '' }}" class="np-fimg">
                            </div>
                            @if (!empty($f['caption']))
                                <div style="font-size:9.5px;color:#475569;line-height:1.3;padding-top:4px;font-style:italic;">{{ $f['caption'] }}</div>
                            @endif
                        </div>

                    @elseif ($ftype === 'qr')
                        @php $qrUrl = rawurlencode($f['url'] ?? 'https://latitud18.com/periodico'); @endphp
                        <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#fff;border:1px solid #e2e8f0;padding:6px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.08);border-radius:3px;box-sizing:border-box;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $qrUrl }}" alt="QR" style="width:calc(100% - 8px);max-height:calc(100% - 22px);object-fit:contain;">
                            <div style="font-size:9px;font-weight:700;color:#334155;margin-top:2px;text-transform:uppercase;letter-spacing:.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;width:100%;">
                                {{ $f['label'] ?? 'Escanea para leer online' }}
                            </div>
                        </div>

                    @elseif ($ftype === 'ad')
                        @php
                            $adStatus = $f['status'] ?? ($f['propiedades']['status'] ?? 'disponible');
                            $adImg = $f['advertiser_image_url'] ?? ($f['propiedades']['advertiser_image_url'] ?? null);
                            $formatCode = $f['format_code'] ?? ($f['propiedades']['format_code'] ?? null);
                        @endphp
                        @if ($adStatus === 'ocupado' && !empty($adImg))
                            <div style="width:100%;height:100%;position:relative;overflow:hidden;">
                                <img src="{{ $adImg }}" alt="{{ $f['advertiser_name'] ?? 'Publicidad' }}" style="width:100%;height:100%;object-fit:fill;display:block;">
                                @if(!empty($formatCode))
                                    <span style="position:absolute;top:4px;left:4px;background:rgba(0,0,0,0.65);color:#fff;font-size:8px;font-weight:bold;padding:1px 4px;border-radius:2px;font-family:'Montserrat',sans-serif;">{{ $formatCode }}</span>
                                @endif
                            </div>
                        @else
                            <div style="width:100%;height:100%;position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#E8F0FE;border:1px dashed #3b82f6;box-sizing:border-box;text-align:center;">
                                @if(!empty($formatCode))
                                    <span style="position:absolute;top:4px;left:4px;background:#1e40af;color:#fff;font-size:8px;font-weight:bold;padding:1px 4px;border-radius:2px;font-family:'Montserrat',sans-serif;">{{ $formatCode }}</span>
                                @endif
                                <span style="font-family:'Montserrat',sans-serif;font-size:13px;font-weight:bold;color:#1e40af;letter-spacing:2px;">DISPONIBLE</span>
                            </div>
                        @endif

                    @elseif ($ftype === 'quote')
                        <div class="np-fquote">{!! $npSafe($f['content'] ?? '') !!}</div>

                    @else
                        {{-- box, article, text y cualquier otro tipo --}}
                        @php
                            $fcols = (int) ($f['columns'] ?? 1);
                            $hasNoticia = !empty($f['noticia_id']) || !empty($f['propiedades']['noticia_id']);
                            $catColor = $f['categoria_color'] ?? ($f['propiedades']['categoria_color'] ?? '#D71920');
                        @endphp
                        @if ($hasNoticia)
                            <div style="position:absolute;top:0;left:0;right:0;height:6px;background:{{ $catColor }};z-index:10;"></div>
                        @endif
                        <div class="np-ftext" style="font-family:'Montserrat',sans-serif;@if($hasNoticia) padding-top:8px; @endif @if ($fcols > 1) column-count:{{ $fcols }};column-gap:14px; @endif">
                            {!! $npSafe($f['content'] ?? '') !!}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        </div>
