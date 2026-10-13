<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pages = [];
foreach (glob(database_path('data/latitud18-v2/*.latitud-template')) as $index => $path) {
    $template = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $pages[] = [
        'numero' => $index + 1, 'seccion' => $template['category'],
        'ancho' => $template['configuracion']['page_width_px'],
        'alto' => $template['configuracion']['page_height_px'],
        'configuracion' => $template['configuracion'], 'frames' => $template['frames'],
    ];
}
if (count($pages) !== 12) throw new RuntimeException('La vista previa debe tener 12 páginas.');
$output = $argv[1] ?? storage_path('app/latitud18-editorial-v2-preview.pdf');
file_put_contents($output, app(App\Services\PeriodicoPdfService::class)->contenido([
    'id' => 'editorial-v2-preview', 'titulo' => 'Latitud18 - Vista previa editorial v2', 'paginas' => $pages,
]));
echo "Vista previa generada: ".$output.PHP_EOL;
