@php
    $paperW = (float) ($p['configuracion']['page_width_mm'] ?? 280);
    $paperH = (float) ($p['configuracion']['page_height_mm'] ?? 430);
    // Una sola escala para conservar la geometría y las proporciones del lienzo.
    $sx = min($paperW / max(1, (float) ($p['ancho'] ?? 720)), $paperH / max(1, (float) ($p['alto'] ?? 1040)));
    $pdfService = app(\App\Services\PeriodicoPdfService::class);
    $scaledHtml = function ($value) use ($npSafe, $sx) {
        return preg_replace_callback('/([0-9]+(?:\.[0-9]+)?)px\b/', fn($m) => ((float)$m[1]*$sx).'mm', $npSafe((string) $value));
    };
@endphp
<div style="position:relative;width:{{ $paperW }}mm;height:{{ $paperH - 0.2 }}mm;overflow:hidden;background:{{ $p['fondo_color'] ?? '#ffffff' }};">
@foreach (collect($p['frames'])->sortBy('z') as $f)
    @php
        $x = (float)($f['x'] ?? 0) * $sx;
        $y = (float)($f['y'] ?? 0) * $sx;
        $w = max(0, (float)($f['w'] ?? 0) * $sx);
        $h = max(0, (float)($f['h'] ?? 0) * $sx);
        $type = $f['type'] ?? 'text';
        $content = $scaledHtml($f['content'] ?? '');
        $src = $type === 'ad' ? ($f['advertiser_image_url'] ?? $f['propiedades']['advertiser_image_url'] ?? '') : ($f['src'] ?? '');
        $image = $pdfService->imagenParaPdf($src);
        $captionH = $type === 'image' && !empty($f['caption']) ? min($h, 17*$sx) : 0;
        $photoH = max(0, $h - $captionH);
        $imageW = $w; $imageH = $photoH; $imageX = 0; $imageY = 0;
        $dim = $image && is_file($image) ? @getimagesize($image) : false;
        if (!$dim && $image && is_file($image) && strtolower(pathinfo($image, PATHINFO_EXTENSION)) === 'svg') {
            if (preg_match('/viewBox="[0-9.]+\s+[0-9.]+\s+([0-9.]+)\s+([0-9.]+)"/', file_get_contents($image), $viewBox)) $dim = [(float)$viewBox[1], (float)$viewBox[2]];
        }
        if ($dim && $dim[0]>0 && $dim[1]>0) {
            $fit = $type === 'ad' ? 'contain' : ($f['image_fit'] ?? 'cover');
            $scale = $fit === 'contain' ? min($w/$dim[0], $photoH/$dim[1]) : max($w/$dim[0], $photoH/$dim[1]);
            $imageW=$dim[0]*$scale; $imageH=$dim[1]*$scale;
            $imageX=($w-$imageW)/2; $imageY=($photoH-$imageH)/2;
        }
        $opacity = max(0, min(1, (float)($f['opacity'] ?? 1)));
    @endphp
    <div data-frame-id="{{ $f['id'] ?? '' }}" style="position:absolute;left:{{ $x }}mm;top:{{ $y }}mm;width:{{ $w }}mm;height:{{ $h }}mm;overflow:hidden;box-sizing:border-box;opacity:{{ $opacity }};font-family:Montserrat,DejaVu Sans,sans-serif;font-size:{{ 13*$sx }}mm;line-height:1.45;color:#111;">
    @if ($type === 'image' || ($type === 'ad' && ($f['status'] ?? $f['propiedades']['status'] ?? '') === 'ocupado' && $src))
        <div style="position:relative;width:{{ $w }}mm;height:{{ $photoH }}mm;overflow:hidden;">
            <img src="{{ $image }}" style="position:absolute;left:{{ $imageX }}mm;top:{{ $imageY }}mm;width:{{ $imageW }}mm;height:{{ $imageH }}mm;">
        </div>
        @if ($captionH)
            <div style="padding-top:{{ 4*$sx }}mm;font-size:{{ 9.5*$sx }}mm;font-style:italic;color:#475569;line-height:1.3;">{{ $f['caption'] }}</div>
        @endif
    @elseif (in_array($type, ['masthead', 'header']))
        @include('periodico.partials.masthead', ['masthead' => $f, 'mastheadScale' => $sx, 'mastheadUnit' => 'mm'])
    @elseif ($type === 'ad')
        <div style="height:{{ $h }}mm;background:#E8F0FE;color:#1e40af;text-align:center;">
            <div style="position:absolute;top:{{ max(0, $h / 2 - 3) }}mm;width:100%;line-height:6mm;font-family:Montserrat;font-size:{{ 13*$sx }}mm;">DISPONIBLE {{ $f['format_code'] ?? '' }}</div>
        </div>
    @elseif ($type === 'divider')
        <div style="height:100%;background:{{ $f['color'] ?? '#cbd5e1' }};"></div>
    @elseif ($type === 'article')
        @if (!empty($f['noticia_id']) || !empty($f['propiedades']['noticia_id']))
            <div style="height:{{ 6*$sx }}mm;margin-bottom:{{ 2*$sx }}mm;background:{{ $f['categoria_color'] ?? '#D71920' }};"></div>
        @endif
        <div style="font-size:{{ 17*$sx }}mm;font-weight:bold;line-height:1.15;color:#0f172a;padding:{{ 4*$sx }}mm;margin-bottom:{{ 4*$sx }}mm;{{ $pdfService->estiloTextoParaPdf($f['article_title_style'] ?? [], $sx) }}">{!! $scaledHtml($f['titular'] ?? $f['content_title'] ?? '') !!}</div>
        @if (!empty($f['subtitulo']))
            <div style="font-size:{{ 10.5*$sx }}mm;font-style:italic;font-weight:bold;color:#64748b;margin-bottom:{{ 4*$sx }}mm;">{!! $scaledHtml($f['subtitulo']) !!}</div>
        @endif
        @php $columns = $pdfService->columnasParaPdf($scaledHtml($f['cuerpo'] ?? $f['content'] ?? ''), (int)($f['columns'] ?? 3)); @endphp
        <table style="width:100%;table-layout:fixed;font-size:{{ 11*$sx }}mm;line-height:1.4;color:#1e293b;"><tr>@foreach ($columns as $column)<td style="width:{{ 100/count($columns) }}%;padding-right:{{ $loop->last ? 0 : 12*$sx }}mm;text-align:justify;">{!! $column !!}</td>@endforeach</tr></table>
        @if (!empty($f['autor']))<div style="font-size:{{ 9*$sx }}mm;font-weight:bold;margin-top:{{ 4*$sx }}mm;color:{{ $f['categoria_color'] ?? '#D71920' }};">Por: {{ $f['autor'] }}</div>@endif
    @elseif ($type === 'headline')
        @if (!empty($f['kicker']))<div style="font-size:{{ 11*$sx }}mm;font-weight:bold;color:#D71920;">{{ $f['kicker'] }}</div>@endif
        <div style="font-family:'Bebas Neue',DejaVu Sans,sans-serif;font-size:{{ 32*$sx }}mm;line-height:1.05;{{ $pdfService->estiloTextoParaPdf($f['text_style'] ?? [], $sx) }}">{!! $content !!}</div>
    @elseif ($type === 'qr')
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&amp;data={{ rawurlencode($f['url'] ?? 'https://latitud18.com/periodico') }}" style="width:{{ min($w,$h) }}mm;height:{{ min($w,$h) }}mm;">
        <div style="font-size:{{ 9*$sx }}mm;">{{ $f['label'] ?? '' }}</div>
    @elseif ($type === 'quote')
        <div style="height:100%;box-sizing:border-box;border-left:{{ 4*$sx }}mm solid #D71920;padding:{{ 8*$sx }}mm {{ 12*$sx }}mm;background:#f8fafc;font-style:italic;font-size:{{ 16*$sx }}mm;line-height:1.4;color:#0f172a;">{!! $content !!}</div>
    @elseif ($type === 'box')
        <div style="height:100%;box-sizing:border-box;border:{{ $sx }}mm solid #cbd5e1;padding:{{ 8*$sx }}mm {{ 10*$sx }}mm;background:#f1f5f9;">{!! $content !!}</div>
    @else
        <div style="padding:{{ !empty($f['editorial_role']) ? 0 : 4*$sx }}mm;{{ $pdfService->estiloTextoParaPdf($f['text_style'] ?? [], $sx) }}">
        @php $columns = $pdfService->columnasParaPdf($content, (int)($f['columns'] ?? 1)); @endphp
        @if (count($columns) > 1)
            <table style="width:100%;table-layout:fixed;"><tr>@foreach ($columns as $column)<td style="width:{{ 100/count($columns) }}%;padding-right:{{ $loop->last ? 0 : 14*$sx }}mm;">{!! $column !!}</td>@endforeach</tr></table>
        @else
            {!! $content !!}
        @endif
        </div>
    @endif
    </div>
@endforeach
</div>
