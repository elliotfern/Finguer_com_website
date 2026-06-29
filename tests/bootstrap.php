<?php
// tests/bootstrap.php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$basePath = __DIR__ . '/..';
if (file_exists($basePath . '/.env')) {
    Dotenv\Dotenv::createImmutable($basePath)->load();
}
