<?php
// tests/bootstrap.php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$basePath = __DIR__ . '/..';

$envFile = file_exists($basePath . '/.env.staging') ? '.env.staging' : '.env';
Dotenv\Dotenv::createImmutable($basePath, $envFile)->load();
