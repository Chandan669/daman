<?php
declare(strict_types=1);
session_start();
$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['timezone']);
require __DIR__ . '/helpers.php';
if (!defined('GHOST_ROOT')) define('GHOST_ROOT', dirname(__DIR__));
$installed = is_file(GHOST_ROOT . '/storage/.installed');
if (!$installed && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/installer')) {
    header('Location: /installer/'); exit;
}
