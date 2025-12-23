<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$dir = storage_path('app/public/top4gamers_images');
echo "Directory: $dir\n";
echo "Exists: " . (is_dir($dir) ? 'YES' : 'NO') . "\n\n";

if (is_dir($dir)) {
    $files = scandir($dir);
    echo "Files found:\n";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
}

