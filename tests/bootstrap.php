<?php
// tests/bootstrap.php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$basePath = dirname(__DIR__);

foreach (['.env.test', '.env.staging', '.env'] as $file) {
    if (is_file($basePath . '/' . $file)) {
        Dotenv\Dotenv::createImmutable($basePath, $file)->load();
        break;
    }
}
