@php
    $mf = $masthead;
    $ms = $mastheadScale ?? 1;
    $mu = $mastheadUnit ?? 'px';
    $nameColor = preg_match('/^#[0-9a-f]{6}$/i', $mf['nameColor'] ?? '') ? $mf['nameColor'] : '#0B1F3A';
    $accentColor = preg_match('/^#[0-9a-f]{6}$/i', $mf['accentColor'] ?? '') ? $mf['accentColor'] : '#D71920';
    $nameSize = max(16, min(80, (float)($mf['nameSize'] ?? 44)));
    $logoWidth = max(30, min(200, (float)($mf['logoWidth'] ?? 80)));
    $logo = $mf['logo_src'] ?? '';
    if (!preg_match('#^(https?://|/(?!/))#i', $logo)) $logo = '';
    if ($logo && $mu === 'mm') {
        $logoPath = parse_url($logo, PHP_URL_PATH);
        $localLogo = $logoPath ? realpath(public_path(ltrim($logoPath, '/'))) : false;
        $publicRoot = realpath(public_path());
        if ($localLogo && $publicRoot && str_starts_with($localLogo, $publicRoot.DIRECTORY_SEPARATOR)) $logo = $localLogo;
    }
@endphp
<div style="width:100%;overflow:hidden;border-bottom:{{ 3*$ms }}{{ $mu }} solid {{ $accentColor }};font-family:Montserrat,DejaVu Sans,sans-serif;color:{{ $nameColor }};">
    <table style="width:100%;font-size:{{ 10*$ms }}{{ $mu }};border-collapse:collapse;"><tr><td>{{ $mf['leftEar'] ?? '' }}</td><td style="text-align:right;">{{ $mf['rightEar'] ?? '' }}</td></tr></table>
    <div style="font-size:{{ $nameSize*$ms }}{{ $mu }};font-weight:bold;line-height:1.2;white-space:nowrap;">
        @if ($logo)<img src="{{ $logo }}" alt="Logotipo" style="width:{{ $logoWidth*$ms }}{{ $mu }};max-height:{{ 65*$ms }}{{ $mu }};vertical-align:middle;">@endif
        {{ $mf['newspaperName'] ?? 'LATITUD' }} <span style="background:{{ $accentColor }};color:#fff;padding:0 {{ 6*$ms }}{{ $mu }};">{{ $mf['subBadge'] ?? '18' }}</span>
    </div>
    <div style="font-size:{{ 11*$ms }}{{ $mu }};line-height:1.3;">{{ $mf['motto'] ?? 'INFORMACIÓN SIN RUIDO' }}</div>
    <div style="font-size:{{ 10*$ms }}{{ $mu }};margin-top:{{ 5*$ms }}{{ $mu }};border-top:1px solid #cbd5e1;padding-top:{{ 3*$ms }}{{ $mu }};">
        {{ $mf['numero_edicion'] ?? $mf['editionNumber'] ?? $edicion['numero_edicion'] ?? '' }} · {{ $mf['fecha'] ?? $mf['editionDate'] ?? $edicion['fecha'] ?? '' }} · {{ $mf['ciudad'] ?? $mf['city'] ?? $edicion['ciudad'] ?? 'Santa Cruz de la Sierra' }} · {{ $mf['price'] ?? $edicion['precio'] ?? 'Bs 7,00' }}
    </div>
    <div style="font-size:{{ 9*$ms }}{{ $mu }};margin-top:{{ 3*$ms }}{{ $mu }};">{{ $mf['website'] ?? 'latitud18.com' }}</div>
</div>
