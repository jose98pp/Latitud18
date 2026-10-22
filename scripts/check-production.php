<?php
// Read-only checks; credentials and environment contents are never printed.
$failed = false;
$check = function (bool $ok, string $label) use (&$failed): void {
    echo ($ok ? '[OK] ' : '[FALLO] ').$label.PHP_EOL;
    $failed = $failed || !$ok;
};
$check(PHP_VERSION_ID >= 80200, 'PHP CLI >= 8.2 (actual: '.PHP_VERSION.')');
foreach (['ctype','curl','dom','fileinfo','filter','gd','hash','mbstring','openssl','pdo_mysql','session','tokenizer','xml','zip'] as $extension) {
    $check(extension_loaded($extension), 'Extensión '.$extension);
}
$check(class_exists('ZipArchive') && ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256), 'ZIP con cifrado AES-256 para respaldos');
$root = dirname(__DIR__);
$check(is_file($root.'/.env'), 'Configuración local presente');
foreach (['storage','bootstrap/cache'] as $directory) $check(is_writable($root.'/'.$directory), $directory.' escribible');
$check(is_file($root.'/public/build/manifest.json'), 'Assets compilados presentes');
echo 'Comprueba también PHP 8.2 o superior para el dominio en MultiPHP Manager; puede diferir de la Terminal.'.PHP_EOL;
exit($failed ? 1 : 0);
