<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
date_default_timezone_set('America/Bogota');
error_reporting(0);
// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the enviroments
$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

// Instantiate the app
$settings = require __DIR__ . '/../app/settings.php';
$app = new \Slim\App($settings);

// Register utilities
require __DIR__ . '/../app/utilities.php';

// Register dependencies
require __DIR__ . '/../app/dependencies.php';

// Register middleware
require __DIR__ . '/../app/middleware.php';

// Register routes
require __DIR__ . '/../app/routes.php';

// Run
$app->run();
