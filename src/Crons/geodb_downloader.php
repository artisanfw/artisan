#!/usr/bin/env php
<?php

use Artisan\Downloader\GeoLite2Downloader;
use Artisan\Routing\Entities\Config;

try {
    define('PROJECT_DIR', realpath(__DIR__ . '/../../'));
    require_once PROJECT_DIR . '/vendor/autoload.php';
    Config::load(PROJECT_DIR . '/.config.php');

    $downloader = new GeoLite2Downloader(Config::get('geoip'));

    $downloader->download();
    echo "✔ Database downloaded successfully.\n";
} catch (Throwable $e) {
    echo "✖ Error: " . $e->getMessage() . "\n";
}
