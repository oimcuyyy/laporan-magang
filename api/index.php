<?php

// Pastikan direktori sementara /tmp tersedia untuk Laravel cache & views di serverless Vercel
$tempDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($tempDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Redirect view compiled path ke /tmp
if (! isset($_ENV['VIEW_COMPILED_PATH'])) {
    $_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
    putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
}

// Teruskan ke entry point standar public/index.php
require __DIR__.'/../public/index.php';
