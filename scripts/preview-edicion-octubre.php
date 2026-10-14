<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$count = (int) ($argv[1] ?? 12);
if (!in_array($count, [12, 28], true)) throw new InvalidArgumentException('Usar 12 o 28 páginas.');
$pages = [];
foreach (glob(database_path("data/latitud18-octubre/{$count}-*.latitud-template")) as $index => $path) {
    $t = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $pages[] = [
        'numero' => $index + 1, 'seccion' => $t['configuracion']['section_label'],
        'ancho' => 720, 'alto' => 1106, 'configuracion' => $t['configuracion'], 'frames' => $t['frames'],
    ];
}
if (count($pages) !== $count) throw new RuntimeException('Número incorrecto de páginas en el catálogo.');
$output = $argv[2] ?? storage_path("app/latitud18-octubre-{$count}.pdf");
file_put_contents($output, app(App\Services\PeriodicoPdfService::class)->contenido([
    'id' => "octubre-{$count}-preview", 'titulo' => "Latitud18 - Clásica Octubre - {$count} páginas", 'paginas' => $pages,
]));
echo $output.PHP_EOL;
