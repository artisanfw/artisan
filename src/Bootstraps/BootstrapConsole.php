<?php

use Api\Services\ServiceContainer;
use Artisan\Routing\Entities\Config;

error_reporting(E_ALL);
date_default_timezone_set('UTC');
define('PROJECT_DIR', realpath(__DIR__ . '/../../'));

require_once PROJECT_DIR . '/vendor/autoload.php';
$confpath = PROJECT_DIR . '/.config.php';

try {
    Config::load($confpath);
} catch(\Exception $e) {
    die("Error loading config file: {$confpath}" . PHP_EOL . $e->getMessage() . PHP_EOL);
}

try {
    ServiceContainer::initialize();
} catch(\Exception $e) {
    die("Error initializing Services: {$e->getMessage()}".PHP_EOL);
}
