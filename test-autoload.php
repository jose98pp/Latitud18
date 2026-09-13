<?php
echo "Testing autoload...\n";
require __DIR__.'/vendor/autoload.php';
echo "Autoload loaded successfully!\n";
$app = require_once __DIR__.'/bootstrap/app.php';
echo "App booted successfully!\n";
