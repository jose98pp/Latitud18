@php
    $sx = 280 / max(1, (float) ($p['ancho'] ?? 720));
    $sy = 430 / max(1, (float) ($p['alto'] ?? 1106));
@endphp
<div style="position:relative;width:280mm;height:429.8mm;overflow:hidden;background:#fff;">
@foreach (collect($p['frames'])->sortBy('z') as $f)
    @php
        $x = max(0, (float)($f['x'] ?? 0)) * $sx;
        $y = max(0, (float)($f['y'] ?? 0)) * $sy;
        $w = min((float)($f['w'] ?? 0)*$sx, 280-$x);
        $h = min((float)($f['h'] ?? 0)*$sy, 430-$y);
        $type = $f['type'] ?? 'text';
        $content = $npSafe($f['content'] ?? '');
        // Convertir los tamaños de tipografía del lienzo a la escala física.
        $content = preg_replace_callback('/([0-9]+(?:\.[0-9]+)?)px\b/', fn($m) => ((float)$m[1]*$sx).'mm', $content);
        $src = $type === 'ad' ? ($f['advertiser_image_url'] ?? '') : ($f['src'] ?? '');
        $local = null;
        if ($src) {
            $path = parse_url($src, PHP_URL_PATH);
            $candidate = $path ? realpath(public_path(ltrim($path, '/'))) : false;
            $root = realpath(public_path());
            if ($candidate && $root && str_starts_with($candidate, $root.DIRECTORY_SEPARATOR) && is_file($candidate)) $local = $candidate;
        }
        $imageW = $w; $imageH = $h; $imageX = 0; $imageY = 0;
        $dim = $local ? @getimagesize($local) : false;
        if (!$dim && $local && strtolower(pathinfo($local, PATHINFO_EXTENSION)) === 'svg') {
            // getimagesize no reconoce SVG; usar su retícula para conservar proporciones.
            if (preg_match('/viewBox="[0-9.]+\s+[0-9.]+\s+([0-9.]+)\s+([0-9.]+)"/', file_get_contents($local), $viewBox)) {
                $dim = [(float) $viewBox[1], (float) $viewBox[2]];
            }
        }
        if ($dim && $dim[0]>0 && $dim[1]>0) {
            // Fotos: recorte centrado. Anuncios: completos, sin deformación.
            $fit = $type === 'ad' ? 'contain' : ($f['image_fit'] ?? 'cover');
            $scale = $fit === 'contain' ? min($w/$dim[0], $h/$dim[1]) : max($w/$dim[0], $h/$dim[1]);
            $imageW=$dim[0]*$scale; $imageH=$dim[1]*$scale;
            $imageX=($w-$imageW)/2; $imageY=($h-$imageH)/2;
        }
    @endphp
    <div style="position:absolute;left:{{ $x }}mm;top:{{ $y }}mm;width:{{ $w }}mm;height:{{ $h }}mm;overflow:hidden;box-sizing:border-box;">
    @if ($type === 'image' || ($type === 'ad' && ($f['status'] ?? '') === 'ocupado' && $src))
        <img src="{{ $local ?? $src }}" style="position:absolute;left:{{ $imageX }}mm;top:{{ $imageY }}mm;width:{{ $imageW }}mm;height:{{ $imageH }}mm;">
    @elseif (in_array($type, ['masthead', 'header']))
        @include('periodico.partials.masthead', ['masthead' => $f, 'mastheadScale' => $sx, 'mastheadUnit' => 'mm'])
    @elseif ($type === 'ad')
        <div style="height:{{ $h }}mm;background:#0B1F3A;color:#ffffff;text-align:center;">
            <div style="position:absolute;top:{{ max(0, $h / 2 - 3) }}mm;width:100%;line-height:6mm;font-family:Montserrat;font-size:10pt;">PUBLICIDAD {{ $f['format_code'] ?? '' }}</div>
        </div>
    @elseif ($type === 'divider')
        <div style="height:100%;background:{{ $f['color'] ?? '#D71920' }};"></div>
    @elseif ($type === 'article')
        <h2 style="font: bold 18pt DejaVu Sans;">{{ $f['titular'] ?? '' }}</h2>
        <p style="font:10pt DejaVu Sans;">{{ $f['subtitulo'] ?? '' }}</p>
        <p style="font:10pt DejaVu Sans;">{{ $f['cuerpo'] ?? '' }}</p>
    @else
        {!! $content !!}
    @endif
    </div>
@endforeach
</div>
