<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Initial setup

define('ROOT', __DIR__);
define('MODE_DEV', '%MODE%' === 'development');

function require_existing(string $path) {
	file_exists($path) && require_once($path);
}

require_existing('vendor/autoload.php');
require_existing('configs/env.php');
require_existing('system/lib/database.php');
require_existing('system/Controllers/Settings.php');
require_existing('system/helpers.php');

try {
	require_existing('configs/routes.php');
} catch (\Throwable $th) {
	die('Error: ' . $th->getMessage());
}
