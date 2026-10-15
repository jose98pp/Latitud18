<?php

// Generador de Logo.jpg y favicon.ico para Latitud 18
$logoPath = __DIR__ . '/../public/images/Logo.jpg';
$icoPath  = __DIR__ . '/../public/favicon.ico';

// 1. GENERAR LOGO.JPG (512x512)
$size = 512;
$img = imagecreatetruecolor($size, $size);
imageantialias($img, true);

// Colores
$bgNavy   = imagecolorallocate($img, 11, 31, 58);      // #0B1F3A
$navyDark = imagecolorallocate($img, 6, 17, 33);       // #061121
$red      = imagecolorallocate($img, 215, 25, 32);     // #D71920
$white    = imagecolorallocate($img, 255, 255, 255);   // #FFFFFF
$slate    = imagecolorallocate($img, 203, 213, 225);   // #CBD5E1
$gold     = imagecolorallocate($img, 245, 158, 11);    // #F59E0B

// Fondo degradado sutil
for ($y = 0; $y < $size; $y++) {
    $r = (int)(11 + (6 - 11) * ($y / $size));
    $g = (int)(31 + (17 - 31) * ($y / $size));
    $b = (int)(58 + (33 - 58) * ($y / $size));
    $lineColor = imagecolorallocate($img, $r, $g, $b);
    imageline($img, 0, $y, $size, $y, $lineColor);
}

// Borde circular doble
imagesetthickness($img, 4);
imagearc($img, $size/2, $size/2, 480, 480, 0, 360, $red);
imagesetthickness($img, 2);
imagearc($img, $size/2, $size/2, 464, 464, 0, 360, $slate);

// Monograma L18 en el centro
// Badge para el "18"
imagefilledrectangle($img, 260, 160, 410, 290, $red);

// Usar fuentes integradas o rectángulos tipográficos
// Texto con imagestring o fuentes TTF si están disponibles
$fontFound = false;
$systemFonts = [
    'C:/Windows/Fonts/arialbd.ttf',
    'C:/Windows/Fonts/arial.ttf',
    'C:/Windows/Fonts/seguiemb.ttf',
    'C:/Windows/Fonts/segoeui.ttf',
];

$ttf = null;
foreach ($systemFonts as $sf) {
    if (file_exists($sf)) {
        $ttf = $sf;
        break;
    }
}

if ($ttf && function_exists('imagettftext')) {
    // Letra L gigante
    imagettftext($img, 100, 0, 110, 275, $white, $ttf, 'L');
    // Número 18 en badge
    imagettftext($img, 80, 0, 280, 265, $white, $ttf, '18');
    
    // Texto "LATITUD 18"
    $text = "LATITUD 18";
    $bbox = imagettfbbox(30, 0, $ttf, $text);
    $textW = $bbox[2] - $bbox[0];
    $x = ($size - $textW) / 2;
    imagettftext($img, 30, 0, $x, 365, $white, $ttf, $text);

    // Slogan "INFORMACIÓN SIN RUIDO"
    $slogan = "INFORMACIÓN SIN RUIDO";
    $bboxS = imagettfbbox(13, 0, $ttf, $slogan);
    $sloganW = $bboxS[2] - $bboxS[0];
    $xS = ($size - $sloganW) / 2;
    imagettftext($img, 13, 0, $xS, 405, $gold, $ttf, $slogan);
} else {
    // Fallback GD nativo
    imagestring($img, 5, 170, 200, "LATITUD 18", $white);
    imagestring($img, 3, 140, 230, "INFORMACION SIN RUIDO", $gold);
}

// Guardar Logo.jpg
imagejpeg($img, $logoPath, 95);
imagedestroy($img);
echo "Logo.jpg generado con exito en {$logoPath}\n";

// 2. GENERAR FAVICON.ICO (32x32)
$iconSize = 32;
$icoImg = imagecreatetruecolor($iconSize, $iconSize);
imageantialias($icoImg, true);

$icoNavy = imagecolorallocate($icoImg, 11, 31, 58);
$icoRed  = imagecolorallocate($icoImg, 215, 25, 32);
$icoWhite= imagecolorallocate($icoImg, 255, 255, 255);

// Fondo
imagefilledrectangle($icoImg, 0, 0, $iconSize, $iconSize, $icoNavy);
// Borde rojo
imagesetthickness($icoImg, 1);
imagerectangle($icoImg, 0, 0, $iconSize-1, $iconSize-1, $icoRed);

// Badge 18
imagefilledrectangle($icoImg, 16, 5, 29, 26, $icoRed);

if ($ttf && function_exists('imagettftext')) {
    imagettftext($icoImg, 16, 0, 3, 25, $icoWhite, $ttf, 'L');
    imagettftext($icoImg, 11, 0, 16, 22, $icoWhite, $ttf, '18');
} else {
    imagestring($icoImg, 4, 3, 8, "L", $icoWhite);
    imagestring($icoImg, 2, 16, 9, "18", $icoWhite);
}

// Convertir a formato ICO real (con cabecera ICO y payload PNG)
ob_start();
imagepng($icoImg);
$pngData = ob_get_clean();
imagedestroy($icoImg);

// Empaquetar como formato .ico (cabecera ICO de 6 bytes + 1 entrada de directorio de 16 bytes + payload PNG)
$icoHeader = pack('vvv', 0, 1, 1);
$icoDir = pack(
    'CCCCvvVV',
    $iconSize,          // Width
    $iconSize,          // Height
    0,                  // Color count
    0,                  // Reserved
    1,                  // Color planes
    32,                 // Bits per pixel
    strlen($pngData),   // Image size
    22                  // Offset (6 header + 16 dir = 22)
);

file_put_contents($icoPath, $icoHeader . $icoDir . $pngData);
echo "favicon.ico generado con exito en {$icoPath}\n";
